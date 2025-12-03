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
        Schema::create('returs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_retur')->unique();
            $table->date('tanggal');
            $table->enum('tipe_retur', ['penjualan', 'pembelian']);
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_kode')->nullable();
            $table->enum('tipe_kompensasi', ['barang', 'uang']);
            $table->decimal('total_nilai_retur', 15, 2)->default(0);
            $table->decimal('nilai_kompensasi', 15, 2)->default(0);
            $table->enum('status', ['draft', 'diproses', 'selesai'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returs');
    }
};
