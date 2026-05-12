<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_retur_penjualans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_penjualan_id')->constrained('retur_penjualans')->onDelete('cascade');
            $table->foreignId('penjualan_detail_id')->constrained('penjualan_details')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->decimal('qty_retur', 15, 4);
            $table->decimal('harga_barang', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_retur_penjualans');
    }
};
