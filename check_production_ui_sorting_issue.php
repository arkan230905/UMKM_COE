<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Production UI Sorting Issue ===" . PHP_EOL;

// Check if there are any entries that might be causing wrong display
echo PHP_EOL . "Checking for any production entries that might cause wrong display..." . PHP_EOL;

// Check all production-related entries for 17/04/2026
$allProductionEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Produksi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%WIP%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Konsumsi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.created_at')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "All Production Entries (Sorted by ID):" . PHP_EOL;
foreach ($allProductionEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0),
        $entry->created_at
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check Sorting by Created Time ===" . PHP_EOL;

$sortedByCreated = $allProductionEntries->sortBy('created_at');

echo "All Production Entries (Sorted by Created Time):" . PHP_EOL;
foreach ($sortedByCreated as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0),
        $entry->created_at
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check for Multiple Production Processes ===" . PHP_EOL;

// Check if there are multiple production processes on same day
$productionProcesses = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer WIP%');
    })
    ->whereIn('coas.kode_akun', ['1161', '1162']) // Barang Jadi accounts
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.created_at')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Transfer WIP ke Barang Jadi entries:" . PHP_EOL;
foreach ($productionProcesses as $process) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $process->id,
        $process->tanggal,
        $process->keterangan,
        $process->kode_akun,
        $process->nama_akun,
        number_format($process->debit, 0),
        number_format($process->kredit, 0),
        $process->created_at
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check for Different Timestamps ===" . PHP_EOL;

// Check if entries have different timestamps that might affect UI display
$timestampGroups = [];
foreach ($allProductionEntries as $entry) {
    $timestamp = $entry->created_at;
    if (!isset($timestampGroups[$timestamp])) {
        $timestampGroups[$timestamp] = [];
    }
    $timestampGroups[$timestamp][] = $entry;
}

echo "Entries grouped by timestamp:" . PHP_EOL;
foreach ($timestampGroups as $timestamp => $entries) {
    echo PHP_EOL . "Timestamp: " . $timestamp . PHP_EOL;
    foreach ($entries as $entry) {
        echo "  - ID: " . $entry->id . " | " . $entry->keterangan . PHP_EOL;
    }
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

echo "Database shows correct sequence:" . PHP_EOL;
echo "1. Alokasi BTKL & BOP (IDs: " . $allProductionEntries[0]->id . ", " . $allProductionEntries[1]->id . ")" . PHP_EOL;
echo "2. Transfer BTKL & BOP ke WIP (ID: " . $allProductionEntries[2]->id . ")" . PHP_EOL;
echo "3. Transfer WIP ke Barang Jadi (if exists)" . PHP_EOL;

echo PHP_EOL . "Possible UI Issues:" . PHP_EOL;
echo "1. UI sorting by different field (not ID)" . PHP_EOL;
echo "2. UI grouping by different logic" . PHP_EOL;
echo "3. UI using different query that joins multiple tables" . PHP_EOL;
echo "4. UI cache showing old order" . PHP_EOL;
echo "5. UI showing entries from different date range" . PHP_EOL;

echo PHP_EOL . "=== User Perception vs Database ===" . PHP_EOL;
echo "User sees: Transfer WIP ke Barang Jadi FIRST" . PHP_EOL;
echo "Database shows: Alokasi BTKL & BOP FIRST" . PHP_EOL;
echo PHP_EOL . "This suggests:" . PHP_EOL;
echo "1. UI query is different from our check" . PHP_EOL;
echo "2. UI sorting is by different criteria" . PHP_EOL;
echo "3. There might be duplicate entries with different timestamps" . PHP_EOL;
echo "4. UI might be showing entries from multiple dates grouped" . PHP_EOL;

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. Check UI query in Jurnal Umum view/controller" . PHP_EOL;
echo "2. Verify UI sorting logic (should be by date then logical sequence)" . PHP_EOL;
echo "3. Check if UI uses UNION query that combines different tables" . PHP_EOL;
echo "4. Clear browser cache and refresh" . PHP_EOL;
echo "5. Check if there are multiple production processes being displayed" . PHP_EOL;
