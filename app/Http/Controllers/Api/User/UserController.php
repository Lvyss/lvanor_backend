<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
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


    // ✅ Ambil Semua User
    public function getAllUsers()
    {
        $users = User::with('detail')->select('id', 'name', 'email', 'role', 'created_at')->get();

        return response()->json([
            'message' => 'Data user berhasil diambil.',
            'data' => $users
        ]);
    }

    // ✅ Hapus User
    public function deleteUser($id)
    {
        $user = User::with('detail')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Hapus gambar profil dari cloudinary jika ada
        if ($user->detail && $user->detail->profile_public_id) {
            try {
                $this->cloudinary->destroy($user->detail->profile_public_id);
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
    // Ambil user dan relasi detail
    $user = $request->user()->load('detail');

    return response()->json([
        'message' => 'Profil berhasil diambil.',
        'data' => $user // Sudah termasuk role, email, name, dll
    ]);
}

// ✅ Endpoint profil publik berdasarkan username
// app/Http/Controllers/UserController.php

public function publicProfile($id)
{
    $user = User::with('detail')->findOrFail($id);
    return response()->json($user);
}



    // ✅ Update Profil User
public function updateProfile(Request $request)
{
    $user = $request->user();
    $detail = $user->detail ?? $user->detail()->create(); // pastikan record detail ada

    $validated = $request->validate([
        'profile_picture' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:10048',
        'full_name' => 'sometimes|nullable|string|max:255',
        'username' => 'sometimes|nullable|string|max:100',
        'bio' => 'sometimes|nullable|string|max:1000',
        'location' => 'sometimes|nullable|string|max:255',
        'email' => 'sometimes|nullable|email|max:255',
        'linkedin' => 'sometimes|nullable|url|max:255',
        'github' => 'sometimes|nullable|url|max:255',
        'website' => 'sometimes|nullable|url|max:255',
        'tiktok' => 'sometimes|nullable|url|max:255',
        'instagram' => 'sometimes|nullable|url|max:255',
        'spline' => 'sometimes|nullable|url|max:255',
    ]);

    // 🔸 Upload profile picture jika ada
    if ($request->hasFile('profile_picture')) {
        try {
            // Hapus gambar lama jika ada
            if ($detail->profile_public_id) {
                $response = $this->cloudinary->destroy($detail->profile_public_id);

                if (!isset($response['result']) || $response['result'] !== 'ok') {
                    Log::warning('Gagal hapus foto profil lama di Cloudinary', [
                        'user_id' => $user->id,
                        'public_id' => $detail->profile_public_id,
                        'response' => $response
                    ]);
                }
            }

            // Upload yang baru
            $upload = $this->cloudinary->upload($request->file('profile_picture'), 'profile_pictures');

            $detail->profile_picture = $upload['secure_url'];
            $detail->profile_public_id = $upload['public_id'];
        } catch (\Exception $e) {
            Log::error('Gagal upload gambar profil user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json(['message' => 'Gagal upload gambar profil.'], 500);
        }
    }

    // 🔸 Update field selain foto
    $fields = [
        'full_name',
        'username',
        'bio',
        'location',
        'email',
        'linkedin',
        'github',
        'website',
        'tiktok',
        'instagram',
        'spline',
    ];

    foreach ($fields as $field) {
        if ($request->has($field)) {
            $detail->$field = $request->$field;
        }
    }

    $detail->save();

    return response()->json([
        'message' => 'Profil berhasil diperbarui.',
        'data' => $detail,
    ]);
}

}
