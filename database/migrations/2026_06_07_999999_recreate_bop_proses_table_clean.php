<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing table if exists
        Schema::dropIfExists('bop_proses');
        
        // Create clean bop_proses table
        Schema::create('bop_proses', function (Blueprint $table) {
            $table->id();
            
            // User ID for multi-tenant (without foreign key constraint for flexibility)
            $table->unsignedBigInteger('user_id');
            
            // Nama BOP Proses
            $table->string('nama_bop_proses');
            
            // Komponen Bahan Pendukung (JSON)
            // Format: [{bahan_pendukung_id, nama, qty_per_produk, harga_satuan, total, coa_debit, coa_kredit, keterangan}]
            $table->json('komponen_bahan_pendukung')->nullable();
            
            // Komponen Lainnya (JSON)
            // Format: [{nama_komponen, nilai_per_produk, coa_debit, coa_kredit, keterangan}]
            $table->json('komponen_lainnya')->nullable();
            
            // Total BOP per produk (sum of all components)
            $table->decimal('total_bop_per_produk', 15, 2)->default(0);
            
            // Total Biaya per produk (BTKL + BOP) - optional, bisa dihitung runtime
            $table->decimal('total_biaya_per_produk', 15, 2)->default(0);
            
            // Keterangan
            $table->text('keterangan')->nullable();
            
            // Status aktif
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bop_proses');
    }
};
