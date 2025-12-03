<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('nomor_penjualan', 50)->nullable()->after('id');
        });
        
        // Generate nomor untuk data yang sudah ada
        DB::statement("
            UPDATE penjualans 
            SET nomor_penjualan = CONCAT('PJ-', DATE_FORMAT(tanggal_penjualan, '%Y%m%d'), '-', LPAD(id, 4, '0'))
            WHERE nomor_penjualan IS NULL
        ");
        
        // Ubah jadi NOT NULL setelah data terisi
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('nomor_penjualan', 50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn('nomor_penjualan');
        });
    }
};
