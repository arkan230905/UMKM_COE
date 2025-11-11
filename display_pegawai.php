<?php

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "=== DATA PEGAWAI ===\n\n";

    $stmt = $pdo->query("SELECT * FROM pegawais ORDER BY id ASC");
    $pegawais = $stmt->fetchAll();

    echo "Total Pegawai: " . count($pegawais) . "\n\n";
    echo str_repeat("=", 120) . "\n";

    foreach ($pegawais as $index => $pegawai) {
        echo "\n[" . ($index + 1) . "] PEGAWAI #" . $pegawai['id'] . "\n";
        echo str_repeat("-", 120) . "\n";
        echo "Kode Pegawai    : " . $pegawai['kode_pegawai'] . "\n";
        echo "Nama            : " . $pegawai['nama'] . "\n";
        echo "Email           : " . $pegawai['email'] . "\n";
        echo "No. Telepon     : " . $pegawai['no_telp'] . "\n";
        echo "Alamat          : " . $pegawai['alamat'] . "\n";
        echo "Jenis Kelamin   : " . ($pegawai['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . "\n";
        echo "Jabatan         : " . $pegawai['jabatan'] . "\n";
        echo "Jenis Pegawai   : " . ($pegawai['jenis_pegawai'] == 'btktl' ? 'Bulanan/Tetap' : 'Harian/Kontrak') . "\n";
        echo "\n--- GAJI & KOMPENSASI ---\n";
        echo "Gaji Pokok      : Rp " . number_format($pegawai['gaji_pokok'], 2, ',', '.') . "\n";
        echo "Tarif per Jam   : Rp " . number_format($pegawai['tarif_per_jam'], 2, ',', '.') . "\n";
        echo "Tunjangan       : Rp " . number_format($pegawai['tunjangan'], 2, ',', '.') . "\n";
        echo "Asuransi        : Rp " . number_format($pegawai['asuransi'], 2, ',', '.') . "\n";
        echo "Total Gaji      : Rp " . number_format($pegawai['gaji'], 2, ',', '.') . "\n";
        echo "\n--- INFORMASI BANK ---\n";
        echo "Bank            : " . strtoupper($pegawai['bank']) . "\n";
        echo "Nomor Rekening  : " . $pegawai['nomor_rekening'] . "\n";
        echo "Nama Rekening   : " . $pegawai['nama_rekening'] . "\n";
        echo "\n--- TIMESTAMP ---\n";
        echo "Dibuat          : " . $pegawai['created_at'] . "\n";
        echo "Diupdate        : " . $pegawai['updated_at'] . "\n";
        echo str_repeat("-", 120) . "\n";
    }

    echo "\n" . str_repeat("=", 120) . "\n";
    echo "SELESAI - Total " . count($pegawais) . " pegawai ditampilkan\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
