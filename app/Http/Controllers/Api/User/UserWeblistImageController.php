<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Weblist;
use App\Models\WeblistImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CloudinaryService;

class UserWeblistImageController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function store(Request $request, $id)
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
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload carousel gagal', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'weblist_id' => $id
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat upload.'
            ], 500);
        }
    }

    public function destroy($imageId)
    {
        $image = WeblistImage::findOrFail($imageId);

        if ($image->weblist->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        DB::beginTransaction();

        try {
            if ($image->public_id) {
                $this->cloudinary->destroy($image->public_id);
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
                'image_id' => $image->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus gambar.'
            ], 500);
        }
    }
}
