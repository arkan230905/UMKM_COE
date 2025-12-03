<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Add columns if they don't exist
        if (!Schema::hasColumn('coas', 'kategori_akun')) {
            DB::statement("ALTER TABLE coas ADD COLUMN kategori_akun VARCHAR(255) NULL AFTER tipe_akun");
        }
        if (!Schema::hasColumn('coas', 'is_akun_header')) {
            DB::statement("ALTER TABLE coas ADD COLUMN is_akun_header TINYINT(1) DEFAULT 0 AFTER kategori_akun");
        }
        if (!Schema::hasColumn('coas', 'kode_induk')) {
            DB::statement("ALTER TABLE coas ADD COLUMN kode_induk VARCHAR(10) NULL AFTER is_akun_header");
        }
        if (!Schema::hasColumn('coas', 'saldo_normal')) {
            DB::statement("ALTER TABLE coas ADD COLUMN saldo_normal ENUM('debit', 'kredit') DEFAULT 'debit' AFTER kode_induk");
        }
        if (!Schema::hasColumn('coas', 'saldo_awal')) {
            DB::statement("ALTER TABLE coas ADD COLUMN saldo_awal DECIMAL(15,2) DEFAULT 0 AFTER saldo_normal");
        }
        if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
            DB::statement("ALTER TABLE coas ADD COLUMN tanggal_saldo_awal DATE NULL AFTER saldo_awal");
        }
        if (!Schema::hasColumn('coas', 'keterangan')) {
            DB::statement("ALTER TABLE coas ADD COLUMN keterangan TEXT NULL AFTER tanggal_saldo_awal");
        }
        if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
            DB::statement("ALTER TABLE coas ADD COLUMN posted_saldo_awal TINYINT(1) DEFAULT 0 AFTER keterangan");
        }

        // Mark the problematic migration as completed
        if (!DB::table('migrations')->where('migration', '2025_10_29_160535_update_coas_table_structure')->exists()) {
            DB::table('migrations')->insert([
                'migration' => '2025_10_29_160535_update_coas_table_structure',
                'batch' => 1,
            ]);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // This is a one-way migration to prevent data loss
    }
};
