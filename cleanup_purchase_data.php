<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE PURCHASE DATA CLEANUP ===\n\n";

// 1. Check and delete purchase-related stock movements
$purchaseMovements = \App\Models\StockMovement::where('ref_type', 'purchase')->count();
echo "Purchase stock movements: " . $purchaseMovements . "\n";
if ($purchaseMovements > 0) {
    \App\Models\StockMovement::where('ref_type', 'purchase')->delete();
    echo "✓ Deleted purchase stock movements\n";
}

// 2. Check and delete purchase-related stock layers
$purchaseLayers = \App\Models\StockLayer::where('ref_type', 'purchase')->count();
echo "Purchase stock layers: " . $purchaseLayers . "\n";
if ($purchaseLayers > 0) {
    \App\Models\StockLayer::where('ref_type', 'purchase')->delete();
    echo "✓ Deleted purchase stock layers\n";
}

// 3. Check and delete purchase journal entries
$purchaseJournals = \App\Models\JournalEntry::where('ref_type', 'purchase')->count();
echo "Purchase journal entries: " . $purchaseJournals . "\n";
if ($purchaseJournals > 0) {
    $entries = \App\Models\JournalEntry::where('ref_type', 'purchase')->get();
    foreach ($entries as $entry) {
        \App\Models\JournalLine::where('journal_entry_id', $entry->id)->delete();
    }
    \App\Models\JournalEntry::where('ref_type', 'purchase')->delete();
    echo "✓ Deleted purchase journal entries\n";
}

// 4. Check and delete purchase return items
if (\Schema::hasTable('purchase_return_items')) {
    $returnItems = \DB::table('purchase_return_items')->count();
    echo "Purchase return items: " . $returnItems . "\n";
    if ($returnItems > 0) {
        \DB::table('purchase_return_items')->delete();
        echo "✓ Deleted purchase return items\n";
    }
}

// 5. Check and delete pembelian detail records
$pembelianDetails = \App\Models\PembelianDetail::count();
echo "Pembelian detail records: " . $pembelianDetails . "\n";
if ($pembelianDetails > 0) {
    \App\Models\PembelianDetail::query()->delete();
    echo "✓ Deleted pembelian detail records\n";
}

// 6. Check and delete pembelian records
$pembelians = \App\Models\Pembelian::count();
echo "Pembelian records: " . $pembelians . "\n";
if ($pembelians > 0) {
    \App\Models\Pembelian::query()->delete();
    echo "✓ Deleted pembelian records\n";
}

echo "\n=== CLEANUP COMPLETED ===\n";
echo "The stock report should now be clean of any purchase data.\n";
echo "Only production and initial stock data will remain.\n";

echo "\nDone!\n";