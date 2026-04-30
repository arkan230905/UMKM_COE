<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing Production BOP calculation for production ID: 1\n";

$produksi = \App\Models\Produksi::find(1);

if (!$produksi) {
    echo "Production ID 1 not found\n";
    exit;
}

// Get BOP details and group by process
$bopDetails = \App\Models\ProduksiBopDetail::where('produksi_id', 1)->get();
$bopByProcess = [];

foreach ($bopDetails as $bopDetail) {
    $namaProses = trim($bopDetail->nama_proses); // Trim whitespace
    if (!isset($bopByProcess[$namaProses])) {
        $bopByProcess[$namaProses] = 0;
    }
    $bopByProcess[$namaProses] += (float)$bopDetail->total;
}

echo "\n=== BOP Amounts by Process (trimmed) ===\n";
foreach ($bopByProcess as $processName => $bopAmount) {
    echo "  '" . $processName . "': Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
}

// Update BOP for each process with better matching
$processes = \App\Models\ProduksiProses::where('produksi_id', 1)->get();

foreach ($processes as $proses) {
    $bopAmount = 0;
    $processName = trim($proses->nama_proses); // Trim whitespace
    
    echo "\nUpdating process: '" . $processName . "'\n";
    
    // Exact match
    if (isset($bopByProcess[$processName])) {
        $bopAmount = $bopByProcess[$processName];
        echo "  Exact match: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    } else {
        // Partial match with better logic
        foreach ($bopByProcess as $bopProcessName => $bopValue) {
            if ($bopProcessName !== 'Umum') {
                // Remove extra spaces and compare
                $cleanProcessName = preg_replace('/\s+/', ' ', $processName);
                $cleanBopProcessName = preg_replace('/\s+/', ' ', $bopProcessName);
                
                if (strcasecmp($cleanProcessName, $cleanBopProcessName) === 0 ||
                    stripos($cleanProcessName, $cleanBopProcessName) !== false ||
                    stripos($cleanBopProcessName, $cleanProcessName) !== false) {
                    $bopAmount = $bopValue;
                    echo "  Partial match with '" . $bopProcessName . "': Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
                    break;
                }
            }
        }
        
        // Fallback to 'Umum'
        if ($bopAmount == 0 && isset($bopByProcess['Umum'])) {
            $bopAmount = $bopByProcess['Umum'];
            echo "  Using 'Umum': Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
        }
    }
    
    // Calculate BTKL multiplied by quantity
    $btklAmount = $proses->biaya_btkl * $produksi->qty_produksi;
    
    echo "  BTKL (x{$produksi->qty_produksi}): Rp " . number_format($btklAmount, 0, ',', '.') . "\n";
    echo "  BOP: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    echo "  Total: Rp " . number_format($btklAmount + $bopAmount, 0, ',', '.') . "\n";
    
    // Update the process
    $proses->update([
        'biaya_btkl' => $btklAmount,
        'biaya_bop' => $bopAmount,
        'total_biaya_proses' => $btklAmount + $bopAmount,
    ]);
    
    echo "  Updated successfully!\n";
}

echo "\nProduction BOP calculation fixed!\n";

// Show final results
echo "\n=== Final Results ===\n";
$finalProcesses = \App\Models\ProduksiProses::where('produksi_id', 1)->get();
foreach ($finalProcesses as $process) {
    echo $process->nama_proses . ":\n";
    echo "  BTKL: Rp " . number_format($process->biaya_btkl, 0, ',', '.') . "\n";
    echo "  BOP: Rp " . number_format($process->biaya_bop, 0, ',', '.') . "\n";
    echo "  Total: Rp " . number_format($process->total_biaya_proses, 0, ',', '.') . "\n";
}
