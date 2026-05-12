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
        Schema::table('produks', function (Blueprint $table) {
            // Tambahkan kolom harga_bom (Harga Bill of Materials / Harga Pokok)
            // Digunakan untuk menghitung HPP (Harga Pokok Penjualan) dalam laporan laba rugi
            if (!Schema::hasColumn('produks', 'harga_bom')) {
                $table->decimal('harga_bom', 15, 2)->nullable()->after('harga_jual')
                    ->comment('Harga Bill of Materials / Harga Pokok Produk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'harga_bom')) {
                $table->dropColumn('harga_bom');
            }
        });
    }
};
