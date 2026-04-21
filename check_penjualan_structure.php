<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Penjualan Structure ===" . PHP_EOL;

// Check penjualan table structure
echo PHP_EOL . "Checking penjualan table structure..." . PHP_EOL;

$columns = DB::select("DESCRIBE penjualans");

echo "Penjualan table columns:" . PHP_EOL;
foreach ($columns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Check Recent Sales ===" . PHP_EOL;

// Check recent sales with correct column names
$salesTransactions = DB::table('penjualans')
    ->whereDate('created_at', '2026-04-21')
    ->orWhereDate('created_at', '2026-04-22')
    ->orWhereDate('created_at', '2026-04-23')
    ->select('id', 'created_at', 'total_penjualan', 'total_hpp', 'subtotal_produk', 'no_faktur')
    ->orderBy('created_at')
    ->get();

echo "Recent Sales:" . PHP_EOL;
foreach ($salesTransactions as $sale) {
    echo sprintf(
        "ID: %d | %s | Total: %s | HPP: %s | Subtotal: %s | Faktur: %s",
        $sale->id,
        $sale->created_at,
        number_format($sale->total_penjualan, 0),
        number_format($sale->total_hpp, 0),
        number_format($sale->subtotal_produk, 0),
        $sale->no_faktur
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check Journal Entries for Sales ===" . PHP_EOL;

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
        ->where(function($query) {
            $query->where('coas.kode_akun', '1600')
                   ->orWhere('coas.nama_akun', 'like', '%hpp%')
                   ->orWhere('coas.nama_akun', 'like', '%harga pokok%');
        })
        ->count();
    
    echo "HPP Journal: " . ($hppJournal > 0 ? "EXISTS" : "NOT FOUND") . PHP_EOL;
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

echo PHP_EOL . "=== Check Penjualan Controller ===" . PHP_EOL;

$salesControllerFile = __DIR__ . '/app/Http/Controllers/PenjualanController.php';
if (file_exists($salesControllerFile)) {
    $controllerContent = file_get_contents($salesControllerFile);
    
    echo "Checking PenjualanController for HPP logic..." . PHP_EOL;
    
    if (strpos($controllerContent, 'hpp') !== false || strpos($controllerContent, 'HPP') !== false) {
        echo "✅ Controller mentions HPP" . PHP_EOL;
        
        if (strpos($controllerContent, '1600') !== false) {
            echo "✅ Controller uses COA 1600" . PHP_EOL;
        } else {
            echo "❌ Controller doesn't use COA 1600" . PHP_EOL;
        }
        
        if (strpos($controllerContent, 'jurnal_umum') !== false) {
            echo "✅ Controller creates jurnal_umum entries" . PHP_EOL;
        } else {
            echo "❌ Controller doesn't create jurnal_umum entries" . PHP_EOL;
        }
        
        if (strpos($controllerContent, 'perpetual') !== false) {
            echo "✅ Controller mentions perpetual" . PHP_EOL;
        } else {
            echo "❌ Controller doesn't mention perpetual" . PHP_EOL;
        }
    } else {
        echo "❌ Controller doesn't mention HPP" . PHP_EOL;
    }
} else {
    echo "❌ PenjualanController not found" . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Issue: HPP journal not created for sales in perpetual system" . PHP_EOL;
echo "Expected: When sale occurs, HPP should be recorded" . PHP_EOL;
echo "Journal Entry: Debit HPP (1600), Credit Persediaan Barang Jadi" . PHP_EOL;
echo "Next: Check why HPP journal is not being created" . PHP_EOL;
