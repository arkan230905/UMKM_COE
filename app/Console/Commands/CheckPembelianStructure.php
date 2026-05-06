<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckPembelianStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:pembelian-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pembelian table structure';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== CHECKING PEMBELIAN TABLE STRUCTURE ===");
        
        // Get table columns
        $columns = Schema::getColumnListing('pembelians');
        $this->info("Pembelian table columns:");
        foreach ($columns as $column) {
            $this->info("  - {$column}");
        }
        
        // Show sample pembelian data
        $this->info("\nSample pembelian data:");
        $samplePembelian = \App\Models\Pembelian::first();
        if ($samplePembelian) {
            $this->info("First pembelian record:");
            foreach ($columns as $column) {
                $value = $samplePembelian->$column ?? 'NULL';
                $this->info("  {$column}: {$value}");
            }
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
