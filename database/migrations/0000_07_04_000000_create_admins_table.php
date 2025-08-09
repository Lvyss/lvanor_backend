<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable(); // nullable karena bisa pakai login Google
            $table->enum('role', ['admin'])->default('admin');

            $table->string('provider')->nullable();     // google
            $table->string('provider_id')->nullable();  // id dari Google

            $table->rememberToken();
            $table->timestamps();

            $table->index(['provider', 'provider_id']);
            $table->index('role');
        });
    }

    public function down(): void {
        Schema::dropIfExists('admins');
    }
};
