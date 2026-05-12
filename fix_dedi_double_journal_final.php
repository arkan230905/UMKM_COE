<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Dedi Gunawan Double Journal Final ===" . PHP_EOL;

// Check all jurnal_umum entries for Dedi Gunawan on 2026-04-26
echo PHP_EOL . "Checking jurnal_umum entries for Dedi Gunawan..." . PHP_EOL;

$allEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('jurnal_umum.keterangan', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Total entries found: " . $allEntries->count() . PHP_EOL;
echo "Expected: 2 entries (1 debit + 1 credit)" . PHP_EOL;

echo PHP_EOL . "Current entries:" . PHP_EOL;
foreach ($allEntries as $entry) {
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

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

if ($allEntries->count() > 2) {
    echo "DUPLICATE DETECTED!" . PHP_EOL;
    echo "Found: " . $allEntries->count() . " entries" . PHP_EOL;
    echo "Expected: 2 entries" . PHP_EOL;
    
    // Identify which entries to keep and which to delete
    $entriesArray = $allEntries->toArray();
    
    // Keep the first 2 entries that form a complete journal (1 debit + 1 credit)
    $keepEntries = [];
    $deleteEntries = [];
    
    $debitFound = false;
    $creditFound = false;
    
    foreach ($entriesArray as $entry) {
        if (!$debitFound && $entry->debit > 0) {
            $keepEntries[] = $entry;
            $debitFound = true;
            continue;
        }
        if (!$creditFound && $entry->kredit > 0) {
            $keepEntries[] = $entry;
            $creditFound = true;
            continue;
        }
        // If we already have both debit and credit, mark for deletion
        $deleteEntries[] = $entry;
    }
    
    echo PHP_EOL . "Entries to keep:" . PHP_EOL;
    foreach ($keepEntries as $entry) {
        echo "- ID: " . $entry->id . " | " . $entry->keterangan . " | " . $entry->kode_akun . PHP_EOL;
    }
    
    echo PHP_EOL . "Entries to delete:" . PHP_EOL;
    foreach ($deleteEntries as $entry) {
        echo "- ID: " . $entry->id . " | " . $entry->keterangan . " | " . $entry->kode_akun . PHP_EOL;
    }
    
    // Delete the duplicate entries
    echo PHP_EOL . "Deleting duplicate entries..." . PHP_EOL;
    $deletedCount = 0;
    
    foreach ($deleteEntries as $entry) {
        echo "Deleting ID: " . $entry->id . PHP_EOL;
        DB::table('jurnal_umum')->where('id', $entry->id)->delete();
        $deletedCount++;
    }
    
    echo "Deleted " . $deletedCount . " duplicate entries" . PHP_EOL;
    
} else {
    echo "No duplicates detected." . PHP_EOL;
    echo "Entries count: " . $allEntries->count() . PHP_EOL;
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Check remaining entries
$remainingEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('jurnal_umum.keterangan', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Remaining entries: " . $remainingEntries->count() . PHP_EOL;

foreach ($remainingEntries as $entry) {
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

// Calculate totals
$totalDebit = $remainingEntries->sum('debit');
$totalCredit = $remainingEntries->sum('kredit');

echo PHP_EOL . "Totals:" . PHP_EOL;
echo "Total Debit: Rp " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Credit: Rp " . number_format($totalCredit, 0) . PHP_EOL;
echo "Balance: " . ($totalDebit == $totalCredit ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Action: Delete duplicate jurnal_umum entries" . PHP_EOL;
echo "Result: " . ($remainingEntries->count() === 2 && $totalDebit == $totalCredit ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;
echo "Expected: 2 entries (1 debit + 1 credit)" . PHP_EOL;
echo "Actual: " . $remainingEntries->count() . " entries" . PHP_EOL;
echo "COA: 54 (BTKTL) - Should be correct for Bagian Gudang" . PHP_EOL;
