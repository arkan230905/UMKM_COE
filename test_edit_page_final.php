<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST EDIT PAGE FINAL ===\n\n";

echo "1. CEK KONDISI EDIT PAGE:\n\n";

try {
    // Check if the product exists
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "✅ Product found: " . $produk->nama_produk . "\n";
        
        // Check BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', 1)
            ->first();
        
        if ($bomJobCosting) {
            echo "✅ BomJobCosting exists: " . $bomJobCosting->total_bbb . "\n";
        } else {
            echo "✅ BomJobCosting: NULL (will use BBB fallback)\n";
        }
        
        // Check BBB data
        $bbbData = \App\Models\BomJobBBB::where('user_id', 1)
            ->where('produk_id', $produk->id)
            ->get();
        
        echo "✅ BBB records: " . $bbbData->count() . " (Total: " . $bbbData->sum('subtotal') . ")\n";
        
    } else {
        echo "❌ Product not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking edit page condition: " . $e->getMessage() . "\n";
}

echo "\n2. CEK BOP DATA UNTUK EDIT PAGE:\n\n";

try {
    // Check if BOP data exists for edit page
    $bopProsesCount = \App\Models\BopProses::count();
    
    if ($bopProsesCount > 0) {
        echo "✅ BOP data exists: " . $bopProsesCount . " records\n";
        
        $bopRecords = \App\Models\BopProses::all();
        foreach ($bopRecords as $bop) {
            $process = \App\Models\ProsesProduksi::find($bop->proses_produksi_id);
            echo "  - " . ($process->nama_proses ?? 'Unknown') . ": " . $bop->bop_per_unit . "\n";
            
            if ($bop->komponen_bop) {
                $komponen = is_array($bop->komponen_bop) ? $bop->komponen_bop : json_decode($bop->komponen_bop, true);
                if (is_array($komponen)) {
                    foreach ($komponen as $k) {
                        echo "    * " . ($k['component'] ?? 'N/A') . ": " . ($k['rate_per_produk'] ?? 0) . "\n";
                    }
                }
            }
        }
    } else {
        echo "❌ No BOP data found - BOP components will show Rp 0\n";
        echo "   Need to restore BOP data for edit page to work properly\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking BOP data: " . $e->getMessage() . "\n";
}

echo "\n3. SIMULASI EDIT PAGE RENDERING:\n\n";

try {
    echo "Simulating edit page rendering:\n";
    
    // Simulate BomController@edit
    $produk = \App\Models\Produk::find(2);
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)
        ->where('user_id', 1)
        ->first();
    
    // Calculate biaya bahan (same logic as view)
    $biayaBahan = 0;
    if ($bomJobCosting) {
        $biayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
        echo "  Biaya Bahan (from BomJobCosting): " . $biayaBahan . "\n";
    } else {
        // Calculate from bom_job_bbb directly
        $biayaBahan = \App\Models\BomJobBBB::where('user_id', 1)
            ->where('produk_id', 2)
            ->sum('subtotal');
        echo "  Biaya Bahan (from BBB): " . $biayaBahan . "\n";
    }
    
    echo "  Display: Rp " . number_format($biayaBahan, 0, ',', '.') . "\n";
    
    // Get processes
    $prosesBtkl = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->with(['jabatan', 'bopProses'])
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    echo "  Processes available: " . $prosesBtkl->count() . "\n";
    
    foreach ($prosesBtkl as $proses) {
        echo "    - " . $proses->nama_proses . "\n";
        
        if ($proses->bopProses) {
            echo "      BOP: " . $proses->bopProses->bop_per_unit . "\n";
            
            if ($proses->bopProses->komponen_bop) {
                $komponen = is_array($proses->bopProses->komponen_bop) ? $proses->bopProses->komponen_bop : json_decode($proses->bopProses->komponen_bop, true);
                if (is_array($komponen)) {
                    foreach ($komponen as $k) {
                        echo "        * " . ($k['component'] ?? 'N/A') . ": " . ($k['rate_per_produk'] ?? 0) . "\n";
                    }
                }
            }
        } else {
            echo "      BOP: None\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error simulating edit page: " . $e->getMessage() . "\n";
}

echo "\n4. CEK POTENTIAL MASALAH:\n\n";

try {
    echo "Checking potential issues:\n";
    
    // Check if BOP data is missing
    $bopProsesCount = \App\Models\BopProses::count();
    if ($bopProsesCount == 0) {
        echo "❌ BOP data missing - components will show Rp 0\n";
        echo "   Solution: Restore BOP data from existing records or create new\n";
    }
    
    // Check if BomJobBTKL exists for pre-selection
    $bomJobBtklCount = \App\Models\BomJobBTKL::count();
    if ($bomJobBtklCount == 0) {
        echo "❌ No BomJobBTKL records - no pre-selection in edit\n";
        echo "   This is normal for new HPP calculations\n";
    }
    
    // Check if form submission will work
    $produk = \App\Models\Produk::find(2);
    if ($produk) {
        echo "✅ Product exists for form submission\n";
    } else {
        echo "❌ Product missing - form submission will fail\n";
    }
    
    echo "\nRecommendations:\n";
    if ($bopProsesCount == 0) {
        echo "1. Restore BOP data first\n";
        echo "2. Test edit page after BOP restoration\n";
    } else {
        echo "1. Edit page should work correctly\n";
        echo "2. Test all functionality\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking issues: " . $e->getMessage() . "\n";
}

echo "\n5. FINAL STATUS:\n\n";

try {
    echo "Edit Page Status:\n";
    
    // Check all components
    $createViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    
    $createViewContent = file_get_contents($createViewFile);
    $editViewContent = file_get_contents($editViewFile);
    
    echo "✅ Structure consistency: " . (abs(strlen($createViewContent) - strlen($editViewContent)) < 5000 ? "GOOD" : "NEEDS WORK") . "\n";
    echo "✅ Biaya bahan logic: " . (strpos($editViewContent, 'BomJobBBB::where') !== false ? "FIXED" : "NEEDS FIX") . "\n";
    echo "✅ BOP JavaScript: " . (strpos($editViewContent, 'Use rate_per_produk directly') !== false ? "FIXED" : "NEEDS FIX") . "\n";
    echo "✅ Form method: " . (strpos($editViewContent, '@method(\'PUT\')') !== false ? "CORRECT" : "NEEDS FIX") . "\n";
    
    echo "\nOverall Status: ";
    
    $issues = [];
    if (\App\Models\BopProses::count() == 0) $issues[] = "BOP data missing";
    if (strpos($editViewContent, 'BomJobBBB::where') === false) $issues[] = "Biaya bahan logic";
    if (strpos($editViewContent, 'Use rate_per_produk directly') === false) $issues[] = "BOP JavaScript";
    
    if (empty($issues)) {
        echo "✅ READY FOR TESTING\n";
    } else {
        echo "⚠️ NEEDS ATTENTION: " . implode(", ", $issues) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking final status: " . $e->getMessage() . "\n";
}

echo "\n=== FINAL TEST COMPLETE ===\n";
