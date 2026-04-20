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
            // Tambahkan kolom coa_persediaan_id jika belum ada
            if (!Schema::hasColumn('produks', 'coa_persediaan_id')) {
                $table->unsignedBigInteger('coa_persediaan_id')->nullable()->after('btkl_per_unit');
                
                // Tambahkan foreign key constraint
                $table->foreign('coa_persediaan_id')
                      ->references('id')
                      ->on('coas')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['coa_persediaan_id']);
            
            // Hapus kolom
            $table->dropColumn('coa_persediaan_id');
        });
    }
};
