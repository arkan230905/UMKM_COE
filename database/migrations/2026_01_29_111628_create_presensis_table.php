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
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk_pegawai');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status_masuk', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->boolean('verifikasi_wajah')->default(false);
            $table->string('foto_wajah')->nullable();
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_keluar', 10, 8)->nullable();
            $table->decimal('longitude_keluar', 11, 8)->nullable();
            $table->timestamps();
            
            $table->foreign('nomor_induk_pegawai')->references('nomor_induk_pegawai')->on('pegawais')->onDelete('cascade');
            $table->unique(['nomor_induk_pegawai', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
