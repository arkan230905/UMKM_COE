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
            $table->text('foto_wajah')->nullable();
            $table->text('encoding_wajah')->nullable();
            $table->boolean('aktif')->default(true);
            $table->date('tanggal_verifikasi')->nullable();
            $table->timestamps();
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
