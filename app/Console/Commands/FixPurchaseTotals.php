<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\PembelianDetail;

class FixPurchaseTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:purchase-totals {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix purchase total amounts based on details';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== FIXING PURCHASE TOTAL AMOUNTS FOR USER ID: {$userId} ===");
        
        // Get all pembelian for this user
        $pembelians = Pembelian::where('user_id', $userId)->get();
        $this->info("Found {$pembelians->count()} pembelian transactions");
        
        $fixed = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($pembelians as $pembelian) {
            try {
                // Calculate correct totals from details
                $details = $pembelian->details ?? collect([]);
                
                if ($details->isEmpty()) {
                    $this->info("⏭️  Skipping Pembelian ID {$pembelian->id} - no details found");
                    $skipped++;
                    continue;
                }
                
                // Calculate subtotal from details
                $subtotal = 0;
                foreach ($details as $detail) {
                    $subtotal += $detail->subtotal ?? 0;
                }
                
                // Calculate PPN
                $ppnNominal = (float) ($pembelian->ppn_nominal ?? 0);
                
                // Calculate biaya kirim
                $biayaKirim = (float) ($pembelian->biaya_kirim ?? 0);
                
                // Calculate total
                $total = $subtotal + $ppnNominal + $biayaKirim;
                
                $this->info("📋 Pembelian ID {$pembelian->id}: {$pembelian->nomor_pembelian}");
                $this->info("  Current Total: Rp " . number_format($pembelian->total ?? 0, 2));
                $this->info("  Details Subtotal: Rp " . number_format($subtotal, 2));
                $this->info("  PPN Nominal: Rp " . number_format($ppnNominal, 2));
                $this->info("  Biaya Kirim: Rp " . number_format($biayaKirim, 2));
                $this->info("  Calculated Total: Rp " . number_format($total, 2));
                
                // Check if update is needed
                if (($pembelian->total ?? 0) != $total) {
                    // Update pembelian
                    $pembelian->subtotal = $subtotal;
                    $pembelian->total_harga = $subtotal;
                    $pembelian->total = $total;
                    $pembelian->save();
                    
                    $this->info("  ✅ Updated total from Rp " . number_format($pembelian->total ?? 0, 2) . " to Rp " . number_format($total, 2));
                    $fixed++;
                } else {
                    $this->info("  ✅ Total already correct");
                    $skipped++;
                }
                
                $this->info("");
                
            } catch (\Exception $e) {
                $this->error("❌ Error processing Pembelian ID {$pembelian->id}: " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->info("=== SUMMARY ===");
        $this->info("Fixed: {$fixed}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");
        
        // Verification
        $this->info("\n=== VERIFICATION ===");
        $updatedPembelians = Pembelian::where('user_id', $userId)->get();
        
        foreach ($updatedPembelians as $pembelian) {
            $this->info("📋 Pembelian ID {$pembelian->id}: Total = Rp " . number_format($pembelian->total ?? 0, 2));
        }
        
        $this->info("\n=== COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
