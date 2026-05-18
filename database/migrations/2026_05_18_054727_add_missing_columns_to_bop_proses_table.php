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
            // Add komponen_bop column (JSON)
            if (!Schema::hasColumn('bop_proses', 'komponen_bop')) {
                $table->json('komponen_bop')->nullable()->after('nama_bop_proses');
            }
            
            // Add total_biaya_per_produk column
            if (!Schema::hasColumn('bop_proses', 'total_biaya_per_produk')) {
                $table->decimal('total_biaya_per_produk', 15, 2)->default(0)->after('total_bop_per_produk');
            }
            
            // Add total_bop_per_jam column
            if (!Schema::hasColumn('bop_proses', 'total_bop_per_jam')) {
                $table->decimal('total_bop_per_jam', 15, 2)->default(0)->after('total_biaya_per_produk');
            }
            
            // Add kapasitas_per_jam column
            if (!Schema::hasColumn('bop_proses', 'kapasitas_per_jam')) {
                $table->integer('kapasitas_per_jam')->default(0)->after('total_bop_per_jam');
            }
            
            // Add bop_per_unit column
            if (!Schema::hasColumn('bop_proses', 'bop_per_unit')) {
                $table->decimal('bop_per_unit', 15, 4)->default(0)->after('kapasitas_per_jam');
            }
            
            // Add budget column
            if (!Schema::hasColumn('bop_proses', 'budget')) {
                $table->decimal('budget', 15, 2)->default(0)->after('bop_per_unit');
            }
            
            // Add aktual column
            if (!Schema::hasColumn('bop_proses', 'aktual')) {
                $table->decimal('aktual', 15, 2)->default(0)->after('budget');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $columnsToDrop = ['komponen_bop', 'total_biaya_per_produk', 'total_bop_per_jam', 'kapasitas_per_jam', 'bop_per_unit', 'budget', 'aktual'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('bop_proses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
