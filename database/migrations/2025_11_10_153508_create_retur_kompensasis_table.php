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
        Schema::create('retur_kompensasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_id')->constrained('returs')->onDelete('cascade');
            $table->enum('tipe_kompensasi', ['barang', 'uang']);
            $table->enum('item_type', ['produk', 'bahan_baku'])->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_nama')->nullable();
            $table->decimal('qty', 15, 2)->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('nilai_kompensasi', 15, 2);
            $table->enum('metode_pembayaran', ['cash', 'transfer'])->nullable();
            $table->unsignedBigInteger('akun_id')->nullable();
            $table->date('tanggal_kompensasi');
            $table->enum('status', ['pending', 'selesai'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->foreign('akun_id')->references('id')->on('coas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_kompensasis');
    }
};
