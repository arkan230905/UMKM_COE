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
        // Drop foreign key constraints from asets table
        // These constraints cause issues when COA IDs don't exist in accounts table
        
        DB::statement('ALTER TABLE asets DROP FOREIGN KEY IF EXISTS asets_asset_coa_id_foreign');
        DB::statement('ALTER TABLE asets DROP FOREIGN KEY IF EXISTS asets_accum_depr_coa_id_foreign');
        DB::statement('ALTER TABLE asets DROP FOREIGN KEY IF EXISTS asets_expense_coa_id_foreign');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - we're just removing constraints
    }
};
