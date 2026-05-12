<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konversi_produksis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke bahan baku yang dikonversi
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            
            // Relasi ke produksi yang menghasilkan konversi ini
            $table->foreignId('produksi_id')->nullable()->constrained('produksis')->onDelete('cascade');
            
            // Data konversi dari proses produksi nyata
            $table->decimal('jumlah_bahan_asli', 15, 4)->comment('Jumlah bahan baku yang digunakan dalam satuan pembelian');
            $table->string('satuan_asli', 50)->comment('Satuan pembelian bahan baku (kg, liter, dll)');
            
            $table->decimal('jumlah_hasil_produksi', 15, 4)->comment('Jumlah hasil produksi yang dihasilkan');
            $table->string('satuan_hasil', 50)->comment('Satuan hasil produksi (potong, porsi, dll)');
            
            // Hasil perhitungan konversi otomatis
            $table->decimal('faktor_konversi', 15, 8)->comment('Faktor konversi otomatis: 1 satuan_asli = X satuan_hasil');
            $table->decimal('harga_per_satuan_hasil', 15, 4)->comment('Harga per satuan hasil (dihitung otomatis)');
            
            // Metadata untuk tracking
            $table->date('tanggal_produksi')->comment('Tanggal produksi terjadi');
            $table->boolean('is_active')->default(true)->comment('Apakah konversi ini masih digunakan');
            $table->decimal('confidence_score', 5, 4)->default(1.0)->comment('Skor kepercayaan konversi (0-1)');
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['bahan_baku_id', 'is_active']);
            $table->index(['tanggal_produksi']);
            $table->index(['produksi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konversi_produksis');
    }
};
