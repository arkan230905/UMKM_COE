<?php
/**
 * Tambah kolom user_id ke tabel yang belum punya
 * dan pastikan semua tabel kritis sudah multi-tenant
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', 'coe12345');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tabel yang perlu ditambah user_id
$tablesToFix = [
    'btkls',
    'journal_lines',
    'produksi_proses',
    'kategori_pegawai',
    'bom_job_bbb',
    'pembelian_detail_konversi',
    'produksi_bop_details',
    'produksi_btkl_details',
    'produksi_btkl_bop_details_tables',
];

echo "=== TAMBAH user_id KE TABEL YANG BELUM PUNYA ===\n\n";

foreach ($tablesToFix as $table) {
    // Cek apakah tabel ada
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount();
    if (!$exists) {
        echo "SKIP (tidak ada): $table\n";
        continue;
    }

    // Cek apakah user_id sudah ada
    $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('user_id', $cols)) {
        echo "SUDAH ADA user_id: $table\n";
        continue;
    }

    // Tambah kolom user_id
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `user_id` bigint(20) unsigned NULL AFTER `id`");
        echo "✓ DITAMBAH user_id: $table\n";
    } catch (Exception $e) {
        echo "ERROR $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== VERIFIKASI AKHIR ===\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$noUserId = [];
$skip = ['migrations','password_reset_tokens','failed_jobs','personal_access_tokens',
         'cache','cache_locks','jobs','job_batches','sessions',
         'bahan_konversi','satuan_conversions','satuan_grups',
         'coa_period_balances','coa_periods','kalender_kerja',
         'jurnal_umum_backup_april_2026','coas_backup_20260503',
         'presensi_records','presensi_users','rekap_presensi_bulanan',
         'verifikasi_wajah','verifikasi_wajahs','kasirs',
         'order_items','paket_menu_details','password_otp_resets',
         'bom_proses_bops','proses_bops','konversi_produksis',
         'retur_details','retur_jurnal_entries','retur_kompensasis',
         'purchase_return_items','sales_return_items',
         'detail_retur_penjualans','bebans',
];

foreach ($tables as $table) {
    if (in_array($table, $skip)) continue;
    $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('user_id', $cols)) {
        $noUserId[] = $table;
    }
}

if (empty($noUserId)) {
    echo "✓ Semua tabel kritis sudah punya user_id!\n";
} else {
    echo "Tabel kritis yang MASIH belum punya user_id:\n";
    foreach ($noUserId as $t) echo "  - $t\n";
}

echo "\nDONE\n";
