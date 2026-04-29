<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debug Trial Balance Cache and COA Issues...\n";

// Check current COA 112 status
echo "\n=== CURRENT COA 112 STATUS ===\n";
$coa112 = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 6)->first();
if ($coa112) {
    echo "COA 112 (User ID 6): {$coa112->nama_akun} - {$coa112->tipe_akun}\n";
} else {
    echo "COA 112 not found for user ID 6\n";
}

// Check all COA with kode 112
echo "\n=== ALL COA WITH KODE 112 ===\n";
$allCoa112 = \App\Models\Coa::where('kode_akun', '112')->get();
foreach ($allCoa112 as $coa) {
    echo "User ID {$coa->user_id}: {$coa->nama_akun} - {$coa->tipe_akun} - Saldo Awal: " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
}

// Check what TrialBalanceService is actually using
echo "\n=== TRIAL BALANCE SERVICE DEBUG ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);

// Use reflection to see the internal COA selection
$reflection = new \ReflectionClass($trialBalanceService);
$method = $reflection->getMethod('calculateTrialBalance');
$method->setAccessible(true);

// Call the method but let's debug the COA selection part
$startDate = now()->startOfMonth()->format('Y-m-d');
$endDate = now()->endOfMonth()->format('Y-m-d');

// Get the COA that the service is using
$coas = \App\Models\Coa::select('id', 'kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
    ->orderBy('kode_akun')
    ->get()
    ->groupBy('kode_akun')
    ->map(function ($group) {
        return $group->first();
    });

echo "COAs used by TrialBalanceService:\n";
foreach ($coas as $coa) {
    if ($coa->kode_akun == '112') {
        echo "112: {$coa->nama_akun} (User ID: " . ($coa->user_id ?? 'NULL') . ") - Saldo Awal: " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
    }
}

// Force refresh and test again
echo "\n=== FORCE REFRESH TRIAL BALANCE ===\n";
$trialBalance = $trialBalanceService->calculateTrialBalance($startDate, $endDate);

echo "After refresh:\n";
foreach ($trialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '112') {
        echo "112: {$account['nama_akun']} - D: {$account['debit']}, K: {$account['kredit']}\n";
        echo "  Saldo Awal: " . number_format($account['saldo_awal'] ?? 0, 0, ',', '.') . "\n";
        echo "  Source: {$account['source']}\n";
    }
}

// Check if there are multiple user IDs causing issues
echo "\n=== CHECK USER ID FILTERING ===\n";
// Check what happens if we specifically get user 6's COA
$coa112User6 = \App\Models\Coa::where('kode_akun', '112')
                              ->where('user_id', 6)
                              ->first();

if ($coa112User6) {
    echo "User 6 COA 112: {$coa112User6->nama_akun}\n";
    
    // Check if this COA has any journal lines
    $hasJournalLines = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->where('journal_lines.coa_id', $coa112User6->id)
        ->exists();
    
    echo "Has journal lines: " . ($hasJournalLines ? 'YES' : 'NO') . "\n";
    
    if ($hasJournalLines) {
        $journalLines = \DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.coa_id', $coa112User6->id)
            ->where('journal_entries.tanggal', '<=', $endDate)
            ->select('journal_lines.debit', 'journal_lines.credit')
            ->get();
            
        $totalDebit = $journalLines->sum('debit');
        $totalCredit = $journalLines->sum('credit');
        
        echo "Journal totals - Debit: {$totalDebit}, Credit: {$totalCredit}\n";
        echo "Expected balance: " . ($coa112User6->saldo_awal + $totalDebit - $totalCredit) . "\n";
    }
}
