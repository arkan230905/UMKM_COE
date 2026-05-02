<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', '');

echo "==============================================\n";
echo "VERIFIKASI DATA MASTER\n";
echo "==============================================\n\n";

// Hitung data master
$stmt = $pdo->query('SELECT COUNT(*) FROM coas WHERE user_id IS NULL');
$coaCount = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM satuans WHERE user_id IS NULL');
$satuanCount = $stmt->fetchColumn();

echo "✓ Data COA Master: {$coaCount} records\n";
echo "✓ Data Satuan Master: {$satuanCount} records\n\n";

// Sample COA
echo "Sample Data COA (10 pertama):\n";
echo "================================\n";
$stmt = $pdo->query('SELECT kode_akun, nama_akun, tipe_akun, saldo_normal FROM coas WHERE user_id IS NULL LIMIT 10');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("  %-6s | %-40s | %-20s | %s\n", 
        $row['kode_akun'], 
        $row['nama_akun'], 
        $row['tipe_akun'],
        $row['saldo_normal']
    );
}

echo "\n";

// Sample Satuan
echo "Sample Data Satuan (semua):\n";
echo "============================\n";
$stmt = $pdo->query('SELECT kode, nama, tipe, kategori FROM satuans WHERE user_id IS NULL');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("  %-8s | %-20s | %-10s | %s\n", 
        $row['kode'], 
        $row['nama'], 
        $row['tipe'],
        $row['kategori']
    );
}

echo "\n==============================================\n";
echo "DATABASE SIAP UNTUK DI-EXPORT!\n";
echo "==============================================\n";
