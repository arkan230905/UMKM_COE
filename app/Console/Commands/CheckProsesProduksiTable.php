<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckProsesProduksiTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-proses-produksi-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check proses_produksis table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Proses Produksi Table Structure...');
        
        $columns = Schema::getColumnListing('proses_produksis');
        
        $this->info('');
        $this->info('Columns in proses_produksis table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check if status column exists
        if (in_array('status', $columns)) {
            $this->info('');
            $this->info('Status column found!');
        } else {
            $this->info('');
            $this->error('Status column NOT found!');
        }
        
        return 0;
    }
}
