<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Production Journal UI Display ===" . PHP_EOL;

// Check all production journals for 17/04/2026 and 18/04/2026
echo PHP_EOL . "Checking all production journals..." . PHP_EOL;

$productionJournals = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->orWhereDate('jurnal_umum.tanggal', '2026-04-18')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Produksi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%WIP%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Alokasi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Transfer%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Konsumsi%');
    })
    ->whereIn('coas.kode_akun', ['117', '52', '53', '1161', '1162', '1142', '1141', '1152', '1153', '1154', '1155', '1156', '1130'])
    ->select('jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.tanggal')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "All Production Journals (17-18/04/2026):" . PHP_EOL;
foreach ($productionJournals as $journal) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s",
        $journal->tanggal,
        substr($journal->keterangan, 0, 40),
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Group by Date ===" . PHP_EOL;

$groupedByDate = [];
foreach ($productionJournals as $journal) {
    $date = $journal->tanggal;
    if (!isset($groupedByDate[$date])) {
        $groupedByDate[$date] = [];
    }
    $groupedByDate[$date][] = $journal;
}

foreach ($groupedByDate as $date => $journals) {
    echo PHP_EOL . "Date: " . $date . PHP_EOL;
    
    // Group by description type
    $byType = [];
    foreach ($journals as $journal) {
        $type = 'other';
        if (strpos($journal->keterangan, 'Konsumsi') !== false) {
            $type = 'konsumsi';
        } elseif (strpos($journal->keterangan, 'Transfer WIP') !== false) {
            $type = 'transfer_wip';
        } elseif (strpos($journal->keterangan, 'Alokasi BTKL') !== false) {
            $type = 'alokasi_btkl';
        } elseif (strpos($journal->keterangan, 'Alokasi BOP') !== false) {
            $type = 'alokasi_bop';
        }
        
        if (!isset($byType[$type])) {
            $byType[$type] = [];
        }
        $byType[$type][] = $journal;
    }
    
    // Display in logical order
    $order = ['konsumsi', 'alokasi_btkl', 'alokasi_bop', 'transfer_wip', 'other'];
    
    foreach ($order as $type) {
        if (isset($byType[$type])) {
            echo "  " . strtoupper($type) . ":" . PHP_EOL;
            foreach ($byType[$type] as $journal) {
                echo sprintf(
                    "    %s | %s | %s | %s",
                    $journal->kode_akun,
                    $journal->nama_akun,
                    number_format($journal->debit, 0),
                    number_format($journal->kredit, 0)
                ) . PHP_EOL;
            }
        }
    }
}

echo PHP_EOL . "=== Check What User Sees ===" . PHP_EOL;

echo "Based on user complaint, they see:" . PHP_EOL;
echo "1. Transfer WIP ke Barang Jadi (FIRST)" . PHP_EOL;
echo "2. Alokasi BTKL & BOP ke Produksi (SECOND)" . PHP_EOL;
echo PHP_EOL . "But database shows:" . PHP_EOL;
echo "1. Alokasi BTKL & BOP ke Produksi (FIRST)" . PHP_EOL;
echo "2. Transfer BTKL & BOP ke WIP (SECOND)" . PHP_EOL;

echo PHP_EOL . "=== Possible UI Issues ===" . PHP_EOL;
echo "1. UI might be sorting by ID instead of logical sequence" . PHP_EOL;
echo "2. UI might be using different query" . PHP_EOL;
echo "3. UI might be showing entries in wrong order" . PHP_EOL;
echo "4. There might be duplicate entries with different descriptions" . PHP_EOL;

echo PHP_EOL . "=== Check for Duplicate Entries ===" . PHP_EOL;

// Check for duplicate WIP transfers
$wipTransfers = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where('jurnal_umum.keterangan', 'like', '%Transfer WIP%')
    ->where('coas.kode_akun', '117')
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "WIP Transfer entries (17/04/2026):" . PHP_EOL;
foreach ($wipTransfers as $transfer) {
    echo sprintf(
        "ID: %d | %s | %s | %s",
        $transfer->id,
        $transfer->keterangan,
        number_format($transfer->debit, 0),
        number_format($transfer->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check for Transfer Barang Jadi ===" . PHP_EOL;

// Check for transfer to finished goods
$barangJadiTransfers = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-17')
    ->where('jurnal_umum.keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
    ->whereIn('coas.kode_akun', ['1161', '1162'])
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Barang Jadi Transfer entries (17/04/2026):" . PHP_EOL;
foreach ($barangJadiTransfers as $transfer) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s",
        $transfer->id,
        $transfer->keterangan,
        $transfer->kode_akun,
        $transfer->nama_akun,
        number_format($transfer->debit, 0),
        number_format($transfer->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

echo "Database Sequence (Correct):" . PHP_EOL;
echo "1. Konsumsi Material" . PHP_EOL;
echo "2. Alokasi BTKL & BOP" . PHP_EOL;
echo "3. Transfer BTKL & BOP ke WIP" . PHP_EOL;
echo "4. Transfer WIP ke Barang Jadi" . PHP_EOL;

echo PHP_EOL . "User Sees (Wrong):" . PHP_EOL;
echo "1. Transfer WIP ke Barang Jadi" . PHP_EOL;
echo "2. Alokasi BTKL & BOP" . PHP_EOL;

echo PHP_EOL . "Possible Causes:" . PHP_EOL;
echo "1. UI sorting by ID (wrong order)" . PHP_EOL;
echo "2. UI query different from database" . PHP_EOL;
echo "3. Cache issue in UI" . PHP_EOL;
echo "4. Multiple entries with different timestamps" . PHP_EOL;

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. Check UI query and sorting logic" . PHP_EOL;
echo "2. Ensure UI displays entries in correct sequence" . PHP_EOL;
echo "3. Clear UI cache if needed" . PHP_EOL;
echo "4. Verify entry timestamps are correct" . PHP_EOL;
