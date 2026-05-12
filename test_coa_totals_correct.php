<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing COA Header Totals with Correct Logic...\n\n";

// Get all COA for user
$coas = \App\Models\Coa::where('user_id', 1)
    ->whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->orderBy('kode_akun')
    ->get();

echo "Found {$coas->count()} COA entries\n\n";

// First, collect all COA data
$allCoaData = [];
foreach ($coas as $coa) {
    $allCoaData[$coa->kode_akun] = [
        'nama' => $coa->nama_akun,
        'saldo' => $coa->saldo_awal ?? 0,
        'is_header' => (strlen($coa->kode_akun) <= 2) || (substr($coa->kode_akun, -1) == '0')
    ];
}

// Calculate totals for header accounts
$headerTotals = [];

foreach ($allCoaData as $kode => $data) {
    if ($data['is_header']) {
        // This is a header account, calculate total including all children
        $total = $data['saldo'];
        
        // Find all children (codes that start with this header code)
        foreach ($allCoaData as $childKode => $childData) {
            if ($childKode != $kode && strpos($childKode, $kode) === 0) {
                // This is a child of the header
                $total += $childData['saldo'];
            }
        }
        
        $headerTotals[$kode] = [
            'nama' => $data['nama'],
            'direct_saldo' => $data['saldo'],
            'total' => $total,
            'children_count' => 0
        ];
        
        // Count children
        foreach ($allCoaData as $childKode => $childData) {
            if ($childKode != $kode && strpos($childKode, $kode) === 0) {
                $headerTotals[$kode]['children_count']++;
            }
        }
    }
}

// Display results
echo "Header Account Totals (Corrected Logic):\n";
echo "==========================================\n";

foreach ($headerTotals as $kode => $header) {
    echo "Header: {$kode} - {$header['nama']}\n";
    echo "  Direct Saldo: " . number_format($header['direct_saldo'], 0, ',', '.') . "\n";
    echo "  Children Count: {$header['children_count']}\n";
    echo "  TOTAL (Header + Children): " . number_format($header['total'], 0, ',', '.') . "\n";
    
    // Show children details for main headers
    if ($header['children_count'] > 0) {
        echo "  Children:\n";
        foreach ($allCoaData as $childKode => $childData) {
            if ($childKode != $kode && strpos($childKode, $kode) === 0) {
                echo "    {$childKode} - {$childData['nama']}: " . number_format($childData['saldo'], 0, ',', '.') . "\n";
            }
        }
    }
    echo "\n";
}

// Show specific example: Aset (11)
echo "Detailed Example - Aset (11):\n";
echo "===============================\n";

if (isset($headerTotals['11'])) {
    $asetHeader = $headerTotals['11'];
    echo "Header: 11 - Aset\n";
    echo "Expected Total: " . number_format($asetHeader['total'], 0, ',', '.') . "\n";
    echo "Calculation:\n";
    
    $calculation = [];
    foreach ($allCoaData as $childKode => $childData) {
        if ($childKode != '11' && strpos($childKode, '11') === 0) {
            $calculation[] = "{$childKode} ({$childData['nama']}): " . number_format($childData['saldo'], 0, ',', '.');
        }
    }
    
    if ($asetHeader['direct_saldo'] != 0) {
        echo "  11 (Aset): " . number_format($asetHeader['direct_saldo'], 0, ',', '.') . "\n";
    }
    
    foreach ($calculation as $calc) {
        echo "  + {$calc}\n";
    }
    
    echo "  = " . number_format($asetHeader['total'], 0, ',', '.') . "\n";
}

echo "\nCOA totals corrected test completed!\n";
