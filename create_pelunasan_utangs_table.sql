-- SCRIPT UNTUK MEMBUAT TABEL PELUNASAN_UTANGS
-- Jalankan di phpMyAdmin atau MySQL command line

-- Hapus tabel lama jika ada
DROP TABLE IF EXISTS `pelunasan_utangs`;

-- Buat tabel pelunasan_utangs
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
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_pelunasan_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pelunasan_pembelian` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelians` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pelunasan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verifikasi tabel berhasil dibuat
SELECT 'Tabel pelunasan_utangs berhasil dibuat!' as status;

-- Tampilkan struktur tabel
DESCRIBE `pelunasan_utangs`;

-- Cek jumlah data (harus 0 karena tabel baru)
SELECT COUNT(*) as total_records FROM `pelunasan_utangs`;