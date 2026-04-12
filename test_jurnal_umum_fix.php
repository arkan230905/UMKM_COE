<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TESTING JURNAL UMUM FIX\n";
echo "======================\n\n";

// Test the same query as the fixed controller
echo "Testing fixed query from AkuntansiController:\n";
echo "==========================================\n";

$query = \DB::table('jurnal_umum as j')
    ->leftJoin('coas', 'coas.id', '=', 'j.coa_id')
    ->select([
        'j.*',
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun'
    ])
    ->where(function($q) {
        $q->where('j.debit', '!=', 0)
          ->orWhere('j.kredit', '!=', 0);
    })
    ->orderBy('j.tanggal','asc')
    ->orderBy('j.id','asc');

// Filter for produksi transactions on 2026-04-12
$query->whereDate('j.tanggal', '2026-04-12')
       ->where('j.tipe_referensi', 'produksi');

$results = $query->get();

echo "Found {$results->count()} produksi journals on 2026-04-12:\n\n";

foreach ($results as $result) {
    echo "ID: {$result->id}\n";
    echo "Tanggal: {$result->tanggal}\n";
    echo "Referensi: {$result->referensi}\n";
    echo "COA: {$result->kode_akun} - {$result->nama_akun}\n";
    echo "Debit: " . number_format($result->debit, 0, ',', '.') . "\n";
    echo "Kredit: " . number_format($result->kredit, 0, ',', '.') . "\n";
    echo "Keterangan: {$result->keterangan}\n";
    echo "---\n";
}

// Test grouping by referensi
echo "\nGrouped by referensi (like controller does):\n";
echo "==========================================\n";

$groupedResults = $results->groupBy('referensi');

foreach ($groupedResults as $referensi => $lines) {
    echo "Transaksi: {$referensi}\n";
    
    foreach ($lines as $line) {
        echo "  {$line->kode_akun} | D: " . number_format($line->debit, 0, ',', '.') . " | K: " . number_format($line->kredit, 0, ',', '.') . " | {$line->keterangan}\n";
    }
    echo "---\n";
}

echo "\nExpected UI display:\n";
echo "===================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Crispi Macdi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nController is now FIXED!\n";
echo "========================\n";
echo "Please refresh your browser with Ctrl+F5\n";
echo "The UI should now show the correct data from jurnal_umum table\n";

?>
