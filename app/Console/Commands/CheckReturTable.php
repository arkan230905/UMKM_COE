<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckReturTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-retur-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check retur table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Retur Table Structure...');
        
        $columns = Schema::getColumnListing('returs');
        
        $this->info('');
        $this->info('Columns in returs table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check sample data
        $this->info('');
        $this->info('Sample data:');
        $returs = \App\Models\Retur::limit(3)->get();
        
        foreach ($returs as $retur) {
            $this->line('  ID: ' . $retur->id . ' - Status: ' . $retur->status . ' - Total: ' . $retur->jumlah);
            if (isset($retur->type)) {
                $this->line('    Type: ' . $retur->type);
            }
            if (isset($retur->pembelian_id)) {
                $this->line('    Pembelian ID: ' . $retur->pembelian_id);
            }
            $this->line('');
        }
        
        return 0;
    }
}
