<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Coa;
use App\Services\JournalService;

class CreateTestPurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-purchase {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test purchase to verify journal functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== CREATE TEST PURCHASE FOR USER ID: {$userId} ===");
        
        // Check if user exists
        $user = \App\Models\User::find($userId);
        if (!$user) {
            $this->error("User ID {$userId} not found");
            return Command::FAILURE;
        }
        
        $this->info("Creating test purchase for user: {$user->name}");
        
        try {
            // Get a vendor for test
            $vendor = Vendor::where('user_id', $userId)->first();
            if (!$vendor) {
                $this->error("No vendors found for user {$userId}");
                return Command::FAILURE;
            }
            
            $this->info("Using vendor: {$vendor->nama_vendor}");
            
            // Get a bahan baku for test
            $bahanBaku = BahanBaku::where('user_id', $userId)->first();
            if (!$bahanBaku) {
                $this->error("No bahan baku found for user {$userId}");
                return Command::FAILURE;
            }
            
            $this->info("Using bahan baku: {$bahanBaku->nama_bahan}");
            
            // Create test pembelian
            $pembelian = new Pembelian();
            $pembelian->user_id = $userId;
            $pembelian->vendor_id = $vendor->id;
            $pembelian->nomor_pembelian = 'PB-TEST-' . date('Ymd-His');
            $pembelian->tanggal = now()->format('Y-m-d');
            $pembelian->payment_method = 'cash';
            $pembelian->total = 10000;
            $pembelian->ppn_persen = 0;
            $pembelian->ppn_nominal = 0;
            $pembelian->biaya_kirim = 0;
            $pembelian->keterangan = 'Test purchase for journal verification';
            $pembelian->save();
            
            // Create pembelian detail
            $detail = new \App\Models\PembelianDetail();
            $detail->pembelian_id = $pembelian->id;
            $detail->bahan_baku_id = $bahanBaku->id;
            $detail->tipe_item = 'bahan_baku';
            $detail->jumlah = 5;
            $detail->satuan = $bahanBaku->satuan_id ?? 1;
            $detail->harga_satuan = 2000;
            $detail->subtotal = 10000;
            $detail->faktor_konversi = 1;
            $detail->jumlah_satuan_utama = 5;
            $detail->save();
            
            $this->info("✅ Test purchase created: ID {$pembelian->id}, Total: Rp {$pembelian->total}");
            
            // Create journal entries
            $this->info("🔄 Creating journal entries...");
            JournalService::createJournalFromPembelian($pembelian, $userId);
            $this->info("✅ Journal entries created");
            
            // Verify the journal entries
            $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->with('lines.coa')
                ->get();
                
            $this->info("📊 Journal entries created: " . $journalEntries->count());
            
            foreach ($journalEntries as $entry) {
                $this->info("  Entry ID: {$entry->id}, Date: {$entry->tanggal}");
                $this->info("  User ID: {$entry->user_id}");
                $this->info("  Lines: " . $entry->lines->count());
                
                foreach ($entry->lines as $line) {
                    $this->info("    - {$line->coa->kode_akun} ({$line->coa->nama_akun}): Debit={$line->debit}, Credit={$line->credit}");
                }
            }
            
            $this->info("\n✅ TEST PURCHASE COMPLETED SUCCESSFULLY");
            $this->info("Now check: http://jobcost.eadtmanufaktur.com/akuntansi/jurnal-umum");
            $this->info("You should see the purchase journal entry there!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error creating test purchase: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}
