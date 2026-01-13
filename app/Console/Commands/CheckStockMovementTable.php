<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckStockMovementTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-stock-movement-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check stock_movement table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Stock Movement Table Structure...');
        
        $columns = Schema::getColumnListing('stock_movements');
        
        $this->info('');
        $this->info('Columns in stock_movements table:');
        foreach ($columns as $column) {
            $this->line('  - ' . $column);
        }
        
        // Check column details
        $this->info('');
        $this->info('Column details:');
        $columnTypes = Schema::getColumnType('stock_movements', 'item_type');
        $this->line('  item_type: ' . $columnTypes);
        
        return 0;
    }
}
