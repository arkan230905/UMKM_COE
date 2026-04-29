<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging Trial Balance Service...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';

echo "Authenticated User ID: " . auth()->id() . "\n";
echo "Period: {$tanggalAwal} to {$tanggalAkhir}\n\n";

// Check COA data
echo "COA Data:\n";
echo "=========\n";

$coas = \App\Models\Coa::select('id', 'kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
    ->where('user_id', auth()->id())
    ->orderBy('kode_akun')
    ->get()
    ->groupBy('kode_akun')
    ->map(function ($group) {
        return $group->first();
    });

echo "Found {$coas->count()} COA records\n\n";

foreach ($coas->take(5) as $coa) {
    echo "COA: {$coa->kode_akun} - {$coa->nama_akun}\n";
    echo "  Saldo Awal: " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
    echo "  Tipe: {$coa->tipe_akun}\n";
    echo "\n";
}

// Check jurnal_umum data
echo "Journal Data:\n";
echo "=============\n";

$journalData = \Illuminate\Support\Facades\DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->whereBetween('ju.tanggal', [$tanggalAwal, $tanggalAkhir])
    ->select([
        'ju.debit',
        'ju.kredit',
        'coas.kode_akun',
        'coas.id as coa_id'
    ])
    ->get();

echo "Found {$journalData->count()} journal records\n\n";

foreach ($journalData->take(5) as $journal) {
    echo "Journal: COA {$journal->coa_id} ({$journal->kode_akun}) - Debit: " . number_format($journal->debit, 0, ',', '.') . ", Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
}

// Test TrialBalanceService step by step
echo "\nTrial Balance Service Debug:\n";
echo "===========================\n";

try {
    $trialBalanceService = app(\App\Services\TrialBalanceService::class);
    $trialBalanceData = $trialBalanceService->calculateTrialBalance($tanggalAwal, $tanggalAkhir);
    
    echo "Service executed successfully\n";
    echo "Total Debit: " . number_format($trialBalanceData['total_debit'], 0, ',', '.') . "\n";
    echo "Total Kredit: " . number_format($trialBalanceData['total_kredit'], 0, ',', '.') . "\n";
    echo "Account Count: " . count($trialBalanceData['accounts']) . "\n";
    
} catch (Exception $e) {
    echo "Error in TrialBalanceService: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDebug completed!\n";
