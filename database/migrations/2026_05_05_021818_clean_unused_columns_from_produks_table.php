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
            // Drop foreign keys first if they exist
            $table->dropForeign(['kategori_produk_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
        });
        
        Schema::table('produks', function (Blueprint $table) {
            // Drop unused columns that are always NULL and not used in the application
            $table->dropColumn([
                'kategori_produk_id',     // Not used, replaced by kategori_id
                'coa_persediaan_id',       // Not used in current implementation
                'coa_hpp_id',              // Not used in current implementation  
                'deskripsi_catalog',       // Not used
                'harga_bom',               // Not used, calculated differently
                'biaya_bahan',             // Not used
                'harga_beli',              // Not used
                'harga_pokok',             // Not used
                'hpp',                     // Not used, calculated differently
                'bopb_method',             // Not used
                'bopb_rate',               // Not used
                'labor_hours_per_unit',    // Not used
                'btkl_default',            // Not used
                'bop_default',             // Not used
                'show_in_catalog'          // Not used
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Restore the dropped columns
            $table->unsignedBigInteger('kategori_produk_id')->nullable();
            $table->unsignedBigInteger('coa_persediaan_id')->nullable();
            $table->unsignedBigInteger('coa_hpp_id')->nullable();
            $table->text('deskripsi_catalog')->nullable();
            $table->decimal('harga_bom', 15, 2)->default(0);
            $table->decimal('biaya_bahan', 15, 2)->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_pokok', 15, 2)->default(0);
            $table->decimal('hpp', 15, 2)->default(0);
            $table->string('bopb_method', 50)->nullable();
            $table->decimal('bopb_rate', 8, 2)->default(0);
            $table->decimal('labor_hours_per_unit', 8, 2)->default(0);
            $table->decimal('btkl_default', 15, 2)->default(0);
            $table->decimal('bop_default', 15, 2)->default(0);
            $table->boolean('show_in_catalog')->default(false);
        });
    }
};
