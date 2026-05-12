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
        Schema::table('produksis', function (Blueprint $table) {
            $table->decimal('jumlah_produksi_bulanan', 15, 4)->after('qty_produksi')->comment('Total produksi yang direncanakan dalam 1 bulan');
            $table->integer('hari_produksi_bulanan')->after('jumlah_produksi_bulanan')->comment('Jumlah hari kerja produksi dalam 1 bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn(['jumlah_produksi_bulanan', 'hari_produksi_bulanan']);
        });
    }
};