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
            $table->string('coa_pembelian_id')->nullable()->after('sub_satuan_3_nilai');
            $table->string('coa_persediaan_id')->nullable()->after('coa_pembelian_id');
            $table->string('coa_hpp_id')->nullable()->after('coa_persediaan_id');
            
            // Add foreign key constraints
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
            // Drop foreign key constraints first
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            
            // Drop columns
            $table->dropColumn(['coa_pembelian_id', 'coa_persediaan_id', 'coa_hpp_id']);
        });
    }
};
