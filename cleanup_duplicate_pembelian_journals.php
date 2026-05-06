<?php
/**
 * Script untuk membersihkan jurnal pembelian yang duplikat atau salah
 * 
 * Jalankan dengan: php cleanup_duplicate_pembelian_journals.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JurnalUmum;
use App\Models\Pembelian;
use Illuminate\Support\Facades\DB;

echo "=== Cleanup Duplicate Pembelian Journals ===\n\n";

// 1. Hapus semua jurnal pembelian yang ada
echo "Step 1: Menghapus semua jurnal pembelian existing...\n";
$deletedCount = JurnalUmum::where('tipe_referensi', 'pembelian')->delete();
echo "✅ Deleted {$deletedCount} existing pembelian journal entries\n\n";

// 2. Recreate jurnal untuk semua pembelian
echo "Step 2: Recreate jurnal untuk semua pembelian...\n";
$pembelians = Pembelian::with(['details.bahanBaku', 'details.bahanPendukung', 'vendor'])->get();
echo "Found {$pembelians->count()} pembelian records\n\n";

$journalService = new \App\Services\PembelianJournalService();
$success = 0;
$failed = 0;

foreach ($pembelians as $pembelian) {
    try {
        echo "Processing Pembelian #{$pembelian->nomor_pembelian}...\n";
        
        // Skip if no details
        if (!$pembelian->details || $pembelian->details->isEmpty()) {
            echo "  ⚠️  Skipped (no details)\n";
            continue;
        }
        
        $journalService->createJournalFromPembelian($pembelian);
        
        // Verify
        $journalCount = JurnalUmum::where('tipe_referensi', 'pembelian')
            ->where('referensi', $pembelian->nomor_pembelian)
            ->count();
        
        echo "  ✅ Created {$journalCount} journal entries\n";
        $success++;
        
    } catch (\Exception $e) {
        echo "  ❌ Failed: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n=== Summary ===\n";
echo "Total Pembelian: {$pembelians->count()}\n";
echo "Success: {$success}\n";
echo "Failed: {$failed}\n";

// 3. Verify balance
echo "\n=== Verifying Balance ===\n";
$pembelians = Pembelian::with('details')->get();

foreach ($pembelians as $pembelian) {
    $journals = JurnalUmum::where('tipe_referensi', 'pembelian')
        ->where('referensi', $pembelian->nomor_pembelian)
        ->get();
    
    if ($journals->isEmpty()) {
        continue;
    }
    
    $totalDebit = $journals->sum('debit');
    $totalKredit = $journals->sum('kredit');
    $balanced = abs($totalDebit - $totalKredit) < 0.01;
    
    $status = $balanced ? '✅' : '❌';
    echo "{$status} Pembelian #{$pembelian->nomor_pembelian}: Debit={$totalDebit}, Kredit={$totalKredit}\n";
}

echo "\n✅ Cleanup completed!\n";
