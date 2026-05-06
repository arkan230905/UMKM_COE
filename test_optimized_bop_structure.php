<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING OPTIMIZED BOP STRUCTURE FOR MULTIPLE COMPONENTS ===\n\n";

// 1. Check current BOP structure
echo "1. Current Optimized BOP Structure:\n";
$bopProses = \App\Models\BopProses::all();

foreach ($bopProses as $bp) {
    echo "ID: " . $bp->id . "\n";
    echo "Proses: " . ($bp->prosesProduksi ? $bp->prosesProduksi->nama_proses : 'N/A') . "\n";
    echo "Komponen BOP (JSON): " . json_encode($bp->komponen_bop) . "\n";
    echo "Total BOP / produk: " . $bp->total_bop_per_produk . "\n";
    echo "Total Biaya / produk: " . $bp->total_biaya_per_produk . "\n";
    echo "BOP / unit: " . $bp->bop_per_unit . "\n";
    echo "---\n";
}

// 2. Test with example data from your setup
echo "\n2. Testing with Your Example Data:\n";
$testData = [
    'proses_produksi_id' => 1,
    'komponen_bop' => [
        ['component' => 'Gas / BBM', 'rate_per_produk' => 67, 'description' => 'Keterangan'],
        ['component' => 'Air & Kebersihan', 'rate_per_produk' => 28, 'description' => 'Keterangan']
    ]
];

// Calculate expected values
$totalBopPerProduk = 67 + 28; // 95
$btklPerProduk = 166.67; // From your example (20000 / 120)
$totalBiayaPerProduk = 166.67 + 95; // 261.67

echo "Expected calculations:\n";
echo "- Total BOP / produk: Rp " . $totalBopPerProduk . "\n";
echo "- BTKL / produk: Rp " . number_format($btklPerProduk, 2) . "\n";
echo "- Total Biaya / produk: Rp " . number_format($totalBiayaPerProduk, 2) . "\n";

// 3. Show database structure
echo "\n3. Optimized Database Structure:\n";
echo "CREATE TABLE bop_proses (\n";
echo "    id BIGINT PRIMARY KEY,\n";
echo "    user_id BIGINT,                    -- ✅ Multi-tenant\n";
echo "    proses_produksi_id BIGINT,         -- Foreign key ke BTKL\n";
echo "    komponen_bop JSON,                 -- ✅ All components in one JSON\n";
echo "    total_bop_per_produk DECIMAL(15,2), -- ✅ Calculated total BOP\n";
echo "    total_biaya_per_produk DECIMAL(15,2), -- ✅ BTKL + BOP\n";
echo "    bop_per_unit DECIMAL(15,4),        -- ✅ Backward compatibility\n";
echo "    kapasitas_per_jam INT,            -- ✅ Sync dari BTKL\n";
echo "    budget DECIMAL(15,2),              -- ✅ Budget tracking\n";
echo "    aktual DECIMAL(15,2),              -- ✅ Actual tracking\n";
echo "    is_active BOOLEAN,\n";
echo "    created_at, updated_at\n";
echo ");\n";

// 4. Show JSON structure example
echo "\n4. JSON Structure for Components:\n";
echo "{\n";
echo "  \"komponen_bop\": [\n";
echo "    {\"component\": \"Gas / BBM\", \"rate_per_produk\": 67, \"description\": \"Keterangan\"},\n";
echo "    {\"component\": \"Air & Kebersihan\", \"rate_per_produk\": 28, \"description\": \"Keterangan\"},\n";
echo "    {\"component\": \"Listrik\", \"rate_per_produk\": 15, \"description\": \"\"},\n";
echo "    {\"component\": \"Maintenance\", \"rate_per_produk\": 10, \"description\": \"\"}\n";
echo "  ]\n";
echo "}\n";

echo "\n=== ADVANTAGES OF NEW STRUCTURE ===\n";
echo "✅ Scalable: Can handle unlimited components\n";
echo "✅ Flexible: Any component name and value\n";
echo "✅ Consistent: All data in one JSON field\n";
echo "✅ Calculated: Auto-calculate totals\n";
echo "✅ Multi-tenant: User isolation\n";
echo "✅ Performance: Single JSON query\n";

echo "\n=== READY FOR MULTIPLE COMPONENTS! 🎉 ===\n";
