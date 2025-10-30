<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_settlements', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('pembelian_id');
            $table->decimal('total_tagihan', 18, 2);
            $table->decimal('diskon', 18, 2)->default(0);
            $table->decimal('denda_bunga', 18, 2)->default(0);
            $table->decimal('dibayar_bersih', 18, 2);
            $table->string('metode_bayar')->default('cash'); // cash/bank
            $table->string('coa_kasbank')->default('101');
            $table->string('keterangan')->nullable();
            $table->string('status')->default('lunas'); // lunas/parsial
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_settlements');
    }
};
