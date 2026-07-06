<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix foreign key constraints on asets table to reference coas table instead of accounts table
     */
    public function up(): void
    {
        // Drop existing foreign keys that reference accounts table (if exists)
        $this->dropForeignKeyIfExists('asets', 'asets_asset_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_accum_depr_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_expense_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_depr_expense_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_depr_accum_coa_id_foreign');
        
        // Add new foreign keys that reference coas table
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'asset_coa_id')) {
                $table->foreign('asset_coa_id')->references('id')->on('coas')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'accum_depr_coa_id')) {
                $table->foreign('accum_depr_coa_id')->references('id')->on('coas')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'expense_coa_id')) {
                $table->foreign('expense_coa_id')->references('id')->on('coas')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'coa_id')) {
                $table->foreign('coa_id')->references('id')->on('coas')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'depr_expense_coa_id')) {
                $table->foreign('depr_expense_coa_id')->references('id')->on('coas')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'depr_accum_coa_id')) {
                $table->foreign('depr_accum_coa_id')->references('id')->on('coas')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys that reference coas table
        $this->dropForeignKeyIfExists('asets', 'asets_asset_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_accum_depr_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_expense_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_depr_expense_coa_id_foreign');
        $this->dropForeignKeyIfExists('asets', 'asets_depr_accum_coa_id_foreign');
        
        // Restore foreign keys that reference accounts table
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'asset_coa_id')) {
                $table->foreign('asset_coa_id')->references('id')->on('accounts')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'accum_depr_coa_id')) {
                $table->foreign('accum_depr_coa_id')->references('id')->on('accounts')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'expense_coa_id')) {
                $table->foreign('expense_coa_id')->references('id')->on('accounts')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'coa_id')) {
                $table->foreign('coa_id')->references('id')->on('accounts')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'depr_expense_coa_id')) {
                $table->foreign('depr_expense_coa_id')->references('id')->on('accounts')->onDelete('set null');
            }
            
            if (Schema::hasColumn('asets', 'depr_accum_coa_id')) {
                $table->foreign('depr_accum_coa_id')->references('id')->on('accounts')->onDelete('set null');
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
