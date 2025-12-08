<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel relasi default BOP per proses produksi
     * Menyimpan komponen BOP default yang digunakan oleh setiap proses
     */
    public function up(): void
    {
        Schema::create('proses_bops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proses_produksi_id')->constrained('proses_produksis')->onDelete('cascade');
            $table->foreignId('komponen_bop_id')->constrained('komponen_bops')->onDelete('cascade');
            $table->decimal('kuantitas_default', 15, 4)->default(0)->comment('Kuantitas default per proses');
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['proses_produksi_id', 'komponen_bop_id'], 'proses_bop_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proses_bops');
    }
};
