<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>CHECK: Expense Payments Data</h1>";
echo "<pre>";

$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban,
           c1.kode_akun as beban_kode, c1.nama_akun as beban_nama,
           c2.kode_akun as kas_kode, c2.nama_akun as kas_nama
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    LEFT JOIN coas c1 ON c1.kode_akun = pb.coa_beban_id
    LEFT JOIN coas c2 ON c2.kode_akun = pb.coa_kasbank
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY pb.id
");

echo "=== EXPENSE_PAYMENTS TABLE ===\n\n";

while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}\n";
    echo "Tanggal: {$row['tanggal']}\n";
    echo "Beban: {$row['nama_beban']}\n";
    echo "COA Beban: {$row['coa_beban_id']} - {$row['beban_nama']}\n";
    echo "COA Kas: {$row['coa_kasbank']} - {$row['kas_nama']}\n";
    echo "Nominal: {$row['nominal_pembayaran']}\n";
    echo "\n";
}

$conn->close();
echo "</pre>";
?>
