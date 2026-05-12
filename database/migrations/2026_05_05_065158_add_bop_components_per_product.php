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
        // Add komponen BOP per produk columns to bop_proses table
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->decimal('gas_per_produk', 15, 2)->default(0)->after('lain_lain_per_jam');
            $table->decimal('air_kebersihan_per_produk', 15, 2)->default(0)->after('gas_per_produk');
            $table->text('komponen_json')->nullable()->after('air_kebersihan_per_produk'); // For dynamic components
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->dropColumn(['gas_per_produk', 'air_kebersihan_per_produk', 'komponen_json']);
        });
    }
};
