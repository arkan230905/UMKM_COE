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
            $table->decimal('biaya_servis', 15, 2)->default(0)->after('biaya_ongkir');
            $table->decimal('biaya_ppn', 15, 2)->default(0)->after('biaya_servis');
            $table->decimal('grand_total', 15, 2)->nullable()->after('biaya_ppn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn(['biaya_ongkir', 'biaya_servis', 'biaya_ppn', 'grand_total']);
        });
    }
};