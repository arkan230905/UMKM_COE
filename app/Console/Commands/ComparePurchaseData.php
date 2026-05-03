<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class ComparePurchaseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:purchase-data {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare purchase data between pembelian page and jurnal umum';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== COMPARING PURCHASE DATA FOR USER ID: {$userId} ===");
        
        // Get all pembelian for this user
        $pembelians = Pembelian::where('user_id', $userId)->get();
        $this->info("\n📋 DATA PEMBELIAN (Halaman Pembelian):");
        $this->info("Total pembelian: " . $pembelians->count());
        
        foreach ($pembelians as $pembelian) {
            $this->info("\n📋 Pembelian ID: {$pembelian->id}");
            $this->info("  Nomor: {$pembelian->nomor_pembelian}");
            $this->info("  Tanggal: {$pembelian->tanggal}");
            $this->info("  Total: Rp " . number_format($pembelian->total ?? 0, 2));
            $this->info("  Vendor: " . ($pembelian->vendor->nama_vendor ?? 'N/A'));
            $this->info("  Status: {$pembelian->status}");
            $this->info("  Payment Method: {$pembelian->payment_method}");
            
            // Check details
            $details = $pembelian->details ?? collect([]);
            $this->info("  Details: " . $details->count() . " items");
            
            foreach ($details as $detail) {
                $this->info("    - {$detail->jumlah} x {$detail->harga_satuan} = {$detail->subtotal}");
            }
        }
        
        // Get all purchase journal entries for this user
        $this->info("\n📊 JOURNAL ENTRIES (Halaman Jurnal Umum):");
        $purchaseEntries = JournalEntry::where('ref_type', 'purchase')
            ->where('user_id', $userId)
            ->with('lines.coa')
            ->get();
            
        $this->info("Total purchase journal entries: " . $purchaseEntries->count());
        
        foreach ($purchaseEntries as $entry) {
            $this->info("\n📊 Journal Entry ID: {$entry->id}");
            $this->info("  Ref ID: {$entry->ref_id}");
            $this->info("  Date: {$entry->tanggal}");
            $this->info("  Memo: {$entry->memo}");
            $this->info("  User ID: {$entry->user_id}");
            
            $this->info("  Lines: " . $entry->lines->count());
            foreach ($entry->lines as $line) {
                $this->info("    - {$line->coa->kode_akun} ({$line->coa->nama_akun}): Debit={$line->debit}, Credit={$line->credit}");
            }
        }
        
        // Compare and identify discrepancies
        $this->info("\n🔍 ANALISIS KETIDAKSESUAIAN:");
        
        // 1. Pembelian tanpa journal entries
        $pembelianIdsWithoutJournals = [];
        foreach ($pembelians as $pembelian) {
            $hasJournal = JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->where('user_id', $userId)
                ->exists();
                
            if (!$hasJournal) {
                $pembelianIdsWithoutJournals[] = $pembelian->id;
            }
        }
        
        if (!empty($pembelianIdsWithoutJournals)) {
            $this->info("❌ Pembelian tanpa journal entries: " . count($pembelianIdsWithoutJournals));
            foreach ($pembelianIdsWithoutJournals as $id) {
                $pembelian = $pembelians->where('id', $id)->first();
                $this->info("  - Pembelian ID {$id}: {$pembelian->nomor_pembelian} (Total: Rp " . number_format($pembelian->total ?? 0, 2) . ")");
            }
        } else {
            $this->info("✅ Semua pembelian memiliki journal entries");
        }
        
        // 2. Journal entries tanpa pembelian yang sesuai
        $journalRefIds = $purchaseEntries->pluck('ref_id')->toArray();
        $pembelianIds = $pembelians->pluck('id')->toArray();
        
        $orphanJournalIds = array_diff($journalRefIds, $pembelianIds);
        if (!empty($orphanJournalIds)) {
            $this->info("❌ Journal entries tanpa pembelian yang sesuai: " . count($orphanJournalIds));
            foreach ($orphanJournalIds as $id) {
                $entry = $purchaseEntries->where('ref_id', $id)->first();
                $this->info("  - Journal Entry Ref ID {$id}: {$entry->memo}");
            }
        } else {
            $this->info("✅ Semua journal entries memiliki pembelian yang sesuai");
        }
        
        // 3. Total amount comparison
        $this->info("\n💰 PERBANDINGAN TOTAL AMOUNT:");
        foreach ($pembelians as $pembelian) {
            $journalEntry = $purchaseEntries->where('ref_id', $pembelian->id)->first();
            
            if ($journalEntry) {
                // Calculate total from journal lines
                $journalTotal = 0;
                foreach ($journalEntry->lines as $line) {
                    $journalTotal += $line->debit; // Sum of debits should equal pembelian total
                }
                
                $pembelianTotal = $pembelian->total ?? 0;
                
                if ($journalTotal != $pembelianTotal) {
                    $this->info("❌ Ketidaksesuaian amount - Pembelian ID {$pembelian->id}:");
                    $this->info("  Pembelian Total: Rp " . number_format($pembelianTotal, 2));
                    $this->info("  Journal Total: Rp " . number_format($journalTotal, 2));
                    $this->info("  Selisih: Rp " . number_format(abs($journalTotal - $pembelianTotal), 2));
                } else {
                    $this->info("✅ Amount sesuai - Pembelian ID {$pembelian->id}: Rp " . number_format($pembelianTotal, 2));
                }
            }
        }
        
        $this->info("\n=== COMPARISON COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
