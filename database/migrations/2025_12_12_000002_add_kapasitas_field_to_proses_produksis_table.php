<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field kapasitas produksi per jam ke tabel proses_produksis
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->decimal('jumlah_produksi_per_jam', 15, 2)->default(0)->after('satuan_btkl')->comment('Jumlah produk yang dapat dihasilkan per jam (pcs/jam)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->dropColumn('jumlah_produksi_per_jam');
        });
    }
};
