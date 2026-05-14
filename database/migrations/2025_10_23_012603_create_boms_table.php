<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            
            // Kolom Multi-Tenant: Memastikan data BOM hanya milik owner tertentu
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relasi Utama
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            
            // Kolom ini harus ada di sini agar migrasi 'add_columns' atau 'make_nullable' tidak error
            $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_bakus')->onDelete('cascade');
            
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->timestamps();
            
            // Indexing untuk performa pencarian per tenant
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};