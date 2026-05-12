<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugJurnalUmum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:jurnal-umum {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug jurnal umum untuk menemukan mengapa penjualan tidak muncul';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUG JURNAL UMUM FOR USER ID: {$userId} ===");
        
        // 1. Check all journal entries for user
        $this->info("\n1. ALL JOURNAL ENTRIES FOR USER:");
        $allEntries = DB::table('journal_entries as je')
            ->where('je.user_id', $userId)
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->select('je.*', 'jl.debit', 'jl.credit', 'jl.coa_id')
            ->orderBy('je.tanggal', 'desc')
            ->get();
            
        $this->info("Total entries: " . $allEntries->count());
        foreach ($allEntries as $entry) {
            $this->info("  Entry ID: {$entry->id}, Date: {$entry->tanggal}, Ref: {$entry->ref_type}-{$entry->ref_id}, Debit: {$entry->debit}, Credit: {$entry->credit}");
        }
        
        // 2. Check specific sale entries
        $this->info("\n2. SALE JOURNAL ENTRIES:");
        $saleEntries = DB::table('journal_entries as je')
            ->where('je.user_id', $userId)
            ->where('je.ref_type', 'sale')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
            ->select('je.*', 'jl.debit', 'jl.credit', 'coas.kode_akun', 'coas.nama_akun')
            ->orderBy('je.tanggal', 'desc')
            ->get();
            
        $this->info("Sale entries: " . $saleEntries->count());
        foreach ($saleEntries as $entry) {
            $this->info("  Entry ID: {$entry->id}, Date: {$entry->tanggal}, Account: {$entry->kode_akun} ({$entry->nama_akun}), Debit: {$entry->debit}, Credit: {$entry->credit}");
        }
        
        // 3. Simulate the exact query from jurnalUmum method
        $this->info("\n3. JURNAL UMUM QUERY SIMULATION:");
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
            
        $results = $query->get();
        $this->info("Query results: " . $results->count());
        
        // Group by ref_type
        $groupedByRefType = $results->groupBy('ref_type');
        foreach ($groupedByRefType as $refType => $entries) {
            $this->info("  {$refType}: " . $entries->count() . " entries");
        }
        
        // 4. Check if there are any filters applied
        $this->info("\n4. FILTER ANALYSIS:");
        $this->info("Current query filters:");
        $this->info("  - user_id: {$userId}");
        $this->info("  - debit/credit: not null");
        $this->info("  - No ref_type filter (shows all)");
        
        // 5. Test with sale filter
        $this->info("\n5. WITH SALE FILTER:");
        $saleQuery = clone $query;
        $saleQuery->where('je.ref_type', 'sale');
        $saleResults = $saleQuery->get();
        $this->info("Sale filter results: " . $saleResults->count());
        
        // 6. Check journal_umum table
        $this->info("\n6. JURNAL UMUM TABLE:");
        $jurnalUmum = DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->where('ju.user_id', $userId)
            ->select('ju.*', 'coas.kode_akun', 'coas.nama_akun')
            ->orderBy('ju.tanggal', 'desc')
            ->get();
            
        $this->info("Jurnal umum records: " . $jurnalUmum->count());
        foreach ($jurnalUmum as $ju) {
            $this->info("  ID: {$ju->id}, Date: {$ju->tanggal}, Type: {$ju->tipe_referensi}, Ref: {$ju->referensi}, Account: {$ju->kode_akun}, Debit: {$ju->debit}, Credit: {$ju->kredit}");
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
