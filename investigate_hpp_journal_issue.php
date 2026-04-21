<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Investigate HPP Journal Issue (Perpetual System) ===" . PHP_EOL;

// Check recent sales transactions
echo PHP_EOL . "Checking recent sales transactions..." . PHP_EOL;

$salesTransactions = DB::table('penjualans')
    ->whereDate('tanggal_penjualan', '2026-04-21')
    ->orWhereDate('tanggal_penjualan', '2026-04-22')
    ->orWhereDate('tanggal_penjualan', '2026-04-23')
    ->select('id', 'tanggal_penjualan', 'total_penjualan', 'total_hpp', 'subtotal_produk', 'no_faktur')
    ->orderBy('tanggal_penjualan')
    ->get();

echo "Sales Transactions:" . PHP_EOL;
foreach ($salesTransactions as $sale) {
    echo sprintf(
        "ID: %d | %s | Total: %s | HPP: %s | Subtotal: %s | Faktur: %s",
        $sale->id,
        $sale->tanggal_penjualan,
        number_format($sale->total_penjualan, 0),
        number_format($sale->total_hpp, 0),
        number_format($sale->subtotal_produk, 0),
        $sale->no_faktur
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Checking Journal Entries for Sales ===" . PHP_EOL;

// Check journal entries for these sales
foreach ($salesTransactions as $sale) {
    echo PHP_EOL . "Journal entries for Sale ID: " . $sale->id . " (" . $sale->no_faktur . ")" . PHP_EOL;
    
    $journalEntries = DB::table('jurnal_umum')
        ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
        ->where('jurnal_umum.tipe_referensi', 'penjualan')
        ->where('jurnal_umum.referensi', $sale->id)
        ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
        ->orderBy('jurnal_umum.id')
        ->get();
    
    echo "Journal count: " . $journalEntries->count() . PHP_EOL;
    foreach ($journalEntries as $entry) {
        echo sprintf(
            "ID: %d | %s | %s | %s | %s | %s",
            $entry->id,
            $entry->keterangan,
            $entry->kode_akun,
            $entry->nama_akun,
            number_format($entry->debit, 0),
            number_format($entry->kredit, 0)
        ) . PHP_EOL;
    }
    
    // Check if HPP journal exists
    $hppJournal = DB::table('jurnal_umum')
        ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
        ->where('jurnal_umum.tipe_referensi', 'penjualan')
        ->where('jurnal_umum.referensi', $sale->id)
        ->where('coas.kode_akun', '1600') // HPP COA
        ->count();
    
    echo "HPP Journal (COA 1600): " . ($hppJournal > 0 ? "EXISTS" : "NOT FOUND") . PHP_EOL;
}

echo PHP_EOL . "=== Check COA for HPP ===" . PHP_EOL;

$coaHpp = DB::table('coas')->where('kode_akun', '1600')->first();
if ($coaHpp) {
    echo "COA 1600 Found: " . $coaHpp->nama_akun . PHP_EOL;
} else {
    echo "COA 1600 NOT FOUND!" . PHP_EOL;
    
    // Check similar COAs
    $similarCoas = DB::table('coas')
        ->where('nama_akun', 'like', '%hpp%')
        ->orWhere('nama_akun', 'like', '%harga pokok%')
        ->orWhere('kode_akun', 'like', '16%')
        ->select('kode_akun', 'nama_akun')
        ->get();
    
    echo "Similar COAs:" . PHP_EOL;
    foreach ($similarCoas as $coa) {
        echo "- " . $coa->kode_akun . " | " . $coa->nama_akun . PHP_EOL;
    }
}

echo PHP_EOL . "=== Check Sales Controller Logic ===" . PHP_EOL;

// Check if sales controller creates HPP journal
$salesControllerFile = __DIR__ . '/app/Http/Controllers/PenjualanController.php';
if (file_exists($salesControllerFile)) {
    $controllerContent = file_get_contents($salesControllerFile);
    
    if (strpos($controllerContent, 'hpp') !== false || strpos($controllerContent, 'HPP') !== false) {
        echo "✅ Sales Controller mentions HPP" . PHP_EOL;
        
        // Look for HPP journal creation
        if (strpos($controllerContent, '1600') !== false) {
            echo "✅ Sales Controller uses COA 1600 for HPP" . PHP_EOL;
        } else {
            echo "❌ Sales Controller doesn't use COA 1600" . PHP_EOL;
        }
        
        // Look for perpetual journal creation
        if (strpos($controllerContent, 'jurnal_umum') !== false) {
            echo "✅ Sales Controller creates jurnal_umum entries" . PHP_EOL;
        } else {
            echo "❌ Sales Controller doesn't create jurnal_umum entries" . PHP_EOL;
        }
    } else {
        echo "❌ Sales Controller doesn't mention HPP" . PHP_EOL;
    }
} else {
    echo "❌ Sales Controller not found" . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

echo "For perpetual system, HPP should be recorded when:" . PHP_EOL;
echo "1. Sale occurs (COA 1600 Debit, Persediaan Barang Jadi Credit)" . PHP_EOL;
echo "2. Cost of goods sold is transferred from inventory" . PHP_EOL;
echo PHP_EOL . "Current Issue:" . PHP_EOL;
echo "- Sales detail shows HPP calculation" . PHP_EOL;
echo "- But Jurnal Umum doesn't show HPP entry" . PHP_EOL;
echo "- This breaks perpetual inventory tracking" . PHP_EOL;

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. Check if sales controller creates HPP journal" . PHP_EOL;
echo "2. Verify COA 1600 exists and is correct" . PHP_EOL;
echo "3. Ensure perpetual journal logic is implemented" . PHP_EOL;
echo "4. Check if there are any errors preventing HPP journal creation" . PHP_EOL;
