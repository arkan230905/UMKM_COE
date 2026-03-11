<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field bahan_pendukung_id untuk mendukung pencatatan bahan pendukung dalam produksi
     */
    public function up(): void
    {
        Schema::table('produksi_details', function (Blueprint $table) {
            $table->foreignId('bahan_pendukung_id')->nullable()->after('bahan_baku_id')->constrained('bahan_pendukungs')->onDelete('cascade');
            
            // Ubah bahan_baku_id menjadi nullable karena bisa bahan baku atau bahan pendukung
            $table->foreignId('bahan_baku_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_pendukung_id']);
            $table->dropColumn('bahan_pendukung_id');
            
            // Kembalikan bahan_baku_id menjadi not nullable
            $table->foreignId('bahan_baku_id')->nullable(false)->change();
        });
    }
};