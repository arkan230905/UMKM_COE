<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asets')) return;

        // Cek kolom yang sudah ada
        $existing = collect(DB::select("PRAGMA table_info('asets')"))
            ->pluck('name')
            ->all();

        DB::beginTransaction();
        try {
            // Tambah kolom yang dibutuhkan controller tapi belum ada di DB
            if (!in_array('kategori_aset_id', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN kategori_aset_id INTEGER NULL");
            }
            if (!in_array('biaya_perolehan', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN biaya_perolehan REAL DEFAULT 0");
            }
            if (!in_array('nilai_residu', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN nilai_residu REAL DEFAULT 0");
            }
            if (!in_array('umur_manfaat', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN umur_manfaat INTEGER DEFAULT 5");
            }
            if (!in_array('penyusutan_per_tahun', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN penyusutan_per_tahun REAL DEFAULT 0");
            }
            if (!in_array('penyusutan_per_bulan', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN penyusutan_per_bulan REAL DEFAULT 0");
            }
            if (!in_array('tanggal_beli', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN tanggal_beli TEXT NULL");
            }
            if (!in_array('tanggal_akuisisi', $existing)) {
                DB::statement("ALTER TABLE asets ADD COLUMN tanggal_akuisisi TEXT NULL");
            }
            
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function down(): void
    {
        // SQLite tidak mendukung DROP COLUMN dengan mudah; skip rollback
    }
};
