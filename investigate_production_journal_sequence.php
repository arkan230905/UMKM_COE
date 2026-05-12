<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Investigate Production Journal Sequence Issue ===" . PHP_EOL;

// Check current production journal sequence
echo PHP_EOL . "Checking current production journal sequence..." . PHP_EOL;

$productionJournals = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Produksi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%WIP%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi%');
    })
    ->whereIn('coas.kode_akun', ['117', '52', '53']) // WIP, BTKL, BOP
    ->select('jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Production Journals (17/04/2026):" . PHP_EOL;
foreach ($productionJournals as $journal) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s",
        $journal->tanggal,
        $journal->keterangan,
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

// Group by description to see sequence
$groupedJournals = [];
foreach ($productionJournals as $journal) {
    $groupedJournals[$journal->keterangan][] = $journal;
}

echo "Current Sequence:" . PHP_EOL;
foreach ($groupedJournals as $keterangan => $journals) {
    echo PHP_EOL . "Keterangan: " . $keterangan . PHP_EOL;
    foreach ($journals as $journal) {
        echo "  - " . $journal->kode_akun . ": " . number_format($journal->debit, 0) . " | " . number_format($journal->kredit, 0) . PHP_EOL;
    }
}

echo PHP_EOL . "=== Expected Correct Sequence ===" . PHP_EOL;
echo "For production process, correct sequence should be:" . PHP_EOL;
echo "1. Alokasi BTKL & BOP ke Produksi" . PHP_EOL;
echo "   - Debit: BTKL (52) + BOP (53)" . PHP_EOL;
echo "   - Credit: WIP (117)" . PHP_EOL;
echo "2. Transfer WIP ke Barang Jadi" . PHP_EOL;
echo "   - Debit: Barang Jadi (116x)" . PHP_EOL;
echo "   - Credit: WIP (117)" . PHP_EOL;

echo PHP_EOL . "=== Current Issue ===" . PHP_EOL;
echo "Current sequence is WRONG:" . PHP_EOL;
echo "1. Transfer WIP ke Barang Jadi (dulu)" . PHP_EOL;
echo "2. Alokasi BTKL & BOP ke Produksi (belakangan)" . PHP_EOL;
echo PHP_EOL . "This causes:" . PHP_EOL;
echo "- WIP account gets credit first, then debit" . PHP_EOL;
echo "- BTKL & BOP allocated after WIP transfer" . PHP_EOL;
echo "- Incorrect cost flow in production" . PHP_EOL;

echo PHP_EOL . "=== Check Produksi Controller ===" . PHP_EOL;

$produksiControllerFile = __DIR__ . '/app/Http/Controllers/ProduksiController.php';
if (file_exists($produksiControllerFile)) {
    $controllerContent = file_get_contents($produksiControllerFile);
    
    echo "Checking ProduksiController logic..." . PHP_EOL;
    
    // Look for production journal creation
    if (strpos($controllerContent, 'alokasi') !== false) {
        echo "✅ Controller mentions alokasi" . PHP_EOL;
    }
    
    if (strpos($controllerContent, 'transfer') !== false) {
        echo "✅ Controller mentions transfer" . PHP_EOL;
    }
    
    if (strpos($controllerContent, 'WIP') !== false) {
        echo "✅ Controller mentions WIP" . PHP_EOL;
    }
    
    // Look for the sequence
    if (strpos($controllerContent, 'transferWipToBarangJadi') !== false) {
        echo "✅ Controller has transferWipToBarangJadi method" . PHP_EOL;
    }
    
    if (strpos($controllerContent, 'alokasiBtklBop') !== false) {
        echo "✅ Controller has alokasiBtklBop method" . PHP_EOL;
    }
    
    // Check the order of operations
    echo PHP_EOL . "Looking for operation sequence..." . PHP_EOL;
    
    // Find the main production method
    if (preg_match('/public function (store|process)/', $controllerContent, $matches)) {
        echo "Main method: " . $matches[1] . PHP_EOL;
        
        // Get the method content
        $methodPattern = '/public function ' . $matches[1] . '\s*\([^)]*\)\s*{([^}]*)}/s';
        if (preg_match($methodPattern, $controllerContent, $methodMatches)) {
            $methodContent = $methodMatches[1];
            
            // Check for sequence
            if (strpos($methodContent, 'transferWipToBarangJadi') < strpos($methodContent, 'alokasiBtklBop')) {
                echo "❌ ISSUE: Transfer WIP called before Alokasi BTKL/BOP" . PHP_EOL;
            } else {
                echo "✅ CORRECT: Alokasi BTKL/BOP called before Transfer WIP" . PHP_EOL;
            }
        }
    }
    
} else {
    echo "❌ ProduksiController not found" . PHP_EOL;
}

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. Fix the sequence in ProduksiController" . PHP_EOL;
echo "2. Alokasi BTKL & BOP should happen FIRST" . PHP_EOL;
echo "3. Transfer WIP to Barang Jadi should happen SECOND" . PHP_EOL;
echo "4. This ensures proper cost accumulation in WIP" . PHP_EOL;
echo "5. Then transfer from WIP to finished goods" . PHP_EOL;

echo PHP_EOL . "=== Next Steps ===" . PHP_EOL;
echo "1. Check ProduksiController store method" . PHP_EOL;
echo "2. Identify where sequence is wrong" . PHP_EOL;
echo "3. Fix the order of operations" . PHP_EOL;
echo "4. Test with new production transaction" . PHP_EOL;
echo "5. Verify journal sequence is correct" . PHP_EOL;
