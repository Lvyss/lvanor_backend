<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([
            'name' => 'Admin Eka',
            'email' => 'ekanandasusila9c@gmail.com',
            'password' => Hash::make(Str::random(32)), // tetap isi password random (jaga2)
            'role' => 'admin',
            'provider' => 'google',
            'provider_id' => 'google-oauth-id-1234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
