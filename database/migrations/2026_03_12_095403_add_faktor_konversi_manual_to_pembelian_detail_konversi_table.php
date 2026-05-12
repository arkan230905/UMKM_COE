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
        Schema::table('pembelian_detail_konversi', function (Blueprint $table) {
            $table->decimal('faktor_konversi_manual', 15, 4)->nullable()->after('jumlah_konversi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_detail_konversi', function (Blueprint $table) {
            $table->dropColumn('faktor_konversi_manual');
        });
    }
};
