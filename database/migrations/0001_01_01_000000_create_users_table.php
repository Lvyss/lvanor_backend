<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Nama user
            $table->string('email')->unique();               // Email user
            $table->string('password');                      // Password user
            $table->string('role')->default('user');         // Role user: admin, user, etc.
            $table->string('profile_picture')->nullable();   // Foto profil user (Cloudinary URL)
            $table->string('profile_public_id')->nullable(); // ID unik gambar Cloudinary
            $table->text('bio')->nullable();                 // (Opsional) Deskripsi diri
            $table->string('phone')->nullable();             // (Opsional) Nomor telepon
            $table->string('address')->nullable();           // (Opsional) Alamat
            $table->timestamp('email_verified_at')->nullable(); // Verifikasi email
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
