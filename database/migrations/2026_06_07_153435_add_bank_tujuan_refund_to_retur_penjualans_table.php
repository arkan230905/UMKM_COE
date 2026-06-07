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
            if (!Schema::hasColumn('retur_penjualans', 'bank_tujuan_refund')) {
                $table->string('bank_tujuan_refund')->nullable();
            }
            if (!Schema::hasColumn('retur_penjualans', 'metode_refund')) {
                $table->string('metode_refund')->nullable();
                $table->unsignedBigInteger('bank_refund_id')->nullable();
                $table->string('nama_penerima_refund')->nullable();
                $table->foreign('bank_refund_id')->references('id')->on('coas')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('retur_penjualans', 'bank_tujuan_refund')) {
                $table->dropColumn('bank_tujuan_refund');
            }
        });
    }
};
