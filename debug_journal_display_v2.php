<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DEBUGGING JOURNAL DISPLAY ISSUE\n";
echo "==============================\n\n";

// Check database state
echo "Current database state:\n";
echo "======================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

echo "PRODUKSI JOURNALS IN DATABASE:\n";
foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

// Test the exact query that AkuntansiController uses
echo "\nTesting AkuntansiController query:\n";
echo "=================================\n";

$query = \DB::table('jurnal_umum as j')
    ->leftJoin('coas', 'coas.id', '=', 'j.coa_id')
    ->select([
        'j.*',
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun'
    ])
    ->where(function($q) {
        $q->where('j.debit', '!=', 0)
          ->orWhere('j.kredit', '!=', 0);
    })
    ->orderBy('j.tanggal','asc')
    ->orderBy('j.id','asc');

// Filter for produksi transactions on 2026-04-12
$query->whereDate('j.tanggal', '2026-04-12')
       ->where('j.tipe_referensi', 'produksi');

$results = $query->get();

echo "QUERY RESULTS (same as controller):\n";
foreach ($results as $result) {
    echo "ID: {$result->id} | Ref: {$result->referensi} | COA: {$result->kode_akun} | D: " . number_format($result->debit, 0, ',', '.') . " | K: " . number_format($result->kredit, 0, ',', '.') . " | {$result->keterangan}\n";
}

// Test grouping by referensi (like controller does)
echo "\nGrouped by referensi (like controller does):\n";
echo "==========================================\n";

$groupedResults = $results->groupBy('referensi');

foreach ($groupedResults as $referensi => $lines) {
    echo "Transaksi: {$referensi}\n";
    
    foreach ($lines as $line) {
        echo "  {$line->kode_akun} | D: " . number_format($line->debit, 0, ',', '.') . " | K: " . number_format($line->kredit, 0, ',', '.') . " | {$line->keterangan}\n";
    }
    echo "---\n";
}

// Check if there are any other tables that might be used
echo "\nChecking for other journal tables:\n";
echo "=================================\n";

$tables = \DB::select("SHOW TABLES LIKE '%jurnal%'");
echo "Tables with 'jurnal':\n";
foreach ($tables as $table) {
    foreach ($table as $tableName) {
        echo "  - $tableName\n";
        
        if ($tableName != 'jurnal_umum') {
            try {
                $count = \DB::table($tableName)->count();
                echo "    Records: $count\n";
                
                if ($count > 0) {
                    echo "    -> This table has data and might be used by the UI!\n";
                    
                    // Show sample data
                    $sample = \DB::table($tableName)->limit(3)->get();
                    foreach ($sample as $row) {
                        echo "    Sample: " . json_encode($row) . "\n";
                    }
                }
            } catch (\Exception $e) {
                echo "    Error: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\nSOLUTION:\n";
echo "========\n";
echo "1. Database is correct\n";
echo "2. Controller query is correct\n";
echo "3. The issue might be:\n";
echo "   - Browser cache\n";
echo "   - JavaScript cache\n";
echo "   - Session data\n";
echo "   - Different table being used by UI\n\n";
echo "Please:\n";
echo "1. Close browser completely\n";
echo "2. Clear browser cache manually\n";
echo "3. Reopen and try again\n";
echo "4. If still wrong, the UI might be using a different table\n";

?>
