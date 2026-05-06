<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugExactJurnalQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:exact-jurnal-query {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug exact query from jurnalUmum controller';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUG EXACT JURNAL QUERY FOR USER ID: {$userId} ===");
        
        // Simulate the exact query from jurnalUmum method
        $query = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
            ->select([
                'je.*',
                'jl.id as line_id',
                'jl.debit',
                'jl.credit',
                'jl.memo as line_memo',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where('je.user_id', $userId)
            ->where(function($q) {
                $q->where('jl.debit', '!=', 0)
                  ->orWhere('jl.credit', '!=', 0);
            })
            ->orderBy('je.tanggal','asc')
            ->orderBy('je.created_at','asc')
            ->orderBy('je.id','asc')
            ->orderBy('jl.id','asc');
            
        $this->info("\n1. EXACT QUERY RESULTS:");
        $results = $query->get();
        $this->info("Total results: " . $results->count());
        
        // Group by journal entry
        $groupedResults = $results->groupBy('id');
        $this->info("Grouped entries: " . $groupedResults->count());
        
        foreach ($groupedResults as $entryId => $lines) {
            $firstLine = $lines->first();
            $this->info("\nEntry ID: {$entryId}");
            $this->info("  Date: {$firstLine->tanggal}");
            $this->info("  Ref Type: {$firstLine->ref_type}");
            $this->info("  Ref ID: {$firstLine->ref_id}");
            $this->info("  Memo: {$firstLine->memo}");
            $this->info("  Lines: " . $lines->count());
            
            foreach ($lines as $line) {
                $this->info("    - {$line->kode_akun} ({$line->nama_akun}): Debit={$line->debit}, Credit={$line->credit}");
            }
        }
        
        // Check if there are any sale entries specifically
        $this->info("\n2. SALE ENTRIES CHECK:");
        $saleEntries = $results->where('ref_type', 'sale');
        $this->info("Sale entries found: " . $saleEntries->count());
        
        if ($saleEntries->count() > 0) {
            $saleGrouped = $saleEntries->groupBy('id');
            foreach ($saleGrouped as $entryId => $lines) {
                $firstLine = $lines->first();
                $this->info("  Sale Entry ID: {$entryId}, Date: {$firstLine->tanggal}");
            }
        }
        
        // Check if the issue is with date filtering
        $this->info("\n3. DATE RANGE CHECK:");
        $today = now()->format('Y-m-d');
        $this->info("Today: {$today}");
        
        $todayEntries = $results->where('tanggal', $today);
        $this->info("Entries for today: " . $todayEntries->count());
        
        $this->info("\n4. ENTRIES BY DATE:");
        $entriesByDate = $groupedResults->groupBy(function($entry) {
            $firstLine = $entry->first();
            return $firstLine->tanggal;
        });
        
        foreach ($entriesByDate as $date => $entries) {
            $this->info("  {$date}: " . $entries->count() . " entries");
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
