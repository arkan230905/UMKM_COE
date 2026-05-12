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
        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->decimal('jumlah_satuan_utama', 15, 4)->nullable()->after('faktor_konversi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->dropColumn('jumlah_satuan_utama');
        });
    }
};