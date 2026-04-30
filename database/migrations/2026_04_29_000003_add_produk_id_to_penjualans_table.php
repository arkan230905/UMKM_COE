<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom produk_id ke tabel penjualans.
     *
     * Kolom ini digunakan untuk penjualan single-item (tanpa detail).
     * Penjualan multi-item menggunakan tabel penjualan_details.
     *
     * Referensi: App\Models\Penjualan::$fillable dan relasi produk()
     */
    public function up(): void
    {
        if (!Schema::hasColumn('penjualans', 'produk_id')) {
            Schema::table('penjualans', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')
                      ->nullable()
                      ->after('nomor_penjualan');

                $table->foreign('produk_id')
                      ->references('id')
                      ->on('produks')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penjualans', 'produk_id')) {
            Schema::table('penjualans', function (Blueprint $table) {
                $table->dropForeign(['produk_id']);
                $table->dropColumn('produk_id');
            });
        }
    }
};
