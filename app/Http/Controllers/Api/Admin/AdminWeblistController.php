<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Weblist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminWeblistController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    // ✅ GET all Weblist (dengan relasi lengkap)
    public function index()
    {
        $weblists = Weblist::with(['user', 'category', 'weblistDetail', 'weblistImages'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Data weblist berhasil diambil.',
            'data' => $weblists
        ]);
    }

    // ✅ SHOW Weblist by ID
    public function show($id)
    {
        try {
            $weblist = Weblist::with(['user', 'category', 'weblistDetail', 'weblistImages'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail weblist ditemukan.',
                'data' => $weblist
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Gagal ambil detail weblist: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Weblist tidak ditemukan.'
            ], 404);
        }
    }

    // ✅ Admin tidak boleh membuat Weblist
    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak. Admin tidak bisa membuat Weblist.'
        ], 403);
    }

    // ✅ Admin tidak boleh update Weblist
    public function update(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak. Admin tidak bisa mengedit Weblist.'
        ], 403);
    }

    // ✅ DESTROY Weblist + hapus gambar Cloudinary + rollback jika gagal
public function destroy($id)
{
    DB::beginTransaction();

    try {
        $weblist = Weblist::with('weblistImages')->findOrFail($id);

        // Hapus thumbnail dari Cloudinary
        if ($weblist->public_id) {
            $response = $this->cloudinary->destroy($weblist->public_id);

            if (!isset($response['result']) || $response['result'] !== 'ok') {
                Log::warning('Gagal hapus thumbnail dari Cloudinary', [
                    'weblist_id' => $weblist->id,
                    'public_id' => $weblist->public_id,
                    'response' => $response,
                    'context' => 'thumbnail'
                ]);
            }
        }

        // Hapus carousel images dari Cloudinary
        foreach ($weblist->weblistImages as $image) {
            if ($image->public_id) {
                $response = $this->cloudinary->destroy($image->public_id);

                if (!isset($response['result']) || $response['result'] !== 'ok') {
                    Log::warning('Gagal hapus carousel dari Cloudinary', [
                        'image_id' => $image->id,
                        'public_id' => $image->public_id,
                        'response' => $response,
                        'context' => 'carousel'
                    ]);
                }
            }
            $image->delete();
        }

        $weblist->delete();
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Weblist berhasil dihapus.',
            'data' => null
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Gagal menghapus Weblist', [
            'error' => $e->getMessage(),
            'weblist_id' => $id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus Weblist.'
        ], 500);
    }
}

}
