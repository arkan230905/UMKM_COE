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
        // Drop table if exists to avoid conflicts
        Schema::dropIfExists('presensis');
        
        // Create fresh table
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->string('pegawai_id'); // Using string to match kode_pegawai
            $table->date('tgl_presensi');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status', ['hadir', 'absen', 'izin', 'sakit'])->default('hadir');
            $table->decimal('jumlah_jam', 5, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('verifikasi_wajah')->default(false);
            $table->string('foto_wajah')->nullable();
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_keluar', 10, 8)->nullable();
            $table->decimal('longitude_keluar', 11, 8)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['pegawai_id', 'tgl_presensi']);
            $table->index('tgl_presensi');
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
