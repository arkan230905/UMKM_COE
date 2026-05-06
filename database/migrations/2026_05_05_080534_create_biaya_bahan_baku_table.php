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
        Schema::create('biaya_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('produk_id');
            $table->unsignedBigInteger('bahan_baku_id');
            
            $table->decimal('jumlah', 15, 4)->default(0)->comment('Jumlah bahan baku yang dibutuhkan');
            $table->string('satuan', 20)->default('KG')->comment('Satuan bahan baku');
            $table->decimal('harga_satuan', 15, 2)->default(0)->comment('Harga per satuan');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Total biaya = jumlah * harga_satuan');
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
            $table->foreign('bahan_baku_id')->references('id')->on('bahan_bakus')->onDelete('restrict');
            
            // Indexes
            $table->index(['user_id', 'produk_id'], 'biaya_bahan_baku_user_produk_index');
            $table->index('bahan_baku_id', 'biaya_bahan_baku_bahan_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_bahan_baku');
    }
};
