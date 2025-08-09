<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ✅ Weblist
        Schema::create('weblist', function (Blueprint $table) {
            $table->id();

            // Pastikan 'category' dan 'users' sudah ada di DB (migrasi lain)
            $table->foreignId('category_id')->constrained('category')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('title');
            $table->string('image_path');         // URL dari Cloudinary
            $table->string('public_id')->nullable(); // Untuk delete Cloudinary

            $table->timestamps();
        });

        // ✅ Weblist Detail
        Schema::create('weblist_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weblist_id')->constrained('weblist')->onDelete('cascade');

            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('tech_stack')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('website_link')->nullable();

            $table->timestamps();
        });

        // ✅ Weblist Images
        Schema::create('weblist_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weblist_id')->constrained('weblist')->onDelete('cascade');

            $table->string('image_path');
            $table->string('public_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weblist_images');
        Schema::dropIfExists('weblist_detail');
        Schema::dropIfExists('weblist');
    }
};
