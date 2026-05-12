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
        Schema::create('ap_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('kode_settlement')->unique();
            $table->unsignedBigInteger('pembelian_id');
            $table->date('tanggal');
            $table->decimal('total_utang', 15, 2);
            $table->decimal('dibayar_bersih', 15, 2);
            $table->decimal('sisa_utang', 15, 2)->default(0);
            $table->string('metode_pembayaran')->default('tunai');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();

            $table->foreign('pembelian_id')->references('id')->on('pembelians')->onDelete('cascade');
            $table->index(['tanggal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ap_settlements');
    }
};