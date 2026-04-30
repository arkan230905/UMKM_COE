<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "VERIFYING JOURNAL VISIBILITY FOR HOSTING\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')->first();

if (!$penjualan) {
    echo "ERROR: Penjualan SJ-20260430-001 not found!\n";
    exit;
}

echo "\n=== Journal Entries Analysis ===\n";
echo "Penjualan ID: " . $penjualan->id . "\n";
echo "Referensi: penjualan#" . $penjualan->id . "\n";

// Get ALL journal entries for this penjualan
$allEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
    ->with('coa')
    ->get();

echo "Total journal entries in database: " . $allEntries->count() . "\n\n";

echo "All entries in database:\n";
echo "ID\tTanggal\tKode Akun\tNama Akun\t\t\tDebit\t\tKredit\tUser ID\n";
echo str_repeat("-", 100) . "\n";

foreach ($allEntries as $jurnal) {
    echo $jurnal->id . "\t";
    echo \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') . "\t";
    echo $jurnal->coa->kode_akun . "\t";
    echo substr($jurnal->coa->nama_akun, 0, 20) . "\t\t";
    echo ($jurnal->debit > 0 ? "Rp " . number_format($jurnal->debit, 0, ',', '.') : "-") . "\t";
    echo ($jurnal->kredit > 0 ? "Rp " . number_format($jurnal->kredit, 0, ',', '.') : "-") . "\t";
    echo $jurnal->user_id . "\n";
}

// Check specifically for HPP entries
$hppEntries = $allEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
           strpos($jurnal->coa->kode_akun, '56') !== false;
});

$persediaanEntries = $allEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
           strpos($jurnal->coa->kode_akun, '116') !== false;
});

echo "\n=== HPP Entry Analysis ===\n";
echo "HPP entries found: " . $hppEntries->count() . "\n";
echo "Persediaan entries found: " . $persediaanEntries->count() . "\n";

if ($hppEntries->count() > 0) {
    echo "\nHPP entries:\n";
    foreach ($hppEntries as $hpp) {
        echo "  - " . $hpp->coa->nama_akun . " (" . $hpp->coa->kode_akun . "): ";
        echo ($hpp->debit > 0 ? "Debit Rp " . number_format($hpp->debit, 0, ',', '.') : "Kredit Rp " . number_format($hpp->kredit, 0, ',', '.'));
        echo " (User ID: " . $hpp->user_id . ")\n";
    }
}

if ($persediaanEntries->count() > 0) {
    echo "\nPersediaan entries:\n";
    foreach ($persediaanEntries as $persediaan) {
        echo "  - " . $persediaan->coa->nama_akun . " (" . $persediaan->coa->kode_akun . "): ";
        echo ($persediaan->debit > 0 ? "Debit Rp " . number_format($persediaan->debit, 0, ',', '.') : "Kredit Rp " . number_format($persediaan->kredit, 0, ',', '.'));
        echo " (User ID: " . $persediaan->persediaan . ")\n";
    }
}

// Check if there might be a filtering issue
echo "\n=== Potential Issues ===\n";

// Check if user_id filtering might be hiding entries
$allUserIds = $allEntries->pluck('user_id')->unique();
echo "User IDs in entries: " . $allUserIds->implode(', ') . "\n";

// Check current authenticated user (simulate)
$currentUserId = 1; // Assuming admin user
echo "Current user ID: " . $currentUserId . "\n";

$entriesForCurrentUser = $allEntries->where('user_id', $currentUserId);
echo "Entries for current user: " . $entriesForCurrentUser->count() . "\n";

if ($entriesForCurrentUser->count() < $allEntries->count()) {
    echo "WARNING: Some entries might be filtered out by user_id!\n";
}

// Check totals
$totalDebit = $allEntries->sum('debit');
$totalKredit = $allEntries->sum('kredit');
echo "\nTotals: Debit Rp " . number_format($totalDebit, 0, ',', '.') . ", Kredit Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Balance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";

echo "\n=== Hosting Readiness Check ===\n";
if ($hppEntries->count() > 0 && $persediaanEntries->count() > 0) {
    echo "READY: HPP entries exist in database\n";
    echo "If not visible in UI, check:\n";
    echo "1. User authentication\n";
    echo "2. UI filtering logic\n";
    echo "3. Cache issues\n";
    echo "4. Multi-tenant scope application\n";
} else {
    echo "NOT READY: HPP entries missing\n";
}

echo "\nVerification completed!\n";
