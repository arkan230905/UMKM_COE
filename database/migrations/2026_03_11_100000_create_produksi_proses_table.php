<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk tracking setiap tahap proses produksi
     */
    public function up(): void
    {
        Schema::create('produksi_proses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->onDelete('cascade');
            $table->string('nama_proses', 100); // Nama proses (Penggorengan, Perbumbuan, dll)
            $table->integer('urutan')->default(1); // Urutan proses (1, 2, 3, ...)
            $table->enum('status', ['pending', 'sedang_dikerjakan', 'selesai'])->default('pending');
            
            // Biaya untuk proses ini
            $table->decimal('biaya_btkl', 15, 2)->default(0);
            $table->decimal('biaya_bop', 15, 2)->default(0);
            $table->decimal('total_biaya_proses', 15, 2)->default(0);
            
            // Waktu proses
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('durasi_menit')->nullable(); // Durasi aktual dalam menit
            
            // Pegawai yang mengerjakan
            $table->text('pegawai_ids')->nullable(); // JSON array of pegawai IDs
            $table->text('catatan')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('produksi_id');
            $table->index('status');
            $table->index('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_proses');
    }
};
