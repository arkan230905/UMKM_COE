<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom produk_id ke paket_menus agar setiap paket
     * otomatis terhubung ke satu baris di tabel produks.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('paket_menus', 'produk_id')) {
            Schema::table('paket_menus', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable()->after('id');
                $table->foreign('produk_id')->references('id')->on('produks')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paket_menus', 'produk_id')) {
            Schema::table('paket_menus', function (Blueprint $table) {
                $table->dropForeign(['produk_id']);
                $table->dropColumn('produk_id');
            });
        }
    }
};
