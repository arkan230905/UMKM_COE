<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks');
            $table->decimal('jumlah', 15, 4)->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0); // 0-100
            $table->decimal('diskon_nominal', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_details');
    }
};
