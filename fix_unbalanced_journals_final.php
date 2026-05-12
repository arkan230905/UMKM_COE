<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\JurnalUmum;

echo "=== FIXING UNBALANCED JOURNALS ===\n\n";

// Find all unbalanced journals
$unbalancedJournals = DB::select("
    SELECT ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, ju.referensi,
           SUM(ju.debit) as total_debit, 
           SUM(ju.kredit) as total_kredit,
           ABS(SUM(ju.debit) - SUM(ju.kredit)) as diff
    FROM jurnal_umum ju
    GROUP BY ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, ju.referensi
    HAVING ABS(SUM(ju.debit) - SUM(ju.kredit)) > 0.01
    ORDER BY ju.tanggal DESC
");

echo "Found " . count($unbalancedJournals) . " unbalanced journals\n\n";

// Delete unbalanced journals
$deletedIds = [];
foreach ($unbalancedJournals as $journal) {
    echo "Deleting Journal ID: {$journal->id} ({$journal->tipe_referensi} #{$journal->referensi})\n";
    JurnalUmum::where('id', $journal->id)->delete();
    $deletedIds[] = $journal->id;
}

echo "\n✓ Deleted " . count($deletedIds) . " unbalanced journal entries\n\n";

// Verify deletion
$result = DB::select("SELECT SUM(debit) as total_debit, SUM(kredit) as total_kredit FROM jurnal_umum");
$totalDebit = $result[0]->total_debit ?? 0;
$totalKredit = $result[0]->total_kredit ?? 0;

echo "=== VERIFICATION ===\n";
echo "Total Debit: " . number_format($totalDebit, 2) . "\n";
echo "Total Kredit: " . number_format($totalKredit, 2) . "\n";
echo "Difference: " . number_format(abs($totalDebit - $totalKredit), 2) . "\n";
echo "Status: " . (abs($totalDebit - $totalKredit) < 0.01 ? "✓ BALANCED" : "✗ NOT BALANCED") . "\n";
