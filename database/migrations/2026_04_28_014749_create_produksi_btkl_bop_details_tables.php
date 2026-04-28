<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detail BTKL per proses
        Schema::create('produksi_btkl_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->onDelete('cascade');
            $table->string('nama_proses');           // Perbumbuan, Penggorengan, Pengemasan
            $table->decimal('harga_per_unit', 15, 4); // tarif per unit
            $table->decimal('total', 15, 2);          // harga_per_unit * qty
            $table->string('coa_debit_kode');         // 1172
            $table->string('coa_debit_nama');
            $table->string('coa_kredit_kode');        // 211
            $table->string('coa_kredit_nama');
            $table->timestamps();
        });

        // Detail BOP per komponen
        Schema::create('produksi_bop_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->onDelete('cascade');
            $table->string('nama_proses');            // Perbumbuan Ayam Crispy Macdi
            $table->string('nama_komponen');          // Tepung Terigu
            $table->decimal('rate_per_unit', 15, 4);  // rate per unit
            $table->decimal('total', 15, 2);          // rate * qty
            $table->string('coa_debit_kode');         // 1173
            $table->string('coa_debit_nama');
            $table->string('coa_kredit_kode');        // 1152 / 210 / 211 / 126 dll
            $table->string('coa_kredit_nama');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_bop_details');
        Schema::dropIfExists('produksi_btkl_details');
    }
};
