<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckReturDetailStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-retur-detail-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check retur_details table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Retur Details Table Structure...');
        
        $columns = Schema::getColumnListing('retur_details');
        
        $this->info('');
        $this->info('Columns in retur_details table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check pembelian details structure
        $this->info('');
        $this->info('Columns in pembelian_details table:');
        $pembelianColumns = Schema::getColumnListing('pembelian_details');
        foreach ($pembelianColumns as $column) {
            $this->line('  - ' . $column);
        }
        
        return 0;
    }
}
