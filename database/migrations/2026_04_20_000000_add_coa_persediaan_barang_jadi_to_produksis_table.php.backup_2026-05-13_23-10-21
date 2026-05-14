<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add COA persediaan barang jadi field to produksis table
     */
    public function up(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->foreignId('coa_persediaan_barang_jadi_id')->nullable()->after('produk_id')->constrained('coas');
        });
    }

    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropForeign(['coa_persediaan_barang_jadi_id']);
            $table->dropColumn('coa_persediaan_barang_jadi_id');
        });
    }
};
