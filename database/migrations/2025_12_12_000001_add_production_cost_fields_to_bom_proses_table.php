<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field untuk perhitungan biaya proses produksi per jam
     */
    public function up(): void
    {
        Schema::table('bom_proses', function (Blueprint $table) {
            $table->decimal('nominal_per_jam', 15, 2)->default(0)->after('biaya_bop')->comment('Nominal biaya per jam untuk proses ini');
            $table->decimal('jumlah_produksi_per_jam', 15, 2)->default(0)->after('nominal_per_jam')->comment('Jumlah produksi per jam (pcs/jam)');
            $table->decimal('biaya_per_pcs', 15, 2)->default(0)->after('jumlah_produksi_per_jam')->comment('Biaya per pcs (calculated: nominal_per_jam / jumlah_produksi_per_jam)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_proses', function (Blueprint $table) {
            $table->dropColumn(['nominal_per_jam', 'jumlah_produksi_per_jam', 'biaya_per_pcs']);
        });
    }
};
