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
        Schema::create('verifikasi_wajahs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk_pegawai');
            $table->string('foto_wajah');
            $table->text('encoding_wajah')->nullable();
            $table->boolean('aktif')->default(true);
            $table->date('tanggal_verifikasi');
            $table->timestamps();
            
            $table->foreign('nomor_induk_pegawai')->references('kode_pegawai')->on('pegawais')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_wajahs');
    }
};
