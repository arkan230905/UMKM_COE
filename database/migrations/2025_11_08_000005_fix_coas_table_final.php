<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCoasTableFinal extends Migration
{
    public function up()
    {
        // Mark the problematic migration as completed
        DB::table('migrations')->insert([
            'migration' => '2025_10_29_160535_update_coas_table_structure',
            'batch' => 1,
        ]);

        // Add missing columns if they don't exist
        if (!Schema::hasColumn('coas', 'kategori_akun')) {
            Schema::table('coas', function ($table) {
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
    }

    public function down()
    {
        // This is a one-way migration
    }
}
