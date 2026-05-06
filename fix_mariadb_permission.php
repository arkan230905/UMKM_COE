<?php
/**
 * Script untuk fix MariaDB permission issue
 * 
 * CARA PAKAI:
 * 1. Stop MySQL di XAMPP Control Panel
 * 2. Edit C:\xampp\mysql\bin\my.ini
 * 3. Tambahkan baris: skip-grant-tables di bagian [mysqld]
 * 4. Start MySQL lagi
 * 5. Jalankan: php fix_mariadb_permission.php
 * 6. Hapus baris skip-grant-tables dari my.ini
 * 7. Restart MySQL
 */

echo "=== MariaDB Permission Fix Script ===\n\n";

// Coba connect tanpa password (karena skip-grant-tables)
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "✓ Connected to MariaDB successfully!\n\n";
    
    // Fix permission untuk root@localhost
    echo "Fixing permissions for root@localhost...\n";
    
    $queries = [
        "CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY ''",
        "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION",
        "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY ''",
        "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION",
        "CREATE USER IF NOT EXISTS 'root'@'::1' IDENTIFIED BY ''",
        "GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' WITH GRANT OPTION",
        "FLUSH PRIVILEGES"
    ];
    
    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
            echo "✓ " . substr($query, 0, 50) . "...\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== DONE! ===\n";
    echo "Sekarang:\n";
    echo "1. Edit C:\\xampp\\mysql\\bin\\my.ini\n";
    echo "2. HAPUS baris: skip-grant-tables\n";
    echo "3. Restart MySQL di XAMPP Control Panel\n";
    echo "4. Test dengan: php artisan db:show\n";
    
} catch (PDOException $e) {
    echo "✗ Cannot connect to MariaDB!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Pastikan:\n";
    echo "1. MySQL service running di XAMPP\n";
    echo "2. File my.ini sudah ditambahkan: skip-grant-tables\n";
    echo "3. MySQL sudah di-restart setelah edit my.ini\n";
}
