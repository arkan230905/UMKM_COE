<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckPembelianTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-pembelian-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pembelian table structure and data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Pembelian Table Structure...');
        
        $columns = Schema::getColumnListing('pembelians');
        
        $this->info('');
        $this->info('Columns in pembelians table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check sample data
        $this->info('');
        $this->info('Sample pembelian data:');
        $pembelians = \App\Models\Pembelian::limit(3)->get();
        
        foreach ($pembelians as $pembelian) {
            $this->line('  ID: ' . $pembelian->id . ' - No: ' . ($pembelian->no_pembelian ?? 'NULL'));
            $this->line('    Total: ' . $pembelian->total . ' - Vendor ID: ' . $pembelian->vendor_id);
        }
        
        return 0;
    }
}
