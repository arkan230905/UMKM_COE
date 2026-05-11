<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DETAIL JURNAL YANG TIDAK BALANCE ===\n\n";

$unbalancedIds = [38, 37, 2, 18, 36, 16, 34, 14, 29, 12];

foreach ($unbalancedIds as $id) {
    $entries = DB::select("
        SELECT ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, ju.referensi,
               coas.kode_akun, coas.nama_akun, ju.debit, ju.kredit
        FROM jurnal_umum ju
        LEFT JOIN coas ON coas.id = ju.coa_id
        WHERE ju.id = ?
        ORDER BY ju.id
    ", [$id]);
    
    if (count($entries) > 0) {
        $first = $entries[0];
        echo "Jurnal ID: {$id}\n";
        echo "  Tanggal: {$first->tanggal}\n";
        echo "  Keterangan: {$first->keterangan}\n";
        echo "  Tipe Referensi: {$first->tipe_referensi}\n";
        echo "  Referensi: {$first->referensi}\n";
        echo "  Entries:\n";
        
        $totalDebit = 0;
        $totalKredit = 0;
        foreach ($entries as $e) {
            echo "    - {$e->kode_akun} ({$e->nama_akun}): Debit={$e->debit}, Kredit={$e->kredit}\n";
            $totalDebit += $e->debit;
            $totalKredit += $e->kredit;
        }
        echo "  Total: Debit={$totalDebit}, Kredit={$totalKredit}\n";
        echo "  Status: " . (abs($totalDebit - $totalKredit) < 0.01 ? "BALANCED" : "NOT BALANCED") . "\n\n";
    }
}
