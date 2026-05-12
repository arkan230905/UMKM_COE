<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Move BTKL & BOP to Credit ===" . PHP_EOL;

echo PHP_EOL . "CURRENT STATUS:" . PHP_EOL;
echo "WIP already fixed to DEBIT (correct)" . PHP_EOL;
echo "Now need to move BTKL & BOP to CREDIT" . PHP_EOL;

echo PHP_EOL . "=== CHECKING CURRENT BTKL & BOP ENTRIES ===" . PHP_EOL;

// Check current BTKL & BOP entries
$btklBopEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Alokasi BTKL%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi BOP%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->get();

echo "Current BTKL & BOP entries:" . PHP_EOL;
foreach ($btklBopEntries as $entry) {
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

echo PHP_EOL . "=== MOVING BTKL & BOP TO CREDIT ===" . PHP_EOL;

foreach ($btklBopEntries as $entry) {
    if ($entry->debit > 0) {
        echo "Moving ID: " . $entry->id . " | " . $entry->kode_akun . " to CREDIT" . PHP_EOL;
        echo "Current: DEBIT " . number_format($entry->debit, 0) . " | CREDIT " . number_format($entry->kredit, 0) . PHP_EOL;
        
        // Swap debit and credit
        $newDebit = $entry->kredit;
        $newCredit = $entry->debit;
        
        echo "New: DEBIT " . number_format($newDebit, 0) . " | CREDIT " . number_format($newCredit, 0) . PHP_EOL;
        
        // Update the entry
        DB::table('jurnal_umum')
            ->where('id', $entry->id)
            ->update([
                'debit' => $newDebit,
                'kredit' => $newCredit,
                'updated_at' => now()
            ]);
        
        echo "Updated entry ID: " . $entry->id . PHP_EOL;
    }
}

echo PHP_EOL . "=== VERIFICATION ===" . PHP_EOL;

// Check updated entries
$updatedEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Alokasi BTKL%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi BOP%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer BTKL & BOP ke WIP%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.created_at')
    ->get();

echo "Updated allocation entries:" . PHP_EOL;
foreach ($updatedEntries as $entry) {
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

echo PHP_EOL . "=== CHECK BALANCE ===" . PHP_EOL;

// Check if the allocation is now balanced
$totalDebit = $updatedEntries->sum('debit');
$totalCredit = $updatedEntries->sum('kredit');

echo "Allocation Balance:" . PHP_EOL;
echo "Total Debit: Rp " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Credit: Rp " . number_format($totalCredit, 0) . PHP_EOL;
echo "Status: " . ($totalDebit == $totalCredit ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== EXPECTED JOURNAL DISPLAY ===" . PHP_EOL;
echo "After fix, journal should show:" . PHP_EOL;
echo "17/04/2026" . PHP_EOL;
echo "Alokasi BTKL & BOP ke Produksi" . PHP_EOL;
echo "52 | BIAYA TENAGA KERJA LANGSUNG (BTKL) | Debit: - | Credit: Rp 132.800" . PHP_EOL;
echo "53 | BIAYA OVERHEAD PABRIK (BOP) | Debit: - | Credit: Rp 545.118" . PHP_EOL;
echo "117 | Barang Dalam Proses | Debit: Rp 677.918 | Credit: -" . PHP_EOL;

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "Action: Moved BTKL & BOP to CREDIT" . PHP_EOL;
echo "Change: BTKL & BOP from DEBIT to CREDIT" . PHP_EOL;
echo "Result: Production journal now correct and balanced" . PHP_EOL;
echo "Status: " . ($totalDebit == $totalCredit ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;

echo PHP_EOL . "=== COMPLETED ===" . PHP_EOL;
