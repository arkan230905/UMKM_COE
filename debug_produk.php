<?php
$p = \App\Models\Produk::find(3);
echo 'Produk: ' . $p->nama_produk . PHP_EOL;
echo 'harga_bom: ' . $p->harga_bom . PHP_EOL;
echo 'harga_pokok: ' . $p->harga_pokok . PHP_EOL;
$bjc = $p->bomJobCosting;
if($bjc) {
    echo 'total_bbb: ' . $bjc->total_bbb . PHP_EOL;
    echo 'total_bahan_pendukung: ' . $bjc->total_bahan_pendukung . PHP_EOL;
    echo 'total_btkl: ' . $bjc->total_btkl . PHP_EOL;
    echo 'total_bop: ' . $bjc->total_bop . PHP_EOL;
    echo 'total_hpp: ' . $bjc->total_hpp . PHP_EOL;
}
