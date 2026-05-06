<?php
// Flush PHP opcache agar controller baru terbaca
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "opcache_reset: OK\n";
} else {
    echo "opcache_reset: not available\n";
}

if (function_exists('opcache_invalidate')) {
    $files = [
        __DIR__ . '/../app/Http/Controllers/MasterData/BtklController.php',
    ];
    foreach ($files as $f) {
        opcache_invalidate($f, true);
        echo "invalidated: $f\n";
    }
}

// Verifikasi controller yang aktif
$content = file_get_contents(__DIR__ . '/../app/Http/Controllers/MasterData/BtklController.php');
echo "\n--- CONTROLLER LINES 19-25 ---\n";
$lines = explode("\n", $content);
for ($i = 18; $i <= 25; $i++) {
    echo ($i+1) . ": " . $lines[$i] . "\n";
}
echo "\nDONE\n";
