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
        // Add foreign key constraints on pembayaran_beban table
        // The table should reference coas table, not accounts table
        
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            // Check if foreign keys already exist before adding
            $fks = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'pembayaran_beban' AND REFERENCED_TABLE_NAME IS NOT NULL");
            
            if (empty($fks)) {
                // Add new foreign keys that reference coas table
                $table->foreign('akun_beban_id')
                    ->references('id')
                    ->on('coas')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
                
                $table->foreign('akun_kas_id')
                    ->references('id')
                    ->on('coas')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            try {
                $table->dropForeign(['akun_beban_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            try {
                $table->dropForeign(['akun_kas_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
        });
    }
};
