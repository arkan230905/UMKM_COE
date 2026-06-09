<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix: Pastikan kolom 'total_tunjangan' ada, hapus kolom 'tunjangan' jika ada
     */
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Cek apakah kolom 'total_tunjangan' sudah ada
            if (!Schema::hasColumn('penggajians', 'total_tunjangan')) {
                $table->decimal('total_tunjangan', 15, 2)->default(0)->after('gaji_pokok');
            }
            
            // Cek apakah ada kolom 'tunjangan' yang salah (singular)
            // Jika ada, copy datanya ke 'total_tunjangan' lalu hapus
            if (Schema::hasColumn('penggajians', 'tunjangan')) {
                // Copy data dari 'tunjangan' ke 'total_tunjangan'
                DB::statement('UPDATE penggajians SET total_tunjangan = tunjangan WHERE tunjangan IS NOT NULL');
                
                // Hapus kolom 'tunjangan' yang salah
                $table->dropColumn('tunjangan');
            }
            
            // Pastikan kolom detail tunjangan ada
            if (!Schema::hasColumn('penggajians', 'tunjangan_jabatan')) {
                $table->decimal('tunjangan_jabatan', 15, 2)->default(0)->after('total_tunjangan');
            }
            
            if (!Schema::hasColumn('penggajians', 'tunjangan_transport')) {
                $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan_jabatan');
            }
            
            if (!Schema::hasColumn('penggajians', 'tunjangan_konsumsi')) {
                $table->decimal('tunjangan_konsumsi', 15, 2)->default(0)->after('tunjangan_transport');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Rollback: kembalikan kolom 'tunjangan' jika diperlukan
            if (!Schema::hasColumn('penggajians', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('gaji_pokok');
            }
            
            // Copy data kembali dari total_tunjangan ke tunjangan
            if (Schema::hasColumn('penggajians', 'total_tunjangan')) {
                DB::statement('UPDATE penggajians SET tunjangan = total_tunjangan WHERE total_tunjangan IS NOT NULL');
            }
        });
    }
};
