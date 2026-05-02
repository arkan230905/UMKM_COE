<?php
/**
 * BERSIHKAN DATA TESTING UNTUK HOSTING
 * =====================================
 * Script ini akan membersihkan data testing/transaksi
 * tapi TETAP MEMPERTAHANKAN data master (COA & Satuan)
 * 
 * CARA PAKAI:
 * php bersihkan_data_testing_untuk_hosting.php
 */

// Konfigurasi database
$config = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'eadt_umkm',
    'username' => 'root',
    'password' => ''
];

echo "==============================================\n";
echo "BERSIHKAN DATA TESTING UNTUK HOSTING\n";
echo "==============================================\n\n";

echo "⚠️  PERINGATAN:\n";
echo "Script ini akan menghapus data transaksi/testing.\n";
echo "Data master (COA & Satuan) akan TETAP DIPERTAHANKAN.\n\n";

// Konfirmasi
if (php_sapi_name() === 'cli') {
    echo "Lanjutkan? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "Dibatalkan.\n";
        exit;
    }
    fclose($handle);
    echo "\n";
}

try {
    // Koneksi ke database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✓ Koneksi database berhasil\n\n";
    
    // Mulai transaction
    $pdo->beginTransaction();
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    echo "Membersihkan data transaksi...\n\n";
    
    // Daftar tabel yang akan dibersihkan (data transaksi)
    $tablesToClean = [
        // Transaksi
        'jurnal_umums',
        'jurnal_umum_lines',
        'sales',
        'sale_lines',
        'purchases',
        'purchase_lines',
        'stock_movements',
        'productions',
        'production_lines',
        'bom_details',
        'bom_job_bahan_pendukung',
        'bom_job_bop',
        'bom_job_coatings',
        'bom_proses',
        'bom_proses_bops',
        'penggajians',
        'ap_settlements',
        'assets',
        'asset_depreciations',
        'beban_operasional',
        'beban_pendukung',
        'bahan_bakus',
        'bahan_konversi',
        
        // User data (opsional - hapus jika ingin clean install)
        // 'users',
        // 'companies',
        
        // Data master yang terikat user (akan dibersihkan)
        // 'products', // Jika ingin hapus produk testing
        // 'customers', // Jika ingin hapus customer testing
        // 'suppliers', // Jika ingin hapus supplier testing
    ];
    
    $cleanedCount = 0;
    
    foreach ($tablesToClean as $table) {
        // Cek apakah tabel ada
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            // Hitung data sebelum dihapus
            $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                // Hapus data
                $pdo->exec("TRUNCATE TABLE `{$table}`");
                echo "  ✓ {$table}: {$count} records dihapus\n";
                $cleanedCount++;
            }
        }
    }
    
    echo "\n";
    
    // Verifikasi data master masih ada
    echo "Verifikasi data master...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM coas WHERE user_id IS NULL");
    $coaCount = $stmt->fetchColumn();
    echo "  ✓ Data COA master: {$coaCount} records (TETAP ADA)\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM satuans WHERE user_id IS NULL");
    $satuanCount = $stmt->fetchColumn();
    echo "  ✓ Data Satuan master: {$satuanCount} records (TETAP ADA)\n";
    
    echo "\n";
    
    // Enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    // Commit transaction
    $pdo->commit();
    
    echo "==============================================\n";
    echo "PEMBERSIHAN SELESAI!\n";
    echo "==============================================\n\n";
    
    echo "✓ {$cleanedCount} tabel transaksi dibersihkan\n";
    echo "✓ Data master COA dan Satuan tetap dipertahankan\n";
    echo "✓ Database siap untuk di-export dan di-hosting\n\n";
    
    echo "LANGKAH SELANJUTNYA:\n";
    echo "1. Export database dari phpMyAdmin\n";
    echo "2. Pastikan centang 'Disable foreign key checks'\n";
    echo "3. File SQL siap untuk hosting\n\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error database:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
