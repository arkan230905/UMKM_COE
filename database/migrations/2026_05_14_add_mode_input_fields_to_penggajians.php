<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah kolom untuk mode input bulanan dan pembulatan gaji.
     */
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Mode input: harian (default) atau bulanan
            if (!Schema::hasColumn('penggajians', 'mode_input')) {
                $table->enum('mode_input', ['harian', 'bulanan'])
                      ->default('harian');
            }

            // Total produk bulanan — diisi langsung oleh user jika mode = bulanan
            if (!Schema::hasColumn('penggajians', 'total_produk_bulanan')) {
                $table->integer('total_produk_bulanan')
                      ->nullable();
            }

            // Apakah pembulatan aktif (hanya untuk mode bulanan)
            if (!Schema::hasColumn('penggajians', 'pembulatan_aktif')) {
                $table->boolean('pembulatan_aktif')
                      ->default(false);
            }

            // Nilai step pembulatan (mis: 1000, 10000, 100000, 500000)
            if (!Schema::hasColumn('penggajians', 'pembulatan_step')) {
                $table->integer('pembulatan_step')
                      ->nullable();
            }

            // Selisih hasil pembulatan: gaji_final - gaji_mentah
            if (!Schema::hasColumn('penggajians', 'nominal_pembulatan')) {
                $table->integer('nominal_pembulatan')
                      ->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $columns = ['mode_input', 'total_produk_bulanan', 'pembulatan_aktif', 'pembulatan_step', 'nominal_pembulatan'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('penggajians', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
