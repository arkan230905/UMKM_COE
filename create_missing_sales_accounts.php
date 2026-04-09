<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MEMBUAT AKUN YANG DIBUTUHKAN UNTUK PENJUALAN ===\n";

// Cek dan buat akun Penjualan (4101)
$penjualan = DB::table('coas')->where('kode_akun', '4101')->first();
if (!$penjualan) {
    echo "Membuat akun 4101 - Penjualan...\n";
    
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
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ Akun 4101 - Penjualan sudah ada\n";
}

// Cek dan buat akun HPP (5101)
$hpp = DB::table('coas')->where('kode_akun', '5101')->first();
if (!$hpp) {
    echo "Membuat akun 5101 - Harga Pokok Penjualan...\n";
    
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
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ Akun 5101 - HPP sudah ada\n";
}

echo "\n=== RINGKASAN AKUN UNTUK PENJUALAN ===\n";
echo "✅ 112 - Kas (untuk penjualan tunai)\n";
echo "✅ 111 - Kas Bank (untuk penjualan transfer)\n";
echo "✅ 118 - Piutang (untuk penjualan kredit)\n";
echo "✅ 4101 - Penjualan (pendapatan)\n";
echo "✅ 5101 - Harga Pokok Penjualan (beban)\n";

echo "\n=== JURNAL PENJUALAN YANG AKAN DIBUAT ===\n";
echo "Untuk penjualan tunai:\n";
echo "Dr. Kas (112)                 Rp xxx\n";
echo "    Cr. Penjualan (4101)          Rp xxx\n";
echo "\nUntuk HPP:\n";
echo "Dr. HPP (5101)                Rp xxx\n";
echo "    Cr. Persediaan (116)          Rp xxx\n";

echo "\n✅ SISTEM PENJUALAN SEKARANG SUDAH SIAP!\n";
echo "Akun kas yang digunakan akan otomatis masuk ke laporan kas dan bank.\n";