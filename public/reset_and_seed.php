<?php
/**
 * Reset semua data + verifikasi seeder COA baru
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', 'coe12345');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$skip = ['migrations', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens'];

echo "=== TRUNCATE SEMUA DATA ===\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (in_array($t, $skip)) { echo "SKIP: $t\n"; continue; }
    $pdo->exec("TRUNCATE TABLE `$t`");
    echo "TRUNCATED: $t\n";
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== VERIFIKASI TABEL btkls PUNYA user_id ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM btkls")->fetchAll(PDO::FETCH_COLUMN);
echo in_array('user_id', $cols) ? "✓ btkls.user_id ADA\n" : "✗ btkls.user_id TIDAK ADA\n";

echo "\nSELESAI. Silakan register akun baru.\n";
