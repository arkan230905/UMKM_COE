<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckPembelianDetailStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:pembelian-detail-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pembelian_detail table structure';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== CHECKING PEMBELIAN_DETAIL TABLE STRUCTURE ===");
        
        // Get table columns
        $columns = Schema::getColumnListing('pembelian_details');
        $this->info("Pembelian_detail table columns:");
        foreach ($columns as $column) {
            $this->info("  - {$column}");
        }
        
        // Show sample pembelian_detail data
        $this->info("\nSample pembelian_detail data:");
        $sampleDetail = \App\Models\PembelianDetail::first();
        if ($sampleDetail) {
            $this->info("First pembelian_detail record:");
            foreach ($columns as $column) {
                $value = $sampleDetail->$column ?? 'NULL';
                $this->info("  {$column}: {$value}");
            }
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
