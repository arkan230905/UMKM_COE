<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Fixed NeracaService...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';

echo "Period: {$tanggalAwal} to {$tanggalAkhir}\n\n";

// Test the actual NeracaService
try {
    $neracaService = app(\App\Services\NeracaService::class);
    $neraca = $neracaService->generateLaporanPosisiKeuangan($tanggalAwal, $tanggalAkhir);
    
    echo "NeracaService Results:\n";
    echo "=====================\n";
    
    echo "ASET:\n";
    echo "-----\n";
    echo "Aset Lancar:\n";
    foreach ($neraca['aset']['lancar'] as $aset) {
        echo "  {$aset['kode_akun']} - {$aset['nama_akun']}: " . number_format($aset['saldo'], 0, ',', '.') . "\n";
    }
    echo "Total Aset Lancar: " . number_format($neraca['aset']['total_lancar'], 0, ',', '.') . "\n";
    
    echo "Aset Tidak Lancar:\n";
    foreach ($neraca['aset']['tidak_lancar'] as $aset) {
        echo "  {$aset['kode_akun']} - {$aset['nama_akun']}: " . number_format($aset['saldo'], 0, ',', '.') . "\n";
    }
    echo "Total Aset Tidak Lancar: " . number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.') . "\n";
    
    echo "TOTAL ASET: " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n\n";
    
    echo "KEWAJIBAN:\n";
    echo "----------\n";
    foreach ($neraca['kewajiban']['detail'] as $kewajiban) {
        echo "  {$kewajiban['kode_akun']} - {$kewajiban['nama_akun']}: " . number_format($kewajiban['saldo'], 0, ',', '.') . "\n";
    }
    echo "Total Kewajiban: " . number_format($neraca['kewajiban']['total'], 0, ',', '.') . "\n\n";
    
    echo "EKUITAS:\n";
    echo "--------\n";
    foreach ($neraca['ekuitas']['detail'] as $ekuitas) {
        echo "  {$ekuitas['kode_akun']} - {$ekuitas['nama_akun']}: " . number_format($ekuitas['saldo'], 0, ',', '.') . "\n";
    }
    echo "Total Ekuitas: " . number_format($neraca['ekuitas']['total'], 0, ',', '.') . "\n\n";
    
    echo "SUMMARY:\n";
    echo "========\n";
    echo "Total Aset: " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
    echo "Total Kewajiban + Ekuitas: " . number_format($neraca['kewajiban']['total'] + $neraca['ekuitas']['total'], 0, ',', '.') . "\n";
    echo "Selisih: " . number_format($neraca['selisih'], 0, ',', '.') . "\n";
    echo "Status: " . ($neraca['is_balanced'] ? "BALANCED" : "NOT BALANCED") . "\n";
    
} catch (Exception $e) {
    echo "Error in NeracaService: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nNeracaService test completed!\n";
