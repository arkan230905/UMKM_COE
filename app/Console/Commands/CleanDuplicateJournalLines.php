<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateJournalLines extends Command
{
    protected $signature = 'clean:duplicate-journal-lines {--force : Force the operation}';
    protected $description = 'Clean duplicate journal_lines for adjustment_depreciation';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete duplicate journal lines?')) {
            $this->info('Cancelled');
            return 0;
        }
        
        // Delete all adjustment_depreciation entries
        $deleted = DB::table('journal_lines')
            ->where('tipe_referensi', 'adjustment_depreciation')
            ->delete();
        
        $this->info("Deleted {$deleted} records");
        
        return 0;
    }
}
