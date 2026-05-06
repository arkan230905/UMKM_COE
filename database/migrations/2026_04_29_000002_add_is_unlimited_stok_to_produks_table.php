<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom is_unlimited_stok ke tabel produks.
     * Digunakan untuk produk paket yang stoknya mengikuti stok produk di dalamnya.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('produks', 'is_unlimited_stok')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->boolean('is_unlimited_stok')->default(false)->after('stok');
            });
        }

        // Set semua produk kategori Paket (kode PKT) jadi unlimited
        $kategoriPaket = \DB::table('kategori_produks')
            ->where('kode_kategori', 'PKT')
            ->first();

        if ($kategoriPaket) {
            \DB::table('produks')
                ->where('kategori_id', $kategoriPaket->id)
                ->update(['is_unlimited_stok' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('produks', 'is_unlimited_stok')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->dropColumn('is_unlimited_stok');
            });
        }
    }
};
