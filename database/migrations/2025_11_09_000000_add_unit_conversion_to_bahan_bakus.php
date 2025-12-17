<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip this migration - columns already exist or not needed
        return;
    }

    public function down()
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn(['satuan_dasar', 'faktor_konversi', 'harga_per_satuan_dasar']);
            // Kembalikan tipe data stok ke semula
            $table->decimal('stok', 10, 2)->default(0)->change();
            $table->decimal('stok_minimum', 10, 2)->default(0)->change();
        });
    }
};
