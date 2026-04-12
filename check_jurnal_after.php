<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking jurnal data after fix...\n";

$jurnals = App\Models\JurnalUmum::with('coa')->get();

echo "Total jurnal: " . $jurnals->count() . "\n\n";

foreach ($jurnals as $j) {
    echo "ID: {$j->id}\n";
    echo "COA: {$j->coa_id} - {$j->coa->kode_akun} - {$j->coa->nama_akun}\n";
    echo "Debit: {$j->debit}\n";
    echo "Kredit: {$j->kredit}\n";
    echo "Tipe Referensi: {$j->tipe_referensi}\n";
    echo "Referensi: {$j->referensi}\n";
    echo "Tanggal: {$j->tanggal}\n";
    echo "Keterangan: {$j->keterangan}\n";
    echo "---\n";
}

echo "\nBy tipe referensi:\n";
$byType = App\Models\JurnalUmum::groupBy('tipe_referensi')->selectRaw('tipe_referensi, count(*) as count')->get();
foreach ($byType as $item) {
    echo "{$item->tipe_referensi}: {$item->count}\n";
}
?>
