<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Checking BOM Job Costing data...\n\n";

$produksi = \App\Models\Produksi::find(8);
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();

if (!$bomJobCosting) {
    echo "❌ BOM Job Costing not found\n";
    exit;
}

echo "📊 BOM Job Costing Data:\n";
echo "Product ID: {$bomJobCosting->produk_id}\n";
echo "Jumlah Produk: {$bomJobCosting->jumlah_produk}\n";
echo "Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 0, ',', '.') . "\n";
echo "Total Bahan Pendukung: Rp " . number_format($bomJobCosting->total_bahan_pendukung, 0, ',', '.') . "\n";
echo "Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 0, ',', '.') . "\n";
echo "Total BOP: Rp " . number_format($bomJobCosting->total_bop, 0, ',', '.') . "\n";
echo "Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 0, ',', '.') . "\n";
echo "HPP per Unit: Rp " . number_format($bomJobCosting->hpp_per_unit, 0, ',', '.') . "\n\n";

echo "📋 Production Data:\n";
echo "Qty Produksi: {$produksi->qty_produksi}\n";
echo "Total BTKL (stored): Rp " . number_format($produksi->total_btkl, 0, ',', '.') . "\n";
echo "Total BOP (stored): Rp " . number_format($produksi->total_bop, 0, ',', '.') . "\n\n";

// Calculate correct per unit values
$btklPerUnit = $bomJobCosting->jumlah_produk > 0 ? $bomJobCosting->total_btkl / $bomJobCosting->jumlah_produk : 0;
$bopPerUnit = $bomJobCosting->jumlah_produk > 0 ? $bomJobCosting->total_bop / $bomJobCosting->jumlah_produk : 0;

echo "💡 Calculated Per Unit Values:\n";
echo "BTKL per Unit: Rp " . number_format($btklPerUnit, 2, ',', '.') . "\n";
echo "BOP per Unit: Rp " . number_format($bopPerUnit, 2, ',', '.') . "\n\n";

// Calculate correct totals for production
$correctTotalBTKL = $btklPerUnit * $produksi->qty_produksi;
$correctTotalBOP = $bopPerUnit * $produksi->qty_produksi;

echo "✅ Correct Totals for Production:\n";
echo "BTKL Total: Rp " . number_format($correctTotalBTKL, 0, ',', '.') . "\n";
echo "BOP Total: Rp " . number_format($correctTotalBOP, 0, ',', '.') . "\n\n";

// Check individual BTKL and BOP components
echo "🔍 Individual BTKL Components:\n";
$btklComponents = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
foreach ($btklComponents as $btkl) {
    echo "- {$btkl->nama_proses}: Rp " . number_format($btkl->subtotal, 2, ',', '.') . "\n";
}

echo "\n🔍 Individual BOP Components:\n";
$bopComponents = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
foreach ($bopComponents as $bop) {
    echo "- {$bop->nama_bop}: Rp " . number_format($bop->subtotal, 2, ',', '.') . "\n";
}

echo "\n🎉 Analysis complete!\n";