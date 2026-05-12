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
        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('item_id');
            $table->enum('item_type', ['bahan_baku', 'bahan_pendukung']);
            $table->decimal('qty_masuk', 15, 4)->nullable();
            $table->decimal('qty_keluar', 15, 4)->nullable();
            $table->string('keterangan');
            $table->string('ref_type')->nullable(); // pembelian, retur, produksi, adjustment
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->timestamps();
            
            $table->index(['item_id', 'item_type', 'tanggal']);
            $table->index(['ref_type', 'ref_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_stok');
    }
};