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
        // Update existing asset with missing depreciation fields
        \DB::table('asets')
            ->where('id', 2)
            ->update([
                'metode_penyusutan' => 'garis_lurus',
                'tarif_penyusutan' => 25.00,
                'penyusutan_per_tahun' => 125000000.00,
                'penyusutan_per_bulan' => 10416666.67,
                'nilai_buku' => 500000000.00
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes
        \DB::table('asets')
            ->where('id', 2)
            ->update([
                'metode_penyusutan' => null,
                'tarif_penyusutan' => null,
                'penyusutan_per_tahun' => 0,
                'penyusutan_per_bulan' => 0,
                'nilai_buku' => 0
            ]);
    }
};
