<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\JournalEntry;

class VerifyPurchaseFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:purchase-fix {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify purchase data consistency after fixes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== VERIFYING PURCHASE DATA CONSISTENCY FOR USER ID: {$userId} ===");
        
        // Get all pembelian for this user
        $pembelians = Pembelian::where('user_id', $userId)->get();
        $this->info("\n📋 PEMBELIAN DATA (Halaman Pembelian):");
        
        foreach ($pembelians as $pembelian) {
            $this->info("📋 Pembelian ID: {$pembelian->id}");
            $this->info("  Nomor: {$pembelian->nomor_pembelian}");
            $this->info("  Total: Rp " . number_format($pembelian->total ?? 0, 2));
            $this->info("  Vendor: " . ($pembelian->vendor->nama_vendor ?? 'N/A'));
        }
        
        // Get all purchase journal entries
        $this->info("\n📊 JOURNAL ENTRIES (Halaman Jurnal Umum):");
        $purchaseEntries = JournalEntry::where('ref_type', 'purchase')
            ->where('user_id', $userId)
            ->get();
            
        foreach ($purchaseEntries as $entry) {
            $this->info("📊 Journal Entry ID: {$entry->id}");
            $this->info("  Ref ID: {$entry->ref_id}");
            $this->info("  Memo: {$entry->memo}");
            
            // Calculate total from journal lines
            $journalTotal = 0;
            foreach ($entry->lines as $line) {
                $journalTotal += $line->debit;
            }
            $this->info("  Journal Total: Rp " . number_format($journalTotal, 2));
        }
        
        // Compare totals
        $this->info("\n🔍 COMPARISON:");
        foreach ($pembelians as $pembelian) {
            $journalEntry = $purchaseEntries->where('ref_id', $pembelian->id)->first();
            
            if ($journalEntry) {
                $journalTotal = $journalEntry->lines->sum('debit');
                $pembelianTotal = $pembelian->total ?? 0;
                
                if ($journalTotal == $pembelianTotal) {
                    $this->info("✅ Pembelian ID {$pembelian->id}: Sesuai (Rp " . number_format($pembelianTotal, 2) . ")");
                } else {
                    $this->info("❌ Pembelian ID {$pembelian->id}: Tidak sesuai");
                    $this->info("   Pembelian: Rp " . number_format($pembelianTotal, 2));
                    $this->info("   Journal: Rp " . number_format($journalTotal, 2));
                }
            } else {
                $this->info("❌ Pembelian ID {$pembelian->id}: Tidak ada journal entry");
            }
        }
        
        $this->info("\n=== VERIFICATION COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
