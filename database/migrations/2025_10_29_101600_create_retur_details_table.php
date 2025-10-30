<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retur_id');
            $table->unsignedBigInteger('produk_id');
            $table->unsignedBigInteger('ref_detail_id')->nullable();
            $table->decimal('qty', 18, 4);
            $table->decimal('harga_satuan_asal', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('retur_id')->references('id')->on('returs')->onDelete('cascade');
            $table->index(['retur_id']);
            $table->index(['produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_details');
    }
};
