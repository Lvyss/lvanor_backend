<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CloudinaryService;

class AdminController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    // 👤 Ambil Profil Admin Login
    public function profile()
    {
        return response()->json([
            'message' => 'Data profil berhasil diambil.',
            'data' => auth()->user(),
        ]);
    }

    // ✏️ Update Profil Admin
public function updateProfile(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'Admin tidak ditemukan.'], 404);
    }

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'bio' => 'sometimes|nullable|string|max:1000',
        'phone' => 'sometimes|nullable|string|max:20',
        'address' => 'sometimes|nullable|string|max:255',
        'profile_picture' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:10048',
    ]);

    // 🔸 Upload gambar jika ada
    if ($request->hasFile('profile_picture')) {
        try {
            // Hapus gambar lama dari Cloudinary jika ada
            if ($user->profile_public_id) {
                $response = $this->cloudinary->destroy($user->profile_public_id);

                if (!isset($response['result']) || $response['result'] !== 'ok') {
                    Log::warning('Gagal hapus foto profil lama admin di Cloudinary', [
                        'admin_id' => $user->id,
                        'public_id' => $user->profile_public_id,
                        'response' => $response
                    ]);
                }
            }

            // Upload gambar baru
            $upload = $this->cloudinary->upload($request->file('profile_picture'), 'profile_pictures');

            $user->profile_picture = $upload['secure_url'];
            $user->profile_public_id = $upload['public_id'];
        } catch (\Exception $e) {
            Log::error('Gagal upload gambar profil admin', [
                'error' => $e->getMessage(),
                'admin_id' => $user->id
            ]);

            return response()->json(['message' => 'Gagal upload gambar profil.'], 500);
        }
    }

    // 🔸 Update hanya field yang dikirim
    foreach (['name', 'bio', 'phone', 'address'] as $field) {
        if ($request->has($field)) {
            $user->$field = $request->$field;
        }
    }

    $user->save();

    return response()->json([
        'message' => 'Profil berhasil diperbarui.',
        'data' => $user
    ]);
}


}
