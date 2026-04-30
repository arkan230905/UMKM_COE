<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Verifying Production BOP display for production ID: 1\n";

// Get production with processes
$produksi = \App\Models\Produksi::with(['proses'])->find(1);

if (!$produksi) {
    echo "Production ID 1 not found\n";
    exit;
}

echo "\n=== Production Summary ===\n";
echo "Produk: " . $produksi->produk->nama_produk . "\n";
echo "Qty: " . number_format($produksi->qty_produksi, 0, ',', '.') . " pcs\n";
echo "Status: " . $produksi->status . "\n";

echo "\n=== Process Table Simulation ===\n";
echo str_repeat("=", 120) . "\n";
printf("%-8s | %-35s | %-12s | %-15s | %-15s | %-15s | %-20s\n", 
    "Urutan", "Nama Proses", "Status", "Biaya BTKL", "Biaya BOP", "Total Biaya", "Waktu");
echo str_repeat("-", 120) . "\n";

$totalBtkl = 0;
$totalBop = 0;
$totalBiaya = 0;

foreach ($produksi->proses as $proses) {
    printf("%-8s | %-35s | %-12s | %-15s | %-15s | %-15s | %-20s\n", 
        $proses->urutan,
        $proses->nama_proses,
        $proses->status,
        "Rp " . number_format($proses->biaya_btkl, 0, ',', '.'),
        "Rp " . number_format($proses->biaya_bop, 0, ',', '.'),
        "Rp " . number_format($proses->total_biaya_proses, 0, ',', '.'),
        $proses->waktu_mulai ? $proses->waktu_mulai->format('d/m H:i') : '-'
    );
    
    $totalBtkl += $proses->biaya_btkl;
    $totalBop += $proses->biaya_bop;
    $totalBiaya += $proses->total_biaya_proses;
}

echo str_repeat("-", 120) . "\n";
printf("%-8s | %-35s | %-12s | %-15s | %-15s | %-15s | %-20s\n", 
    "", "TOTAL", "", 
    "Rp " . number_format($totalBtkl, 0, ',', '.'),
    "Rp " . number_format($totalBop, 0, ',', '.'),
    "Rp " . number_format($totalBiaya, 0, ',', '.'),
    ""
);
echo str_repeat("=", 120) . "\n";

echo "\n=== Verification Results ===\n";
echo "Total BTKL: Rp " . number_format($totalBtkl, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($totalBop, 0, ',', '.') . "\n";
echo "Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n";

echo "\nExpected vs Actual:\n";
echo "BOP should be > 0: " . ($totalBop > 0 ? "YES" : "NO") . "\n";
echo "Pengukusan BOP > 0: " . ($produksi->proses[0]->biaya_bop > 0 ? "YES" : "NO") . "\n";
echo "Pengemasan BOP > 0: " . ($produksi->proses[1]->biaya_bop > 0 ? "YES" : "NO") . "\n";

echo "\nProduction BOP display verification completed!\n";
