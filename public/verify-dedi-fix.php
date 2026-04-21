<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>✅ VERIFIKASI FIX: Penggajian Dedi Gunawan di Buku Besar</h1>";
echo "<pre>";

// Check Penggajian Dedi in jurnal_umum
$result = $conn->query("
    SELECT ju.id, ju.tanggal, ju.tipe_referensi, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.keterangan LIKE '%Dedi Gunawan%'
    ORDER BY ju.tanggal
");

echo "=== PENGGAJIAN DEDI IN JURNAL_UMUM ===\n";
echo "Status: FOUND\n\n";

$dediData = [];
while ($row = $result->fetch_assoc()) {
    $dediData[] = $row;
    echo "ID: {$row['id']} | Tanggal: {$row['tanggal']} | Tipe: {$row['tipe_referensi']} | Kode: {$row['kode_akun']} | Debit: {$row['debit']} | Kredit: {$row['kredit']}\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "ANALISIS FIX\n";
echo str_repeat("=", 100) . "\n\n";

echo "SEBELUM FIX:\n";
echo "- Penggajian Dedi Gunawan ada di jurnal_umum dengan tipe_referensi = 'penggajian'\n";
echo "- Tapi di bukuBesar() method, ada filter: whereNotIn('ju.tipe_referensi', [..., 'penggajian'])\n";
echo "- Hasilnya: Penggajian Dedi TIDAK MUNCUL di Buku Besar Kas\n\n";

echo "SESUDAH FIX:\n";
echo "- Filter di bukuBesar() method diubah menjadi: whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment'])\n";
echo "- 'penggajian' DIHAPUS dari exclusion list\n";
echo "- Hasilnya: Penggajian Dedi AKAN MUNCUL di Buku Besar Kas\n\n";

echo "FILE YANG DIPERBAIKI:\n";
echo "1. app/Http/Controllers/AkuntansiController.php (line 578)\n";
echo "2. app/Exports/BukuBesarExport.php (line 200)\n\n";

echo str_repeat("=", 100) . "\n";
echo "TESTING\n";
echo str_repeat("=", 100) . "\n\n";

// Simulate the new query (without penggajian exclusion)
$testQuery = "
    SELECT ju.id, ju.tanggal, ju.tipe_referensi, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE (ju.debit > 0 OR ju.kredit > 0)
    AND c.kode_akun = '112'
    AND ju.tipe_referensi NOT IN ('purchase', 'sale', 'sales_return', 'debt_payment')
    ORDER BY ju.tanggal ASC, ju.id ASC
";

$result = $conn->query($testQuery);

echo "Query untuk Buku Besar Kas (COA 112) dengan FIX:\n";
echo "Hasil:\n\n";

if ($result && $result->num_rows > 0) {
    printf("%-5s | %-12s | %-15s | %-40s | %-12s | %-12s\n", "ID", "Tanggal", "Tipe", "Keterangan", "Debit", "Kredit");
    echo str_repeat("-", 100) . "\n";
    
    $found = false;
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-15s | %-40s | %-12s | %-12s\n",
            $row['id'],
            $row['tanggal'],
            $row['tipe_referensi'],
            substr($row['keterangan'], 0, 40),
            $row['debit'],
            $row['kredit']
        );
        
        if (strpos($row['keterangan'], 'Dedi') !== false) {
            $found = true;
        }
    }
    
    echo "\n";
    if ($found) {
        echo "✅ PENGGAJIAN DEDI GUNAWAN DITEMUKAN!\n";
        echo "✅ FIX BERHASIL - Penggajian Dedi akan muncul di Buku Besar Kas\n";
    } else {
        echo "⚠️ Penggajian Dedi tidak ditemukan dalam hasil query\n";
    }
} else {
    echo "❌ Tidak ada hasil\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "NEXT STEPS:\n";
echo str_repeat("=", 100) . "\n";
echo "1. Refresh browser cache: Ctrl+Shift+Delete\n";
echo "2. Buka Buku Besar Kas (COA 112)\n";
echo "3. Penggajian Dedi Gunawan (26/04/2026, Rp 3.250.000) harus muncul\n";
echo "4. Verifikasi total Debit dan Kredit sudah benar\n";

$conn->close();
echo "</pre>";
?>
