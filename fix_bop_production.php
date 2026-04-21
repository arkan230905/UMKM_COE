<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix BOP Production Journals ===" . PHP_EOL;

// Delete wrong production entries
$wrongEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.ref_type', 'production_labor_overhead')
    ->where('coas.kode_akun', '53')
    ->where('journal_lines.debit', 0)
    ->where('journal_lines.credit', '>', 0)
    ->pluck('journal_entries.id');

foreach ($wrongEntries as $entryId) {
    // Delete journal lines first
    DB::table('journal_lines')->where('journal_entry_id', $entryId)->delete();
    // Delete journal entry
    DB::table('journal_entries')->where('id', $entryId)->delete();
    echo "Deleted wrong BOP entry ID: $entryId" . PHP_EOL;
}
