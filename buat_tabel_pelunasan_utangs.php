<?php
/**
 * Script untuk membuat tabel pelunasan_utangs
 * Jalankan dengan: php buat_tabel_pelunasan_utangs.php
 */

// Konfigurasi database - sesuaikan dengan .env Anda
$host = 'localhost';
$dbname = 'umkm_production'; // Ganti sesuai nama database Anda
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== MEMBUAT TABEL PELUNASAN_UTANGS ===\n\n";
    echo "✅ Koneksi database berhasil!\n";
    echo "Database: $dbname\n";
    echo "Host: $host\n\n";
    
    // Cek apakah tabel sudah ada
    $checkTable = $pdo->query("SHOW TABLES LIKE 'pelunasan_utangs'");
    if ($checkTable->rowCount() > 0) {
        echo "⚠️  Tabel pelunasan_utangs sudah ada!\n";
        echo "Menghapus tabel lama...\n";
        $pdo->exec("DROP TABLE IF EXISTS pelunasan_utangs");
        echo "🗑️  Tabel lama dihapus.\n\n";
    }
    
    // Buat tabel pelunasan_utangs
    echo "📋 Membuat tabel pelunasan_utangs...\n";
    
    $createTableSQL = "
    CREATE TABLE `pelunasan_utangs` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `tanggal` date NOT NULL,
        `vendor_id` bigint(20) UNSIGNED NOT NULL,
        `pembelian_id` bigint(20) UNSIGNED NOT NULL,
        `total_tagihan` decimal(15,2) NOT NULL DEFAULT 0.00,
        `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
        `denda_bunga` decimal(15,2) NOT NULL DEFAULT 0.00,
        `dibayar_bersih` decimal(15,2) NOT NULL DEFAULT 0.00,
        `metode_bayar` varchar(50) NOT NULL DEFAULT 'tunai',
        `coa_kasbank` varchar(10) NOT NULL DEFAULT '101',
        `keterangan` text DEFAULT NULL,
        `status` varchar(20) NOT NULL DEFAULT 'lunas',
        `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_tanggal` (`tanggal`),
        KEY `idx_vendor` (`vendor_id`),
        KEY `idx_pembelian` (`pembelian_id`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ Tabel pelunasan_utangs berhasil dibuat!\n\n";
    
    // Tambahkan foreign key constraints (opsional, jika tabel referensi ada)
    echo "🔗 Menambahkan foreign key constraints...\n";
    
    try {
        // Cek apakah tabel vendors ada
        $vendorsExists = $pdo->query("SHOW TABLES LIKE 'vendors'")->rowCount() > 0;
        if ($vendorsExists) {
            $pdo->exec("ALTER TABLE `pelunasan_utangs` ADD CONSTRAINT `fk_pelunasan_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE");
            echo "   ✅ Foreign key ke vendors berhasil ditambahkan\n";
        } else {
            echo "   ⚠️  Tabel vendors tidak ditemukan, skip foreign key\n";
        }
        
        // Cek apakah tabel pembelians ada
        $pembeliansExists = $pdo->query("SHOW TABLES LIKE 'pembelians'")->rowCount() > 0;
        if ($pembeliansExists) {
            $pdo->exec("ALTER TABLE `pelunasan_utangs` ADD CONSTRAINT `fk_pelunasan_pembelian` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelians` (`id`) ON DELETE CASCADE");
            echo "   ✅ Foreign key ke pembelians berhasil ditambahkan\n";
        } else {
            echo "   ⚠️  Tabel pembelians tidak ditemukan, skip foreign key\n";
        }
        
        // Cek apakah tabel users ada
        $usersExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
        if ($usersExists) {
            $pdo->exec("ALTER TABLE `pelunasan_utangs` ADD CONSTRAINT `fk_pelunasan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
            echo "   ✅ Foreign key ke users berhasil ditambahkan\n";
        } else {
            echo "   ⚠️  Tabel users tidak ditemukan, skip foreign key\n";
        }
        
    } catch (Exception $e) {
        echo "   ⚠️  Warning: " . $e->getMessage() . "\n";
        echo "   💡 Foreign key constraints mungkin tidak ditambahkan, tapi tabel tetap berfungsi\n";
    }
    
    // Verifikasi tabel berhasil dibuat
    echo "\n📊 Verifikasi tabel:\n";
    
    $checkTable = $pdo->query("SHOW TABLES LIKE 'pelunasan_utangs'");
    if ($checkTable->rowCount() > 0) {
        echo "✅ Tabel pelunasan_utangs berhasil dibuat!\n\n";
        
        // Tampilkan struktur tabel
        echo "📋 Struktur tabel:\n";
        $structure = $pdo->query("DESCRIBE pelunasan_utangs");
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
        }
        
        // Tampilkan jumlah data
        $count = $pdo->query("SELECT COUNT(*) as total FROM pelunasan_utangs")->fetch();
        echo "\n📊 Total data: {$count['total']} record (tabel kosong - siap digunakan)\n";
        
    } else {
        echo "❌ Gagal membuat tabel pelunasan_utangs!\n";
        exit(1);
    }
    
    echo "\n=== PEMBUATAN TABEL SELESAI ===\n";
    echo "🎉 Tabel pelunasan_utangs siap digunakan!\n";
    echo "💡 Sekarang Anda bisa melakukan transaksi pelunasan utang melalui aplikasi.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "💡 Pastikan konfigurasi database sudah benar\n";
    exit(1);
}
?>