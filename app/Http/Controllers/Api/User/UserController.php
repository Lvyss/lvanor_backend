<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
// ✅ Ambil profil login
public function profile(Request $request)
{
    $user = $request->user()->load('detail');

    return response()->json([
        'message' => 'Profil berhasil diambil.',
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'detail' => $user->detail,
        ]
    ]);
}

// ✅ Ambil profil publik (by id)
public function publicProfile($id)
{
    $user = User::with('detail')->select('id','name','email','role')->findOrFail($id);

    return response()->json([
        'message' => 'Profil publik berhasil diambil.',
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'detail' => $user->detail,
        ]
    ]);
}




    // ✅ Update Profil User
public function updateProfile(Request $request)
{
    $user = $request->user();
    $detail = $user->detail ?? $user->detail()->create(); // pastikan record detail ada

    $validated = $request->validate([
        'profile_picture' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:10048',
        'banner_image'    => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:10048',
        'full_name' => 'sometimes|nullable|string|max:255',
        'username' => [
            'sometimes',
            'nullable',
            'string',
            'max:100',
            Rule::unique('user_details', 'username')->ignore($detail->id),
        ],
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
            if ($detail->profile_public_id) {
                $this->cloudinary->destroy($detail->profile_public_id);
            }

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

    // 🔸 Upload banner image jika ada
    if ($request->hasFile('banner_image')) {
        try {
            if ($detail->banner_public_id) {
                $this->cloudinary->destroy($detail->banner_public_id);
            }

            $upload = $this->cloudinary->upload($request->file('banner_image'), 'banner_images');

            $detail->banner_image = $upload['secure_url'];
            $detail->banner_public_id = $upload['public_id'];
        } catch (\Exception $e) {
            Log::error('Gagal upload banner user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return response()->json(['message' => 'Gagal upload banner.'], 500);
        }
    }

    // 🔸 Update field selain foto/banner
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
