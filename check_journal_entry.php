<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

echo "=== CHECKING LATEST JOURNAL ENTRY ===\n\n";

// Get the latest journal entry
$stmt = $pdo->prepare('
    SELECT je.*, jl.account_id, jl.debit, jl.credit, c.kode_akun, c.nama_akun
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
    LEFT JOIN coas c ON jl.account_id = c.id
    WHERE je.id = 13
    ORDER BY jl.id
');
$stmt->execute();
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($lines)) {
    echo "No journal entry found with ID 13\n";
    exit;
}

$journalEntry = $lines[0];
echo "Journal Entry ID: {$journalEntry['id']}\n";
echo "Journal Number: {$journalEntry['tanggal']}\n";
echo "Date: {$journalEntry['tanggal']}\n";
echo "Reference: {$journalEntry['ref_type']} #{$journalEntry['ref_id']}\n";
echo "Description: {$journalEntry['memo']}\n\n";

echo "Journal Lines:\n";
$totalDebit = 0;
$totalCredit = 0;

foreach ($lines as $line) {
    $debit = (float) $line['debit'];
    $credit = (float) $line['credit'];
    $totalDebit += $debit;
    $totalCredit += $credit;
    
    $amount = $debit ?: $credit;
    $type = $debit ? 'Debit' : 'Credit';
    $indent = $credit ? '    ' : '';
    
    echo "{$indent}{$line['nama_akun']} ({$line['kode_akun']}) - {$type}: Rp " . number_format($amount) . "\n";
}

echo "\nTotals:\n";
echo "Total Debit: Rp " . number_format($totalDebit) . "\n";
echo "Total Credit: Rp " . number_format($totalCredit) . "\n";
echo "Balance: " . ($totalDebit == $totalCredit ? "✅ Balanced" : "❌ Not Balanced") . "\n";

// Check the purchase details
echo "\n=== PURCHASE DETAILS ===\n";
$stmt2 = $pdo->prepare('
    SELECT p.*, v.nama_vendor
    FROM pembelians p
    LEFT JOIN vendors v ON p.vendor_id = v.id
    WHERE p.id = 9
');
$stmt2->execute();
$purchase = $stmt2->fetch(PDO::FETCH_ASSOC);

if ($purchase) {
    echo "Purchase ID: {$purchase['id']}\n";
    echo "Vendor: {$purchase['nama_vendor']}\n";
    echo "Payment Method: {$purchase['payment_method']}\n";
    echo "Bank ID: {$purchase['bank_id']}\n";
    echo "Subtotal: Rp " . number_format($purchase['subtotal']) . "\n";
    echo "PPN: Rp " . number_format($purchase['ppn_nominal']) . "\n";
    echo "Total: Rp " . number_format($purchase['total_harga']) . "\n";
}