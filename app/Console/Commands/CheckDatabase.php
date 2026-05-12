<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database structure and tables';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== DATABASE STRUCTURE CHECK ===');
        
        // Get all tables
        $tables = Schema::getTableListing();
        $this->info("\nTotal tables: " . count($tables));
        
        // Look for penjualan related tables
        $penjualanTables = array_filter($tables, function($table) {
            return strpos($table, 'penjualan') !== false;
        });
        
        $this->info("\nPenjualan related tables:");
        foreach ($penjualanTables as $table) {
            $this->info("  - {$table}");
        }
        
        // Look for journal related tables
        $journalTables = array_filter($tables, function($table) {
            return strpos($table, 'journal') !== false;
        });
        
        $this->info("\nJournal related tables:");
        foreach ($journalTables as $table) {
            $this->info("  - {$table}");
        }
        
        // Check specific tables
        $criticalTables = ['penjualans', 'journal_entries', 'journal_lines', 'coas'];
        $this->info("\nCritical tables status:");
        foreach ($criticalTables as $table) {
            $exists = Schema::hasTable($table);
            $this->info("  - {$table}: " . ($exists ? '✓ EXISTS' : '✗ MISSING'));
        }
        
        // If penjualans table doesn't exist, try to run migration
        if (!Schema::hasTable('penjualans')) {
            $this->info("\n❌ Penjualans table is missing!");
            $this->info("🔄 Attempting to run penjualan migration...");
            
            try {
                // Run specific migration
                \Artisan::call('migrate', [
                    '--path' => 'database/migrations/2025_10_23_012604_create_penjualans_table.php',
                    '--force' => true
                ]);
                
                $this->info("✅ Migration completed successfully");
                
                // Check again
                if (Schema::hasTable('penjualans')) {
                    $this->info("✅ Penjualans table created successfully");
                } else {
                    $this->info("❌ Penjualans table still missing");
                }
            } catch (\Exception $e) {
                $this->info("❌ Migration failed: " . $e->getMessage());
            }
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
