<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeblistImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CloudinaryService;

class AdminWeblistImageController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

public function destroy($imageId)
{
    $image = WeblistImage::findOrFail($imageId);

    DB::beginTransaction();

    try {
        // Hapus dari Cloudinary
        if ($image->public_id) {
            $response = $this->cloudinary->destroy($image->public_id);

            if (!isset($response['result']) || $response['result'] !== 'ok') {
                Log::warning('Gagal hapus Cloudinary (admin)', [
                    'admin_id' => auth()->id(),
                    'image_id' => $image->id,
                    'public_id' => $image->public_id,
                    'response' => $response,
                ]);
            }
        }

        // Hapus dari DB
        $image->delete();
        DB::commit();

        Log::info('Admin menghapus gambar carousel', [
            'admin_id' => auth()->id(),
            'image_id' => $imageId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gambar carousel berhasil dihapus.',
            'deleted_image_id' => $imageId
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Gagal hapus gambar carousel (admin)', [
            'error' => $e->getMessage(),
            'admin_id' => auth()->id(),
            'image_id' => $imageId
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menghapus gambar.'
        ], 500);
    }
}

}
