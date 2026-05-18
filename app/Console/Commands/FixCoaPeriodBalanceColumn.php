<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCoaPeriodBalanceColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:coa-period-balance-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix coa_period_balances table column name from period_id to coa_period_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking coa_period_balances table structure...');
        
        if (!Schema::hasTable('coa_period_balances')) {
            $this->error('Table coa_period_balances does not exist!');
            return Command::FAILURE;
        }
        
        // Check current columns
        $columns = Schema::getColumnListing('coa_period_balances');
        $this->info('Current columns: ' . implode(', ', $columns));
        
        $hasPeriodId = in_array('period_id', $columns);
        $hasCoaPeriodId = in_array('coa_period_id', $columns);
        
        if ($hasCoaPeriodId) {
            $this->info('✅ Column coa_period_id already exists. No action needed.');
            return Command::SUCCESS;
        }
        
        if ($hasPeriodId) {
            $this->info('Found period_id column. Renaming to coa_period_id...');
            
            try {
                // Rename column using raw SQL (more reliable than Laravel's renameColumn)
                DB::statement('ALTER TABLE coa_period_balances CHANGE period_id coa_period_id BIGINT UNSIGNED NOT NULL');
                
                $this->info('✅ Successfully renamed period_id to coa_period_id');
                return Command::SUCCESS;
                
            } catch (\Exception $e) {
                $this->error('❌ Failed to rename column: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }
        
        // If neither column exists, add coa_period_id
        $this->info('Neither period_id nor coa_period_id found. Adding coa_period_id column...');
        
        try {
            Schema::table('coa_period_balances', function ($table) {
                $table->foreignId('coa_period_id')->after('company_id')->constrained('coa_periods')->onDelete('cascade');
            });
            
            $this->info('✅ Successfully added coa_period_id column');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to add column: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}