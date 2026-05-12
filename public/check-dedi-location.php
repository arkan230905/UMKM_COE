<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

// Check Penggajian Dedi in jurnal_umum
$result = $conn->query("
    SELECT ju.id, ju.tanggal, ju.tipe_referensi, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.keterangan LIKE '%Dedi%' OR ju.keterangan LIKE '%dedi%'
    ORDER BY ju.tanggal
");

echo "=== PENGGAJIAN DEDI IN JURNAL_UMUM ===\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Tanggal: {$row['tanggal']} | Tipe: {$row['tipe_referensi']} | Kode: {$row['kode_akun']} | Debit: {$row['debit']} | Kredit: {$row['kredit']}\n";
}

// Check if it's in journal_entries
$result2 = $conn->query("
    SELECT je.id, je.tanggal, je.ref_type, je.memo, jl.debit, jl.credit, c.kode_akun, c.nama_akun
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
    LEFT JOIN coas c ON c.id = jl.coa_id
    WHERE je.memo LIKE '%Dedi%' OR je.memo LIKE '%dedi%'
    ORDER BY je.tanggal
");

echo "\n=== PENGGAJIAN DEDI IN JOURNAL_ENTRIES ===\n";
if ($result2->num_rows == 0) {
    echo "NOT FOUND\n";
} else {
    while ($row = $result2->fetch_assoc()) {
        echo "ID: {$row['id']} | Tanggal: {$row['tanggal']} | Type: {$row['ref_type']} | Kode: {$row['kode_akun']} | Debit: {$row['debit']} | Kredit: {$row['kredit']}\n";
    }
}

$conn->close();
?>
