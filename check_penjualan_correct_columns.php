<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Penjualan Correct Columns ===" . PHP_EOL;

// Check recent sales with correct column names
echo PHP_EOL . "Checking recent sales with correct columns..." . PHP_EOL;

$salesTransactions = DB::table('penjualans')
    ->whereDate('tanggal', '2026-04-21')
    ->orWhereDate('tanggal', '2026-04-22')
    ->orWhereDate('tanggal', '2026-04-23')
    ->select('id', 'tanggal', 'total', 'nomor_penjualan')
    ->orderBy('tanggal')
    ->get();

echo "Recent Sales:" . PHP_EOL;
foreach ($salesTransactions as $sale) {
    echo sprintf(
        "ID: %d | %s | Total: %s | Faktur: %s",
        $sale->id,
        $sale->tanggal,
        number_format($sale->total, 0),
        $sale->nomor_penjualan
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check Journal Entries for Sales ===" . PHP_EOL;

// Check journal entries for these sales
foreach ($salesTransactions as $sale) {
    echo PHP_EOL . "Journal entries for Sale ID: " . $sale->id . " (" . $sale->nomor_penjualan . ")" . PHP_EOL;
    
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
        
        // Look for store method
        if (strpos($controllerContent, 'public function store') !== false) {
            echo "✅ Controller has store method" . PHP_EOL;
        } else {
            echo "❌ Controller doesn't have store method" . PHP_EOL;
        }
        
    } else {
        echo "❌ Controller doesn't mention HPP" . PHP_EOL;
    }
} else {
    echo "❌ PenjualanController not found" . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

echo "For perpetual system, when sale occurs:" . PHP_EOL;
echo "1. Debit: HPP (COA 1600)" . PHP_EOL;
echo "2. Credit: Persediaan Barang Jadi (COA 1600+)" . PHP_EOL;
echo "3. This transfers cost from inventory to COGS" . PHP_EOL;

echo PHP_EOL . "Current Issue:" . PHP_EOL;
echo "- Sales detail shows HPP calculation" . PHP_EOL;
echo "- But Jurnal Umum doesn't show HPP entry" . PHP_EOL;
echo "- This breaks perpetual inventory tracking" . PHP_EOL;

echo PHP_EOL . "=== Possible Causes ===" . PHP_EOL;
echo "1. COA 1600 doesn't exist" . PHP_EOL;
echo "2. Controller doesn't create HPP journal" . PHP_EOL;
echo "3. HPP logic not implemented in perpetual system" . PHP_EOL;
echo "4. Error in journal creation prevents HPP entry" . PHP_EOL;

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. Create COA 1600 for HPP if not exists" . PHP_EOL;
echo "2. Add HPP journal creation to PenjualanController" . PHP_EOL;
echo "3. Ensure perpetual logic is properly implemented" . PHP_EOL;
echo "4. Test with new sale to verify HPP journal creation" . PHP_EOL;
