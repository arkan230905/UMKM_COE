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
        Schema::create('verifikasi_wajah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pegawai'); // Menggunakan kode_pegawai sebagai foreign key
            $table->string('foto_wajah');
            $table->text('encoding_wajah')->nullable();
            $table->boolean('aktif')->default(true);
            $table->date('tanggal_verifikasi');
            $table->timestamps();
            
            // Foreign key ke tabel pegawais dengan kolom kode_pegawai
            $table->foreign('kode_pegawai')->references('kode_pegawai')->on('pegawais')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_wajah');
    }
};
