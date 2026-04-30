<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging BOP mapping for production ID: 1\n";

// Get production processes
$processes = \App\Models\ProduksiProses::where('produksi_id', 1)->get();

// Get BOP details grouped by process
$bopDetails = \App\Models\ProduksiBopDetail::where('produksi_id', 1)->get();
$bopByProcess = [];

foreach ($bopDetails as $bopDetail) {
    $namaProses = $bopDetail->nama_proses;
    if (!isset($bopByProcess[$namaProses])) {
        $bopByProcess[$namaProses] = 0;
    }
    $bopByProcess[$namaProses] += (float)$bopDetail->total;
}

echo "\n=== Process Names Comparison ===\n";
echo "Process names in ProduksiProses:\n";
foreach ($processes as $process) {
    echo "  - '" . $process->nama_proses . "'\n";
}

echo "\nProcess names in BOP Details:\n";
foreach (array_keys($bopByProcess) as $processName) {
    echo "  - '" . $processName . "'\n";
}

echo "\n=== BOP Amounts by Process ===\n";
foreach ($bopByProcess as $processName => $bopAmount) {
    echo "  '" . $processName . "': Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
}

echo "\n=== Mapping Test ===\n";
foreach ($processes as $process) {
    $bopAmount = 0;
    $processName = $process->nama_proses;
    
    echo "\nTesting process: '" . $processName . "'\n";
    
    // Exact match
    if (isset($bopByProcess[$processName])) {
        $bopAmount = $bopByProcess[$processName];
        echo "  Exact match found: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    } else {
        echo "  No exact match\n";
        
        // Partial match
        foreach ($bopByProcess as $bopProcessName => $bopValue) {
            if ($bopProcessName !== 'Umum' &&
                (stripos($processName, $bopProcessName) !== false ||
                 stripos($bopProcessName, $processName) !== false)) {
                $bopAmount = $bopValue;
                echo "  Partial match with '" . $bopProcessName . "': Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
                break;
            }
        }
        
        if ($bopAmount == 0 && isset($bopByProcess['Umum'])) {
            $bopAmount = $bopByProcess['Umum'];
            echo "  Using 'Umum' bucket: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
        }
    }
    
    echo "  Final BOP amount: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    echo "  Current stored BOP: Rp " . number_format($process->biaya_bop, 0, ',', '.') . "\n";
}

echo "\nBOP mapping debug completed!\n";
