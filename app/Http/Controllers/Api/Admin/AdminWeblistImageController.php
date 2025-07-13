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
                try {
                    $this->cloudinary->destroy($image->public_id);
                } catch (\Exception $e) {
                    Log::warning('Gagal hapus Cloudinary (admin)', [
                        'error' => $e->getMessage(),
                        'image_id' => $image->id,
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
