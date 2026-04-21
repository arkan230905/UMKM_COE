<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>PERBANDINGAN: Query Buku Besar SEBELUM vs SESUDAH FIX</h1>";
echo "<pre>";

$accountCode = '112'; // Kas

echo "=== QUERY SEBELUM FIX (DENGAN PENGGAJIAN EXCLUSION) ===\n";
echo "WHERE clause: whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment', 'penggajian'])\n\n";

$queryBefore = "
    SELECT ju.id, ju.tanggal, ju.tipe_referensi, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE (ju.debit > 0 OR ju.kredit > 0)
    AND c.kode_akun = '$accountCode'
    AND ju.tipe_referensi NOT IN ('purchase', 'sale', 'sales_return', 'debt_payment', 'penggajian')
    ORDER BY ju.tanggal ASC, ju.id ASC
";

$result = $conn->query($queryBefore);
echo "Hasil: " . $result->num_rows . " baris\n\n";

if ($result && $result->num_rows > 0) {
    printf("%-5s | %-12s | %-15s | %-40s | %-12s | %-12s\n", "ID", "Tanggal", "Tipe", "Keterangan", "Debit", "Kredit");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-15s | %-40s | %-12s | %-12s\n",
            $row['id'],
            $row['tanggal'],
            $row['tipe_referensi'],
            substr($row['keterangan'], 0, 40),
            $row['debit'],
            $row['kredit']
        );
    }
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "=== QUERY SESUDAH FIX (TANPA PENGGAJIAN EXCLUSION) ===\n";
echo "WHERE clause: whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment'])\n\n";

$queryAfter = "
    SELECT ju.id, ju.tanggal, ju.tipe_referensi, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE (ju.debit > 0 OR ju.kredit > 0)
    AND c.kode_akun = '$accountCode'
    AND ju.tipe_referensi NOT IN ('purchase', 'sale', 'sales_return', 'debt_payment')
    ORDER BY ju.tanggal ASC, ju.id ASC
";

$result = $conn->query($queryAfter);
echo "Hasil: " . $result->num_rows . " baris\n\n";

if ($result && $result->num_rows > 0) {
    printf("%-5s | %-12s | %-15s | %-40s | %-12s | %-12s\n", "ID", "Tanggal", "Tipe", "Keterangan", "Debit", "Kredit");
    echo str_repeat("-", 100) . "\n";
    
    $dediFound = false;
    while ($row = $result->fetch_assoc()) {
        $highlight = (strpos($row['keterangan'], 'Dedi') !== false) ? " ← PENGGAJIAN DEDI" : "";
        printf("%-5s | %-12s | %-15s | %-40s | %-12s | %-12s%s\n",
            $row['id'],
            $row['tanggal'],
            $row['tipe_referensi'],
            substr($row['keterangan'], 0, 40),
            $row['debit'],
            $row['kredit'],
            $highlight
        );
        
        if (strpos($row['keterangan'], 'Dedi') !== false) {
            $dediFound = true;
        }
    }
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "KESIMPULAN\n";
echo str_repeat("=", 100) . "\n\n";

if ($dediFound) {
    echo "✅ PENGGAJIAN DEDI GUNAWAN MUNCUL SETELAH FIX\n";
    echo "✅ Fix berhasil diterapkan\n";
} else {
    echo "❌ Penggajian Dedi masih tidak muncul\n";
}

echo "\nPerbedaan:\n";
echo "- SEBELUM: Penggajian Dedi TIDAK muncul (dikecualikan)\n";
echo "- SESUDAH: Penggajian Dedi MUNCUL (tidak dikecualikan)\n";

$conn->close();
echo "</pre>";
?>
