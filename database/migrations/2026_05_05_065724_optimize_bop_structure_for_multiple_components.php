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
        // Optimize bop_proses table for multiple BOP components
        Schema::table('bop_proses', function (Blueprint $table) {
            // Remove old structure columns that are not needed
            $table->dropColumn(['gas_per_produk', 'air_kebersihan_per_produk', 'komponen_json']);
            
            // Add proper JSON column for components
            $table->json('komponen_bop')->nullable()->after('lain_lain_per_jam');
            
            // Add calculation fields
            $table->decimal('total_bop_per_produk', 15, 2)->default(0)->after('komponen_bop');
            $table->decimal('total_biaya_per_produk', 15, 2)->default(0)->after('total_bop_per_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            // Add back old columns
            $table->decimal('gas_per_produk', 15, 2)->default(0)->after('lain_lain_per_jam');
            $table->decimal('air_kebersihan_per_produk', 15, 2)->default(0)->after('gas_per_produk');
            $table->json('komponen_json')->nullable()->after('air_kebersihan_per_produk');
            
            // Remove new columns
            $table->dropColumn(['komponen_bop', 'total_bop_per_produk', 'total_biaya_per_produk']);
        });
    }
};
