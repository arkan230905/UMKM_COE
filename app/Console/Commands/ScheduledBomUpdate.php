<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ScheduledBomUpdate extends Command
{
    protected $signature = 'bom:scheduled-update';
    protected $description = 'Scheduled BOM update from stock report calculations (for automation)';

    public function handle()
    {
        $this->info('🕐 Running scheduled BOM update...');
        
        // Run the main update command
        $exitCode = Artisan::call('bom:update-from-stock-report');
        
        if ($exitCode === 0) {
            $this->info('✅ Scheduled BOM update completed successfully');
        } else {
            $this->error('❌ Scheduled BOM update failed');
        }
        
        return $exitCode;
    }
}