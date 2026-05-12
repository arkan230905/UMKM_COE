<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Find UI Display Discrepancy ===" . PHP_EOL;

// Check if there are any other entries that might be causing wrong display
echo PHP_EOL . "Checking for any other production entries that might affect UI display..." . PHP_EOL;

// Check all entries on 17/04/2026 that involve production accounts
$allProductionEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->whereIn('coas.kode_akun', ['117', '52', '53', '1161', '1162'])
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.created_at', 'jurnal_umum.tipe_referensi', 'jurnal_umum.referensi')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "All production-related entries (17/04/2026):" . PHP_EOL;
foreach ($allProductionEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        substr($entry->keterangan, 0, 50),
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0),
        $entry->tipe_referensi,
        $entry->referensi
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check for Transfer Barang Jadi Entries ===" . PHP_EOL;

// Check if there are any "Transfer WIP ke Barang Jadi" entries
$transferBarangJadi = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where('jurnal_umum.keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
    ->whereIn('coas.kode_akun', ['1161', '1162'])
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.created_at')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Transfer WIP ke Barang Jadi entries:" . PHP_EOL;
foreach ($transferBarangJadi as $entry) {
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

// Check if there are multiple production processes with different timestamps
$productionProcesses = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer WIP%');
    })
    ->whereIn('coas.kode_akun', ['117', '1161', '1162'])
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.created_at')
    ->orderBy('jurnal_umum.created_at')
    ->get();

echo "All Transfer WIP related entries:" . PHP_EOL;
foreach ($productionProcesses as $entry) {
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

echo PHP_EOL . "=== Check UI Query Simulation ===" . PHP_EOL;

// Simulate what UI might be querying
echo "Simulating possible UI query..." . PHP_EOL;

// Check if UI might be using a different date range or filter
$uiSimulated = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Transfer%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Produksi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi%');
    })
    ->whereIn('coas.kode_akun', ['117', '52', '53', '1161', '1162'])
    ->select('jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.tanggal')
    ->orderBy('jurnal_umum.keterangan')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "UI simulation results:" . PHP_EOL;
foreach ($uiSimulated as $entry) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s",
        $entry->tanggal,
        substr($entry->keterangan, 0, 40),
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

echo "Database Analysis:" . PHP_EOL;
echo "- Total production entries: " . $allProductionEntries->count() . PHP_EOL;
echo "- Transfer WIP ke Barang Jadi: " . $transferBarangJadi->count() . PHP_EOL;
echo "- All Transfer WIP related: " . $productionProcesses->count() . PHP_EOL;

echo PHP_EOL . "Possible UI Display Issues:" . PHP_EOL;
echo "1. UI might be showing entries in wrong order" . PHP_EOL;
echo "2. UI might be using different sorting criteria" . PHP_EOL;
echo "3. UI might be grouping by different logic" . PHP_EOL;
echo "4. UI might be showing entries from different date ranges" . PHP_EOL;
echo "5. UI might be using UNION query with different tables" . PHP_EOL;

echo PHP_EOL . "=== User Perception vs Database ===" . PHP_EOL;
echo "User sees: Transfer WIP ke Barang Jadi FIRST" . PHP_EOL;
echo "Database shows: Alokasi BTKL & BOP FIRST" . PHP_EOL;
echo PHP_EOL . "This suggests:" . PHP_EOL;
echo "1. UI query is different from our database check" . PHP_EOL;
echo "2. UI sorting is by different criteria (maybe by keterangan alphabetically)" . PHP_EOL;
echo "3. UI might be showing entries from different date or process" . PHP_EOL;
echo "4. There might be multiple production processes with different timestamps" . PHP_EOL;

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. Check UI query in Jurnal Umum view/controller" . PHP_EOL;
echo "2. Verify UI sorting logic (should be by date then logical production sequence)" . PHP_EOL;
echo "3. Check if UI uses different date range or filters" . PHP_EOL;
echo "4. Clear browser cache and refresh" . PHP_EOL;
echo "5. Check if there are multiple production processes being displayed together" . PHP_EOL;

echo PHP_EOL . "=== Quick Fix ===" . PHP_EOL;

// If there are Transfer WIP ke Barang Jadi entries that are causing wrong display
if ($transferBarangJadi->count() > 0) {
    echo "Found " . $transferBarangJadi->count() . " Transfer WIP ke Barang Jadi entries" . PHP_EOL;
    echo "These might be causing wrong display sequence" . PHP_EOL;
    
    echo PHP_EOL . "Option: Temporarily hide these entries to test UI display" . PHP_EOL;
    echo "This would help identify if they are causing the wrong sequence" . PHP_EOL;
    
    // Option: Comment out these entries temporarily
    // foreach ($transferBarangJadi as $entry) {
    //     DB::table('jurnal_umum')
    //         ->where('id', $entry->id)
    //         ->update(['keterangan' => 'TEMP_HIDDEN_' . $entry->keterangan]);
    // }
    // echo "Temporarily hidden " . $transferBarangJadi->count() . " entries" . PHP_EOL;
}
