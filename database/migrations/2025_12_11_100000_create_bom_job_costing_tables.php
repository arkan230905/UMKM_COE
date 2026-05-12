<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BOM Sederhana untuk Job Process Costing
     * Komponen: BBB, BTKL (dari Proses Produksi), Bahan Pendukung, BOP
     * 
     * Konsep:
     * - Input jumlah produk yang mau dibuat
     * - Input semua komponen biaya
     * - Sistem hitung HPP per unit = Total Biaya / Jumlah Produk
     */
    public function up(): void
    {
        // Tabel utama BOM - 1 Produk = 1 BOM
        Schema::create('bom_job_costings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->unique()->constrained('produks')->onDelete('cascade');
            
            // Jumlah produk yang akan dibuat per batch
            $table->integer('jumlah_produk')->default(1)->comment('Jumlah produk yang akan dibuat');
            
            // Total per komponen
            $table->decimal('total_bbb', 15, 2)->default(0)->comment('Total Biaya Bahan Baku');
            $table->decimal('total_btkl', 15, 2)->default(0)->comment('Total Biaya Tenaga Kerja Langsung');
            $table->decimal('total_bahan_pendukung', 15, 2)->default(0)->comment('Total Biaya Bahan Pendukung');
            $table->decimal('total_bop', 15, 2)->default(0)->comment('Total Biaya Overhead Pabrik');
            
            // Total HPP
            $table->decimal('total_hpp', 15, 2)->default(0)->comment('Total HPP = BBB + BTKL + BP + BOP');
            $table->decimal('hpp_per_unit', 15, 2)->default(0)->comment('HPP per unit = Total HPP / Jumlah Produk');
            
            $table->timestamps();
        });

        // Detail BBB (Bahan Baku)
        Schema::create('bom_job_bbb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_job_costing_id')->constrained('bom_job_costings')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('restrict');
            
            $table->decimal('jumlah', 15, 4)->default(0);
            $table->string('satuan', 20)->default('KG');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail BTKL (dari Proses Produksi)
        Schema::create('bom_job_btkl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_job_costing_id')->constrained('bom_job_costings')->onDelete('cascade');
            $table->foreignId('proses_produksi_id')->nullable()->constrained('proses_produksis')->onDelete('set null');
            
            $table->string('nama_proses');
            $table->decimal('durasi_jam', 15, 4)->default(0)->comment('Durasi dalam jam');
            $table->decimal('tarif_per_jam', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail Bahan Pendukung
        Schema::create('bom_job_bahan_pendukung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_job_costing_id')->constrained('bom_job_costings')->onDelete('cascade');
            $table->foreignId('bahan_pendukung_id')->constrained('bahan_pendukungs')->onDelete('restrict');
            
            $table->decimal('jumlah', 15, 4)->default(0);
            $table->string('satuan', 20)->default('PCS');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Detail BOP (dari master BOP)
        Schema::create('bom_job_bop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_job_costing_id')->constrained('bom_job_costings')->onDelete('cascade');
            $table->foreignId('bop_id')->nullable()->constrained('bops')->onDelete('set null');
            
            $table->string('nama_bop');
            $table->decimal('jumlah', 15, 4)->default(1);
            $table->decimal('tarif', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_job_bop');
        Schema::dropIfExists('bom_job_bahan_pendukung');
        Schema::dropIfExists('bom_job_btkl');
        Schema::dropIfExists('bom_job_bbb');
        Schema::dropIfExists('bom_job_costings');
    }
};
