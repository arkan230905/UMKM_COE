<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing COA Header Account Totals...\n\n";

// Get all COA for user
$coas = \App\Models\Coa::where('user_id', 1)
    ->whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->orderBy('kode_akun')
    ->get();

echo "Found {$coas->count()} COA entries\n\n";

// Group COA by hierarchy
$headerAccounts = [];
$subAccounts = [];

foreach ($coas as $coa) {
    $kode = $coa->kode_akun;
    $nama = $coa->nama_akun;
    $saldoAwal = $coa->saldo_awal ?? 0;
    
    echo "COA: {$kode} - {$nama} - Saldo: " . number_format($saldoAwal, 0, ',', '.') . "\n";
    
    // Determine if this is a header account (no digits or ends with 0)
    $isHeader = (strlen($kode) <= 2) || (substr($kode, -1) == '0');
    
    if ($isHeader) {
        $headerAccounts[$kode] = [
            'nama' => $nama,
            'saldo' => $saldoAwal,
            'children' => []
        ];
        echo "  -> Header Account\n";
    } else {
        // Find parent header
        $parentKode = findParentCode($kode);
        if ($parentKode && isset($headerAccounts[$parentKode])) {
            $headerAccounts[$parentKode]['children'][] = [
                'kode' => $kode,
                'nama' => $nama,
                'saldo' => $saldoAwal
            ];
            echo "  -> Child of Header: {$parentKode}\n";
        } else {
            echo "  -> Orphan Account (no header found)\n";
        }
    }
    echo "\n";
}

// Calculate totals for each header
echo "Header Account Totals:\n";
echo "=======================\n";

foreach ($headerAccounts as $kode => $header) {
    $totalChildren = 0;
    foreach ($header['children'] as $child) {
        $totalChildren += $child['saldo'];
    }
    
    $totalHeader = $header['saldo'] + $totalChildren;
    
    echo "Header: {$kode} - {$header['nama']}\n";
    echo "  Direct Saldo: " . number_format($header['saldo'], 0, ',', '.') . "\n";
    echo "  Children Count: " . count($header['children']) . "\n";
    echo "  Children Total: " . number_format($totalChildren, 0, ',', '.') . "\n";
    echo "  GRAND TOTAL: " . number_format($totalHeader, 0, ',', '.') . "\n";
    echo "\n";
}

// Helper function to find parent code
function findParentCode($kode) {
    $length = strlen($kode);
    
    if ($length <= 2) {
        return null; // This is already a main header
    }
    
    // For 3-digit codes (114), parent is 2-digit (11)
    if ($length == 3) {
        return substr($kode, 0, 2);
    }
    
    // For 4-digit codes (1141), parent is 3-digit (114)
    if ($length == 4) {
        return substr($kode, 0, 3);
    }
    
    return null;
}

echo "COA header totals test completed!\n";
