<?php

$models = [
    'Account.php', 'ApSettlement.php', 'Aset.php', 'BebanOperasional.php',
    'BiayaBahanBaku.php', 'BomJobBBB.php', 'BomJobBbbSelection.php',
    'BomJobBopSelection.php', 'BomJobBtklSelection.php', 'BomJobCosting.php',
    'Bop.php', 'BopProses.php', 'CustomerAddress.php', 'ExpensePayment.php',
    'JenisAset.php', 'JournalEntry.php', 'JournalLine.php', 'JurnalUmum.php',
    'KategoriBahanPendukung.php', 'KategoriPegawai.php', 'KomponenBop.php',
    'OngkirSetting.php', 'Order.php', 'PelunasanUtang.php',
    'PembayaranBeban.php', 'PembelianDetailKonversi.php', 'Penggajian.php',
    'Penjualan.php', 'Presensi.php', 'PresensiRecord.php', 'PresensiUser.php',
    'Produksi.php', 'ProduksiDetail.php', 'ProduksiProses.php', 'ProsesProduksi.php',
    'PurchaseReturn.php', 'PurchaseReturnItem.php', 'RekapPresensiBulanan.php',
    'ReturPenjualan.php', 'StockLayer.php', 'Perusahaan.php'
];

foreach ($models as $model) {
    $path = "app/Models/{$model}";
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // Check if it's already using HasUserScope
    if (strpos($content, 'HasUserScope') !== false) {
        continue;
    }
    
    // Check if it's a model
    if (preg_match('/class\s+[A-Za-z0-9_]+\s+extends\s+(Model|Authenticatable)\s*\{/', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $insertPos = $matches[0][1] + strlen($matches[0][0]);
        
        $newContent = substr($content, 0, $insertPos) . "\n    use \\App\\Traits\\HasUserScope;" . substr($content, $insertPos);
        
        file_put_contents($path, $newContent);
        echo "Added HasUserScope to {$model}\n";
    }
}
echo "Done.\n";
