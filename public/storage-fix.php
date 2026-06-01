<?php
/**
 * STORAGE FIX SCRIPT - HAPUS FILE INI SETELAH SELESAI!
 * Akses via: http://jobcost.eadtmanufaktur.com/storage-fix.php
 * 
 * Script ini membuat folder runtime Laravel yang dibutuhkan
 * dan memverifikasi write permission.
 */

// Keamanan: password sederhana
$pass = $_GET['key'] ?? '';
if ($pass !== 'fixstorage2026') {
    die('Akses ditolak. Tambahkan ?key=fixstorage2026 di URL.');
}

$base = dirname(__DIR__); // root project (satu level di atas public/)

$dirs = [
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/logs',
    'bootstrap/cache',
];

echo '<html><head><meta charset="utf-8"><style>
body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px}
.ok{color:#4ec9b0}.err{color:#f48771}.warn{color:#dcdcaa}
h2{color:#569cd6}pre{background:#252526;padding:10px;border-radius:4px}
</style></head><body>';
echo '<h2>🔧 Laravel Storage Fix Script</h2>';
echo '<p>Base path: <b>' . htmlspecialchars($base) . '</b></p>';
echo '<hr>';

$allOk = true;

foreach ($dirs as $dir) {
    $fullPath = $base . '/' . $dir;
    echo "<b>$dir</b><br>";

    // Buat folder jika belum ada
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0775, true)) {
            echo "<span class='ok'>  ✅ CREATED</span><br>";
        } else {
            echo "<span class='err'>  ❌ FAILED to create</span><br>";
            $allOk = false;
            continue;
        }
    } else {
        echo "<span class='ok'>  ✅ EXISTS</span><br>";
    }

    // Buat .gitignore jika belum ada
    $gitignore = $fullPath . '/.gitignore';
    if (!file_exists($gitignore)) {
        file_put_contents($gitignore, "*\n!.gitignore\n");
        echo "<span class='warn'>  📄 .gitignore created</span><br>";
    }

    // Test write permission
    $testFile = $fullPath . '/.write_test_' . time();
    if (file_put_contents($testFile, 'test') !== false) {
        unlink($testFile);
        echo "<span class='ok'>  ✅ WRITABLE</span><br>";
    } else {
        echo "<span class='err'>  ❌ NOT WRITABLE — jalankan: chmod -R 775 $dir</span><br>";
        $allOk = false;
    }

    // Tampilkan permissions
    $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($fullPath))['name'] : 'n/a';
    $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($fullPath))['name'] : 'n/a';
    echo "<span class='warn'>  📋 Permission: $perms | Owner: $owner | Group: $group</span><br>";
    echo "<br>";
}

echo '<hr>';
if ($allOk) {
    echo "<h2 class='ok'>✅ Semua folder siap! Sekarang jalankan artisan commands di bawah.</h2>";
} else {
    echo "<h2 class='err'>❌ Ada folder yang tidak writable. Jalankan chmod via SSH atau cPanel.</h2>";
}

echo '<hr><h2>📋 Commands untuk dijalankan via SSH:</h2>';
echo '<pre>';
echo htmlspecialchars(
"# Dari root project (/var/www/html)
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Jika perlu (set permission manual)
chmod -R 775 storage/ bootstrap/cache/
# Ganti 'www-data' dengan user PHP hosting Anda:
chown -R www-data:www-data storage/ bootstrap/cache/
"
);
echo '</pre>';

// Info PHP user
echo '<hr><h2>🔍 Info Server</h2>';
echo '<pre>';
echo 'PHP version : ' . PHP_VERSION . "\n";
echo 'PHP user    : ' . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user()) . "\n";
echo 'Server OS   : ' . PHP_OS . "\n";
echo 'storage/    : ' . (is_writable($base . '/storage') ? '✅ WRITABLE' : '❌ NOT WRITABLE') . "\n";
echo 'bootstrap/  : ' . (is_writable($base . '/bootstrap/cache') ? '✅ WRITABLE' : '❌ NOT WRITABLE') . "\n";
echo '</pre>';

echo '<p style="color:#f48771"><b>⚠️ PENTING: Hapus file ini setelah selesai! (storage-fix.php di folder public/)</b></p>';
echo '</body></html>';
