<?php

echo "🧪 Testing HPP Form Functionality\n";
echo "================================\n\n";

// Test the API endpoints
echo "📡 Testing API Endpoints:\n";

$baseUrl = 'http://127.0.0.1:8000';

// Test BBB API
echo "1. Testing BBB API...\n";
$bbbUrl = "$baseUrl/api/get-available-bbb/2"; // Using product ID 2 (Jasuke)
$bbbResponse = @file_get_contents($bbbUrl);
if ($bbbResponse !== false) {
    $bbbData = json_decode($bbbResponse, true);
    echo "   ✅ BBB API working - Found " . count($bbbData) . " items\n";
    if (!empty($bbbData)) {
        echo "   📋 Sample BBB item:\n";
        $sample = $bbbData[0];
        echo "      - Name: " . ($sample['nama_bahan'] ?? 'N/A') . "\n";
        echo "      - Subtotal: Rp " . number_format($sample['subtotal'] ?? 0, 0, ',', '.') . "\n";
    }
} else {
    echo "   ❌ BBB API failed\n";
}

echo "\n2. Testing BTKL API...\n";
$btklUrl = "$baseUrl/api/get-available-btkl/2";
$btklResponse = @file_get_contents($btklUrl);
if ($btklResponse !== false) {
    $btklData = json_decode($btklResponse, true);
    echo "   ✅ BTKL API working - Found " . count($btklData) . " items\n";
    if (!empty($btklData)) {
        echo "   📋 Sample BTKL item:\n";
        $sample = $btklData[0];
        echo "      - Name: " . ($sample['nama_proses'] ?? 'N/A') . "\n";
        echo "      - Cost per product: Rp " . number_format($sample['biaya_per_produk'] ?? 0, 0, ',', '.') . "\n";
    }
} else {
    echo "   ❌ BTKL API failed\n";
}

echo "\n3. Testing BOP API...\n";
$bopUrl = "$baseUrl/api/get-available-bop";
$bopResponse = @file_get_contents($bopUrl);
if ($bopResponse !== false) {
    $bopData = json_decode($bopResponse, true);
    echo "   ✅ BOP API working - Found " . count($bopData) . " items\n";
    if (!empty($bopData)) {
        echo "   📋 Sample BOP item:\n";
        $sample = $bopData[0];
        echo "      - Name: " . ($sample['nama_bop'] ?? 'N/A') . "\n";
        echo "      - Tariff: Rp " . number_format($sample['tarif'] ?? 0, 0, ',', '.') . "\n";
    }
} else {
    echo "   ❌ BOP API failed\n";
}

echo "\n🎯 Key Improvements Implemented:\n";
echo "================================\n";
echo "✅ BBB Auto-Selection: All biaya bahan baku automatically included\n";
echo "✅ Product Identification: Shows which product the BBB belongs to\n";
echo "✅ Improved Design: Full-width cards with gradient backgrounds\n";
echo "✅ White Badge Text: All badge text is now white and centered\n";
echo "✅ Dynamic Updates: BBB section updates when product changes\n";
echo "✅ Hidden Inputs: Uses hidden inputs instead of checkboxes for BBB\n";
echo "✅ Proper Calculations: Fixed updateTotals() for automatic BBB inclusion\n\n";

echo "🌐 Test URLs:\n";
echo "============\n";
echo "Main Form: $baseUrl/master-data/harga-pokok-produksi/create\n";
echo "BBB API: $baseUrl/api/get-available-bbb/2\n";
echo "BTKL API: $baseUrl/api/get-available-btkl/2\n";
echo "BOP API: $baseUrl/api/get-available-bop\n\n";

echo "📝 Testing Instructions:\n";
echo "=======================\n";
echo "1. Open the main form URL in browser\n";
echo "2. Select 'Jasuke' from product dropdown\n";
echo "3. Verify BBB section shows automatically with product name\n";
echo "4. Check that badge text is white and centered\n";
echo "5. Select some BTKL and BOP items\n";
echo "6. Verify totals update correctly in summary section\n";
echo "7. Submit form to test data saving\n\n";

?>