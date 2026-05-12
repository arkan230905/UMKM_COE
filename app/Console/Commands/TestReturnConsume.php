<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StockService;

class TestReturnConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-return-consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test returnConsume method';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing returnConsume method...');
        
        $stock = new StockService();
        
        try {
            // Test return consume for material
            $result = $stock->returnConsume('material', 1, 5, 'pcs', 'return', 999, '2025-12-12');
            $this->info('returnConsume for material successful: ' . $result);
        } catch (\Exception $e) {
            $this->error('Error testing returnConsume for material: ' . $e->getMessage());
        }
        
        return 0;
    }
}
