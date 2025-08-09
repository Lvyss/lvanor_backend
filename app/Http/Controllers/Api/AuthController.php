<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ✅ Login via Google (cek di User & Admin)
    public function loginWithProvider(Request $request)
    {
        // 🔐 Validasi input
        $validator = Validator::make($request->all(), [
            'provider'     => 'required|in:google',
            'provider_id'  => 'required|string',
            'name'         => 'required|string',
            'email'        => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // 🔎 Cek user
        $user = User::where('provider', $data['provider'])
                    ->where('provider_id', $data['provider_id'])
                    ->first();

        if (!$user && isset($data['email'])) {
            $user = User::where('email', $data['email'])->first();
        }

        if ($user) {
            $user->update([
                'provider'     => $data['provider'],
                'provider_id'  => $data['provider_id'],
            ]);

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login user berhasil',
                'token'   => $token,
                'type'    => 'user',
                'user'    => $user,
            ]);
        }

        // 🔎 Cek admin
        $admin = Admin::where('provider', $data['provider'])
                      ->where('provider_id', $data['provider_id'])
                      ->first();

        if (!$admin && isset($data['email'])) {
            $admin = Admin::where('email', $data['email'])->first();
        }

        if ($admin) {
            $admin->update([
                'provider'     => $data['provider'],
                'provider_id'  => $data['provider_id'],
            ]);

            $admin->tokens()->delete();
            $token = $admin->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login admin berhasil',
                'token'   => $token,
                'type'    => 'admin',
                'user'    => $admin,
            ]);
        }

        // 👶 Jika user belum ada, buat baru
        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'] ?? null,
            'provider'     => $data['provider'],
            'provider_id'  => $data['provider_id'],
            'role'         => 'user',
            'password'     => Hash::make(uniqid()),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User baru dibuat & login berhasil',
            'token'   => $token,
            'type'    => 'user',
            'user'    => $user,
        ]);
    }

    // ✅ Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
