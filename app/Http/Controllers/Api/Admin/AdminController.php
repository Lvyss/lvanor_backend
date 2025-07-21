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
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Admin tidak ditemukan.'], 404);
        }

        // Handle upload gambar profil jika ada
        if ($request->hasFile('profile_picture')) {
            try {
                // Hapus gambar lama dari Cloudinary jika ada
                if ($user->profile_public_id) {
                    $response = $this->cloudinary->destroy($user->profile_public_id);

                    if (!isset($response['result']) || $response['result'] !== 'ok') {
                        Log::warning('Gagal hapus gambar profil lama dari Cloudinary', [
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
                    'admin_id' => $user->id,
                ]);
                return response()->json(['message' => 'Gagal upload gambar profil.'], 500);
            }
        }

        // Update data teks profil
        $user->update([
            'name' => $validated['name'],
            'bio' => $validated['bio'] ?? $user->bio,
            'phone' => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'] ?? $user->address,
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ]);

    } catch (\Exception $e) {
        Log::error('Gagal update profil admin', [
            'error' => $e->getMessage(),
            'admin_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Terjadi kesalahan saat memperbarui profil.'
        ], 500);
    }
}

}
