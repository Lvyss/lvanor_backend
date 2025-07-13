<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weblist', function (Blueprint $table) {
            $table->id();
             $table->foreignId('category_id')->constrained('category')->onDelete('cascade');
             $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->string('title');
            $table->string('image_path'); // URL gambar Cloudinary
            $table->string('public_id')->nullable(); // ID unik Cloudinary, untuk delete

            // Relasi ke category
           

            // ✅ Relasi ke admins (bukan users lagi)
            

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weblist');
    }
};
