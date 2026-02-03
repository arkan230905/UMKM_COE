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
        Schema::table('penjualans', function (Blueprint $table) {
            $table->decimal('biaya_ongkir', 15, 2)->default(0)->after('total');
            $table->decimal('biaya_service', 15, 2)->default(0)->after('biaya_ongkir');
            $table->decimal('ppn_persen', 5, 2)->default(0)->after('biaya_service');
            $table->decimal('total_ppn', 15, 2)->default(0)->after('ppn_persen');
            $table->decimal('subtotal_produk', 15, 2)->default(0)->after('total_ppn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn(['biaya_ongkir', 'biaya_service', 'ppn_persen', 'total_ppn', 'subtotal_produk']);
        });
    }
};
