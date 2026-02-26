<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Tabel journal_lines ===\n";

if (\Schema::hasTable('journal_lines')) {
    $columns = \Schema::getColumnListing('journal_lines');
    echo "Kolom di tabel journal_lines:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
    
    // Cek sample data
    echo "\n=== Sample Data journal_lines ===\n";
    $sample = \DB::table('journal_lines')->limit(5)->get();
    foreach ($sample as $row) {
        echo "ID: {$row->id}, COA ID: " . ($row->coa_id ?? 'N/A') . ", Account ID: " . ($row->account_id ?? 'N/A') . ", Debit: " . ($row->debit ?? 'N/A') . ", Credit: " . ($row->credit ?? 'N/A') . "\n";
    }
    
    // Cek data untuk COA Kas dan Bank
    echo "\n=== Cek Data untuk COA Kas & Bank ===\n";
    $coas = \App\Models\Coa::whereIn('kode_akun', ['1110', '1120'])->get();
    
    foreach ($coas as $coa) {
        echo "\nCOA: {$coa->nama_akun} ({$coa->kode_akun}), ID: {$coa->id}\n";
        
        $journalLines = \DB::table('journal_lines')
            ->where('coa_id', $coa->id)
            ->get();
            
        echo "Journal lines count: " . $journalLines->count() . "\n";
        
        foreach ($journalLines->take(3) as $jl) {
            echo "  - Journal Line ID: {$jl->id}, Debit: " . ($jl->debit ?? 0) . ", Credit: " . ($jl->credit ?? 0) . "\n";
        }
    }
} else {
    echo "Tabel journal_lines tidak ada\n";
}

echo "\n=== Cek LaporanKasBankController Logic ===\n";
$laporanController = new \App\Http\Controllers\LaporanKasBankController();
$request = new \Illuminate\Http\Request([
    'start_date' => now()->startOfMonth()->format('Y-m-d'),
    'end_date' => now()->endOfMonth()->format('Y-m-d')
]);

// Cek method getSaldoAwal
$akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
foreach ($akunKasBank as $akun) {
    echo "\nTesting getSaldoAwal untuk {$akun->nama_akun}:\n";
    try {
        $saldoAwal = $laporanController->getSaldoAwal($akun, $request->start_date);
        echo "✅ Saldo awal: Rp " . number_format($saldoAwal, 2) . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
