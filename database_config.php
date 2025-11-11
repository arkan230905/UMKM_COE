<?php
// Konfigurasi Database untuk XAMPP
$config = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'eadt_umkm_lama',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_PREFIX' => '',
];

// Update file .env
$envPath = __DIR__ . '/.env';
$envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

foreach ($config as $key => $value) {
    $pattern = "/^" . preg_quote($key, '/') . "=.*$/m";
    $replacement = "{$key}={$value}";
    
    if (preg_match($pattern, $envContent)) {
        $envContent = preg_replace($pattern, $replacement, $envContent);
    } else {
        $envContent .= "\n{$replacement}";
    }
}

file_put_contents($envPath, trim($envContent));

echo "Konfigurasi database telah diperbarui untuk XAMPP.\n";
