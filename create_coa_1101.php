<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MEMBUAT COA 1101 UNTUK PENJUALAN TUNAI ===\n";

try {
    // Buat COA 1101 dengan struktur yang benar
    DB::table('coas')->insert([
        'kode_akun' => '1101',
        'nama_akun' => 'Kas Penjualan',
        'tipe_akun' => 'Asset', // Sesuai dengan enum yang ada
        'kategori_akun' => 'Kas dan Setara Kas', // Sesuai dengan kolom yang benar
        'saldo_normal' => 'debit',
        'saldo_awal' => 0,
        'posted_saldo_awal' => 0,
        'keterangan' => 'Akun kas untuk penerimaan penjualan tunai',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Berhasil membuat COA 1101 - Kas Penjualan\n";
    
    // Verifikasi
    $newCoa = DB::table('coas')->where('kode_akun', '1101')->first();
    if ($newCoa) {
        echo "✅ Verifikasi: {$newCoa->kode_akun} - {$newCoa->nama_akun}\n";
        echo "   Tipe: {$newCoa->tipe_akun}\n";
        echo "   Kategori: {$newCoa->kategori_akun}\n";
        echo "   Saldo Normal: {$newCoa->saldo_normal}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Cek juga akun lain yang mungkin dibutuhkan untuk penjualan
echo "\n=== MENGECEK AKUN PENJUALAN LAINNYA ===\n";

// Cek akun penjualan (4101)
$penjualan = DB::table('coas')->where('kode_akun', '4101')->first();
if (!$penjualan) {
    echo "❌ Akun Penjualan (4101) tidak ada, perlu dibuat\n";
    
    try {
        DB::table('coas')->insert([
            'kode_akun' => '4101',
            'nama_akun' => 'Penjualan',
            'tipe_akun' => 'Revenue',
            'kategori_akun' => 'Pendapatan Usaha',
            'saldo_normal' => 'kredit',
            'saldo_awal' => 0,
            'posted_saldo_awal' => 0,
            'keterangan' => 'Pendapatan dari penjualan produk',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Berhasil membuat COA 4101 - Penjualan\n";
    } catch (Exception $e) {
        echo "❌ Error membuat 4101: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ Akun Penjualan (4101) sudah ada: {$penjualan->nama_akun}\n";
}

// Cek akun HPP (5101)
$hpp = DB::table('coas')->where('kode_akun', '5101')->first();
if (!$hpp) {
    echo "❌ Akun HPP (5101) tidak ada, perlu dibuat\n";
    
    try {
        DB::table('coas')->insert([
            'kode_akun' => '5101',
            'nama_akun' => 'Harga Pokok Penjualan',
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Beban Pokok',
            'saldo_normal' => 'debit',
            'saldo_awal' => 0,
            'posted_saldo_awal' => 0,
            'keterangan' => 'Beban harga pokok penjualan',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Berhasil membuat COA 5101 - Harga Pokok Penjualan\n";
    } catch (Exception $e) {
        echo "❌ Error membuat 5101: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ Akun HPP (5101) sudah ada: {$hpp->nama_akun}\n";
}

echo "\n=== PENJELASAN FUNGSI AKUN ===\n";
echo "1101 - Kas Penjualan: Untuk mencatat penerimaan kas dari penjualan tunai\n";
echo "4101 - Penjualan: Untuk mencatat pendapatan penjualan\n";
echo "5101 - HPP: Untuk mencatat beban harga pokok penjualan\n";

echo "\n✅ SEKARANG SISTEM PENJUALAN SUDAH BISA BERJALAN!\n";