<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class DebugPenjualanJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:penjualan-journal {--user=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug jurnal penjualan yang hilang';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUG JURNAL PENJUALAN FOR USER ID: {$userId} ===");
        
        // Get all penjualan for user
        $penjualans = Penjualan::where('user_id', $userId)->get();
        $this->info("\nFound {$penjualans->count()} penjualan records:");
        
        foreach ($penjualans as $penjualan) {
            $this->info("\nPenjualan #{$penjualan->id}:");
            $this->info("  Tanggal: {$penjualan->tanggal}");
            $this->info("  Total: {$penjualan->total}");
            $this->info("  Status: {$penjualan->status}");
            $this->info("  Payment Method: {$penjualan->payment_method}");
            
            // Check journal entries for this penjualan
            $journalEntries = \App\Models\JournalEntry::where('ref_type', 'sale')
                ->where('ref_id', $penjualan->id)
                ->get();
                
            $this->info("  Journal Entries: {$journalEntries->count()}");
            
            if ($journalEntries->count() > 0) {
                foreach ($journalEntries as $entry) {
                    $this->info("    Entry ID: {$entry->id}, Date: {$entry->tanggal}");
                    
                    $journalLines = \App\Models\JournalLine::where('journal_entry_id', $entry->id)->get();
                    $this->info("    Journal Lines: {$journalLines->count()}");
                    
                    foreach ($journalLines as $line) {
                        $this->info("      - COA: {$line->coa->kode_akun} ({$line->coa->nama_akun}) | Debit: {$line->debit} | Credit: {$line->credit}");
                    }
                }
            } else {
                $this->info("    ❌ NO JOURNAL ENTRIES FOUND");
                
                // Try to create journal entries manually
                $this->info("    🔄 Attempting to create journal entries...");
                try {
                    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
                    $this->info("    ✅ Journal entries created successfully");
                    
                    // Check again
                    $newEntries = \App\Models\JournalEntry::where('ref_type', 'sale')
                        ->where('ref_id', $penjualan->id)
                        ->get();
                    $this->info("    New Journal Entries: {$newEntries->count()}");
                    
                } catch (\Exception $e) {
                    $this->info("    ❌ Error creating journal entries: " . $e->getMessage());
                }
            }
        }
        
        // Summary
        $totalPenjualan = Penjualan::where('user_id', $userId)->count();
        $totalJournalEntries = \App\Models\JournalEntry::where('ref_type', 'sale')
            ->whereIn('ref_id', function($query) use ($userId) {
                $query->select('id')->from('penjualans')->where('user_id', $userId);
            })
            ->count();
            
        $this->info("\n=== SUMMARY ===");
        $this->info("Total Penjualan: {$totalPenjualan}");
        $this->info("Total Journal Entries: {$totalJournalEntries}");
        $this->info("Coverage: " . ($totalPenjualan > 0 ? round(($totalJournalEntries / $totalPenjualan) * 100, 2) : 0) . "%");
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
