<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateStockMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:cleanup-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate stock movements for purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Scanning for duplicate stock movements...');
        
        // Get all purchase-related stock movements
        $purchaseMovements = StockMovement::where('ref_type', 'purchase')
            ->where('direction', 'in')
            ->orderBy('ref_id')
            ->orderBy('item_id')
            ->orderBy('created_at')
            ->get();

        $duplicatesFound = [];
        $duplicatesRemoved = 0;

        // Group by purchase ID and item ID to find duplicates
        $grouped = $purchaseMovements->groupBy(['ref_id', 'item_id']);

        foreach ($grouped as $purchaseId => $itemGroups) {
            foreach ($itemGroups as $itemId => $movements) {
                if ($movements->count() > 1) {
                    // Found duplicates for this purchase + item combination
                    $duplicatesFound[] = [
                        'purchase_id' => $purchaseId,
                        'item_id' => $itemId,
                        'item_type' => $movements->first()->item_type,
                        'count' => $movements->count(),
                        'movements' => $movements->pluck('id')->toArray()
                    ];
                    
                    if (!$dryRun) {
                        // Keep the first movement (oldest), remove the rest
                        $movementsToRemove = $movements->skip(1);
                        
                        foreach ($movementsToRemove as $movement) {
                            $this->line("Removing duplicate movement ID {$movement->id} for purchase {$purchaseId}, item {$itemId}");
                            $movement->delete();
                            $duplicatesRemoved++;
                        }
                    } else {
                        $this->line("Would remove " . ($movements->count() - 1) . " duplicate movements for purchase {$purchaseId}, item {$itemId}");
                        $duplicatesRemoved += ($movements->count() - 1);
                    }
                }
            }
        }

        $this->info('');
        $this->info('=== CLEANUP SUMMARY ===');
        $this->info("Duplicate groups found: " . count($duplicatesFound));
        
        if ($dryRun) {
            $this->info("Duplicate movements that would be removed: " . $duplicatesRemoved);
            $this->warn('This was a dry run. Use --no-dry-run to actually remove duplicates.');
        } else {
            $this->info("Duplicate movements removed: " . $duplicatesRemoved);
        }

        if (count($duplicatesFound) > 0) {
            $this->info('');
            $this->info('Duplicate details:');
            foreach ($duplicatesFound as $duplicate) {
                $this->line("- Purchase {$duplicate['purchase_id']}, Item {$duplicate['item_id']} ({$duplicate['item_type']}): {$duplicate['count']} movements");
            }
        }

        if (!$dryRun && $duplicatesRemoved > 0) {
            $this->info('');
            $this->success('Cleanup completed successfully!');
        } elseif ($dryRun && $duplicatesRemoved > 0) {
            $this->info('');
            $this->comment('Run without --dry-run to perform the actual cleanup.');
        } else {
            $this->info('');
            $this->success('No duplicates found. Stock movements are clean!');
        }
        
        return 0;
    }
}