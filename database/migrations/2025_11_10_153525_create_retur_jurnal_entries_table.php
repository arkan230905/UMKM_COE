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
        Schema::create('retur_jurnal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_id')->constrained('returs')->onDelete('cascade');
            $table->foreignId('jurnal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->enum('tipe_jurnal', ['penerimaan_barang', 'pengiriman_barang', 'kompensasi_barang', 'kompensasi_uang']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_jurnal_entries');
    }
};
