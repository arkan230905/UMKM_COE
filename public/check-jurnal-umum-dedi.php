<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Check Dedi Gunawan in jurnal_umum</h1>";
echo "<pre>";

// Check in jurnal_umum
echo "=== JURNAL_UMUM TABLE ===\n";
$sql = "SELECT ju.*, c.kode_akun, c.nama_akun
        FROM jurnal_umum ju
        LEFT JOIN coas c ON ju.coa_id = c.id
        WHERE ju.keterangan LIKE '%Dedi Gunawan%'
        ORDER BY ju.tanggal DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " entries for Dedi Gunawan:\n\n";
    printf("%-5s | %-12s | %-6s | %-40s | %-15s | %-15s | %-30s\n", "ID", "Tanggal", "Kode", "Nama Akun", "Debit", "Kredit", "Keterangan");
    echo str_repeat("-", 150) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-6s | %-40s | %15s | %15s | %-30s\n",
            $row['id'],
            $row['tanggal'],
            $row['kode_akun'],
            substr($row['nama_akun'], 0, 40),
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['kredit'], 0, ',', '.'),
            substr($row['keterangan'], 0, 30)
        );
    }
} else {
    echo "No entries found for Dedi Gunawan in jurnal_umum\n";
}

// Check in penggajians table
echo "\n\n=== PENGGAJIANS TABLE ===\n";
$sql = "SELECT * FROM penggajians WHERE nama_pegawai LIKE '%Dedi%' ORDER BY tanggal DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " penggajian entries for Dedi:\n\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Tanggal: " . $row['tanggal'] . "\n";
        echo "Nama Pegawai: " . $row['nama_pegawai'] . "\n";
        echo "Jumlah: " . number_format($row['jumlah'] ?? 0, 0, ',', '.') . "\n";
        echo "Keterangan: " . $row['keterangan'] . "\n";
        echo "Created At: " . $row['created_at'] . "\n\n";
    }
} else {
    echo "No penggajian entries found for Dedi\n";
}

// Check in journal_entries for penggajian ref_type
echo "\n\n=== JOURNAL_ENTRIES FOR PENGGAJIAN ===\n";
$sql = "SELECT * FROM journal_entries WHERE ref_type = 'penggajian' ORDER BY tanggal DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " penggajian journal entries:\n\n";
    printf("%-5s | %-12s | %-10s | %-40s\n", "ID", "Tanggal", "Ref ID", "Memo");
    echo str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-10s | %-40s\n",
            $row['id'],
            $row['tanggal'],
            $row['ref_id'],
            substr($row['memo'], 0, 40)
        );
    }
} else {
    echo "No penggajian journal entries found\n";
}

// Summary
echo "\n\n=== SUMMARY ===\n";
echo "Penggajian Dedi Gunawan ada di jurnal_umum (tabel lama)\n";
echo "Tapi TIDAK ada di journal_entries dan journal_lines (tabel baru)\n";
echo "\nIni berarti data belum di-migrate dari jurnal_umum ke journal_entries\n";

$conn->close();
echo "</pre>";
?>
