<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Mengubah unique constraint pada nomor_penjualan menjadi unique per user (multi-tenant)
     */
    public function up(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Drop unique constraint lama yang global
            $table->dropUnique('penjualans_nomor_penjualan_unique');
            
            // Tambah composite unique constraint: user_id + nomor_penjualan
            $table->unique(['user_id', 'nomor_penjualan'], 'penjualans_user_nomor_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Kembalikan ke unique constraint global
            $table->dropUnique('penjualans_user_nomor_unique');
            $table->unique('nomor_penjualan', 'penjualans_nomor_penjualan_unique');
        });
    }
};
