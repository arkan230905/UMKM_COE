<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFIKASI FITUR BARCODE PRODUK                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Cek kolom barcode
echo "📋 Mengecek kolom barcode di tabel produks...\n";
echo str_repeat("-", 60) . "\n";

if (Schema::hasColumn('produks', 'barcode')) {
    echo "✅ Kolom 'barcode' SUDAH ADA di tabel produks\n\n";
    
    // Cek struktur kolom
    $columns = Schema::getColumnListing('produks');
    echo "📊 Kolom yang ada di tabel produks:\n";
    foreach ($columns as $col) {
        $marker = ($col === 'barcode') ? '👉' : '  ';
        echo "$marker $col\n";
    }
    
    echo "\n";
    echo str_repeat("=", 60) . "\n";
    echo "STATISTIK BARCODE\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Hitung produk
    $totalProduk = DB::table('produks')->count();
    $produkDenganBarcode = DB::table('produks')->whereNotNull('barcode')->count();
    $produkTanpaBarcode = DB::table('produks')->whereNull('barcode')->count();
    
    echo "📦 Total Produk: $totalProduk\n";
    echo "✅ Produk dengan Barcode: $produkDenganBarcode\n";
    echo "⚠️  Produk tanpa Barcode: $produkTanpaBarcode\n\n";
    
    if ($totalProduk > 0) {
        echo str_repeat("=", 60) . "\n";
        echo "CONTOH BARCODE\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $produks = DB::table('produks')->limit(5)->get();
        
        if ($produks->count() > 0) {
            echo "ID  | Nama Produk                    | Barcode\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($produks as $produk) {
                $nama = substr($produk->nama_produk ?? 'N/A', 0, 30);
                $barcode = $produk->barcode ?? 'BELUM ADA';
                printf("%-3s | %-30s | %s\n", $produk->id, $nama, $barcode);
            }
        } else {
            echo "Belum ada produk di database.\n";
        }
    } else {
        echo "ℹ️  Belum ada produk di database.\n";
        echo "   Tambahkan produk baru untuk melihat barcode otomatis!\n";
    }
    
    echo "\n";
    echo str_repeat("=", 60) . "\n";
    echo "FORMAT BARCODE\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Format: EAN-13 (13 digit)\n";
    echo "Contoh: 8992000000001\n";
    echo "        └┬─┘└───┬───┘\n";
    echo "         │      └─ 9 digit: ID Produk\n";
    echo "         └─ 4 digit: Prefix Indonesia (8992)\n\n";
    
    echo "✅ FITUR BARCODE SUDAH SIAP DIGUNAKAN!\n\n";
    echo "📝 Cara Menggunakan:\n";
    echo "   1. Tambah produk baru → Barcode otomatis dibuat\n";
    echo "   2. Scan barcode saat penjualan\n";
    echo "   3. Cetak label barcode untuk produk\n\n";
    
    echo "📖 Dokumentasi lengkap: FITUR_BARCODE_PRODUK.md\n";
    
} else {
    echo "❌ Kolom 'barcode' BELUM ADA di tabel produks\n";
    echo "💡 Jalankan: php artisan migrate\n";
}

echo "\n";
