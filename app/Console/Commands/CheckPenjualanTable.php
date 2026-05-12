<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckPenjualanTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-penjualan-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check penjualan table structure and data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Penjualan Table Structure...');
        
        $columns = Schema::getColumnListing('penjualans');
        
        $this->info('');
        $this->info('Columns in penjualans table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check sample data
        $this->info('');
        $this->info('Sample penjualan data:');
        $penjualans = \App\Models\Penjualan::limit(3)->get();
        
        foreach ($penjualans as $penjualan) {
            $this->line('  ID: ' . $penjualan->id . ' - No: ' . ($penjualan->no_penjualan ?? 'NULL'));
            $this->line('    Total: ' . $penjualan->total . ' - Customer ID: ' . $penjualan->customer_id);
        }
        
        return 0;
    }
}
