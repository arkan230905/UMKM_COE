<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Add asset COA field if it doesn't exist
            if (!Schema::hasColumn('asets', 'asset_coa_id')) {
                $table->foreignId('asset_coa_id')->nullable()->after('coa_id')->constrained('coas')->onDelete('set null');
            }
            
            // Add accumulated depreciation COA field if it doesn't exist
            if (!Schema::hasColumn('asets', 'accum_depr_coa_id')) {
                $table->foreignId('accum_depr_coa_id')->nullable()->after('asset_coa_id')->constrained('coas')->onDelete('set null');
            }
            
            // Add expense COA field if it doesn't exist
            if (!Schema::hasColumn('asets', 'expense_coa_id')) {
                $table->foreignId('expense_coa_id')->nullable()->after('accum_depr_coa_id')->constrained('coas')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'expense_coa_id')) {
                $table->dropForeign(['expense_coa_id']);
                $table->dropColumn('expense_coa_id');
            }
            
            if (Schema::hasColumn('asets', 'accum_depr_coa_id')) {
                $table->dropForeign(['accum_depr_coa_id']);
                $table->dropColumn('accum_depr_coa_id');
            }
            
            if (Schema::hasColumn('asets', 'asset_coa_id')) {
                $table->dropForeign(['asset_coa_id']);
                $table->dropColumn('asset_coa_id');
            }
        });
    }
};