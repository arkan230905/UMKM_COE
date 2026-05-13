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
        // Pengaman: Hanya buat tabel jika belum ada di database
        if (!Schema::hasTable('kategori_bahan_pendukung')) {
            Schema::create('kategori_bahan_pendukung', function (Blueprint $table) {
                $table->id();
                
                /** 
                 * Menghubungkan kategori ke user sebagai owner 
                 * (Sesuai struktur multi-tenant SIMACOST) 
                 */
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('cascade');
                
                $table->string('nama_kategori');
                $table->text('deskripsi')->nullable();
                $table->timestamps();

                // Tambahkan index untuk optimasi pencarian bahan
                $table->index('user_id');
                $table->index('nama_kategori');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_bahan_pendukung');
    }
};