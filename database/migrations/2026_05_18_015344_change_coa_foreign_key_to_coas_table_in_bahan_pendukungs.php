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
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Add new foreign keys referencing coas table
            $table->foreign('coa_pembelian_id')->references('kode_akun')->on('coas')->onDelete('set null');
            $table->foreign('coa_persediaan_id')->references('kode_akun')->on('coas')->onDelete('set null');
            $table->foreign('coa_hpp_id')->references('kode_akun')->on('coas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            // Drop foreign keys to coas table
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Restore foreign keys to accounts table
            $table->foreign('coa_pembelian_id')->references('kode_akun')->on('accounts')->onDelete('set null');
            $table->foreign('coa_persediaan_id')->references('kode_akun')->on('accounts')->onDelete('set null');
            $table->foreign('coa_hpp_id')->references('kode_akun')->on('accounts')->onDelete('set null');
        });
    }
};
