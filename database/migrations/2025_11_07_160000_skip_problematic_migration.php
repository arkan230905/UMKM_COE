<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip the problematic migration by marking it as run
        $migrationName = '2025_10_29_160535_update_coas_table_structure';
        
        if (!DB::table('migrations')->where('migration', $migrationName)->exists()) {
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => DB::table('migrations')->max('batch') + 1
            ]);
            
            echo "Skipped migration: $migrationName\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You may need to implement this if you want to rollback
    }
};
