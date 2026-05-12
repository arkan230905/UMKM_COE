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
        Schema::create('pembelian_detail_konversi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembelian_detail_id');
            $table->unsignedBigInteger('satuan_id'); // ID satuan sub (misal: siung, ons, gram)
            $table->string('satuan_nama'); // Nama satuan untuk backup
            $table->decimal('jumlah_konversi', 15, 4); // Jumlah hasil konversi manual
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->timestamps();
            
            $table->foreign('pembelian_detail_id')->references('id')->on('pembelian_details')->onDelete('cascade');
            $table->foreign('satuan_id')->references('id')->on('satuans')->onDelete('cascade');
            
            $table->index(['pembelian_detail_id', 'satuan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_detail_konversi');
    }
};