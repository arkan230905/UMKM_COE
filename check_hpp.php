<?php
// Check all products and their HPP status
$produks = \App\Models\Produk::with('bomJobCosting')->get();
echo "PRODUK DAN HPP STATUS:\n";
echo "========================\n";
foreach($produks as $p) {
    echo "ID: {$p->id}\n";
    echo "Nama: {$p->nama_produk}\n";
    echo "harga_bom: " . ($p->harga_bom ?? 0) . "\n";
    echo "BomJobCosting ID: " . ($p->bomJobCosting ? $p->bomJobCosting->id : 'NULL') . "\n";
    if ($p->bomJobCosting) {
        echo "  - total_bbb: " . $p->bomJobCosting->total_bbb . "\n";
        echo "  - total_bahan_pendukung: " . $p->bomJobCosting->total_bahan_pendukung . "\n";
        echo "  - total_btkl: " . $p->bomJobCosting->total_btkl . "\n";
        echo "  - total_bop: " . $p->bomJobCosting->total_bop . "\n";
        echo "  - total_hpp: " . $p->bomJobCosting->total_hpp . "\n";
    }
    echo "---\n";
}
