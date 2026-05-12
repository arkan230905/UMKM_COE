<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DebugJurnalFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:jurnal-filter {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug journal filters and user authentication';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUG JURNAL FILTER FOR USER ID: {$userId} ===");
        
        // 1. Check if user exists
        $user = User::find($userId);
        if ($user) {
            $this->info("User found: {$user->name} (ID: {$user->id})");
        } else {
            $this->info("User ID {$userId} NOT FOUND");
            return Command::FAILURE;
        }
        
        // 2. Check all journal entries for this user without any filters
        $this->info("\n2. ALL JOURNAL ENTRIES (NO FILTERS):");
        $allEntries = DB::table('journal_entries')
            ->where('user_id', $userId)
            ->get();
            
        $this->info("Total entries: " . $allEntries->count());
        
        // Group by ref_type
        $byRefType = $allEntries->groupBy('ref_type');
        foreach ($byRefType as $refType => $entries) {
            $this->info("  {$refType}: " . $entries->count() . " entries");
        }
        
        // 3. Check sale entries specifically
        $this->info("\n3. SALE ENTRIES SPECIFICALLY:");
        $saleEntries = DB::table('journal_entries')
            ->where('user_id', $userId)
            ->where('ref_type', 'sale')
            ->get();
            
        $this->info("Sale entries: " . $saleEntries->count());
        foreach ($saleEntries as $entry) {
            $this->info("  ID: {$entry->id}, Date: {$entry->tanggal}, Ref: {$entry->ref_id}, Memo: {$entry->memo}");
        }
        
        // 4. Check journal lines for sale entries
        if ($saleEntries->count() > 0) {
            $this->info("\n4. SALE JOURNAL LINES:");
            $saleEntryIds = $saleEntries->pluck('id');
            $saleLines = DB::table('journal_lines')
                ->whereIn('journal_entry_id', $saleEntryIds)
                ->leftJoin('coas', 'coas.id', '=', 'journal_lines.coa_id')
                ->select('journal_lines.*', 'coas.kode_akun', 'coas.nama_akun')
                ->get();
                
            $this->info("Sale journal lines: " . $saleLines->count());
            foreach ($saleLines as $line) {
                $this->info("  Entry ID: {$line->journal_entry_id}, Account: {$line->kode_akun} ({$line->nama_akun}), Debit: {$line->debit}, Credit: {$line->credit}");
            }
        }
        
        // 5. Simulate the exact query with joins
        $this->info("\n5. EXACT QUERY SIMULATION:");
        $query = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
            ->select('je.*', 'jl.debit', 'jl.credit', 'coas.kode_akun')
            ->where('je.user_id', $userId)
            ->where(function($q) {
                $q->where('jl.debit', '!=', 0)
                  ->orWhere('jl.credit', '!=', 0);
            });
            
        $queryResults = $query->get();
        $this->info("Query results: " . $queryResults->count());
        
        $saleResults = $queryResults->where('ref_type', 'sale');
        $this->info("Sale results: " . $saleResults->count());
        
        // 6. Check if there's an issue with the join
        $this->info("\n6. JOIN VERIFICATION:");
        $entriesWithoutLines = DB::table('journal_entries')
            ->where('user_id', $userId)
            ->where('ref_type', 'sale')
            ->leftJoin('journal_lines', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereNull('journal_lines.id')
            ->count();
            
        $this->info("Sale entries without lines: " . $entriesWithoutLines);
        
        // 7. Check if the issue is with coa_id
        $this->info("\n7. COA VERIFICATION:");
        $linesWithNullCoa = DB::table('journal_lines')
            ->whereIn('journal_entry_id', $saleEntryIds)
            ->whereNull('coa_id')
            ->count();
            
        $this->info("Sale lines with null coa_id: " . $linesWithNullCoa);
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
