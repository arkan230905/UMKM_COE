<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Tambahkan kolom yang hilang
            if (!Schema::hasColumn('produks', 'foto')) {
                $table->string('foto')->nullable()->after('nama_produk');
            }
            
            if (!Schema::hasColumn('produks', 'kategori_id')) {
                $table->unsignedBigInteger('kategori_id')->nullable()->after('deskripsi');
            }
            
            if (!Schema::hasColumn('produks', 'satuan_id')) {
                $table->unsignedBigInteger('satuan_id')->nullable()->after('kategori_id');
            }
            
            if (!Schema::hasColumn('produks', 'harga_bom')) {
                $table->decimal('harga_bom', 15, 2)->nullable()->after('harga_jual');
            }
            
            if (!Schema::hasColumn('produks', 'harga_beli')) {
                $table->decimal('harga_beli', 15, 2)->nullable()->after('harga_bom');
            }
            
            if (!Schema::hasColumn('produks', 'stok_minimum')) {
                $table->integer('stok_minimum')->default(0)->after('stok');
            }
            
            if (!Schema::hasColumn('produks', 'kode_produk')) {
                $table->string('kode_produk')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $columns = ['foto', 'kategori_id', 'satuan_id', 'harga_bom', 'harga_beli', 'stok_minimum', 'kode_produk'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('produks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
