<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixBopsForeignKey extends Migration
{
    public function up()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop the existing foreign key if it exists
        Schema::table('bops', function (Blueprint $table) {
            $foreignKeys = DB::select(
                "SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_NAME = 'bops' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY' 
                AND CONSTRAINT_NAME = 'bops_kode_akun_foreign'"
            );

            if (count($foreignKeys) > 0) {
                $table->dropForeign('bops_kode_akun_foreign');
            }
        });

        // Mark the problematic migration as completed
        if (!DB::table('migrations')->where('migration', '2025_10_29_160535_update_coas_table_structure')->exists()) {
            DB::table('migrations')->insert([
                'migration' => '2025_10_29_160535_update_coas_table_structure',
                'batch' => 1,
            ]);
        }

        // Add missing columns to coas table if they don't exist
        if (!Schema::hasColumn('coas', 'kategori_akun')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kategori_akun')->nullable()->after('tipe_akun');
                $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
                $table->string('kode_induk', 255)->nullable()->after('is_akun_header');
                $table->enum('saldo_normal', ['debit', 'kredit'])->default('debit')->after('kode_induk');
                $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_normal');
                $table->date('tanggal_saldo_awal')->nullable()->after('saldo_awal');
                $table->text('keterangan')->nullable()->after('tanggal_saldo_awal');
                $table->boolean('posted_saldo_awal')->default(false)->after('keterangan');
            });
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // This is a one-way migration
    }
}
