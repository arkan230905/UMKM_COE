<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', 'coe12345');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$skip = ['migrations', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens'];

echo "=== TRUNCATE SEMUA DATA ===\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN) as $t) {
    if (in_array($t, $skip)) { echo "SKIP: $t\n"; continue; }
    $pdo->exec("TRUNCATE TABLE `$t`");
    echo "TRUNCATED: $t\n";
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== TEST SEEDER COA (simulasi user_id=1) ===\n";

// Jalankan seeder langsung via PHP
require_once '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$seeder = new \Database\Seeders\DefaultCoaSeeder();
$seeder->run(1); // test dengan user_id=1

$count = $pdo->query("SELECT COUNT(*) FROM coas WHERE user_id=1")->fetchColumn();
echo "COA dibuat untuk user_id=1: $count akun\n";

// Tampilkan semua COA
echo "\n=== DAFTAR COA ===\n";
foreach ($pdo->query("SELECT kode_akun, nama_akun, tipe_akun, saldo_normal FROM coas WHERE user_id=1 ORDER BY kode_akun+0, kode_akun")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['kode_akun']} | {$r['nama_akun']} | {$r['tipe_akun']} | {$r['saldo_normal']}\n";
}

// Hapus data test
$pdo->exec("DELETE FROM coas WHERE user_id=1");
echo "\nData test dihapus. Database siap.\n";
echo "Silakan register di: http://jobcost.eadtmanufaktur.com/register\n";
