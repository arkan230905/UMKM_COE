<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckCoaStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:coa-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check COA table structure';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== CHECKING COA TABLE STRUCTURE ===");
        
        // Get table columns
        $columns = Schema::getColumnListing('coas');
        $this->info("COA table columns:");
        foreach ($columns as $column) {
            $this->info("  - {$column}");
        }
        
        // Show sample COA data
        $this->info("\nSample COA data:");
        $sampleCoa = \App\Models\Coa::first();
        if ($sampleCoa) {
            $this->info("First COA record:");
            foreach ($columns as $column) {
                $value = $sampleCoa->$column ?? 'NULL';
                $this->info("  {$column}: {$value}");
            }
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
