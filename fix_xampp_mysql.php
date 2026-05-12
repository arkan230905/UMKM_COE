<?php

echo "=== PERBAIKAN MASALAH XAMPP MYSQL ===\n\n";

// 1. Cek proses yang menggunakan port MySQL
echo "1. CEK PROSES YANG MENGGUNAKAN PORT 3306:\n";
exec('netstat -ano | findstr ":3306"', $output, $returnCode);
echo "Output: $output\n";

if (strpos($output, ':3306') !== false) {
    echo "⚠️  PORT 3306 SUDAH DIGUNAKAN!\n";
    echo "   Solusi 1: Matikan aplikasi lain yang menggunakan port 3306\n";
    echo "   Solusi 2: Ubah port MySQL di XAMPP\n";
    echo "   Solusi 3: Stop service lain yang menggunakan port 3306\n\n";
} else {
    echo "✅ Port 3306 tersedia\n\n";
}

// 2. Cek service yang berjalan
echo "2. CEK SERVICE YANG BERJALAN:\n";
exec('tasklist | findstr "mysql"', $output, $returnCode);
echo "Output: $output\n";

if (strpos($output, 'mysql') !== false) {
    echo "⚠️  MYSQL SERVICE SUDAH BERJALAN!\n";
    echo "   Solusi: Stop service MySQL terlebih dahulu\n\n";
} else {
    echo "✅ Tidak ada service MySQL yang berjalan\n\n";
}

// 3. Cek XAMPP Control Panel
echo "3. CEK XAMPP CONTROL PANEL:\n";
if (file_exists('C:/xampp/mysql/data/mysql.pid')) {
    echo "⚠️  MYSQL PID FILE DITEMUKAN!\n";
    echo "   MySQL mungkin masih berjalan di background\n";
    echo "   Solusi: Hapus file C:/xampp/mysql/data/mysql.pid\n\n";
} else {
    echo "✅ Tidak ada MySQL PID file\n\n";
}

// 4. Cek konfigurasi my.ini
echo "4. CEK KONFIGURASI MY.INI:\n";
$myIniPath = 'C:/xampp/mysql/bin/my.ini';
if (file_exists($myIniPath)) {
    echo "✅ File my.ini ditemukan di: $myIniPath\n";
    
    $myIniContent = file_get_contents($myIniPath);
    
    // Cek konfigurasi port
    if (strpos($myIniContent, 'port=3306') !== false) {
        echo "✅ Port di my.ini sudah 3306\n";
    } else {
        echo "⚠️  Port di my.ini bukan 3306!\n";
    }
    
    // Cek konfigurasi datadir
    if (strpos($myIniContent, 'datadir=C:/xampp/mysql/data') !== false) {
        echo "✅ Data directory sudah benar\n";
    } else {
        echo "⚠️  Data directory mungkin salah!\n";
    }
} else {
    echo "❌ File my.ini tidak ditemukan!\n";
}

// 5. Berikan solusi langkah demi langkah
echo "\n=== SOLUSI LANGKAH DEMI LANGKAH ===\n";
echo "LANGKAH 1: Matikan semua aplikasi yang menggunakan port 3306\n";
echo "   - Buka Task Manager (Ctrl+Shift+Esc)\n";
echo "   - Cari proses 'mysql' atau 'mysqld'\n";
echo "   - End task semua proses MySQL\n\n";

echo "LANGKAH 2: Ubah port MySQL di XAMPP (opsional)\n";
echo "   - Buka XAMPP Control Panel\n";
echo "   - Klik MySQL -> Config\n";
echo "   - Ubah port dari 3306 ke 3307 (atau port lain)\n";
echo "   - Restart MySQL\n\n";

echo "LANGKAH 3: Hapus file PID yang tersisa\n";
echo "   - Buka File Explorer\n";
echo "   - Navigasi ke C:/xampp/mysql/data/\n";
echo "   - Hapus file mysql.pid (jika ada)\n";
echo "   - Hapus file *.err (jika ada)\n\n";

echo "LANGKAH 4: Restart XAMPP dengan urutan benar\n";
echo "   - Stop XAMPP Control Panel\n";
echo "   - Tunggu 5 detik\n";
echo "   - Start XAMPP Control Panel\n";
echo "   - Start Apache dan MySQL\n\n";

echo "LANGKAH 5: Test koneksi setelah perbaikan\n";
echo "   - Buka command prompt\n";
echo "   - Jalankan: cd C:/xampp/mysql/bin\n";
echo "   - Jalankan: mysql -u root -e \"SHOW DATABASES;\"\n";
echo "   - Jika berhasil, berarti MySQL sudah berjalan\n\n";

echo "=== CEK PORT LAIN YANG MUNGKIN DIGUNAKAN ===\n";
exec('netstat -ano | findstr "LISTENING"', $output, $returnCode);
echo "Output: $output\n";

// Cek port yang umum digunakan
$portsToCheck = ['3307', '3308', '3309', '5432'];
foreach ($portsToCheck as $port) {
    if (strpos($output, ':' . $port) !== false) {
        echo "Port $port sedang digunakan oleh aplikasi lain\n";
    }
}

echo "\n=== REKOMENDASI PORT ===\n";
echo "Jika port 3306 bermasalah, gunakan port berikut:\n";
echo "- Port 3307 (rekomendasi untuk XAMPP)\n";
echo "- Port 3308\n";
echo "- Port 3309\n";
echo "- Port 5432 (untuk PostgreSQL)\n\n";

echo "=== SETELAH SELESAI ===\n";
echo "1. Restart XAMPP Control Panel\n";
echo "2. Start Apache dan MySQL\n";
echo "3. Test koneksi dengan: php artisan tinker --execute=\"DB::connection()->getPdo(); echo 'OK';\"\n";
echo "4. Jika berhasil, jalankan: php artisan migrate:fresh\n";
echo "5. Jalankan: php artisan serve --host=127.0.0.1 --port=8000\n\n";

echo "✅ SEMUA SOLUSI TELAH DIBERIKAN!\n";
?>
