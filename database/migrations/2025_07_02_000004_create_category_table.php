<?php

// database/migrations/xxxx_xx_xx_create_category_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama kategori
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category');
    }
};
