<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('weblist_detail', function (Blueprint $table) {
        $table->id();
        $table->foreignId('weblist_id')->constrained('weblist')->onDelete('cascade');
        $table->text('description')->nullable();
        $table->json('features')->nullable(); // bisa juga text biasa kalau mau simple
        $table->string('tech_stack')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->string('website_link')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weblist_detail');
    }
};
