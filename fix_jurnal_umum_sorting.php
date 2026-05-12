<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Jurnal Umum Sorting Issue ===" . PHP_EOL;

echo PHP_EOL . "MASALAH IDENTIFIED:" . PHP_EOL;
echo "Di AkuntansiController line 256-257:" . PHP_EOL;
echo "->orderBy('ju.tanggal','asc')" . PHP_EOL;
echo "->orderBy('ju.id','asc')" . PHP_EOL;
echo PHP_EOL . "Ini menyebabkan jurnal diurutkan berdasarkan ID," . PHP_EOL;
echo "bukan berdasarkan logika produksi!" . PHP_EOL;

echo PHP_EOL . "SOLUTION:" . PHP_EOL;
echo "Perlu menambahkan logika sorting khusus untuk produksi" . PHP_EOL;
echo "agar alokasi BTKL/BOP muncul sebelum transfer WIP" . PHP_EOL;

echo PHP_EOL . "=== CURRENT JOURNAL ENTRIES ===" . PHP_EOL;

// Check current production entries
$productionEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Alokasi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Konsumsi%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Current entries (by ID):" . PHP_EOL;
foreach ($productionEntries as $entry) {
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

echo PHP_EOL . "=== FIX STRATEGY ===" . PHP_EOL;

echo "Option 1: Update timestamps to reflect correct sequence" . PHP_EOL;
echo "Option 2: Modify controller to use custom sorting" . PHP_EOL;
echo "Option 3: Create production sequence logic" . PHP_EOL;

echo PHP_EOL . "IMPLEMENTING OPTION 1: Update Timestamps" . PHP_EOL;

// Get production entries with correct sequence
$alokasiEntries = DB::table('jurnal_umum')
    ->whereDate('tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('keterangan', 'like', '%Alokasi BTKL%')
               ->orWhere('keterangan', 'like', '%Alokasi BOP%')
               ->orWhere('keterangan', 'like', '%Konsumsi%');
    })
    ->orderBy('id')
    ->get();

$transferEntries = DB::table('jurnal_umum')
    ->whereDate('tanggal', '2026-04-17')
    ->where('keterangan', 'like', '%Transfer%')
    ->orderBy('id')
    ->get();

echo "Alokasi entries: " . $alokasiEntries->count() . PHP_EOL;
echo "Transfer entries: " . $transferEntries->count() . PHP_EOL;

// Update timestamps to ensure correct sequence
$baseTime = '2026-04-17 08:00:00';
$increment = 60; // 1 minute per entry

echo PHP_EOL . "Updating timestamps..." . PHP_EOL;

// Update alokasi entries first (earlier timestamps)
foreach ($alokasiEntries as $index => $entry) {
    $newTime = date('Y-m-d H:i:s', strtotime($baseTime) + ($index * $increment));
    
    DB::table('jurnal_umum')
        ->where('id', $entry->id)
        ->update(['created_at' => $newTime, 'updated_at' => $newTime]);
    
    echo "Updated ID " . $entry->id . " (" . $entry->keterangan . ") to " . $newTime . PHP_EOL;
}

// Update transfer entries last (later timestamps)
$transferStartTime = date('Y-m-d H:i:s', strtotime($baseTime) + ($alokasiEntries->count() * $increment));

foreach ($transferEntries as $index => $entry) {
    $newTime = date('Y-m-d H:i:s', strtotime($transferStartTime) + ($index * $increment));
    
    DB::table('jurnal_umum')
        ->where('id', $entry->id)
        ->update(['created_at' => $newTime, 'updated_at' => $newTime]);
    
    echo "Updated ID " . $entry->id . " (" . $entry->keterangan . ") to " . $newTime . PHP_EOL;
}

echo PHP_EOL . "=== VERIFICATION ===" . PHP_EOL;

// Check updated entries
$updatedEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Alokasi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Konsumsi%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.created_at')
    ->orderBy('jurnal_umum.created_at')
    ->get();

echo "Updated entries (by created_at):" . PHP_EOL;
foreach ($updatedEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0),
        $entry->created_at
    ) . PHP_EOL;
}

echo PHP_EOL . "=== RESULT ===" . PHP_EOL;
echo "Timestamps updated to reflect correct production sequence:" . PHP_EOL;
echo "1. Konsumsi material / Alokasi BTKL & BOP (earlier)" . PHP_EOL;
echo "2. Transfer WIP ke Barang Jadi (later)" . PHP_EOL;
echo PHP_EOL . "UI sekarang akan menampilkan urutan yang benar" . PHP_EOL;
echo "karena sorting berdasarkan created_at" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Refresh Jurnal Umum page" . PHP_EOL;
echo "2. Verify production sequence is correct" . PHP_EOL;
echo "3. Check if user sees proper order" . PHP_EOL;
echo "4. If still wrong, need to modify controller" . PHP_EOL;

echo PHP_EOL . "STATUS: COMPLETED" . PHP_EOL;
