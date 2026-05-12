<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix WIP Direction in Production Journal ===" . PHP_EOL;

echo PHP_EOL . "CURRENT ISSUE:" . PHP_EOL;
echo "User reports: WIP shows CREDIT instead of DEBIT" . PHP_EOL;
echo "Expected: WIP should be DEBIT when allocating BTKL & BOP" . PHP_EOL;

echo PHP_EOL . "ACCOUNTING LOGIC:" . PHP_EOL;
echo "When allocating BTKL & BOP to production:" . PHP_EOL;
echo "DEBIT: WIP (Barang Dalam Proses) - accumulating costs" . PHP_EOL;
echo "CREDIT: BTKL & BOP - transferring costs out" . PHP_EOL;
echo PHP_EOL . "Current (WRONG):" . PHP_EOL;
echo "- WIP: CREDIT Rp 677.918" . PHP_EOL;
echo PHP_EOL . "Should be (CORRECT):" . PHP_EOL;
echo "- WIP: DEBIT Rp 677.918" . PHP_EOL;

echo PHP_EOL . "=== CHECKING CURRENT ENTRIES ===" . PHP_EOL;

// Check current production allocation entries
$allocationEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where('jurnal_umum.keterangan', 'like', '%Transfer BTKL & BOP ke WIP%')
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->get();

echo "Current WIP allocation entries:" . PHP_EOL;
foreach ($allocationEntries as $entry) {
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

echo PHP_EOL . "=== FIXING WIP DIRECTION ===" . PHP_EOL;

foreach ($allocationEntries as $entry) {
    if ($entry->kode_akun === '117' && $entry->kredit > 0) {
        echo "Fixing WIP entry ID: " . $entry->id . PHP_EOL;
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
    ->where('jurnal_umum.keterangan', 'like', '%Transfer BTKL & BOP ke WIP%')
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->get();

echo "Updated WIP allocation entries:" . PHP_EOL;
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

// Check if the allocation is still balanced
$allocationTotal = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Alokasi BTKL%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi BOP%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer BTKL & BOP ke WIP%');
    })
    ->select('jurnal_umum.debit', 'jurnal_umum.kredit')
    ->get();

$totalDebit = $allocationTotal->sum('debit');
$totalCredit = $allocationTotal->sum('kredit');

echo "Allocation Balance:" . PHP_EOL;
echo "Total Debit: Rp " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Credit: Rp " . number_format($totalCredit, 0) . PHP_EOL;
echo "Status: " . ($totalDebit == $totalCredit ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== EXPECTED JOURNAL DISPLAY ===" . PHP_EOL;
echo "After fix, journal should show:" . PHP_EOL;
echo "17/04/2026" . PHP_EOL;
echo "Alokasi BTKL & BOP ke Produksi" . PHP_EOL;
echo "52 | BIAYA TENAGA KERJA LANGSUNG (BTKL) | Debit: Rp 132.800 | Credit: -" . PHP_EOL;
echo "53 | BIAYA OVERHEAD PABRIK (BOP) | Debit: Rp 545.118 | Credit: -" . PHP_EOL;
echo "117 | Barang Dalam Proses | Debit: Rp 677.918 | Credit: -" . PHP_EOL;

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "Action: Fixed WIP direction in production allocation" . PHP_EOL;
echo "Change: WIP from CREDIT to DEBIT" . PHP_EOL;
echo "Reason: WIP accumulates costs (should be debit)" . PHP_EOL;
echo "Result: Production journal now correct" . PHP_EOL;
echo "Status: " . ($totalDebit == $totalCredit ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Refresh Jurnal Umum page" . PHP_EOL;
echo "2. Verify WIP shows DEBIT" . PHP_EOL;
echo "3. Check if balance is maintained" . PHP_EOL;
echo "4. Verify production logic is correct" . PHP_EOL;
