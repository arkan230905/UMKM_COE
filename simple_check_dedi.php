<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Simple Check Dedi Gunawan ===" . PHP_EOL;

// Check jurnal_umum table
echo PHP_EOL . "Checking jurnal_umum for Dedi Gunawan..." . PHP_EOL;

$jurnalUmum = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('jurnal_umum.keterangan', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->select('jurnal_umum.id', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Jurnal Umum Count: " . $jurnalUmum->count() . PHP_EOL;

foreach ($jurnalUmum as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s",
        $entry->id,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

if ($jurnalUmum->count() > 2) {
    echo "Too many entries! Found: " . $jurnalUmum->count() . PHP_EOL;
    echo "Expected: 2 (1 debit + 1 credit)" . PHP_EOL;
    
    // Delete duplicates
    $entries = $jurnalUmum->toArray();
    $keepFirstTwo = array_slice($entries, 0, 2);
    $deleteRest = array_slice($entries, 2);
    
    echo PHP_EOL . "Deleting duplicates..." . PHP_EOL;
    foreach ($deleteRest as $entry) {
        echo "Deleting ID: " . $entry->id . PHP_EOL;
        DB::table('jurnal_umum')->where('id', $entry->id)->delete();
    }
    
    echo "Deleted " . count($deleteRest) . " entries" . PHP_EOL;
    
} else {
    echo "Correct number of entries: " . $jurnalUmum->count() . PHP_EOL;
}

echo PHP_EOL . "=== Final Check ===" . PHP_EOL;

$finalCount = DB::table('jurnal_umum')
    ->where('keterangan', 'like', '%Dedi Gunawan%')
    ->whereDate('tanggal', '2026-04-26')
    ->count();

echo "Final count: " . $finalCount . PHP_EOL;
echo "Status: " . ($finalCount === 2 ? "FIXED" : "NEEDS ATTENTION") . PHP_EOL;
