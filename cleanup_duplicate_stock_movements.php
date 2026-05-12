<?php

/**
 * Script to clean up duplicate stock movements for purchases
 * 
 * This script identifies and removes duplicate stock movements that were created
 * by both the direct stock update and the stock service layer.
 */

require_once 'vendor/autoload.php';

use App\Models\StockMovement;
use App\Models\Pembelian;
use Illuminate\Support\Facades\DB;

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
            
            // Keep the first movement (oldest), remove the rest
            $movementsToKeep = $movements->first();
            $movementsToRemove = $movements->skip(1);
            
            foreach ($movementsToRemove as $movement) {
                echo "Removing duplicate movement ID {$movement->id} for purchase {$purchaseId}, item {$itemId}\n";
                $movement->delete();
                $duplicatesRemoved++;
            }
        }
    }
}

echo "\n=== CLEANUP SUMMARY ===\n";
echo "Duplicate groups found: " . count($duplicatesFound) . "\n";
echo "Duplicate movements removed: " . $duplicatesRemoved . "\n";

if (count($duplicatesFound) > 0) {
    echo "\nDuplicate details:\n";
    foreach ($duplicatesFound as $duplicate) {
        echo "- Purchase {$duplicate['purchase_id']}, Item {$duplicate['item_id']} ({$duplicate['item_type']}): {$duplicate['count']} movements\n";
    }
}

echo "\nCleanup completed!\n";