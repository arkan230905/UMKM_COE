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
        // Pengaman agar tidak terjadi error jika tabel sudah ada di database
        if (!Schema::hasTable('rekap_presensi_bulanan')) {
            Schema::create('rekap_presensi_bulanan', function (Blueprint $table) {
                $table->id();
                
                // Relasi Owner/User (Multi-tenant)
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                // Relasi ke Pegawai - Pastikan tabel 'pegawais' sudah ada sebelumnya
                $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
                
                // Periode Rekap
                $table->integer('periode_bulan');
                $table->integer('periode_tahun');
                
                // Data Kehadiran
                $table->integer('total_hari_hadir')->default(0);
                $table->integer('total_alpha')->default(0);
                $table->integer('total_masuk_saja')->default(0);
                
                // Kalkulasi Waktu & Estimasi Biaya untuk Costing
                $table->decimal('total_jam_bulanan', 15, 2)->default(0);
                $table->integer('target_hari_kerja')->default(0);
                $table->decimal('persentase_kehadiran', 5, 2)->default(0);
                $table->decimal('estimasi_gaji', 15, 2)->default(0);
                
                $table->timestamps();

                // Indexing untuk mempercepat penarikan laporan bulanan
                $table->index(['user_id', 'pegawai_id'], 'idx_rekap_bulanan_user_pegawai');
                $table->index(['periode_tahun', 'periode_bulan'], 'idx_rekap_bulanan_periode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_presensi_bulanan');
    }
};