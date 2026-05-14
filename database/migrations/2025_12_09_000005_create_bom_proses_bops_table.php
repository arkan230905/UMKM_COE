<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel detail BOP per proses dalam BOM
     * Menyimpan komponen BOP yang digunakan untuk setiap proses dalam BOM
     */
    public function up(): void
    {
        Schema::create('bom_proses_bops', function (Blueprint $table) {
            $table->id();
            // Relasi ke proses dalam BOM
            $table->foreignId('bom_proses_id')->constrained('bom_proses')->onDelete('cascade');
            
            // Perbaikan: Tambahkan bop_id agar migrasi 'add_nama_bop' tidak error
            $table->foreignId('bop_id')->nullable()->constrained('bops')->onDelete('restrict');
            
            // Tetap pertahankan komponen_bop_id jika memang digunakan di bagian lain sistem
            $table->foreignId('komponen_bop_id')->constrained('komponen_bops')->onDelete('restrict');
            
            $table->decimal('kuantitas', 15, 4)->default(0)->comment('Kuantitas komponen BOP');
            $table->decimal('tarif', 15, 2)->default(0)->comment('Tarif per satuan (snapshot)');
            $table->decimal('total_biaya', 15, 2)->default(0)->comment('Calculated: kuantitas × tarif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_proses_bops');
    }
};