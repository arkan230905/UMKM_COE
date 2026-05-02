<?php
/**
 * SCRIPT EXPORT DATABASE LANGSUNG
 * ================================
 * Script ini akan langsung export database eadt_umkm
 * dan memperbaikinya agar siap dibagikan ke teman-teman
 * 
 * CARA PAKAI:
 * Jalankan di terminal: php export_database_sekarang.php
 * Atau buka di browser: http://localhost/export_database_sekarang.php
 */

// Konfigurasi database dari .env
$config = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'eadt_umkm',
    'username' => 'root',
    'password' => ''
];

// Nama file output
$outputFileName = 'eadt_umkm_export_fixed_' . date('Ymd_His') . '.sql';
$outputFilePath = __DIR__ . '/' . $outputFileName;

echo "==============================================\n";
echo "EXPORT & PERBAIKI DATABASE OTOMATIS\n";
echo "==============================================\n\n";

echo "Database: {$config['database']}\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Output: {$outputFileName}\n\n";

echo "Memulai proses export...\n\n";

try {
    // Koneksi ke database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✓ Koneksi database berhasil\n";
    
    $output = "";
    
    // ============================================
    // HEADER SQL - BAGIAN PENTING UNTUK FIX ERROR
    // ============================================
    $output .= "-- ============================================\n";
    $output .= "-- Database Export: {$config['database']}\n";
    $output .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Host: {$config['host']}\n";
    $output .= "-- File sudah diperbaiki dan siap dibagikan!\n";
    $output .= "-- ============================================\n\n";
    
    // PERINTAH PENTING: Disable foreign key checks
    $output .= "-- Disable foreign key checks untuk mencegah error\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET AUTOCOMMIT = 0;\n";
    $output .= "START TRANSACTION;\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";
    
    $output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
    $output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
    $output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
    $output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
    
    // Create database statement
    $output .= "-- ============================================\n";
    $output .= "-- Database: `{$config['database']}`\n";
    $output .= "-- ============================================\n\n";
    $output .= "CREATE DATABASE IF NOT EXISTS `{$config['database']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    $output .= "USE `{$config['database']}`;\n\n";
    
    echo "✓ Header SQL ditambahkan\n";
    
    // Ambil semua tabel
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        throw new Exception('Database kosong atau tidak ada tabel');
    }
    
    $totalTables = count($tables);
    echo "✓ Ditemukan {$totalTables} tabel\n\n";
    
    echo "Memproses tabel:\n";
    
    // Loop setiap tabel
    foreach ($tables as $index => $table) {
        $tableNum = $index + 1;
        echo "  [{$tableNum}/{$totalTables}] {$table}";
        
        $output .= "-- ============================================\n";
        $output .= "-- Tabel: `{$table}` ({$tableNum}/{$totalTables})\n";
        $output .= "-- ============================================\n\n";
        
        // DROP TABLE
        $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // CREATE TABLE
        $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= $createTable['Create Table'] . ";\n\n";
        
        // Hitung jumlah rows
        $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        $rowCount = $stmt->fetchColumn();
        
        echo " - {$rowCount} rows";
        
        // INSERT DATA
        if ($rowCount > 0) {
            $output .= "-- Data untuk tabel `{$table}` ({$rowCount} rows)\n\n";
            
            // Ambil data dengan batch untuk efisiensi
            $batchSize = 1000;
            $offset = 0;
            
            while ($offset < $rowCount) {
                $stmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    // Ambil nama kolom
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    
                    // Insert data dalam batch (100 rows per INSERT untuk keamanan)
                    $insertBatchSize = 100;
                    $insertBatches = array_chunk($rows, $insertBatchSize);
                    
                    foreach ($insertBatches as $batch) {
                        $output .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                        
                        $values = [];
                        foreach ($batch as $row) {
                            $rowValues = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    $rowValues[] = $pdo->quote($value);
                                }
                            }
                            $values[] = '(' . implode(', ', $rowValues) . ')';
                        }
                        
                        $output .= implode(",\n", $values) . ";\n\n";
                    }
                }
                
                $offset += $batchSize;
            }
        }
        
        $output .= "\n";
        echo " ✓\n";
    }
    
    echo "\n";
    
    // ============================================
    // FOOTER SQL - RESTORE SETTINGS
    // ============================================
    $output .= "-- ============================================\n";
    $output .= "-- Selesai - Restore settings\n";
    $output .= "-- ============================================\n\n";
    $output .= "COMMIT;\n";
    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $output .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
    $output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
    $output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
    
    echo "✓ Footer SQL ditambahkan\n";
    
    // Simpan ke file
    file_put_contents($outputFilePath, $output);
    
    $fileSize = filesize($outputFilePath);
    $fileSizeFormatted = formatBytes($fileSize);
    
    echo "\n==============================================\n";
    echo "BERHASIL! ✓\n";
    echo "==============================================\n\n";
    echo "File output: {$outputFileName}\n";
    echo "Ukuran file: {$fileSizeFormatted}\n";
    echo "Total tabel: {$totalTables}\n";
    echo "Lokasi: {$outputFilePath}\n\n";
    
    echo "File sudah diperbaiki dan siap dibagikan!\n";
    echo "Teman Anda bisa langsung import tanpa error.\n\n";
    
    // Jika dijalankan di browser, tampilkan link download
    if (php_sapi_name() !== 'cli') {
        echo '<br><br>';
        echo '<a href="' . $outputFileName . '" download style="display:inline-block;padding:15px 30px;background:#28a745;color:white;text-decoration:none;border-radius:8px;font-weight:bold;">⬇️ Download File SQL</a>';
    }
    
} catch (PDOException $e) {
    echo "\n✗ Error koneksi database:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
