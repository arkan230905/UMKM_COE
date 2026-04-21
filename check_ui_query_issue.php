<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check UI Query Issue ===" . PHP_EOL;

// Simulate different queries that UI might be using
echo PHP_EOL . "Testing different query patterns..." . PHP_EOL;

// Pattern 1: UI might be combining both tables
echo PHP_EOL . "Pattern 1: Combined query (both tables)" . PHP_EOL;

$combinedQuery = "
    (SELECT 
        jurnal_umum.tanggal,
        jurnal_umum.keterangan,
        coas.kode_akun,
        coas.nama_akun,
        jurnal_umum.debit,
        jurnal_umum.kredit,
        'jurnal_umum' as source
    FROM jurnal_umum
    JOIN coas ON jurnal_umum.coa_id = coas.id
    WHERE jurnal_umum.tanggal = '2026-04-26'
    AND (jurnal_umum.keterangan LIKE '%Dedi%' OR jurnal_umum.keterangan LIKE '%Gaji%'))
    
    UNION ALL
    
    (SELECT 
        journal_entries.tanggal,
        journal_lines.memo as keterangan,
        coas.kode_akun,
        coas.nama_akun,
        journal_lines.debit,
        journal_lines.credit as kredit,
        'journal_entries' as source
    FROM journal_entries
    JOIN journal_lines ON journal_entries.id = journal_lines.journal_entry_id
    JOIN coas ON journal_lines.coa_id = coas.id
    WHERE journal_entries.tanggal = '2026-04-26'
    AND (journal_entries.memo LIKE '%Dedi%' OR journal_lines.memo LIKE '%Dedi%'))
";

$combinedResults = DB::select(DB::raw($combinedQuery));

echo "Combined query results: " . count($combinedResults) . PHP_EOL;
foreach ($combinedResults as $result) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s | %s",
        $result->tanggal,
        $result->keterangan,
        $result->kode_akun,
        $result->nama_akun,
        number_format($result->debit, 0),
        number_format($result->kredit, 0),
        $result->source
    ) . PHP_EOL;
}

// Pattern 2: UI might be using a different date format
echo PHP_EOL . "Pattern 2: Different date format check" . PHP_EOL;

$dateFormats = [
    '2026-04-26',
    '26-04-2026',
    '26/04/2026'
];

foreach ($dateFormats as $date) {
    $entries = DB::table('jurnal_umum')
        ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
        ->where('jurnal_umum.tanggal', $date)
        ->where(function($query) {
            $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
                   ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
        })
        ->count();
    
    echo "Date '$date': $entries entries" . PHP_EOL;
}

// Pattern 3: Check for any entries with empty keterangan that might be shown
echo PHP_EOL . "Pattern 3: Empty keterangan check" . PHP_EOL;

$emptyKeterangan = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->where(function($query) {
        $query->whereNull('jurnal_umum.keterangan')
               ->orWhere('jurnal_umum.keterangan', '')
               ->orWhere('jurnal_umum.keterangan', '-');
    })
    ->count();

echo "Empty keterangan entries: $emptyKeterangan" . PHP_EOL;

// Pattern 4: Check if UI is reading from a different table or view
echo PHP_EOL . "Pattern 4: Check for views or other tables" . PHP_EOL;

$tables = DB::select("SHOW TABLES");
$relevantTables = [];

foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (strpos(strtolower($tableName), 'jurnal') !== false || strpos(strtolower($tableName), 'journal') !== false) {
        $relevantTables[] = $tableName;
    }
}

echo "Relevant tables: " . implode(', ', $relevantTables) . PHP_EOL;

// Check each relevant table for Dedi entries
foreach ($relevantTables as $table) {
    try {
        $count = DB::table($table)
            ->whereDate('tanggal', '2026-04-26')
            ->where(function($query) {
                $query->where('keterangan', 'like', '%Dedi%')
                       ->orWhere('keterangan', 'like', '%Gaji%')
                       ->orWhere('memo', 'like', '%Dedi%')
                       ->orWhere('memo', 'like', '%Gaji%');
            })
            ->count();
        
        echo "Table '$table': $count entries" . PHP_EOL;
    } catch (\Exception $e) {
        echo "Table '$table': Error - " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Cache and Session Check ===" . PHP_EOL;

// Check if there are any session variables that might affect display
echo "Session data related to journal: " . (session()->has('journal_filters') ? 'EXISTS' : 'NONE') . PHP_EOL;

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "If database is correct but UI shows double:" . PHP_EOL;
echo "1. Clear browser cache (Ctrl+F5)" . PHP_EOL;
echo "2. Clear Laravel cache: php artisan cache:clear" . PHP_EOL;
echo "3. Clear config cache: php artisan config:clear" . PHP_EOL;
echo "4. Clear view cache: php artisan view:clear" . PHP_EOL;
echo "5. Restart web server" . PHP_EOL;
echo "6. Check if UI is using UNION query (combining both tables)" . PHP_EOL;

echo PHP_EOL . "=== Quick Fix Commands ===" . PHP_EOL;
echo "Run these commands:" . PHP_EOL;
echo "php artisan cache:clear" . PHP_EOL;
echo "php artisan config:clear" . PHP_EOL;
echo "php artisan view:clear" . PHP_EOL;
echo "php artisan route:clear" . PHP_EOL;
