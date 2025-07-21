<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Weblist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Services\CloudinaryService;

class UserWeblistController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function index()
    {
        $weblists = Weblist::with(['category', 'weblistDetail', 'weblistImages'])
            ->where('user_id', auth()->id())
            ->get();

        return response()->json([
            'message' => 'Weblist kamu berhasil diambil.',
            'data' => $weblists
        ]);
    }

    public function show($id)
    {
        $weblist = Weblist::with(['category', 'weblistDetail', 'weblistImages'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$weblist) {
            return response()->json([
                'message' => 'Data tidak ditemukan atau bukan milik kamu.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail Weblist berhasil diambil.',
            'data' => $weblist
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => ['required', Rule::exists('category', 'id')],
        ]);

        DB::beginTransaction();

        try {
            $upload = $this->cloudinary->upload($request->file('image'), 'weblist_thumbnails');

            $weblist = Weblist::create([
                'title' => $validated['title'],
                'image_path' => $upload['secure_url'],
                'public_id' => $upload['public_id'],
                'category_id' => $validated['category_id'],
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Weblist berhasil dibuat.',
                'data' => $weblist->load(['category'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal membuat Weblist', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Gagal tambah data.',
                'error' => 'Terjadi kesalahan internal.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $weblist = Weblist::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => ['required', Rule::exists('category', 'id')],
        ]);

        DB::beginTransaction();

        try {
            $weblist->update([
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
            ]);

            if ($request->hasFile('image')) {
                $this->replaceThumbnail($weblist, $request->file('image'));
            }

            DB::commit();

            return response()->json([
                'message' => 'Weblist berhasil diupdate.',
                'data' => $weblist->load(['category'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal update Weblist', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'weblist_id' => $id,
            ]);

            return response()->json([
                'message' => 'Gagal update data.',
                'error' => 'Terjadi kesalahan internal.'
            ], 500);
        }
    }

public function destroy($id)
{
    $weblist = Weblist::with('weblistImages')
        ->where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    DB::beginTransaction();

    try {
        // Hapus thumbnail utama
        if ($weblist->public_id) {
            $result = $this->cloudinary->destroy($weblist->public_id);

            if (!isset($result['result']) || $result['result'] !== 'ok') {
                Log::warning('Gagal hapus thumbnail Cloudinary', [
                    'user_id' => auth()->id(),
                    'public_id' => $weblist->public_id,
                    'cloudinary_response' => $result,
                ]);
            }
        }

        // Hapus semua gambar lain
        foreach ($weblist->weblistImages as $image) {
            if ($image->public_id) {
                $result = $this->cloudinary->destroy($image->public_id);

                if (!isset($result['result']) || $result['result'] !== 'ok') {
                    Log::warning('Gagal hapus image Cloudinary', [
                        'user_id' => auth()->id(),
                        'image_id' => $image->id,
                        'public_id' => $image->public_id,
                        'cloudinary_response' => $result,
                    ]);
                }
            }

            $image->delete();
        }

        $weblist->delete();

        DB::commit();

        return response()->json([
            'message' => 'Weblist berhasil dihapus.'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Gagal hapus Weblist', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
            'weblist_id' => $id
        ]);

        return response()->json([
            'message' => 'Gagal hapus data.',
            'error' => 'Terjadi kesalahan internal.'
        ], 500);
    }
}



    private function replaceThumbnail($weblist, $image)
    {
        if ($weblist->public_id) {
            $this->cloudinary->destroy($weblist->public_id);
        }

        $upload = $this->cloudinary->upload($image, 'weblist_thumbnails');

        $weblist->update([
            'image_path' => $upload['secure_url'],
            'public_id' => $upload['public_id']
        ]);
    }
}
