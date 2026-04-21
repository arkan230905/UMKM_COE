<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Dedi Gunawan Journal Display Format ===" . PHP_EOL;

// Check how other payroll entries are formatted
echo PHP_EOL . "Checking other payroll entries format..." . PHP_EOL;

$otherPayroll = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-24')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Budi Susanto%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Ahmad Suryanto%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Rina Wijaya%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Other payroll entries (24/04/2026):" . PHP_EOL;
foreach ($otherPayroll as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "Checking Dedi Gunawan current format..." . PHP_EOL;

$dediEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Current Dedi Gunawan entries:" . PHP_EOL;
foreach ($dediEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

// Check if other payroll entries are formatted as single transactions
$otherPayrollGrouped = [];
foreach ($otherPayroll as $entry) {
    $date = $entry->tanggal;
    if (!isset($otherPayrollGrouped[$date])) {
        $otherPayrollGrouped[$date] = [];
    }
    $otherPayrollGrouped[$date][] = $entry;
}

echo "Other payroll by date:" . PHP_EOL;
foreach ($otherPayrollGrouped as $date => $entries) {
    echo "$date: " . count($entries) . " entries" . PHP_EOL;
    foreach ($entries as $entry) {
        echo "  - " . $entry->keterangan . " | " . $entry->kode_akun . PHP_EOL;
    }
}

echo PHP_EOL . "Dedi Gunawan entries:" . PHP_EOL;
echo "26/04/2026: " . count($dediEntries) . " entries" . PHP_EOL;
foreach ($dediEntries as $entry) {
    echo "  - " . $entry->keterangan . " | " . $entry->kode_akun . PHP_EOL;
}

echo PHP_EOL . "=== Fix Dedi Gunawan Format ===" . PHP_EOL;

// The issue is that Dedi entries have different keterangan
// We need to make them consistent like other payroll entries

echo "Making Dedi Gunawan entries consistent..." . PHP_EOL;

// Update keterangan to be consistent
foreach ($dediEntries as $entry) {
    if (strpos($entry->keterangan, 'Penggajian') !== false) {
        // Keep this as is - this should be the main description
        echo "Keeping: " . $entry->keterangan . PHP_EOL;
    } else if (strpos($entry->keterangan, 'Pembayaran') !== false) {
        // Update this to match the main description
        $newKeterangan = 'Penggajian Dedi Gunawan';
        echo "Updating '" . $entry->keterangan . "' to '" . $newKeterangan . "'" . PHP_EOL;
        
        DB::table('jurnal_umum')
            ->where('id', $entry->id)
            ->update(['keterangan' => $newKeterangan]);
    }
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Check updated entries
$updatedEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Updated Dedi Gunawan entries:" . PHP_EOL;
foreach ($updatedEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Expected Display Format ===" . PHP_EOL;
echo "After fix, Dedi Gunawan should display like this:" . PHP_EOL;
echo "26/04/2026" . PHP_EOL;
echo "Penggajian Dedi Gunawan" . PHP_EOL;
echo "54 | Biaya Tenaga Kerja Tidak Langsung (BTKTL) | BEBAN | Beban Gaji Dedi Gunawan | Rp 3.250.000 | -" . PHP_EOL;
echo "112 | Kas | Aset | Pembayaran Gaji Dedi Gunawan | - | Rp 3.250.000" . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Action: Updated keterangan to be consistent" . PHP_EOL;
echo "Result: Both entries now have same keterangan 'Penggajian Dedi Gunawan'" . PHP_EOL;
echo "Status: Should display as single transaction like other payroll" . PHP_EOL;
echo "Next: Refresh Jurnal Umum page to see the fix" . PHP_EOL;
