<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\AkuntansiController;
use Illuminate\Http\Request;

echo "=== TESTING CONTROLLER DIRECTLY ===\n\n";

// Test 1: Buku Besar COA dropdown
echo "=== TEST 1: BUKU BESAR COA DROPDOWN ===\n";

$controller = new AkuntansiController();
$request = new Request();

// Simulate the bukuBesar method call
try {
    // Get COAs using the same method as controller
    $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun')
        ->groupBy('kode_akun', 'nama_akun', 'tipe_akun')
        ->orderBy('kode_akun')
        ->get();
    
    echo "COA count from controller method: " . $coas->count() . "\n";
    echo "First 15 COAs:\n";
    foreach ($coas->take(15) as $coa) {
        echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
    }
    
    // Check if there are any missing common accounts
    $common_accounts = ['111', '112', '114', '115', '210', '310', '410', '510', '550'];
    echo "\nChecking common accounts:\n";
    foreach ($common_accounts as $code) {
        $found = $coas->where('kode_akun', $code)->first();
        if ($found) {
            echo "  ✅ {$code} - {$found->nama_akun}\n";
        } else {
            echo "  ❌ {$code} - NOT FOUND\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error in buku besar test: " . $e->getMessage() . "\n";
}

// Test 2: Financial Position Report
echo "\n=== TEST 2: FINANCIAL POSITION REPORT ===\n";

try {
    $bulan = date('m');
    $tahun = date('Y');
    
    // Call the private method using reflection
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getLaporanPosisiKeuanganData');
    $method->setAccessible(true);
    
    $data = $method->invoke($controller, $bulan, $tahun);
    
    echo "Financial Position Report Data:\n";
    echo "Total Aset: " . number_format($data['totalAset'], 0, ',', '.') . "\n";
    echo "Total Kewajiban: " . number_format($data['totalKewajiban'], 0, ',', '.') . "\n";
    echo "Total Ekuitas: " . number_format($data['totalEkuitas'], 0, ',', '.') . "\n";
    echo "Total Kewajiban + Ekuitas: " . number_format($data['totalKewajibanEkuitas'], 0, ',', '.') . "\n";
    echo "Profit/Loss: " . number_format($data['profitLoss'], 0, ',', '.') . "\n";
    
    $balance = $data['totalAset'] - $data['totalKewajibanEkuitas'];
    echo "Balance (Aset - Kewajiban - Ekuitas): " . number_format($balance, 0, ',', '.') . "\n";
    
    if (abs($balance) < 1) {
        echo "✅ BALANCE SHEET IS BALANCED!\n";
    } else {
        echo "❌ BALANCE SHEET IS NOT BALANCED!\n";
    }
    
    // Show equity accounts
    echo "\nEquity accounts:\n";
    foreach ($data['ekuitas'] as $equity) {
        $saldo = $data['getFinalBalance']($equity);
        echo "  {$equity->kode_akun} - {$equity->nama_akun}: " . number_format($saldo, 0, ',', '.') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error in financial position test: " . $e->getMessage() . "\n";
}

echo "\n🔍 CONTROLLER TESTING COMPLETED!\n";