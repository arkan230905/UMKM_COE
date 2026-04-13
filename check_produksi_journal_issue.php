<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING PRODUKSI JOURNAL ISSUE\n";
echo "================================\n\n";

// Check journals on 12/04/2026
echo "1. Journals on 12/04/2026:\n";
echo "==========================\n";

$journals = App\Models\JurnalUmum::whereDate('tanggal', '2026-04-12')
    ->with('coa')
    ->orderBy('id')
    ->get();

foreach ($journals as $journal) {
    $type = $journal->debit > 0 ? 'DEBIT' : 'KREDIT';
    $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
    echo "  {$journal->referensi} | {$journal->coa->kode_akun} - {$journal->coa->nama_akun} | {$type} | Rp " . number_format($amount, 0, ',', '.') . " | {$journal->tipe_referensi}\n";
}

echo "\n2. Check produksi data:\n";
echo "=====================\n";

$produksi = App\Models\Produksi::whereDate('tanggal', '2026-04-12')->get();

foreach ($produksi as $p) {
    echo "Produksi ID: {$p->id}\n";
    echo "Tanggal: {$p->tanggal}\n";
    echo "Status: {$p->status}\n";
    echo "Total Biaya: " . number_format($p->total_biaya, 0, ',', '.') . "\n";
    
    echo "Details:\n";
    $details = App\Models\ProduksiDetail::where('produksi_id', $p->id)->get();
    foreach ($details as $d) {
        echo "  - {$d->tipe}: {$d->nama_item} | Qty: {$d->jumlah} | Harga: {$d->harga_satuan} | Total: " . number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') . "\n";
    }
    echo "---\n";
}

echo "\n3. Check produk data for 'Ayam Goreng Bundo':\n";
echo "===========================================\n";

$produk = App\Models\Produk::where('nama_produk', 'Ayam Goreng Bundo')->first();

if ($produk) {
    echo "Produk Found:\n";
    echo "  ID: {$produk->id}\n";
    echo "  Nama: {$produk->nama_produk}\n";
    echo "  COA Persediaan: " . ($produk->coa_persediaan_id ?? 'NULL') . "\n";
    
    if ($produk->coa_persediaan_id) {
        $coa = App\Models\Coa::find($produk->coa_persediaan_id);
        if ($coa) {
            echo "  COA: {$coa->kode_akun} - {$coa->nama_akun}\n";
        }
    }
} else {
    echo "Produk 'Ayam Goreng Bundo' not found!\n";
}

echo "\n4. Check available COA for persediaan:\n";
echo "=================================\n";

$coaPersediaan = App\Models\Coa::where('nama_akun', 'like', '%Pers.%')
    ->orWhere('kode_akun', 'like', '116%')
    ->orWhere('kode_akun', 'like', '117%')
    ->get();

foreach ($coaPersediaan as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

echo "\n5. Check if there's a specific COA for 'Ayam Goreng Bundo':\n";
echo "===================================================\n";

$coaAyamBundo = App\Models\Coa::where('nama_akun', 'like', '%Ayam Goreng Bundo%')
    ->orWhere('nama_akun', 'like', '%Ayam Goreng%')
    ->get();

foreach ($coaAyamBundo as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

?>
