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
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'metode_penyusutan')) {
                $table->string('metode_penyusutan')->nullable();
            }
            if (!Schema::hasColumn('asets', 'tarif_penyusutan')) {
                $table->decimal('tarif_penyusutan', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('asets', 'penyusutan_per_tahun')) {
                $table->decimal('penyusutan_per_tahun', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('asets', 'penyusutan_per_bulan')) {
                $table->decimal('penyusutan_per_bulan', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('asets', 'nilai_buku')) {
                $table->decimal('nilai_buku', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('asets', 'akumulasi_penyusutan')) {
                $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['metode_penyusutan', 'tarif_penyusutan', 'penyusutan_per_tahun', 'penyusutan_per_bulan', 'nilai_buku', 'akumulasi_penyusutan']);
        });
    }
};
