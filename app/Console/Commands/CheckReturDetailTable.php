<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckReturDetailTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-retur-detail-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check retur_details table structure and data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Retur Detail Table Structure...');
        
        $columns = Schema::getColumnListing('retur_details');
        
        $this->info('');
        $this->info('Columns in retur_details table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check sample data
        $this->info('');
        $this->info('Sample retur detail data:');
        $details = \App\Models\ReturDetail::limit(3)->get();
        
        foreach ($details as $detail) {
            $this->line('  ID: ' . $detail->id . ' - Retur ID: ' . $detail->retur_id);
            $this->line('    Produk ID: ' . $detail->produk_id);
            $this->line('    Qty: ' . $detail->qty);
            $this->line('    Harga: ' . $detail->harga_satuan_asal);
        }
        
        return 0;
    }
}
