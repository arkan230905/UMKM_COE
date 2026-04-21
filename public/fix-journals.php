<?php
// Direct database fix for BTKL & BOP journals

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$db = $app->make('db');

echo "<h1>Memperbaiki Jurnal BTKL & BOP</h1>";
echo "<pre>";

try {
    // Fix BTKL (52) and BOP (53) - move from debit to credit
    $updated = $db->update("
        UPDATE journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        SET jl.credit = jl.debit, jl.debit = 0
        WHERE je.ref_type = 'production_labor_overhead'
        AND jl.coa_code IN ('52', '53')
        AND jl.debit > 0
    ");
    
    echo "✓ Berhasil memperbaiki {$updated} baris jurnal\n\n";
    
    // Show the fixed entries
    $entries = $db->select("
        SELECT 
            je.tanggal,
            je.memo,
            jl.coa_code,
            jl.debit,
            jl.credit
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        WHERE je.ref_type = 'production_labor_overhead'
        ORDER BY je.tanggal, jl.coa_code
    ");
    
    echo "Hasil Perbaikan:\n";
    echo str_repeat("=", 80) . "\n";
    printf("%-12s | %-40s | %-10s | %-15s | %-15s\n", "Tanggal", "Memo", "Kode", "Debit", "Kredit");
    echo str_repeat("=", 80) . "\n";
    
    foreach ($entries as $e) {
        printf("%-12s | %-40s | %-10s | %15s | %15s\n", 
            $e->tanggal,
            substr($e->memo, 0, 40),
            $e->coa_code,
            number_format($e->debit, 0, ',', '.'),
            number_format($e->credit, 0, ',', '.')
        );
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "\n✓ Selesai! Jurnal BTKL & BOP telah diperbaiki.\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
