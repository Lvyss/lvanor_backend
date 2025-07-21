<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\CloudinaryService;

class UserController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    // ✅ Register User
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User berhasil terdaftar.',
            'user' => $user->only(['id', 'name', 'email', 'created_at']),
            'token' => $token,
        ], 201);
    }

    // ✅ Ambil Semua User
    public function getAllUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();

        return response()->json([
            'message' => 'Data user berhasil diambil.',
            'data' => $users
        ]);
    }

    // ✅ Hapus User
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Hapus gambar profil jika ada
        if ($user->profile_public_id) {
            try {
                $this->cloudinary->destroy($user->profile_public_id);
            } catch (\Exception $e) {
                Log::warning('Gagal hapus foto profil user saat delete', [
                    'error' => $e->getMessage(),
                    'user_id' => $id
                ]);
            }
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    // ✅ Ambil Profil User yang Login
    public function profile(Request $request)
    {
        return response()->json([
            'message' => 'Profil berhasil diambil.',
            'data' => $request->user()
        ]);
    }

    // ✅ Update Profil User
public function updateProfile(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'bio' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    // Upload gambar baru jika ada
    if ($request->hasFile('profile_picture')) {
        try {
            // Hapus gambar lama dari Cloudinary
            if ($user->profile_public_id) {
                $response = $this->cloudinary->destroy($user->profile_public_id);

                if (!isset($response['result']) || $response['result'] !== 'ok') {
                    Log::warning('Gagal hapus foto profil lama di Cloudinary', [
                        'user_id' => $user->id,
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
            Log::error('Gagal upload profil user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
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
}

}
