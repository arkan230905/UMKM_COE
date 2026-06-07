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
        Schema::table('retur_penjualans', function (Blueprint $table) {
            $table->string('metode_refund')->nullable()->after('jenis_retur'); // kas / transfer
            $table->string('bank_refund_id')->nullable()->after('metode_refund'); // coa account_code
            $table->string('nama_penerima_refund')->nullable()->after('bank_refund_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_penjualans', function (Blueprint $table) {
            //
        });
    }
};
