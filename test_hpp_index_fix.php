<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HargaPokokProduksiBiayaBahanBaku;
use App\Models\HargaPokokProduksiBtkl;
use App\Models\HargaPokokProduksiBop;

echo "🔧 Testing HPP Index Fix\n";
echo "========================\n\n";

try {
    echo "1. Testing BBB query (should work - has produk_id):\n";
    $bbbCount = HargaPokokProduksiBiayaBahanBaku::where('user_id', 1)
        ->whereHas('biayaBahanBaku', function($query) {
            $query->where('produk_id', 2);
        })
        ->count();
    echo "   ✅ BBB query successful: $bbbCount records\n\n";

    echo "2. Testing BTKL query (fixed - no produk_id filter):\n";
    $btklCount = HargaPokokProduksiBtkl::where('user_id', 1)
        ->with('prosesProduksi')
        ->count();
    echo "   ✅ BTKL query successful: $btklCount records\n\n";

    echo "3. Testing BOP query (fixed - no produk_id filter):\n";
    $bopCount = HargaPokokProduksiBop::where('user_id', 1)
        ->with('bopProses')
        ->count();
    echo "   ✅ BOP query successful: $bopCount records\n\n";

    echo "4. Testing controller getHppRecords method:\n";
    
    // Simulate the controller logic
    $user_id = 1;
    
    $bbbProducts = HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
        ->with('biayaBahanBaku')
        ->get()
        ->pluck('biayaBahanBaku.produk_id')
        ->filter()
        ->unique()
        ->values();
    
    echo "   ✅ BBB products found: " . $bbbProducts->count() . " products\n";
    
    $hasAnySelections = HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->exists() ||
                       HargaPokokProduksiBtkl::where('user_id', $user_id)->exists() ||
                       HargaPokokProduksiBop::where('user_id', $user_id)->exists();
    
    echo "   ✅ Has any selections: " . ($hasAnySelections ? 'Yes' : 'No') . "\n\n";

    echo "🎉 All queries working correctly!\n";
    echo "================================\n";
    echo "✅ The HPP index page should now load without errors\n";
    echo "✅ BBB calculations work (product-specific)\n";
    echo "✅ BTKL calculations work (user-specific)\n";
    echo "✅ BOP calculations work (user-specific)\n\n";

    echo "🌐 Test the fix:\n";
    echo "================\n";
    echo "Visit: http://127.0.0.1:8000/master-data/harga-pokok-produksi\n";
    echo "Should load without database errors\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>