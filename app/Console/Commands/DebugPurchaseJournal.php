<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Services\JournalService;

class DebugPurchaseJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:purchase-journal {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug purchase journal creation issues';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUGGING PURCHASE JOURNAL CREATION FOR USER ID: {$userId} ===");
        
        // Get latest pembelian
        $pembelian = Pembelian::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$pembelian) {
            $this->error("No pembelian found for user {$userId}");
            return Command::FAILURE;
        }
        
        $this->info("📋 Pembelian ID: {$pembelian->id}");
        $this->info("  Nomor: {$pembelian->nomor_pembelian}");
        $this->info("  Tanggal: {$pembelian->tanggal}");
        $this->info("  Total: Rp " . number_format($pembelian->total, 2));
        $this->info("  User ID: {$pembelian->user_id}");
        
        // Check pembelian details
        $this->info("\n📋 Pembelian Details:");
        $details = $pembelian->details;
        $this->info("  Details count: " . $details->count());
        
        if ($details->isEmpty()) {
            $this->info("  ❌ NO DETAILS FOUND - This is why no journal entries are created!");
            return Command::SUCCESS;
        }
        
        foreach ($details as $detail) {
            $this->info("  - ID: {$detail->id}, Bahan Baku ID: {$detail->bahan_baku_id}, Jumlah: {$detail->jumlah}, Harga: {$detail->harga_satuan}, Subtotal: {$detail->subtotal}");
        }
        
        // Try to create journal entries manually
        $this->info("\n🔄 Attempting to create journal entries...");
        try {
            // Check existing journal entries
            $existingEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->count();
            $this->info("  Existing journal entries: {$existingEntries}");
            
            // Delete existing entries first
            \App\Models\JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->delete();
            $this->info("  ✅ Deleted existing entries");
            
            // Create new entries
            JournalService::createJournalFromPembelian($pembelian, $userId);
            $this->info("  ✅ Called createJournalFromPembelian");
            
            // Check results
            $newEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->get();
            $this->info("  📊 New journal entries: " . $newEntries->count());
            
            foreach ($newEntries as $entry) {
                $this->info("    Entry ID: {$entry->id}, User ID: " . ($entry->user_id ?? 'NULL'));
                
                $lines = $entry->lines;
                $this->info("    Lines: " . $lines->count());
                foreach ($lines as $line) {
                    $this->info("      - {$line->coa->kode_akun}: Debit={$line->debit}, Credit={$line->credit}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
