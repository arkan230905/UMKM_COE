<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK NERACA SALDO ===\n\n";

// 1. Check total debit & kredit di jurnal_umum
echo "1. TOTAL DEBIT & KREDIT DI JURNAL_UMUM:\n";
$result = DB::select("SELECT SUM(debit) as total_debit, SUM(kredit) as total_kredit FROM jurnal_umum");
$totalDebit = $result[0]->total_debit ?? 0;
$totalKredit = $result[0]->total_kredit ?? 0;
echo "   Total Debit: " . number_format($totalDebit, 2) . "\n";
echo "   Total Kredit: " . number_format($totalKredit, 2) . "\n";
echo "   Difference: " . number_format(abs($totalDebit - $totalKredit), 2) . "\n";
echo "   Status: " . (abs($totalDebit - $totalKredit) < 0.01 ? "✓ BALANCED" : "✗ NOT BALANCED") . "\n\n";

// 2. Check jurnal entries yang tidak balance
echo "2. CEK JURNAL YANG TIDAK BALANCE:\n";
$unbalancedJurnals = DB::select("
    SELECT ju.id, ju.tanggal, ju.keterangan, 
           SUM(ju.debit) as total_debit, 
           SUM(ju.kredit) as total_kredit,
           ABS(SUM(ju.debit) - SUM(ju.kredit)) as diff
    FROM jurnal_umum ju
    GROUP BY ju.id, ju.tanggal, ju.keterangan
    HAVING ABS(SUM(ju.debit) - SUM(ju.kredit)) > 0.01
    ORDER BY ju.tanggal DESC
    LIMIT 10
");

if (count($unbalancedJurnals) > 0) {
    echo "   Ditemukan " . count($unbalancedJurnals) . " jurnal yang tidak balance:\n";
    foreach ($unbalancedJurnals as $j) {
        echo "   - ID: {$j->id}, Tgl: {$j->tanggal}, Debit: {$j->total_debit}, Kredit: {$j->total_kredit}, Diff: {$j->diff}\n";
    }
} else {
    echo "   ✓ Semua jurnal sudah balance\n";
}
echo "\n";

// 3. Check struktur tabel jurnal_umum
echo "3. STRUKTUR TABEL JURNAL_UMUM:\n";
$columns = DB::select("DESCRIBE jurnal_umum");
foreach ($columns as $col) {
    echo "   - {$col->Field}: {$col->Type}\n";
}
echo "\n";

// 4. Check sample data
echo "4. SAMPLE DATA JURNAL_UMUM (5 baris terakhir):\n";
$samples = DB::select("SELECT id, tanggal, coa_id, debit, kredit, keterangan FROM jurnal_umum ORDER BY id DESC LIMIT 5");
foreach ($samples as $s) {
    echo "   ID: {$s->id}, Tgl: {$s->tanggal}, COA: {$s->coa_id}, Debit: {$s->debit}, Kredit: {$s->kredit}\n";
}
