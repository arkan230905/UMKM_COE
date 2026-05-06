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
        Schema::table('produks', function (Blueprint $table) {
            // Add coa_persediaan_id column to link product to specific persediaan COA
            // This allows each product to have its own persediaan account (e.g., 1161 for Jasuke)
            // instead of using the parent account (116)
            $table->string('coa_persediaan_id', 20)->nullable()->after('harga_jual');
            
            // Add foreign key constraint to ensure COA exists
            $table->foreign('coa_persediaan_id')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['coa_persediaan_id']);
            
            // Drop column
            $table->dropColumn('coa_persediaan_id');
        });
    }
};
