<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the incorrect foreign key constraint (if exists)
        $this->dropForeignKeyIfExists('produksis', 'produksis_coa_persediaan_barang_jadi_id_foreign');
        
        // Clean up invalid data: set coa_persediaan_barang_jadi_id to NULL if it doesn't exist in coas table
        DB::statement("
            UPDATE produksis 
            SET coa_persediaan_barang_jadi_id = NULL 
            WHERE coa_persediaan_barang_jadi_id IS NOT NULL 
            AND coa_persediaan_barang_jadi_id NOT IN (SELECT id FROM coas)
        ");
        
        // Add the correct foreign key constraint referencing coas table
        Schema::table('produksis', function (Blueprint $table) {
            if (Schema::hasColumn('produksis', 'coa_persediaan_barang_jadi_id')) {
                $table->foreign('coa_persediaan_barang_jadi_id')
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
        // Drop foreign key that references coas
        $this->dropForeignKeyIfExists('produksis', 'produksis_coa_persediaan_barang_jadi_id_foreign');
        
        // Restore foreign key that references accounts
        Schema::table('produksis', function (Blueprint $table) {
            if (Schema::hasColumn('produksis', 'coa_persediaan_barang_jadi_id')) {
                $table->foreign('coa_persediaan_barang_jadi_id')
                      ->references('id')
                      ->on('accounts')
                      ->onDelete('set null');
            }
        });
    }
    
    /**
     * Helper method to drop foreign key if exists
     */
    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        // Check if foreign key exists
        $exists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$databaseName, $table, $foreignKey]);
        
        // Drop if exists
        if (!empty($exists)) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKey}`");
        }
    }
};
