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
        // Add produk_id to harga_pokok_produksi_btkl
        Schema::table('harga_pokok_produksi_btkl', function (Blueprint $table) {
            $table->unsignedBigInteger('produk_id')->nullable()->after('user_id');
            $table->index(['user_id', 'produk_id'], 'hpp_btkl_user_produk_idx');
        });

        // Add produk_id to harga_pokok_produksi_bop
        Schema::table('harga_pokok_produksi_bop', function (Blueprint $table) {
            $table->unsignedBigInteger('produk_id')->nullable()->after('user_id');
            $table->index(['user_id', 'produk_id'], 'hpp_bop_user_produk_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harga_pokok_produksi_btkl', function (Blueprint $table) {
            $table->dropIndex('hpp_btkl_user_produk_idx');
            $table->dropColumn('produk_id');
        });

        Schema::table('harga_pokok_produksi_bop', function (Blueprint $table) {
            $table->dropIndex('hpp_bop_user_produk_idx');
            $table->dropColumn('produk_id');
        });
    }
};
