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
        // Fix foreign key constraints for bahan_bakus table
        Schema::table('bahan_bakus', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Add new foreign key constraints with ON UPDATE CASCADE
            $table->foreign('coa_pembelian_id')->references('kode_akun')->on('coas')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('coa_persediaan_id')->references('kode_akun')->on('coas')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('coa_hpp_id')->references('kode_akun')->on('coas')->onDelete('set null')->onUpdate('cascade');
        });
        
        // Fix foreign key constraints for bahan_pendukungs table
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Add new foreign key constraints with ON UPDATE CASCADE
            $table->foreign('coa_pembelian_id')->references('kode_akun')->on('coas')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('coa_persediaan_id')->references('kode_akun')->on('coas')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('coa_hpp_id')->references('kode_akun')->on('coas')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert bahan_bakus foreign key constraints
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Add back original constraints without ON UPDATE CASCADE
            $table->foreign('coa_pembelian_id')->references('kode_akun')->on('coas')->onDelete('set null');
            $table->foreign('coa_persediaan_id')->references('kode_akun')->on('coas')->onDelete('set null');
            $table->foreign('coa_hpp_id')->references('kode_akun')->on('coas')->onDelete('set null');
        });
        
        // Revert bahan_pendukungs foreign key constraints
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Add back original constraints without ON UPDATE CASCADE
            $table->foreign('coa_pembelian_id')->references('kode_akun')->on('coas')->onDelete('set null');
            $table->foreign('coa_persediaan_id')->references('kode_akun')->on('coas')->onDelete('set null');
            $table->foreign('coa_hpp_id')->references('kode_akun')->on('coas')->onDelete('set null');
        });
    }
};
