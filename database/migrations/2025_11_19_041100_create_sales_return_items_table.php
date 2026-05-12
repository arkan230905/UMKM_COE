<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_return_id');
            $table->unsignedBigInteger('penjualan_detail_id');
            $table->unsignedBigInteger('produk_id');
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->foreign('sales_return_id')->references('id')->on('sales_returns')->onDelete('cascade');
            $table->foreign('penjualan_detail_id')->references('id')->on('penjualan_details')->onDelete('cascade');
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
