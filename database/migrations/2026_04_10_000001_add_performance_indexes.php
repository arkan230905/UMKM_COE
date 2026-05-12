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
        // Add indexes for better performance
        Schema::table('penjualans', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('payment_method');
            $table->index(['tanggal', 'total']);
            $table->index('nomor_penjualan');
        });

        Schema::table('penjualan_details', function (Blueprint $table) {
            $table->index('penjualan_id');
            $table->index('produk_id');
        });

        Schema::table('produks', function (Blueprint $table) {
            $table->index('barcode');
            $table->index('nama_produk');
        });

        Schema::table('retur_penjualans', function (Blueprint $table) {
            $table->index('penjualan_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['tanggal', 'total']);
            $table->dropIndex(['nomor_penjualan']);
        });

        Schema::table('penjualan_details', function (Blueprint $table) {
            $table->dropIndex(['penjualan_id']);
            $table->dropIndex(['produk_id']);
        });

        Schema::table('produks', function (Blueprint $table) {
            $table->dropIndex(['barcode']);
            $table->dropIndex(['nama_produk']);
        });

        Schema::table('retur_penjualans', function (Blueprint $table) {
            $table->dropIndex(['penjualan_id']);
            $table->dropIndex(['created_at']);
        });
    }
};