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
        // Check if account_id column exists before trying to rename
        if (Schema::hasColumn('journal_lines', 'account_id')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                // Rename account_id to coa_id to match the model
                $table->renameColumn('account_id', 'coa_id');
            });
        } else {
            // Column already renamed or doesn't exist, skip
            // This migration has already been applied or is not needed
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if coa_id column exists before trying to rename back
        if (Schema::hasColumn('journal_lines', 'coa_id')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                // Rename back to account_id
                $table->renameColumn('coa_id', 'account_id');
            });
        }
    }
};
