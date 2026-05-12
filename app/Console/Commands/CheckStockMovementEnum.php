<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckStockMovementEnum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-stock-movement-enum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check stock_movement item_type enum values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Stock Movement item_type Enum Values...');
        
        try {
            $result = DB::select("SHOW COLUMNS FROM stock_movements WHERE Field = 'item_type'");
            
            if (!empty($result)) {
                $column = $result[0];
                $this->info('item_type column details:');
                $this->line('  Type: ' . $column->Type);
                
                // Extract enum values
                if (preg_match("/^enum\((.*)\)$/", $column->Type, $matches)) {
                    $enumValues = str_getcsv($matches[1], ",", "'");
                    $this->info('  Valid enum values:');
                    foreach ($enumValues as $value) {
                        $this->line('    - ' . $value);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('Error checking enum values: ' . $e->getMessage());
        }
        
        return 0;
    }
}
