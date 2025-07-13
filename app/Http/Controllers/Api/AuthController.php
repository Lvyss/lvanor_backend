<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 🔑 Login Universal (Admin dan User)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Cek apakah email milik admin atau user
        $account = Admin::where('email', $request->email)->first();
        $role = 'admin';

        if (!$account) {
            $account = User::where('email', $request->email)->first();
            $role = 'user';
        }

        // Kalau akun tidak ditemukan atau password salah
        if (!$account || !Hash::check($request->password, $account->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Hapus semua token lama (supaya single device login)
        $account->tokens()->delete();

        // Buat token baru
        $token = $account->createToken('auth_token')->plainTextToken;

        // Tambahkan role di response
        $userData = $account->toArray();
        $userData['role'] = $role;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $userData,
        ]);
    }

    // 🔓 Logout Universal
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai (lebih Laravel way)
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
