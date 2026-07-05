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
            $table->string('no_rekening_refund')->nullable()->after('bank_tujuan_refund');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_penjualans', function (Blueprint $table) {
            $table->dropColumn('no_rekening_refund');
        });
    }
};
