<?php
/**
 * AUDIT semua tabel + TRUNCATE semua data
 * Jalankan via: php /tmp/audit_and_reset.php
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', 'coe12345');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ============================================================
// STEP 1: AUDIT - cek kolom user_id di setiap tabel
// ============================================================
echo "=== AUDIT KOLOM user_id PER TABEL ===\n";

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$tablesWithUserId    = [];
$tablesWithoutUserId = [];

foreach ($tables as $table) {
    $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('user_id', $cols)) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $tablesWithUserId[] = "$table (rows: $count)";
    } else {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $tablesWithoutUserId[] = "$table (rows: $count)";
    }
}

echo "\nTABEL DENGAN user_id (multi-tenant OK):\n";
foreach ($tablesWithUserId as $t) echo "  ✓ $t\n";

echo "\nTABEL TANPA user_id (shared/global):\n";
foreach ($tablesWithoutUserId as $t) echo "  - $t\n";

// ============================================================
// STEP 2: TRUNCATE semua data
// ============================================================
echo "\n=== TRUNCATE SEMUA DATA ===\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$skipped = ['migrations', 'password_reset_tokens', 'failed_jobs', 'personal_access_tokens'];

foreach ($tables as $table) {
    if (in_array($table, $skipped)) {
        echo "  SKIP: $table\n";
        continue;
    }
    $pdo->exec("TRUNCATE TABLE `$table`");
    echo "  TRUNCATED: $table\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== SELESAI ===\n";
echo "Semua data sudah dihapus. Tabel masih ada.\n";
echo "Silakan daftar akun baru di: http://jobcost.eadtmanufaktur.com/register\n";
