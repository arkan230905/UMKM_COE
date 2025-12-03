<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            // Tambahkan kolom untuk konversi satuan
            $table->string('satuan_dasar', 20)->default('gram')->after('satuan');
            $table->decimal('faktor_konversi', 10, 4)->default(1)->after('satuan_dasar');
            $table->decimal('harga_per_satuan_dasar', 15, 2)->default(0)->after('harga_satuan');
            
            // Ubah tipe data stok untuk presisi yang lebih tinggi
            $table->decimal('stok', 15, 4)->default(0)->change();
            $table->decimal('stok_minimum', 15, 4)->default(0)->change();
        });

        // Update data yang sudah ada
        \DB::table('bahan_bakus')->update([
            'satuan_dasar' => 'gram',
            'faktor_konversi' => 1,
            'harga_per_satuan_dasar' => \DB::raw('harga_satuan')
        ]);
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
