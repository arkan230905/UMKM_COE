<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', 'coe12345');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$skip = ['migrations', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens'];

// TRUNCATE semua data
echo "=== TRUNCATE SEMUA DATA ===\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN) as $t) {
    if (in_array($t, $skip)) { echo "SKIP: $t\n"; continue; }
    $pdo->exec("TRUNCATE TABLE `$t`");
    echo "TRUNCATED: $t\n";
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Bootstrap Laravel untuk test seeder
require_once '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== TEST SEEDER COA ===\n";

// Buat user test sementara
$now = date('Y-m-d H:i:s');
$pdo->exec("INSERT INTO users (id, name, email, password, created_at, updated_at) VALUES (999, 'Test User', 'test@test.com', 'xxx', '$now', '$now')");

// Jalankan seeder
$seeder = new \Database\Seeders\DefaultCoaSeeder();
$seeder->run(999);

$count = $pdo->query("SELECT COUNT(*) FROM coas WHERE user_id=999")->fetchColumn();
echo "COA dibuat: $count akun\n\n";

echo "=== DAFTAR 51 COA ===\n";
$stmt = $pdo->query("SELECT kode_akun, nama_akun, tipe_akun, saldo_normal FROM coas WHERE user_id=999 ORDER BY kode_akun+0, kode_akun");
$no = 1;
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo sprintf("%2d. %-6s | %-45s | %-12s | %s\n", $no++, $r['kode_akun'], $r['nama_akun'], $r['tipe_akun'], $r['saldo_normal']);
}

// Hapus data test
$pdo->exec("DELETE FROM coas WHERE user_id=999");
$pdo->exec("DELETE FROM users WHERE id=999");

echo "\n✓ Test selesai. Data test dihapus.\n";
echo "✓ Database bersih dan siap.\n";
echo "✓ Silakan register di: http://jobcost.eadtmanufaktur.com/register\n";
