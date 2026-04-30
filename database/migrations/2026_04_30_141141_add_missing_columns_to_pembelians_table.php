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
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('bukti_faktur')->nullable()->after('nomor_faktur');
            $table->string('kode_pembelian')->nullable()->after('bukti_faktur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn(['bukti_faktur', 'kode_pembelian']);
        });
    }
};
