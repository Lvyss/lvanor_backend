<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 🔐 Users table (untuk auth & role)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password');

            $table->enum('role', ['user', 'admin'])->default('user');
            $table->string('provider')->nullable();      // google, github
            $table->string('provider_id')->nullable();   // ID dari provider

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['provider', 'provider_id']);
            $table->index('role');
        });

        // 👤 User Details table (profil lengkap)
Schema::create('user_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    
    // Sudah ada
    $table->string('profile_picture')->nullable();
    $table->string('profile_public_id')->nullable();

    // Tambahan
    $table->string('full_name')->nullable();
    $table->string('username')->nullable(); // jika tidak pakai dari users table
    $table->text('bio')->nullable();
    $table->string('location')->nullable();

    $table->string('email')->nullable(); // optional, bisa ditampilkan kabur
    $table->string('linkedin')->nullable();
    $table->string('github')->nullable();
    $table->string('website')->nullable();
    $table->string('tiktok')->nullable();
    $table->string('instagram')->nullable();
    $table->string('spline')->nullable();

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('users');
    }
};
