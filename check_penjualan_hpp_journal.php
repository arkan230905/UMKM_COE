<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Penjualan HPP Journal entries...\n";

// Check recent penjualan transactions
$penjualans = \App\Models\Penjualan::with(['details', 'jurnalEntries'])
    ->where('user_id', 1)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "\n=== Recent Penjualan Transactions ===\n";
foreach ($penjualans as $penjualan) {
    echo "\nPenjualan ID: " . $penjualan->id . "\n";
    echo "  Tanggal: " . $penjualan->tanggal . "\n";
    echo "  Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
    echo "  Total HPP: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
    echo "  Status: " . $penjualan->status . "\n";
    
    // Check journal entries
    echo "  Journal Entries:\n";
    $jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'LIKE', 'penjualan#' . $penjualan->id)->get();
    
    if ($jurnalEntries->count() > 0) {
        foreach ($jurnalEntries as $jurnal) {
            echo "    - " . $jurnal->coa->nama_akun . " | ";
            echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
            echo "\n";
        }
    } else {
        echo "    No journal entries found\n";
    }
    
    // Check if there are HPP related accounts
    $hppJournals = $jurnalEntries->filter(function($jurnal) {
        return strpos($jurnal->coa->nama_akun, 'HPP') !== false || 
               strpos($jurnal->coa->kode_akun, '51') !== false;
    });
    
    echo "  HPP Journals Found: " . $hppJournals->count() . "\n";
}

// Check COA accounts for HPP
echo "\n=== HPP COA Accounts ===\n";
$hppCoas = \App\Models\Coa::where('user_id', 1)
    ->where(function($query) {
        $query->where('nama_akun', 'LIKE', '%HPP%')
              ->orWhere('kode_akun', 'LIKE', '51%');
    })
    ->get();

echo "HPP COA Accounts: " . $hppCoas->count() . "\n";
foreach ($hppCoas as $coa) {
    echo "  " . $coa->kode_akun . " - " . $coa->nama_akun . "\n";
}

echo "\nPenjualan HPP Journal check completed!\n";
