<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Penjualan Journal COA Fix...\n\n";

// Check if the COA codes exist
echo "Checking COA Codes:\n";
echo "==================\n";

$coaCodes = ['112', '111', '118'];
foreach ($coaCodes as $code) {
    $coa = \App\Models\Coa::where('kode_akun', $code)->first();
    if ($coa) {
        echo "COA {$code}: {$coa->nama_akun} - {$coa->tipe_akun} - Found\n";
    } else {
        echo "COA {$code}: Not Found\n";
    }
}

echo "\n";

// Check if COA 1111 exists (should not exist)
$coa1111 = \App\Models\Coa::where('kode_akun', '1111')->first();
if ($coa1111) {
    echo "COA 1111: {$coa1111->nama_akun} - {$coa1111->tipe_akun} - Found (this was causing error)\n";
} else {
    echo "COA 1111: Not Found (good - this was the problem)\n";
}

echo "\n";

// Test the JournalService coaId method
echo "Testing JournalService coaId method:\n";
echo "===================================\n";

$journalService = new \App\Services\JournalService();

try {
    $coaId112 = $journalService->coaId('112');
    echo "COA ID for 112: {$coaId112} - Success\n";
} catch (Exception $e) {
    echo "COA ID for 112: Error - " . $e->getMessage() . "\n";
}

try {
    $coaId111 = $journalService->coaId('111');
    echo "COA ID for 111: {$coaId111} - Success\n";
} catch (Exception $e) {
    echo "COA ID for 111: Error - " . $e->getMessage() . "\n";
}

try {
    $coaId118 = $journalService->coaId('118');
    echo "COA ID for 118: {$coaId118} - Success\n";
} catch (Exception $e) {
    echo "COA ID for 118: Error - " . $e->getMessage() . "\n";
}

try {
    $coaId1111 = $journalService->coaId('1111');
    echo "COA ID for 1111: {$coaId1111} - Success\n";
} catch (Exception $e) {
    echo "COA ID for 1111: Error - " . $e->getMessage() . "\n";
}

echo "\n";

// Test penjualan journal creation simulation
echo "Testing Penjualan Journal Creation Simulation:\n";
echo "==============================================\n";

// Create a mock penjualan object
$mockPenjualan = (object) [
    'id' => 999,
    'payment_method' => 'cash',
    'grand_total' => 500000,
    'user_id' => 1,
    'coa_id' => null,
    'tanggal' => '2026-04-30'
];

echo "Mock Penjualan:\n";
echo "- Payment Method: {$mockPenjualan->payment_method}\n";
echo "- Grand Total: " . number_format($mockPenjualan->grand_total, 0, ',', '.') . "\n";
echo "- User ID: {$mockPenjualan->user_id}\n\n";

// Simulate the COA finding logic from JournalService
echo "Simulating COA Finding Logic:\n";
echo "==============================\n";

$userId = $mockPenjualan->user_id;

switch ($mockPenjualan->payment_method) {
    case 'cash':
        $coa = \App\Models\Coa::withoutGlobalScopes()
            ->where('nama_akun', 'Kas')
            ->where('tipe_akun', 'Asset')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderBy('id', 'desc')
            ->first();
        
        $debitAccount = $coa ? $coa->kode_akun : '112'; // Fixed fallback
        echo "Cash Payment - Found COA: " . ($coa ? $coa->nama_akun . ' (' . $coa->kode_akun . ')' : 'None, using fallback 112') . "\n";
        echo "Debit Account Code: {$debitAccount}\n";
        break;
}

echo "\nPenjualan journal fix test completed!\n";
