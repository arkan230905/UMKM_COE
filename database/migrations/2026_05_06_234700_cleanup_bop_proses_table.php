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
            // Add nama_bop_proses column
            if (!Schema::hasColumn('bop_proses', 'nama_bop_proses')) {
                $table->string('nama_bop_proses', 255)->nullable()->after('id');
            }
            
            // Drop unused columns
            $columnsToRemove = [
                'listrik_per_jam',
                'gas_bbm_per_jam', 
                'penyusutan_mesin_per_jam',
                'maintenance_per_jam',
                'gaji_mandor_per_jam',
                'lain_lain_per_jam'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('bop_proses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            // Remove nama_bop_proses
            if (Schema::hasColumn('bop_proses', 'nama_bop_proses')) {
                $table->dropColumn('nama_bop_proses');
            }
            
            // Re-add removed columns
            $table->decimal('listrik_per_jam', 15, 2)->default(0);
            $table->decimal('gas_bbm_per_jam', 15, 2)->default(0);
            $table->decimal('penyusutan_mesin_per_jam', 15, 2)->default(0);
            $table->decimal('maintenance_per_jam', 15, 2)->default(0);
            $table->decimal('gaji_mandor_per_jam', 15, 2)->default(0);
            $table->decimal('lain_lain_per_jam', 15, 2)->default(0);
        });
    }
};
