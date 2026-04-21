<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Jurnal Umum Duplicates ===" . PHP_EOL;

// Check jurnal_umum table for Dedi Gunawan
echo PHP_EOL . "Mengecek jurnal_umum untuk Dedi Gunawan..." . PHP_EOL;

$jurnalUmumEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->join('pegawais', 'jurnal_umum.keterangan', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'jurnal_umum.keterangan', 'coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.tipe_referensi', 'jurnal_umum.referensi')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Jurnal Umum Dedi Gunawan (26/04/2026):" . PHP_EOL;
foreach ($jurnalUmumEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s | %s | %d",
        $entry->id,
        $entry->tanggal,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0),
        $entry->keterangan,
        $entry->tipe_referensi,
        $entry->referensi
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

if ($jurnalUmumEntries->count() > 2) {
    echo "Double jurnal_umum terdeteksi!" . PHP_EOL;
    echo "Total jurnal_umum: " . $jurnalUmumEntries->count() . PHP_EOL;
    echo "Seharusnya: 2 jurnal (debit + credit)" . PHP_EOL;
    
    echo PHP_EOL . "=== Deleting Duplicate Jurnal Umum ===" . PHP_EOL;
    
    // Keep first 2 entries, delete the rest
    $keepCount = 0;
    $deletedCount = 0;
    
    foreach ($jurnalUmumEntries as $index => $entry) {
        if ($keepCount < 2) {
            $keepCount++;
            echo "Keeping ID: " . $entry->id . PHP_EOL;
            continue;
        }
        
        echo "Deleting ID: " . $entry->id . PHP_EOL;
        DB::table('jurnal_umum')->where('id', $entry->id)->delete();
        $deletedCount++;
    }
    
    echo PHP_EOL . "Deleted " . $deletedCount . " duplicate jurnal_umum entries" . PHP_EOL;
    
} else {
    echo "No double jurnal_umum detected." . PHP_EOL;
    echo "Total jurnal_umum: " . $jurnalUmumEntries->count() . PHP_EOL;
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Check remaining jurnal_umum
$remainingJurnalUmum = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->join('pegawais', 'jurnal_umum.keterangan', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->select('jurnal_umum.id', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Remaining Jurnal Umum:" . PHP_EOL;
foreach ($remainingJurnalUmum as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "Total Remaining: " . $remainingJurnalUmum->count() . PHP_EOL;

// Calculate total debit and credit
$totalDebit = $remainingJurnalUmum->sum('debit');
$totalCredit = $remainingJurnalUmum->sum('kredit');

echo "Total Debit: Rp " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Credit: Rp " . number_format($totalCredit, 0) . PHP_EOL;
echo "Balance: " . ($totalDebit == $totalCredit ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Action: Delete duplicate jurnal_umum entries" . PHP_EOL;
echo "Result: Keep only 2 entries (debit + credit)" . PHP_EOL;
echo "Status: " . ($remainingJurnalUmum->count() === 2 && $totalDebit == $totalCredit ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;
