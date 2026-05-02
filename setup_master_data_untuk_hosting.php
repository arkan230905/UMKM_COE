<?php
/**
 * SETUP MASTER DATA UNTUK HOSTING
 * ================================
 * Script ini akan:
 * 1. Membersihkan data COA dan Satuan yang ada
 * 2. Insert data master COA dan Satuan yang menetap
 * 3. Memastikan data ini tidak terikat dengan user tertentu (user_id = NULL)
 * 4. Siap untuk di-export dan di-hosting
 * 
 * CARA PAKAI:
 * php setup_master_data_untuk_hosting.php
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
echo "SETUP MASTER DATA UNTUK HOSTING\n";
echo "==============================================\n\n";

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
    
    // ============================================
    // STEP 1: BACKUP DATA LAMA (OPSIONAL)
    // ============================================
    echo "[STEP 1] Backup data lama...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM coas");
    $oldCoaCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM satuans");
    $oldSatuanCount = $stmt->fetchColumn();
    
    echo "    Data COA lama: {$oldCoaCount} records\n";
    echo "    Data Satuan lama: {$oldSatuanCount} records\n\n";
    
    // ============================================
    // STEP 2: HAPUS DATA LAMA
    // ============================================
    echo "[STEP 2] Membersihkan data lama...\n";
    
    // Disable foreign key checks sementara
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Hapus data COA dan Satuan
    $pdo->exec("TRUNCATE TABLE coas");
    echo "    ✓ Data COA dihapus\n";
    
    $pdo->exec("TRUNCATE TABLE satuans");
    echo "    ✓ Data Satuan dihapus\n\n";
    
    // Enable foreign key checks kembali
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    // ============================================
    // STEP 3: INSERT DATA MASTER COA
    // ============================================
    echo "[STEP 3] Insert data master COA...\n";
    
    $masterCoas = [
        ['Aset', '11', 'Aset', 'Debit', null, null],
        ['Kas Bank', '111', 'Aset', 'Debit', null, null],
        ['Kas', '112', 'Aset', 'Debit', null, null],
        ['Kas Kecil', '113', 'Aset', 'Debit', null, null],
        ['Pers. Bahan Baku', '114', 'Aset', 'Debit', null, null],
        ['Pers. Bahan Baku Jagung', '1141', 'Aset', 'Debit', null, null],
        ['Pers. Bahan Pendukung', '115', 'Aset', 'Debit', null, null],
        ['Pers. Bahan Pendukung Susu', '1151', 'Aset', 'Debit', null, null],
        ['Pers. Bahan Pendukung Keju', '1152', 'Aset', 'Debit', null, null],
        ['Pers. Bahan Pendukung Kemasan (Cup)', '1153', 'Aset', 'Debit', null, null],
        ['Pers. Barang Jadi', '116', 'Aset', 'Debit', null, null],
        ['Pers. Barang Jadi Jasuke', '1161', 'Aset', 'Debit', null, null],
        ['Pers. Barang dalam Proses', '117', 'Aset', 'Debit', null, null],
        ['Pers. Barang Dalam Proses - BBB', '1171', 'Aset', 'Debit', null, null],
        ['Pers. Barang Dalam Proses - BTKL', '1172', 'Aset', 'Debit', null, null],
        ['Pers. Barang Dalam Proses - BOP', '1173', 'Aset', 'Debit', null, null],
        ['Piutang', '118', 'Aset', 'Debit', null, null],
        ['Peralatan', '119', 'Aset', 'Debit', null, null],
        ['Akumulasi Penyusutan Peralatan', '120', 'Aset', 'Debit', null, null],
        ['Mesin', '125', 'Aset', 'Debit', null, null],
        ['Akumulasi Penyusutan Mesin', '126', 'Aset', 'Debit', null, null],
        ['PPN Masukkan', '127', 'Aset', 'Debit', null, null],
        ['Hutang', '21', 'Kewajiban', 'Kredit', null, null],
        ['Hutang Usaha', '210', 'Kewajiban', 'Kredit', null, null],
        ['Hutang Gaji', '211', 'Kewajiban', 'Kredit', null, null],
        ['PPN Keluaran', '212', 'Kewajiban', 'Kredit', null, null],
        ['Modal', '31', 'Equity', 'Kredit', null, null],
        ['Modal Usaha', '310', 'Equity', 'Kredit', null, null],
        ['Prive', '311', 'Modal', 'Kredit', null, null],
        ['Penjualan', '41', 'Pendapatan', 'Kredit', null, null],
        ['Penjualan - Jasuke', '410', 'Pendapatan', 'Kredit', null, null],
        ['Retur Penjualan', '42', 'Pendapatan', 'Kredit', null, null],
        ['BBB - Biaya Bahan Baku', '51', 'Biaya', 'Debit', null, null],
        ['BBB - Jagung', '510', 'Biaya', 'Debit', null, null],
        ['Beban Tunjangan', '513', 'Equity', 'Debit', null, null],
        ['Beban Asuransi', '514', 'Equity', 'Debit', null, null],
        ['Beban Bonus', '515', 'Equity', 'Debit', null, null],
        ['Potongan Gaji', '516', 'Equity', 'Debit', null, null],
        ['BTKL', '52', 'Biaya', 'Debit', null, null],
        ['BTKL - Produksi Jasuke', '520', 'Biaya', 'Debit', null, null],
        ['BOP', '53', 'Biaya', 'Debit', null, null],
        ['BOP - Susu', '530', 'Biaya', 'Debit', null, null],
        ['BOP - Keju', '531', 'Biaya', 'Debit', null, null],
        ['BOP - Kemasan', '532', 'Biaya', 'Debit', null, null],
        ['Beban Sewa', '54', 'Expense', 'Debit', null, null],
        ['BOP Lain', '55', 'Biaya', 'Debit', null, null],
        ['BOP - Listrik', '550', 'Biaya', 'Debit', null, null],
        ['BOP - Air', '551', 'Biaya', 'Debit', null, null],
        ['BOP - Gas', '552', 'Biaya', 'Debit', null, null],
        ['BOP - Penyusutan Peralatan', '553', 'Biaya', 'Debit', null, null],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO coas (nama_akun, kode_akun, tipe, posisi, user_id, company_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, NULL, NULL, NOW(), NOW())
    ");
    
    $coaCount = 0;
    foreach ($masterCoas as $coa) {
        $stmt->execute($coa);
        $coaCount++;
    }
    
    echo "    ✓ {$coaCount} data COA master berhasil di-insert\n\n";
    
    // ============================================
    // STEP 4: INSERT DATA MASTER SATUAN
    // ============================================
    echo "[STEP 4] Insert data master Satuan...\n";
    
    $masterSatuans = [
        ['ONS', 'Ons', null, null],
        ['KG', 'Kilogram', null, null],
        ['ML', 'Mililiter', null, null],
        ['G', 'Gram', null, null],
        ['LTR', 'Liter', null, null],
        ['PTG', 'Potong', null, null],
        ['EKOR', 'Ekor', null, null],
        ['SDT', 'Sendok Teh', null, null],
        ['SDM', 'Sendok Makan', null, null],
        ['PCS', 'Pieces', null, null],
        ['BNGKS', 'Bungkus', null, null],
        ['CUP', 'Cup', null, null],
        ['GL', 'Galon', null, null],
        ['TBG', 'Tabung', null, null],
        ['SNG', 'Siung', null, null],
        ['KLG', 'Kaleng', null, null],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO satuans (kode_satuan, nama_satuan, user_id, company_id, created_at, updated_at)
        VALUES (?, ?, NULL, NULL, NOW(), NOW())
    ");
    
    $satuanCount = 0;
    foreach ($masterSatuans as $satuan) {
        $stmt->execute($satuan);
        $satuanCount++;
    }
    
    echo "    ✓ {$satuanCount} data Satuan master berhasil di-insert\n\n";
    
    // ============================================
    // STEP 5: VERIFIKASI DATA
    // ============================================
    echo "[STEP 5] Verifikasi data...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM coas WHERE user_id IS NULL");
    $newCoaCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM satuans WHERE user_id IS NULL");
    $newSatuanCount = $stmt->fetchColumn();
    
    echo "    ✓ Data COA master: {$newCoaCount} records\n";
    echo "    ✓ Data Satuan master: {$newSatuanCount} records\n\n";
    
    // Commit transaction
    $pdo->commit();
    
    echo "==============================================\n";
    echo "SETUP SELESAI!\n";
    echo "==============================================\n\n";
    
    echo "✓ Data master COA dan Satuan sudah siap!\n";
    echo "✓ Semua data memiliki user_id = NULL (data global)\n";
    echo "✓ Database siap untuk di-export dan di-hosting\n\n";
    
    echo "LANGKAH SELANJUTNYA:\n";
    echo "1. Export database dari phpMyAdmin\n";
    echo "2. Pastikan centang 'Disable foreign key checks'\n";
    echo "3. File SQL siap dibagikan/di-hosting\n\n";
    
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
