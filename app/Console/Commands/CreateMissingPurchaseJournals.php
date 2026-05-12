<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Services\JournalService;

class CreateMissingPurchaseJournals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:missing-purchase-journals {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create journal entries for purchases that don\'t have them';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== CREATING MISSING PURCHASE JOURNAL ENTRIES FOR USER ID: {$userId} ===");
        
        // Get all pembelian for this user
        $pembelians = Pembelian::where('user_id', $userId)->get();
        $this->info("Found {$pembelians->count()} pembelian transactions");
        
        $processed = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($pembelians as $pembelian) {
            try {
                // Check if journal entries already exist
                $existingEntries = JournalEntry::where('ref_type', 'purchase')
                    ->where('ref_id', $pembelian->id)
                    ->count();
                    
                if ($existingEntries > 0) {
                    $this->info("⏭️  Skipping Pembelian ID {$pembelian->id} - already has {$existingEntries} journal entries");
                    $skipped++;
                    continue;
                }
                
                // Check if pembelian has details
                if (!$pembelian->details || $pembelian->details->isEmpty()) {
                    $this->info("⏭️  Skipping Pembelian ID {$pembelian->id} - no details found");
                    $skipped++;
                    continue;
                }
                
                $this->info("🔄 Creating journal for Pembelian ID {$pembelian->id} - {$pembelian->nomor_pembelian}");
                
                // Create journal entries
                JournalService::createJournalFromPembelian($pembelian, $userId);
                
                // Verify creation
                $newEntries = JournalEntry::where('ref_type', 'purchase')
                    ->where('ref_id', $pembelian->id)
                    ->count();
                    
                if ($newEntries > 0) {
                    $this->info("✅ Created {$newEntries} journal entries for Pembelian ID {$pembelian->id}");
                    $processed++;
                } else {
                    $this->info("❌ Failed to create journal entries for Pembelian ID {$pembelian->id}");
                    $errors++;
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Error processing Pembelian ID {$pembelian->id}: " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->info("\n=== SUMMARY ===");
        $this->info("Processed: {$processed}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");
        
        // Final verification
        $this->info("\n=== FINAL VERIFICATION ===");
        $totalPurchaseEntries = JournalEntry::where('ref_type', 'purchase')
            ->where('user_id', $userId)
            ->count();
            
        $this->info("Total purchase journal entries for user {$userId}: {$totalPurchaseEntries}");
        
        $this->info("\n=== COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
