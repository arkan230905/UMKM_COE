<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SUMMARY LOGIC PRODUKSI BARU ===\n\n";

echo "✅ USER REQUIREMENT:\n";
echo "   \"Seluruh nominal di BOM keluar lalu dikalikan dengan quantity produksi\"\n\n";

echo "📋 DATA BOM (Per Unit):\n";
$produk = \App\Models\Produk::find(1);
$bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

$totalBBB = $bom ? $bom->details->sum('total_harga') : 0;
$totalBTKL = 0;
$totalBOP = 0;

if ($bomJobCosting) {
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    $totalBTKL = $btklDetails->sum('total_biaya');
    $totalBOP = $bomJobCosting->total_bop;
}

echo "   📦 Bahan Baku (BBB): Rp " . number_format($totalBBB, 0, ',', '.') . "\n";
echo "   👥 BTKL: Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
echo "   🏭 BOP: Rp " . number_format($totalBOP, 0, ',', '.') . "\n";
echo "   💰 Total BOM: Rp " . number_format($totalBBB + $totalBTKL + $totalBOP, 0, ',', '.') . "\n\n";

echo "🔄 PRODUKSI CALCULATION:\n";
echo "   📝 Qty Produksi × Total BOM = Total Biaya Produksi\n\n";

echo "📊 CONTOH PERHITUNGAN:\n";
for ($qty = 1; $qty <= 3; $qty++) {
    $totalProduksi = ($totalBBB + $totalBTKL + $totalBOP) * $qty;
    echo "   Qty " . $qty . ": Rp " . number_format($totalProduksi, 0, ',', '.') . 
         " (Rp " . number_format($totalBBB + $totalBTKL + $totalBOP, 0, ',', '.') . " × " . $qty . ")\n";
}

echo "\n🎯 HASIL AKHIR DI DETAIL PRODUKSI:\n";
echo "   ✅ Total Bahan = Total BBB BOM × Qty Produksi\n";
echo "   ✅ Total BTKL = Total BTKL BOM × Qty Produksi\n";
echo "   ✅ Total BOP = Total BOP BOM × Qty Produksi\n";
echo "   ✅ Total Biaya = Total BOM × Qty Produksi\n\n";

echo "📝 IMPLEMENTATION:\n";
echo "   Logic sudah diupdate di ProduksiController::store()\n";
echo "   Mengambil data langsung dari BOM dan BomJobCosting\n";
echo "   Mengalikan dengan qty produksi yang diinput user\n";
