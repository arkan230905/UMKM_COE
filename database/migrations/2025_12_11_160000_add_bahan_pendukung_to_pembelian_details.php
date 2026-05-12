<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_details', function (Blueprint $table) {
            // Buat bahan_baku_id nullable karena bisa jadi pembelian bahan pendukung
            $table->unsignedBigInteger('bahan_baku_id')->nullable()->change();
            
            // Tambah kolom untuk bahan pendukung
            $table->unsignedBigInteger('bahan_pendukung_id')->nullable()->after('bahan_baku_id');
            
            // Tambah tipe item untuk membedakan bahan baku vs bahan pendukung
            $table->enum('tipe_item', ['bahan_baku', 'bahan_pendukung'])->default('bahan_baku')->after('pembelian_id');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->dropColumn(['bahan_pendukung_id', 'tipe_item']);
        });
    }
};
