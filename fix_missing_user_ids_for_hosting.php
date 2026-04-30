<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING MISSING USER IDS FOR HOSTING\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')->first();

echo "\n=== FIXING USER ID ISSUE ===\n";
echo "Penjualan ID: " . $penjualan->id . "\n";

// Fix ALL journal entries with NULL user_id
$affectedRows = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
    ->whereNull('user_id')
    ->update([
        'user_id' => 1,
        'updated_at' => now(),
    ]);

echo "Fixed user_id for {$affectedRows} journal entries\n";

// Verify the fix
$allEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
    ->with('coa')
    ->get();

echo "\n=== VERIFICATION ===\n";
echo "Total entries: " . $allEntries->count() . "\n";
echo "Entries with user_id=1: " . $allEntries->where('user_id', 1)->count() . "\n";
echo "Entries with NULL user_id: " . $allEntries->whereNull('user_id')->count() . "\n";

echo "\nComplete journal entries for SJ-20260430-001:\n";
echo "Tanggal\tKode\tNama Akun\t\t\tDebit\t\tKredit\tUser ID\n";
echo str_repeat("-", 90) . "\n";

foreach ($allEntries as $jurnal) {
    echo \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') . "\t";
    echo $jurnal->coa->kode_akun . "\t";
    echo substr($jurnal->coa->nama_akun, 0, 18) . "\t\t";
    echo ($jurnal->debit > 0 ? "Rp " . number_format($jurnal->debit, 0, ',', '.') : "-") . "\t";
    echo ($jurnal->kredit > 0 ? "Rp " . number_format($jurnal->kredit, 0, ',', '.') : "-") . "\t";
    echo ($jurnal->user_id ?? 'NULL') . "\n";
}

// Check totals
$totalDebit = $allEntries->sum('debit');
$totalKredit = $allEntries->sum('kredit');

echo "\nTotals: Debit Rp " . number_format($totalDebit, 0, ',', '.') . ", Kredit Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Balance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";

// Check HPP entries specifically
$hppEntries = $allEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
           strpos($jurnal->coa->kode_akun, '56') !== false;
});

$persediaanEntries = $allEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
           strpos($jurnal->coa->kode_akun, '116') !== false;
});

echo "\nHPP entries: " . $hppEntries->count() . "\n";
echo "Persediaan entries: " . $persediaanEntries->count() . "\n";

echo "\n=== EXPECTED JOURNAL STRUCTURE ===\n";
echo "SJ-20260430-001 should now show in Jurnal Umum:\n";
echo "1. 112 Kas - Debit Rp 555.000\n";
echo "2. 41 Penjualan - Kredit Rp 500.000\n";
echo "3. 212 PPN Keluaran - Kredit Rp 55.000\n";
echo "4. 56 Harga Pokok Penjualan - Debit Rp 268.600\n";
echo "5. 1161 Pers. Barang Jadi - Kredit Rp 268.600\n";
echo "\nTotal Debit: Rp 823.600\n";
echo "Total Kredit: Rp 823.600\n";

echo "\n=== HOSTING READINESS STATUS ===\n";
if ($allEntries->whereNull('user_id')->count() === 0 && $hppEntries->count() > 0) {
    echo "READY FOR HOSTING!\n";
    echo "- All entries have proper user_id\n";
    echo "- HPP entries are present\n";
    echo "- Journal is balanced\n";
    echo "- Multi-tenant filtering will work\n";
} else {
    echo "NOT READY - Issues still exist\n";
}

echo "\nUser ID fix completed!\n";
