<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Models\JurnalUmum;
use App\Services\JournalService;

class RebuildMissingJournalsPenjualan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'journal:rebuild-penjualan 
                            {--user= : User ID to rebuild journals for (optional)}
                            {--all : Rebuild all journals even if they exist}
                            {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild missing journal entries for penjualan (sales) transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $rebuildAll = $this->option('all');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🔄 Starting journal rebuild for penjualan...');

        // Query penjualan with payment_status = 'paid'
        $query = Penjualan::with(['details.produk', 'produk'])
            ->where('payment_status', 'paid');

        // Filter by user if specified
        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("   Filtering for user_id: {$userId}");
        }

        $penjualans = $query->get();
        $this->info("   Found {$penjualans->count()} paid penjualan transactions");

        $processed = 0;
        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($penjualans as $penjualan) {
            $processed++;

            // Check if journal already exists
            $hasJournal = JurnalUmum::where('tipe_referensi', 'sale')
                ->where('referensi', (string)$penjualan->id)
                ->exists();

            if ($hasJournal && !$rebuildAll) {
                $skipped++;
                continue;
            }

            $status = $hasJournal ? 'rebuilding' : 'creating';
            $this->line("   [{$processed}/{$penjualans->count()}] {$status} journal for penjualan #{$penjualan->id} ({$penjualan->nomor_penjualan})...");

            if ($dryRun) {
                $this->line("      → Would {$status} journal (dry run)");
                $created++;
                continue;
            }

            try {
                // Delete existing journals if rebuilding
                if ($hasJournal) {
                    JurnalUmum::where('tipe_referensi', 'sale')
                        ->where('referensi', (string)$penjualan->id)
                        ->delete();
                }

                // Create journal entries
                JournalService::createJournalFromPenjualan($penjualan, $penjualan->user_id);
                
                $this->info("      ✓ Successfully {$status} journal");
                $created++;

            } catch (\Exception $e) {
                $this->error("      ✗ Failed to {$status} journal: " . $e->getMessage());
                $failed++;
                
                // Log the error
                \Log::error("Failed to rebuild journal for penjualan", [
                    'penjualan_id' => $penjualan->id,
                    'nomor_penjualan' => $penjualan->nomor_penjualan,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Total Processed', $processed],
                ['Created/Rebuilt', $created],
                ['Skipped (already exists)', $skipped],
                ['Failed', $failed],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('🔍 DRY RUN COMPLETE - Run without --dry-run to apply changes');
        } else {
            $this->newLine();
            $this->info('✅ Journal rebuild complete!');
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
