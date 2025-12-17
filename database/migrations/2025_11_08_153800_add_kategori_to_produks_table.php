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
            // Tambahkan kolom kategori_id dengan foreign key jika belum ada
            if (!Schema::hasColumn('produks', 'kategori_id')) {
                $table->foreignId('kategori_id')->nullable()->after('id')
                      ->constrained('kategori_produks')
                      ->onDelete('set null');
            }
                  
            // Tambahkan kolom satuan_id dengan foreign key jika belum ada
            if (!Schema::hasColumn('produks', 'satuan_id')) {
                $table->foreignId('satuan_id')->nullable()->after('kategori_id')
                      ->constrained('satuans')
                      ->onDelete('set null');
            }
                  
            // Tambahkan kolom lain yang diperlukan jika belum ada
            if (!Schema::hasColumn('produks', 'kode_produk')) {
                $table->string('kode_produk')->unique()->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('produks', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('nama_produk');
            }
            
            if (!Schema::hasColumn('produks', 'harga_beli')) {
                $table->decimal('harga_beli', 15, 2)->default(0)->after('harga_jual');
            }
            
            if (!Schema::hasColumn('produks', 'stok')) {
                $table->integer('stok')->default(0);
            }
            
            if (!Schema::hasColumn('produks', 'stok_minimum')) {
                $table->integer('stok_minimum')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Hapus foreign key dan kolom yang ditambahkan
            $table->dropForeign(['kategori_id']);
            $table->dropForeign(['satuan_id']);
            $table->dropColumn([
                'kategori_id',
                'satuan_id',
                'kode_produk',
                'deskripsi',
                'harga_beli',
                'stok',
                'stok_minimum'
            ]);
        });
    }
};
