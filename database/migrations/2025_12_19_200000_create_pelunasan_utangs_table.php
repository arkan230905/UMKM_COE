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
        Schema::create('pelunasan_utangs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('vendor_id')->default(1);
            $table->unsignedBigInteger('pembelian_id')->default(1);
            $table->decimal('total_tagihan', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('denda_bunga', 15, 2)->default(0);
            $table->decimal('dibayar_bersih', 15, 2)->default(0);
            $table->string('metode_bayar', 50)->default('tunai');
            $table->string('coa_kasbank', 10)->default('101');
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('lunas');
            $table->unsignedBigInteger('user_id')->default(1);
            $table->timestamps();
            
            $table->index('tanggal');
            $table->index('vendor_id');
            $table->index('pembelian_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelunasan_utangs');
    }
};