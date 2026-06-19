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
        Schema::table('bop_proses', function (Blueprint $table) {
            // Jumlah produksi produk per bulan (untuk perhitungan Rp/produk)
            $table->integer('jumlah_produksi_perbulan')->nullable()->after('total_bop_per_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->dropColumn('jumlah_produksi_perbulan');
        });
    }
};
