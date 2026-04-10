<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== CHECKING JOURNAL ID 16 ===\n";

$stmt = $pdo->prepare('
    SELECT jl.*, c.kode_akun, c.nama_akun
    FROM journal_lines jl
    LEFT JOIN coas c ON jl.coa_id = c.id
    WHERE jl.journal_entry_id = 16
    ORDER BY jl.id
');
$stmt->execute();
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($lines as $line) {
    $debit = (float) $line['debit'];
    $credit = (float) $line['credit'];
    
    echo "Line ID: {$line['id']}\n";
    echo "COA: {$line['nama_akun']} ({$line['kode_akun']})\n";
    echo "Debit: Rp " . number_format($debit) . "\n";
    echo "Credit: Rp " . number_format($credit) . "\n";
    echo "Memo: {$line['memo']}\n";
    echo "---\n";
}