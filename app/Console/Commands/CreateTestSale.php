<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Coa;
use App\Services\JournalService;

class CreateTestSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-sale {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test sale to verify journal umum functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== CREATE TEST SALE FOR USER ID: {$userId} ===");
        
        // Check if user exists
        $user = \App\Models\User::find($userId);
        if (!$user) {
            $this->error("User ID {$userId} not found");
            return Command::FAILURE;
        }
        
        $this->info("Creating test sale for user: {$user->name}");
        
        try {
            // Get a product for test
            $produk = Produk::where('user_id', $userId)->first();
            if (!$produk) {
                $this->error("No products found for user {$userId}");
                return Command::FAILURE;
            }
            
            $this->info("Using product: {$produk->nama_produk}");
            
            // Create test sale
            $penjualan = new Penjualan();
            $penjualan->user_id = $userId;
            $penjualan->produk_id = $produk->id;
            $penjualan->tanggal = now()->format('Y-m-d');
            $penjualan->payment_method = 'cash';
            $penjualan->harga_satuan = 10000;
            $penjualan->jumlah = 5;
            $penjualan->diskon_nominal = 0;
            $penjualan->total = 50000;
            $penjualan->catatan_pembayaran = 'Test sale for journal verification';
            $penjualan->save();
            
            $this->info("✅ Test sale created: ID {$penjualan->id}, Total: Rp {$penjualan->total}");
            
            // Create journal entries
            $this->info("🔄 Creating journal entries...");
            JournalService::createJournalFromPenjualan($penjualan);
            $this->info("✅ Journal entries created");
            
            // Verify the journal entries
            $journalEntries = \App\Models\JournalEntry::where('ref_type', 'sale')
                ->where('ref_id', $penjualan->id)
                ->with('lines.coa')
                ->get();
                
            $this->info("📊 Journal entries created: " . $journalEntries->count());
            
            foreach ($journalEntries as $entry) {
                $this->info("  Entry ID: {$entry->id}, Date: {$entry->tanggal}");
                $this->info("  Lines: " . $entry->lines->count());
                
                foreach ($entry->lines as $line) {
                    $this->info("    - {$line->coa->kode_akun} ({$line->coa->nama_akun}): Debit={$line->debit}, Credit={$line->credit}");
                }
            }
            
            $this->info("\n✅ TEST SALE COMPLETED SUCCESSFULLY");
            $this->info("Now check: http://jobcost.eadtmanufaktur.com/akuntansi/jurnal-umum");
            $this->info("You should see the sale journal entry there!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error creating test sale: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}
