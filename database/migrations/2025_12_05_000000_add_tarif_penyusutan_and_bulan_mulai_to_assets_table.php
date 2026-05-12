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
        Schema::table('asets', function (Blueprint $table) {
            $table->decimal('tarif_penyusutan', 5, 2)->nullable()->after('metode_penyusutan')->comment('Tarif penyusutan dalam persen untuk metode saldo menurun');
            $table->integer('bulan_mulai')->nullable()->after('tarif_penyusutan')->comment('Bulan mulai penyusutan (1-12)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['tarif_penyusutan', 'bulan_mulai']);
        });
    }
};
