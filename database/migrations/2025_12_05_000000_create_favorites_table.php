<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pengaman agar tidak terjadi error jika tabel sudah ada di database
        if (!Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();
                
                // Relasi ke User
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                
                // Relasi ke Produk (Pastikan tabel 'produks' sudah ada sebelum migrasi ini)
                $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
                
                $table->timestamps();

                // Unique constraint agar user tidak bisa memfavoritkan produk yang sama dua kali
                $table->unique(['user_id', 'produk_id'], 'unique_user_favorite');
                
                // Index tambahan untuk performa pencarian
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};