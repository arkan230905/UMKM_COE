<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('nomor_pembelian', 50)->nullable()->after('id');
        });
        
        // Generate nomor untuk data yang sudah ada
        DB::statement("
            UPDATE pembelians 
            SET nomor_pembelian = CONCAT('PB-', DATE_FORMAT(tanggal, '%Y%m%d'), '-', LPAD(id, 4, '0'))
            WHERE nomor_pembelian IS NULL
        ");
        
        // Ubah jadi NOT NULL setelah data terisi
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('nomor_pembelian', 50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('nomor_pembelian');
        });
    }
};
