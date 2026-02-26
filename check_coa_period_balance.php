<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check CoaPeriodBalance untuk Februari 2026 ===\n";

// Cek periode Februari 2026
$periode = \App\Models\CoaPeriod::where('periode', '2026-02')->first();

if ($periode) {
    echo "Periode ditemukan: {$periode->periode} (ID: {$periode->id})\n";
    echo "Tanggal Mulai: {$periode->tanggal_mulai}\n";
    echo "Tanggal Selesai: {$periode->tanggal_selesai}\n";
    echo "Status: " . ($periode->is_closed ? 'Closed' : 'Open') . "\n";
    
    // Cek balances untuk Bank (1120)
    $balance = \DB::table('coa_period_balances')
        ->where('period_id', $periode->id)
        ->where('kode_akun', '1120')
        ->first();
    
    if ($balance) {
        echo "Balance Bank: Saldo Awal = Rp " . number_format($balance->saldo_awal, 2) . ", Saldo Akhir = Rp " . number_format($balance->saldo_akhir, 2) . "\n";
    } else {
        echo "Balance Bank: Tidak ada data\n";
    }
} else {
    echo "Periode Februari 2026 tidak ditemukan\n";
}

// Cek semua periode
echo "\n=== Semua Periode ===\n";
$allPeriods = \App\Models\CoaPeriod::orderBy('periode', 'desc')->get();
foreach ($allPeriods as $p) {
    echo "- {$p->periode}: {$p->tanggal_mulai} s/d {$p->tanggal_selesai} (Closed: " . ($p->is_closed ? 'Yes' : 'No') . ")\n";
}
