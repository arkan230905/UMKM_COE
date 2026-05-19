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
        if (Schema::hasTable('coa_period_balances')) {
            // Check if the old column exists
            if (Schema::hasColumn('coa_period_balances', 'coa_period_id')) {
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    // Drop the unique constraint if it exists
                    $table->dropUnique('unique_balance_per_company_period');
                    
                    // Drop the foreign key constraint first
                    $table->dropForeign(['coa_period_id']);
                    
                    // Drop the index
                    $table->dropIndex(['coa_period_id']);
                });
                
                // Rename the column
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    $table->renameColumn('coa_period_id', 'period_id');
                });
                
                // Re-add the constraints with the new column name
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    $table->foreign('period_id')->references('id')->on('coa_periods')->onDelete('cascade');
                    $table->index('period_id');
                    $table->unique(['company_id', 'period_id', 'kode_akun'], 'unique_balance_per_company_period');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('coa_period_balances')) {
            if (Schema::hasColumn('coa_period_balances', 'period_id')) {
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    // Drop the new constraints
                    $table->dropUnique('unique_balance_per_company_period');
                    $table->dropForeign(['period_id']);
                    $table->dropIndex(['period_id']);
                });
                
                // Rename back
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    $table->renameColumn('period_id', 'coa_period_id');
                });
                
                // Re-add the old constraints
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    $table->foreign('coa_period_id')->references('id')->on('coa_periods')->onDelete('cascade');
                    $table->index('coa_period_id');
                    $table->unique(['company_id', 'coa_period_id', 'kode_akun'], 'unique_balance_per_company_period');
                });
            }
        }
    }
};
