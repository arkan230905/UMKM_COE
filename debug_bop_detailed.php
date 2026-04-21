<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Detailed BOP Analysis ===" . PHP_EOL;

// Check if there are existing production entries that need to be corrected
echo PHP_EOL . "Checking Existing Production Entries:" . PHP_EOL;

$existingEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_lines.memo', 'like', '%Alokasi BOP%')
    ->where('coas.kode_akun', '53')
    ->select('journal_entries.id', 'journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo')
    ->get();

echo "Existing BOP Allocation Entries:" . PHP_EOL;
foreach ($existingEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s",
        $entry->id,
        $entry->tanggal,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0),
        $entry->memo
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Need to Delete and Recreate Production Journals ===" . PHP_EOL;

// Get all production journal entries that need correction
$productionEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.ref_type', 'production_labor_overhead')
    ->select('journal_entries.id', 'journal_entries.ref_id', 'journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo', 'coas.kode_akun')
    ->get();

echo "Production Journal Entries to Fix:" . PHP_EOL;
foreach ($productionEntries as $entry) {
    echo sprintf(
        "Production ID: %d | Date: %s | COA: %s | Debit: %s | Credit: %s | Memo: %s",
        $entry->ref_id,
        $entry->tanggal,
        $entry->kode_akun,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0),
        $entry->memo
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Solution: Recreate Production Journals ===" . PHP_EOL;

// Get production data
$produksiData = DB::table('produksis')
    ->whereMonth('tanggal', 4)
    ->whereYear('tanggal', 2026)
    ->get();

echo "Production Records to Process:" . PHP_EOL;
foreach ($produksiData as $produksi) {
    echo sprintf(
        "ID: %d | Date: %s | BTKL: %s | BOP: %s",
        $produksi->id,
        $produksi->tanggal,
        number_format($produksi->total_btkl, 0),
        number_format($produksi->total_bop, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Action Required ===" . PHP_EOL;
echo "1. Delete existing production journal entries with wrong BOP direction" . PHP_EOL;
echo "2. Recreate production journal entries with correct BOP direction (DEBIT)" . PHP_EOL;
echo "3. This will fix the negative BOP balance issue" . PHP_EOL;

echo PHP_EOL . "Creating Fix Script..." . PHP_EOL;

// Create script to fix BOP entries
$fixScript = "<?php" . PHP_EOL;
$fixScript .= "require __DIR__.'/vendor/autoload.php';" . PHP_EOL;
$fixScript .= "\$app = require_once __DIR__.'/bootstrap/app.php';" . PHP_EOL;
$fixScript .= "\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);" . PHP_EOL;
$fixScript .= "\$kernel->bootstrap();" . PHP_EOL;
$fixScript .= PHP_EOL . "echo \"=== Fix BOP Production Journals ===\" . PHP_EOL;" . PHP_EOL;
$fixScript .= PHP_EOL . "// Delete wrong production entries" . PHP_EOL;
$fixScript .= "\$wrongEntries = DB::table('journal_entries')" . PHP_EOL;
$fixScript .= "    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')" . PHP_EOL;
$fixScript .= "    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')" . PHP_EOL;
$fixScript .= "    ->where('journal_entries.ref_type', 'production_labor_overhead')" . PHP_EOL;
$fixScript .= "    ->where('coas.kode_akun', '53')" . PHP_EOL;
$fixScript .= "    ->where('journal_lines.debit', 0)" . PHP_EOL;
$fixScript .= "    ->where('journal_lines.credit', '>', 0)" . PHP_EOL;
$fixScript .= "    ->pluck('journal_entries.id');" . PHP_EOL;
$fixScript .= PHP_EOL . "foreach (\$wrongEntries as \$entryId) {" . PHP_EOL;
$fixScript .= "    // Delete journal lines first" . PHP_EOL;
$fixScript .= "    DB::table('journal_lines')->where('journal_entry_id', \$entryId)->delete();" . PHP_EOL;
$fixScript .= "    // Delete journal entry" . PHP_EOL;
$fixScript .= "    DB::table('journal_entries')->where('id', \$entryId)->delete();" . PHP_EOL;
$fixScript .= "    echo \"Deleted wrong BOP entry ID: \$entryId\" . PHP_EOL;" . PHP_EOL;
$fixScript .= "}" . PHP_EOL;

file_put_contents(__DIR__ . '/fix_bop_production.php', $fixScript);

echo "✅ Created fix_bop_production.php script" . PHP_EOL;
echo "Run this script to fix BOP entries: php fix_bop_production.php" . PHP_EOL;
