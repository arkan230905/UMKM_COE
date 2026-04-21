<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Find Empty Keterangan Dedi Entries ===" . PHP_EOL;

// Check for entries with empty or null keterangan on 2026-04-26
echo PHP_EOL . "Checking for empty/null keterangan entries..." . PHP_EOL;

$emptyEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->whereNull('jurnal_umum.keterangan')
               ->orWhere('jurnal_umum.keterangan', '')
               ->orWhere('jurnal_umum.keterangan', '-');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Empty keterangan entries: " . $emptyEntries->count() . PHP_EOL;
foreach ($emptyEntries as $entry) {
    echo sprintf(
        "ID: %d | '%s' | %s | %s | %s | %s",
        $entry->id,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

if ($emptyEntries->count() > 0) {
    echo PHP_EOL . "=== Deleting Empty Keterangan Entries ===" . PHP_EOL;
    
    foreach ($emptyEntries as $entry) {
        echo "Deleting ID: " . $entry->id . " (empty keterangan)" . PHP_EOL;
        DB::table('jurnal_umum')->where('id', $entry->id)->delete();
    }
    
    echo "Deleted " . $emptyEntries->count() . " empty keterangan entries" . PHP_EOL;
}

echo PHP_EOL . "=== Final Check ===" . PHP_EOL;

// Check all Dedi Gunawan entries again
$finalEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Final Dedi-related entries: " . $finalEntries->count() . PHP_EOL;
foreach ($finalEntries as $entry) {
    echo sprintf(
        "ID: %d | '%s' | %s | %s | %s | %s",
        $entry->id,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Status: " . ($finalEntries->count() === 2 ? "FIXED" : "NEEDS ATTENTION") . PHP_EOL;
echo "Expected: 2 entries with proper keterangan" . PHP_EOL;
echo "Actual: " . $finalEntries->count() . " entries" . PHP_EOL;

if ($finalEntries->count() === 2) {
    echo PHP_EOL . "Recommendations:" . PHP_EOL;
    echo "1. Clear browser cache and refresh Jurnal Umum page" . PHP_EOL;
    echo "2. Check if date filters are correct" . PHP_EOL;
    echo "3. Verify UI query matches database structure" . PHP_EOL;
}
