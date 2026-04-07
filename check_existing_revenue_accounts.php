<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MENGECEK AKUN PENDAPATAN YANG SUDAH ADA ===\n";

// Cek akun pendapatan (Revenue)
$revenueAccounts = DB::table('coas')
    ->where('tipe_akun', 'Revenue')
    ->orWhere('tipe_akun', 'Pendapatan')
    ->orderBy('kode_akun')
    ->get();

if ($revenueAccounts->count() > 0) {
    echo "Akun Pendapatan yang sudah ada:\n";
    foreach ($revenueAccounts as $account) {
        echo "- {$account->kode_akun}: {$account->nama_akun}\n";
    }
} else {
    echo "Tidak ada akun pendapatan ditemukan\n";
}

// Cek akun yang digunakan di laporan kas dan bank
echo "\n=== MENGECEK LAPORAN KAS DAN BANK ===\n";

// Cari file laporan kas bank
$laporanFiles = [
    'app/Http/Controllers/LaporanController.php',
    'resources/views/laporan/kas-bank/index.blade.php',
    'app/Services/LaporanKasBankService.php'
];

foreach ($laporanFiles as $file) {
    if (file_exists($file)) {
        echo "Mengecek file: {$file}\n";
        $content = file_get_contents($file);
        
        // Cari akun kas yang digunakan
        if (strpos($content, '111') !== false || strpos($content, '112') !== false || strpos($content, '113') !== false) {
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (preg_match('/[\'"]11[123][\'"]/', $line)) {
                    echo "  Baris " . ($lineNum + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

// Cek akun kas mana yang digunakan untuk laporan kas bank
echo "\n=== AKUN KAS UNTUK LAPORAN KAS BANK ===\n";
echo "Berdasarkan COA yang ada:\n";
echo "- 111: Kas Bank (untuk transaksi bank/transfer)\n";
echo "- 112: Kas (untuk transaksi tunai)\n";
echo "- 113: Kas Kecil (untuk pengeluaran kecil)\n";

echo "\n=== REKOMENDASI PENGATURAN ===\n";
echo "Untuk penjualan:\n";
echo "- Penjualan TUNAI → Akun 112 (Kas)\n";
echo "- Penjualan TRANSFER → Akun 111 (Kas Bank)\n";
echo "- Penjualan KREDIT → Akun 118 (Piutang)\n";

echo "\nAkun-akun ini akan otomatis masuk ke laporan kas dan bank\n";
echo "karena mereka adalah akun kas/bank yang legitimate.\n";

// Cek apakah ada akun pendapatan yang cocok untuk penjualan
echo "\n=== MENCARI AKUN PENDAPATAN UNTUK PENJUALAN ===\n";
$salesRevenue = DB::table('coas')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%penjualan%')
              ->orWhere('nama_akun', 'like', '%sales%')
              ->orWhere('nama_akun', 'like', '%pendapatan%');
    })
    ->orderBy('kode_akun')
    ->get();

if ($salesRevenue->count() > 0) {
    echo "Akun pendapatan yang cocok untuk penjualan:\n";
    foreach ($salesRevenue as $account) {
        echo "- {$account->kode_akun}: {$account->nama_akun}\n";
    }
} else {
    echo "Tidak ada akun pendapatan khusus penjualan ditemukan\n";
    echo "Perlu menggunakan akun pendapatan umum yang ada\n";
}

echo "\n✅ KESIMPULAN:\n";
echo "1. Gunakan akun kas yang sudah ada (111, 112, 113)\n";
echo "2. Gunakan akun pendapatan yang sudah ada\n";
echo "3. Akun kas akan otomatis masuk laporan kas dan bank\n";
echo "4. Tidak perlu membuat akun baru\n";