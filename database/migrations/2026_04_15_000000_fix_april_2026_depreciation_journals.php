<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backup data lama
        DB::statement("
            CREATE TABLE IF NOT EXISTS jurnal_umum_backup_april_2026 AS
            SELECT * FROM jurnal_umum 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
        ");

        // Update Mesin Produksi: 1.416.667 -> 1.333.333
        DB::update("
            UPDATE jurnal_umum 
            SET debit = 1333333.00 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
              AND keterangan LIKE '%Mesin%' 
              AND debit = 1416667.00
        ");

        DB::update("
            UPDATE jurnal_umum 
            SET kredit = 1333333.00 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
              AND keterangan LIKE '%Mesin%' 
              AND kredit = 1416667.00
        ");

        // Update Peralatan Produksi: 2.833.333 -> 659.474
        DB::update("
            UPDATE jurnal_umum 
            SET debit = 659474.00 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
              AND keterangan LIKE '%Peralatan%' 
              AND debit = 2833333.00
        ");

        DB::update("
            UPDATE jurnal_umum 
            SET kredit = 659474.00 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
              AND keterangan LIKE '%Peralatan%' 
              AND kredit = 2833333.00
        ");

        // Update Kendaraan: 2.361.111 -> 888.889
        DB::update("
            UPDATE jurnal_umum 
            SET debit = 888889.00 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
              AND keterangan LIKE '%Kendaraan%' 
              AND debit = 2361111.00
        ");

        DB::update("
            UPDATE jurnal_umum 
            SET kredit = 888889.00 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
              AND keterangan LIKE '%Kendaraan%' 
              AND kredit = 2361111.00
        ");

        // Update data aset agar konsisten
        DB::update("
            UPDATE asets 
            SET penyusutan_per_bulan = 1333333.00,
                penyusutan_per_tahun = 16000000.00
            WHERE nama_aset LIKE '%Mesin%'
              AND nama_aset LIKE '%Produksi%'
        ");

        DB::update("
            UPDATE asets 
            SET penyusutan_per_bulan = 659474.00,
                penyusutan_per_tahun = 7913688.00
            WHERE nama_aset LIKE '%Peralatan%'
              AND nama_aset LIKE '%Produksi%'
        ");

        DB::update("
            UPDATE asets 
            SET penyusutan_per_bulan = 888889.00,
                penyusutan_per_tahun = 10666668.00
            WHERE nama_aset LIKE '%Kendaraan%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore dari backup
        DB::statement("
            UPDATE jurnal_umum ju
            JOIN jurnal_umum_backup_april_2026 backup ON ju.id = backup.id
            SET ju.debit = backup.debit,
                ju.kredit = backup.kredit
            WHERE ju.tanggal = '2026-04-30' 
              AND ju.keterangan LIKE '%Penyusutan%'
        ");

        // Drop backup table
        DB::statement("DROP TABLE IF EXISTS jurnal_umum_backup_april_2026");
    }
};