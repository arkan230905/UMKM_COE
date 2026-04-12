<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== AVAILABLE COA ACCOUNTS ===\n\n";

// Check for Hutang/Utang accounts
echo "Hutang/Utang accounts:\n";
$stmt = $pdo->prepare("SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%utang%' OR nama_akun LIKE '%hutang%' ORDER BY kode_akun");
$stmt->execute();
$hutangAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($hutangAccounts as $account) {
    echo "- {$account['kode_akun']}: {$account['nama_akun']}\n";
}

// Check for Kas/Bank accounts
echo "\nKas/Bank accounts:\n";
$stmt2 = $pdo->prepare("SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%kas%' OR nama_akun LIKE '%bank%' ORDER BY kode_akun");
$stmt2->execute();
$kasAccounts = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($kasAccounts as $account) {
    echo "- {$account['kode_akun']}: {$account['nama_akun']}\n";
}

// Check for PPN accounts
echo "\nPPN accounts:\n";
$stmt3 = $pdo->prepare("SELECT kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%ppn%' ORDER BY kode_akun");
$stmt3->execute();
$ppnAccounts = $stmt3->fetchAll(PDO::FETCH_ASSOC);

foreach ($ppnAccounts as $account) {
    echo "- {$account['kode_akun']}: {$account['nama_akun']}\n";
}

// Check all liability accounts (2xxx)
echo "\nAll liability accounts (2xxx):\n";
$stmt4 = $pdo->prepare("SELECT kode_akun, nama_akun FROM coas WHERE kode_akun LIKE '2%' ORDER BY kode_akun");
$stmt4->execute();
$liabilityAccounts = $stmt4->fetchAll(PDO::FETCH_ASSOC);

foreach ($liabilityAccounts as $account) {
    echo "- {$account['kode_akun']}: {$account['nama_akun']}\n";
}