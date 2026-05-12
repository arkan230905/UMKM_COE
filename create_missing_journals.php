<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREATING MISSING JOURNALS FOR ALL TRANSACTIONS ===\n\n";

// Get all pembelians that don't have journal entries
echo "Checking Pembelian transactions...\n";
$pembelians = \App\Models\Pembelian::whereDoesntHave('jurnalUmum')->get();

echo "Found {$pembelians->count()} pembelian without journals\n\n";

$pembelianSuccess = 0;
$pembelianFailed = 0;

foreach ($pembelians as $pembelian) {
    echo "Processing Pembelian #{$pembelian->nomor_pembelian} (ID: {$pembelian->id})...\n";
    
    try {
        $service = new \App\Services\PembelianJournalService();
        $result = $service->createJournalFromPembelian($pembelian);
        
        if ($result) {
            $pembelianSuccess++;
            echo "  ✓ Journal created successfully\n";
        } else {
            $pembelianFailed++;
            echo "  ✗ Journal creation returned null\n";
        }
    } catch (\Exception $e) {
        $pembelianFailed++;
        echo "  ✗ ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Get all penjualans that don't have journal entries
echo "\nChecking Penjualan transactions...\n";
$penjualans = \App\Models\Penjualan::all();

echo "Found {$penjualans->count()} penjualan records\n\n";

$penjualanSuccess = 0;
$penjualanFailed = 0;

foreach ($penjualans as $penjualan) {
    // Check if journal already exists
    $existingJournal = \DB::table('jurnal_umum')
        ->where('tipe_referensi', 'sale')
        ->where('referensi', $penjualan->id)
        ->exists();
    
    if ($existingJournal) {
        echo "Penjualan #{$penjualan->nomor_penjualan} already has journal, skipping...\n";
        continue;
    }
    
    echo "Processing Penjualan #{$penjualan->nomor_penjualan} (ID: {$penjualan->id})...\n";
    
    try {
        \App\Services\JournalService::createJournalFromPenjualan($penjualan);
        $penjualanSuccess++;
        echo "  ✓ Journal created successfully\n";
    } catch (\Exception $e) {
        $penjualanFailed++;
        echo "  ✗ ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Pembelian: {$pembelianSuccess} success, {$pembelianFailed} failed\n";
echo "Penjualan: {$penjualanSuccess} success, {$penjualanFailed} failed\n";

echo "\n=== FINAL JOURNAL COUNT ===\n";
$journalsByType = \DB::table('jurnal_umum')
    ->select('tipe_referensi', \DB::raw('COUNT(*) as count'))
    ->groupBy('tipe_referensi')
    ->get();

foreach ($journalsByType as $j) {
    echo "{$j->tipe_referensi}: {$j->count} entries\n";
}

echo "\nTotal journal entries: " . \DB::table('jurnal_umum')->count() . "\n";
