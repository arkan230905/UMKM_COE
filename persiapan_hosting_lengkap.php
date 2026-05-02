<?php
/**
 * PERSIAPAN HOSTING LENGKAP
 * ==========================
 * Script ALL-IN-ONE untuk persiapan database hosting
 * 
 * Yang dilakukan:
 * 1. Perbaiki struktur database (foreign key, collation, dll)
 * 2. Setup data master COA dan Satuan
 * 3. Bersihkan data testing/transaksi (opsional)
 * 4. Siap export untuk hosting
 * 
 * CARA PAKAI:
 * php persiapan_hosting_lengkap.php
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
echo "PERSIAPAN DATABASE UNTUK HOSTING\n";
echo "==============================================\n\n";

echo "Database: {$config['database']}\n";
echo "Host: {$config['host']}:{$config['port']}\n\n";

// Menu pilihan
echo "Pilih mode persiapan:\n";
echo "1. Setup data master saja (COA & Satuan)\n";
echo "2. Setup data master + Bersihkan data testing\n";
echo "3. Perbaiki struktur database saja\n";
echo "4. Lengkap (Perbaiki struktur + Setup master + Bersihkan testing)\n\n";

if (php_sapi_name() === 'cli') {
    echo "Pilihan (1-4): ";
    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    fclose($handle);
} else {
    // Jika dijalankan di browser, default ke pilihan 1
    $choice = $_GET['mode'] ?? '1';
}

echo "\n";

try {
    // Koneksi ke database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✓ Koneksi database berhasil\n\n";
    
    // ============================================
    // TAHAP 1: PERBAIKI STRUKTUR (jika dipilih)
    // ============================================
    if ($choice == '3' || $choice == '4') {
        echo "==============================================\n";
        echo "TAHAP 1: PERBAIKI STRUKTUR DATABASE\n";
        echo "==============================================\n\n";
        
        // Cek orphaned records
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM coas
            WHERE user_id IS NOT NULL
            AND NOT EXISTS (SELECT 1 FROM users WHERE users.id = coas.user_id)
        ");
        $orphanedCoa = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM satuans
            WHERE user_id IS NOT NULL
            AND NOT EXISTS (SELECT 1 FROM users WHERE users.id = satuans.user_id)
        ");
        $orphanedSatuan = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($orphanedCoa > 0 || $orphanedSatuan > 0) {
            echo "Memperbaiki orphaned records...\n";
            
            if ($orphanedCoa > 0) {
                $pdo->exec("UPDATE coas SET user_id = NULL WHERE user_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE users.id = coas.user_id)");
                echo "  ✓ COA: {$orphanedCoa} records diperbaiki\n";
            }
            
            if ($orphanedSatuan > 0) {
                $pdo->exec("UPDATE satuans SET user_id = NULL WHERE user_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE users.id = satuans.user_id)");
                echo "  ✓ Satuan: {$orphanedSatuan} records diperbaiki\n";
            }
        } else {
            echo "✓ Tidak ada orphaned records\n";
        }
        
        echo "\n";
    }
    
    // ============================================
    // TAHAP 2: SETUP DATA MASTER
    // ============================================
    if ($choice == '1' || $choice == '2' || $choice == '4') {
        echo "==============================================\n";
        echo "TAHAP 2: SETUP DATA MASTER\n";
        echo "==============================================\n\n";
        
        $pdo->beginTransaction();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        
        // Hapus data lama
        echo "Membersihkan data COA dan Satuan lama...\n";
        $pdo->exec("TRUNCATE TABLE coas");
        $pdo->exec("TRUNCATE TABLE satuans");
        echo "  ✓ Data lama dihapus\n\n";
        
        // Insert data master COA
        echo "Insert data master COA...\n";
        
        $masterCoas = [
            ['Aset', '11', 'Aset', 'Asset', 'debit', 1],
            ['Kas Bank', '111', 'Asset', 'Asset', 'debit', 0],
            ['Kas', '112', 'Asset', 'Asset', 'debit', 0],
            ['Kas Kecil', '113', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Bahan Baku', '114', 'Asset', 'Asset', 'debit', 1],
            ['Pers. Bahan Baku Jagung', '1141', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Bahan Pendukung', '115', 'Asset', 'Asset', 'debit', 1],
            ['Pers. Bahan Pendukung Susu', '1151', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Bahan Pendukung Keju', '1152', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Bahan Pendukung Kemasan (Cup)', '1153', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Barang Jadi', '116', 'Asset', 'Asset', 'debit', 1],
            ['Pers. Barang Jadi Jasuke', '1161', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Barang dalam Proses', '117', 'Asset', 'Asset', 'debit', 1],
            ['Pers. Barang Dalam Proses - BBB', '1171', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Barang Dalam Proses - BTKL', '1172', 'Asset', 'Asset', 'debit', 0],
            ['Pers. Barang Dalam Proses - BOP', '1173', 'Asset', 'Asset', 'debit', 0],
            ['Piutang', '118', 'Asset', 'Asset', 'debit', 0],
            ['Peralatan', '119', 'Asset', 'Asset', 'debit', 0],
            ['Akumulasi Penyusutan Peralatan', '120', 'Asset', 'Asset', 'kredit', 0],
            ['Mesin', '125', 'Asset', 'Asset', 'debit', 0],
            ['Akumulasi Penyusutan Mesin', '126', 'Asset', 'Asset', 'kredit', 0],
            ['PPN Masukkan', '127', 'Asset', 'Asset', 'debit', 0],
            ['Hutang', '21', 'Kewajiban', 'Liability', 'kredit', 1],
            ['Hutang Usaha', '210', 'Kewajiban', 'Liability', 'kredit', 0],
            ['Hutang Gaji', '211', 'Kewajiban', 'Liability', 'kredit', 0],
            ['PPN Keluaran', '212', 'Kewajiban', 'Liability', 'kredit', 0],
            ['Modal', '31', 'Equity', 'Equity', 'kredit', 1],
            ['Modal Usaha', '310', 'Equity', 'Equity', 'kredit', 0],
            ['Prive', '311', 'Equity', 'Equity', 'debit', 0],
            ['Penjualan', '41', 'Pendapatan', 'Revenue', 'kredit', 1],
            ['Penjualan - Jasuke', '410', 'Pendapatan', 'Revenue', 'kredit', 0],
            ['Retur Penjualan', '42', 'Pendapatan', 'Revenue', 'debit', 0],
            ['BBB - Biaya Bahan Baku', '51', 'Biaya Bahan Baku', 'Expense', 'debit', 1],
            ['BBB - Jagung', '510', 'Biaya Bahan Baku', 'Expense', 'debit', 0],
            ['Beban Tunjangan', '513', 'Beban', 'Expense', 'debit', 0],
            ['Beban Asuransi', '514', 'Beban', 'Expense', 'debit', 0],
            ['Beban Bonus', '515', 'Beban', 'Expense', 'debit', 0],
            ['Potongan Gaji', '516', 'Beban', 'Expense', 'debit', 0],
            ['BTKL', '52', 'Biaya Tenaga Kerja Langsung', 'Expense', 'debit', 1],
            ['BTKL - Produksi Jasuke', '520', 'Biaya Tenaga Kerja Langsung', 'Expense', 'debit', 0],
            ['BOP', '53', 'Biaya Overhead Pabrik', 'Expense', 'debit', 1],
            ['BOP - Susu', '530', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
            ['BOP - Keju', '531', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
            ['BOP - Kemasan', '532', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
            ['Beban Sewa', '54', 'Beban', 'Expense', 'debit', 0],
            ['BOP Lain', '55', 'Biaya Overhead Pabrik', 'Expense', 'debit', 1],
            ['BOP - Listrik', '550', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
            ['BOP - Air', '551', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
            ['BOP - Gas', '552', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
            ['BOP - Penyusutan Peralatan', '553', 'Biaya Overhead Pabrik', 'Expense', 'debit', 0],
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO coas (nama_akun, kode_akun, kategori_akun, tipe_akun, saldo_normal, is_akun_header, user_id, company_id, saldo_awal, posted_saldo_awal, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, 0, 0, NOW(), NOW())
        ");
        
        foreach ($masterCoas as $coa) {
            $stmt->execute($coa);
        }
        
        echo "  ✓ " . count($masterCoas) . " data COA master di-insert\n\n";
        
        // Insert data master Satuan
        echo "Insert data master Satuan...\n";
        
        $masterSatuans = [
            ['ONS', 'Ons', 'weight', 'berat', 1, 1],
            ['KG', 'Kilogram', 'weight', 'berat', 1, 1],
            ['ML', 'Mililiter', 'volume', 'volume', 1, 1],
            ['G', 'Gram', 'weight', 'berat', 1, 1],
            ['LTR', 'Liter', 'volume', 'volume', 1, 1],
            ['PTG', 'Potong', 'unit', 'jumlah', 1, 1],
            ['EKOR', 'Ekor', 'unit', 'jumlah', 1, 1],
            ['SDT', 'Sendok Teh', 'volume', 'volume', 1, 1],
            ['SDM', 'Sendok Makan', 'volume', 'volume', 1, 1],
            ['PCS', 'Pieces', 'unit', 'jumlah', 1, 1],
            ['BNGKS', 'Bungkus', 'unit', 'jumlah', 1, 1],
            ['CUP', 'Cup', 'volume', 'volume', 1, 1],
            ['GL', 'Galon', 'volume', 'volume', 1, 1],
            ['TBG', 'Tabung', 'unit', 'jumlah', 1, 1],
            ['SNG', 'Siung', 'unit', 'jumlah', 1, 1],
            ['KLG', 'Kaleng', 'unit', 'jumlah', 1, 1],
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO satuans (kode, nama, tipe, kategori, is_dasar, is_active, nilai_konversi, faktor_ke_dasar, user_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, 1, NULL, NOW(), NOW())
        ");
        
        foreach ($masterSatuans as $satuan) {
            $stmt->execute($satuan);
        }
        
        echo "  ✓ " . count($masterSatuans) . " data Satuan master di-insert\n\n";
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }
    
    // ============================================
    // TAHAP 3: BERSIHKAN DATA TESTING (jika dipilih)
    // ============================================
    if ($choice == '2' || $choice == '4') {
        echo "==============================================\n";
        echo "TAHAP 3: BERSIHKAN DATA TESTING\n";
        echo "==============================================\n\n";
        
        echo "⚠️  Akan menghapus data transaksi/testing\n";
        echo "   Data master tetap dipertahankan\n\n";
        
        $pdo->beginTransaction();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        
        $tablesToClean = [
            'jurnal_umums', 'jurnal_umum_lines', 'sales', 'sale_lines',
            'purchases', 'purchase_lines', 'stock_movements', 'productions',
            'production_lines', 'bom_details', 'penggajians', 'assets',
            'asset_depreciations', 'beban_operasional'
        ];
        
        $cleanedCount = 0;
        foreach ($tablesToClean as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                $count = $stmt->fetchColumn();
                if ($count > 0) {
                    $pdo->exec("TRUNCATE TABLE `{$table}`");
                    echo "  ✓ {$table}: {$count} records dihapus\n";
                    $cleanedCount++;
                }
            }
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
        
        echo "\n  Total: {$cleanedCount} tabel dibersihkan\n\n";
    }
    
    // ============================================
    // RINGKASAN AKHIR
    // ============================================
    echo "==============================================\n";
    echo "PERSIAPAN SELESAI!\n";
    echo "==============================================\n\n";
    
    // Verifikasi data master
    $stmt = $pdo->query("SELECT COUNT(*) FROM coas WHERE user_id IS NULL");
    $coaCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM satuans WHERE user_id IS NULL");
    $satuanCount = $stmt->fetchColumn();
    
    echo "Status database:\n";
    echo "  ✓ Data COA master: {$coaCount} records\n";
    echo "  ✓ Data Satuan master: {$satuanCount} records\n";
    echo "  ✓ Database siap untuk di-export\n\n";
    
    echo "==============================================\n";
    echo "PANDUAN EXPORT UNTUK HOSTING\n";
    echo "==============================================\n\n";
    
    echo "1. Buka phpMyAdmin\n";
    echo "2. Pilih database 'eadt_umkm'\n";
    echo "3. Klik tab 'Export'\n";
    echo "4. Pilih 'Custom - display all possible options'\n";
    echo "5. Di 'Format-specific options':\n";
    echo "   ✓ Centang 'Add DROP TABLE'\n";
    echo "   ✓ Centang 'Add IF NOT EXISTS'\n";
    echo "   ✓ PENTING: Centang 'Disable foreign key checks'\n";
    echo "   ✓ Centang 'Add CREATE DATABASE / USE statement'\n";
    echo "6. Pilih 'Save output to a file'\n";
    echo "7. Klik 'Export'\n\n";
    
    echo "File SQL yang dihasilkan siap untuk:\n";
    echo "  - Dibagikan ke teman-teman\n";
    echo "  - Di-upload ke hosting\n";
    echo "  - Di-import tanpa error!\n\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error database:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
