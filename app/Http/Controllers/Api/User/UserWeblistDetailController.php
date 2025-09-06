<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Weblist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\WeblistImage;
use App\Services\CloudinaryService;
class UserWeblistDetailController extends Controller
{



     protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

public function updateDetail(Request $request, $id)
{
    $weblist = Weblist::where('user_id', auth()->id())->findOrFail($id);

    $validated = $request->validate([
        'description' => 'sometimes|string',
        'features' => 'sometimes|array|min:1',
        'features.*' => 'string|max:255',
        'tech_stack' => 'sometimes|string|max:255',
        'price' => 'nullable|numeric',
        'website_link' => 'nullable|url',
    ]);

    try {
        DB::transaction(function () use ($weblist, $validated) {
            $existingDetail = $weblist->weblistDetail;

            $data = [];

            // Isi field yang dikirim saja
            if (array_key_exists('description', $validated)) {
                $data['description'] = $validated['description'];
            }

            if (array_key_exists('features', $validated)) {
                $data['features'] = $validated['features']; // sudah array

            }

            if (array_key_exists('tech_stack', $validated)) {
                $data['tech_stack'] = $validated['tech_stack'];
            }

            if (array_key_exists('price', $validated)) {
                $data['price'] = $validated['price'];
            }

            if (array_key_exists('website_link', $validated)) {
                $data['website_link'] = $validated['website_link'];
            }

            if ($existingDetail) {
                $existingDetail->update($data);
            } else {
                $weblist->weblistDetail()->create($data);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail Weblist berhasil diperbarui.',
            'data' => $weblist->fresh('weblistDetail')->weblistDetail
        ]);

    } catch (\Exception $e) {
        Log::error('Gagal update detail Weblist oleh user', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
            'weblist_id' => $id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menyimpan detail.'
        ], 500);
    }
}
    public function storeImg(Request $request, $id)
    {
        $request->validate([
            'carousel_images' => 'required|array|min:1|max:5',
            'carousel_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $weblist = Weblist::where('user_id', auth()->id())->findOrFail($id);

        $existingCount = $weblist->weblistImages()->count();
        $incomingCount = count($request->file('carousel_images'));

        if ($existingCount + $incomingCount > 5) {
            return response()->json([
                'message' => 'Total maksimal 5 gambar per Weblist diperbolehkan.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $uploadedImages = [];

            foreach ($request->file('carousel_images') as $image) {
                $upload = $this->cloudinary->uploadWithRetry($image, 'weblist_carousel');

                // Jika respons bukan array atau tidak mengandung secure_url/public_id
                if (!is_array($upload) || !isset($upload['secure_url'], $upload['public_id'])) {
                    throw new \Exception('Upload Cloudinary tidak valid');
                }

                $img = $weblist->weblistImages()->create([
                    'image_path' => $upload['secure_url'],
                    'public_id' => $upload['public_id'],
                ]);

                $uploadedImages[] = $img;
            }

            DB::commit();

            return response()->json([
                'message' => 'Gambar berhasil diupload.',
                'data' => $uploadedImages
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload carousel gagal', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'weblist_id' => $id
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat upload.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyImg($id)
    {
        $image = WeblistImage::with('weblist')->findOrFail($id);

        if ($image->weblist->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        DB::beginTransaction();

        try {
            if ($image->public_id) {
                $response = $this->cloudinary->destroy($image->public_id);

                if (!isset($response['result']) || $response['result'] !== 'ok') {
                    Log::warning('Gagal hapus file Cloudinary', [
                        'user_id' => auth()->id(),
                        'image_id' => $image->id,
                        'public_id' => $image->public_id,
                        'cloudinary_response' => $response,
                    ]);
                }
            }

            $image->delete();

            DB::commit();

            return response()->json([
                'message' => 'Gambar carousel berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal hapus carousel', [
                'error' => $e->getMessage(),
                'image_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus gambar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
