-- Setup sederhana untuk tabel presensi dengan kolom durasi kerja

-- Hapus tabel lama jika ada
DROP TABLE IF EXISTS presensis;

-- Buat tabel presensi baru dengan struktur lengkap
CREATE TABLE presensis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pegawai_id BIGINT UNSIGNED NOT NULL,
    tgl_presensi DATE NOT NULL,
    jam_masuk VARCHAR(5),
    jam_keluar VARCHAR(5),
    status VARCHAR(50),
    jumlah_menit_kerja INT DEFAULT 0,
    jumlah_jam_kerja DECIMAL(5,1) DEFAULT 0,
    jumlah_jam DECIMAL(5,2) DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_pegawai_tanggal (pegawai_id, tgl_presensi),
    INDEX idx_tgl_presensi (tgl_presensi),
    INDEX idx_status (status)
);

-- Catatan: Foreign key ke pegawais akan ditambahkan setelah tabel pegawais ada
