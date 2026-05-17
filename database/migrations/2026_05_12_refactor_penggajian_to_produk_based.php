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
        Schema::table('penggajians', function (Blueprint $table) {
            // Add new produk-based columns
            if (!Schema::hasColumn('penggajians', 'produk_hari_1_5')) {
                $table->integer('produk_hari_1_5')->default(0);
            }

            if (!Schema::hasColumn('penggajians', 'produk_hari_6_10')) {
                $table->integer('produk_hari_6_10')->default(0);
            }

            if (!Schema::hasColumn('penggajians', 'produk_hari_11_20')) {
                $table->integer('produk_hari_11_20')->default(0);
            }

            if (!Schema::hasColumn('penggajians', 'produk_hari_21_30')) {
                $table->integer('produk_hari_21_30')->default(0);
            }

            if (!Schema::hasColumn('penggajians', 'total_produk_bulan')) {
                $table->integer('total_produk_bulan')->default(0);
            }

            if (!Schema::hasColumn('penggajians', 'tarif_produk')) {
                $table->decimal('tarif_produk', 15, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('penggajians', 'produk_hari_1_5')) {
                $table->dropColumn('produk_hari_1_5');
            }
            if (Schema::hasColumn('penggajians', 'produk_hari_6_10')) {
                $table->dropColumn('produk_hari_6_10');
            }
            if (Schema::hasColumn('penggajians', 'produk_hari_11_20')) {
                $table->dropColumn('produk_hari_11_20');
            }
            if (Schema::hasColumn('penggajians', 'produk_hari_21_30')) {
                $table->dropColumn('produk_hari_21_30');
            }
            if (Schema::hasColumn('penggajians', 'total_produk_bulan')) {
                $table->dropColumn('total_produk_bulan');
            }
            if (Schema::hasColumn('penggajians', 'tarif_produk')) {
                $table->dropColumn('tarif_produk');
            }
        });
    }
};
