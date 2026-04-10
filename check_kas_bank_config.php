<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MENGECEK KONFIGURASI KAS DAN BANK ===\n";

// Cek akun kas yang sudah ada
echo "\n1. AKUN KAS YANG SUDAH ADA:\n";
$kasAccounts = DB::table('coas')
    ->where('nama_akun', 'like', '%kas%')
    ->orWhere('nama_akun', 'like', '%bank%')
    ->orderBy('kode_akun')
    ->get();

foreach ($kasAccounts as $kas) {
    echo "- {$kas->kode_akun}: {$kas->nama_akun} (Tipe: {$kas->tipe_akun})\n";
}

// Cek JournalService untuk melihat akun apa yang dicari
echo "\n2. MENGECEK JOURNAL SERVICE:\n";
$journalServicePath = 'app/Services/JournalService.php';
if (file_exists($journalServicePath)) {
    echo "Mencari referensi akun 1101 di JournalService...\n";
    $content = file_get_contents($journalServicePath);
    
    // Cari baris yang menggunakan 1101
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, '1101') !== false) {
            echo "Baris " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "File JournalService.php tidak ditemukan\n";
}

// Cek apakah ada konfigurasi akun kas di config atau database
echo "\n3. MENCARI KONFIGURASI AKUN KAS:\n";

// Cek apakah ada tabel konfigurasi
$tables = DB::select('SHOW TABLES');
$configTables = [];
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (strpos($tableName, 'config') !== false || strpos($tableName, 'setting') !== false) {
        $configTables[] = $tableName;
    }
}

if (!empty($configTables)) {
    echo "Tabel konfigurasi ditemukan:\n";
    foreach ($configTables as $table) {
        echo "- {$table}\n";
        
        try {
            $configs = DB::table($table)->get();
            foreach ($configs as $config) {
                if (isset($config->key) && strpos($config->key, 'kas') !== false) {
                    echo "  Config: {$config->key} = {$config->value}\n";
                }
            }
        } catch (Exception $e) {
            echo "  Error reading table: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "Tidak ada tabel konfigurasi ditemukan\n";
}

// Cek controller penjualan untuk melihat bagaimana akun kas dipilih
echo "\n4. SOLUSI YANG DISARANKAN:\n";
echo "Opsi 1: Ubah kode di JournalService.php untuk menggunakan akun 112 (Kas)\n";
echo "Opsi 2: Buat akun 1101 yang merujuk ke akun kas utama\n";
echo "Opsi 3: Tambahkan konfigurasi akun kas di sistem\n";

echo "\n5. AKUN YANG SEBAIKNYA DIGUNAKAN:\n";
echo "Untuk penjualan tunai, gunakan: 112 - Kas\n";
echo "Untuk transfer bank, gunakan: 111 - Kas Bank\n";

// Cek apakah ada laporan kas bank dan akun apa saja yang digunakan
echo "\n6. MENGECEK LAPORAN KAS BANK:\n";
$laporanKasPath = 'app/Http/Controllers/LaporanController.php';
if (file_exists($laporanKasPath)) {
    echo "Mencari konfigurasi laporan kas bank...\n";
    $content = file_get_contents($laporanKasPath);
    
    // Cari method kas bank
    if (strpos($content, 'kasBank') !== false || strpos($content, 'kas_bank') !== false) {
        echo "✅ Method laporan kas bank ditemukan\n";
        
        // Cari akun yang digunakan
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, '111') !== false || strpos($line, '112') !== false || strpos($line, '113') !== false) {
                echo "Baris " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "Method laporan kas bank tidak ditemukan\n";
    }
} else {
    echo "File LaporanController.php tidak ditemukan\n";
}

echo "\n=== REKOMENDASI ===\n";
echo "1. Gunakan akun 112 (Kas) untuk penjualan tunai\n";
echo "2. Pastikan akun 112 masuk dalam laporan kas dan bank\n";
echo "3. Ubah JournalService.php untuk menggunakan akun 112 instead of 1101\n";