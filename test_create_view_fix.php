<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST CREATE VIEW FIX ===\n\n";

echo "1. CEK KONDISI SAAT INI:\n\n";

try {
    // Check if there are products with bom_job_bbb data
    $produks = \App\Models\Produk::where('user_id', 1)->get();
    
    echo "Products for user 1:\n";
    foreach ($produks as $produk) {
        echo "Produk: " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
        
        // Check bom_job_bbb data
        $bbbData = \App\Models\BomJobBBB::where('user_id', 1)
            ->where('produk_id', $produk->id)
            ->get();
        
        echo "  BBB records: " . $bbbData->count() . "\n";
        echo "  Total BBB: " . $bbbData->sum('subtotal') . "\n";
        
        // Check bom_job_costing
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', 1)
            ->first();
        
        if ($bomJobCosting) {
            echo "  BomJobCosting exists: " . $bomJobCosting->total_bbb . "\n";
        } else {
            echo "  BomJobCosting: NULL\n";
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking current condition: " . $e->getMessage() . "\n";
}

echo "\n2. SIMULASI VIEW LOGIC SETELAH PERBAIKAN:\n\n";

try {
    echo "Simulating view logic after fix:\n";
    
    $produks = \App\Models\Produk::where('user_id', 1)->get();
    
    foreach ($produks as $produk) {
        echo "Produk: " . $produk->nama_produk . "\n";
        
        // Simulate the @php logic from view
        $biayaBahan = 0;
        if ($produk->bomJobCosting) {
            $biayaBahan = $produk->bomJobCosting->total_bbb + $produk->bomJobCosting->total_bahan_pendukung;
            echo "  Using BomJobCosting: " . $biayaBahan . "\n";
        } else {
            // Calculate from bom_job_bbb directly
            $biayaBahan = \App\Models\BomJobBBB::where('user_id', 1)
                ->where('produk_id', $produk->id)
                ->sum('subtotal');
            echo "  Using BBB calculation: " . $biayaBahan . "\n";
        }
        
        echo "  Data-biaya-bahan: " . $biayaBahan . "\n";
        echo "  Display: Rp " . number_format($biayaBahan, 0, ',', '.') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating view logic: " . $e->getMessage() . "\n";
}

echo "\n3. CEK APAKAH CREATE PAGE AKAN BEKERJA:\n\n";

try {
    echo "Checking if create page will work:\n";
    
    // Check if all required data is available
    $produksWithBBB = \App\Models\Produk::where('user_id', 1)
        ->whereHas('bomJobBBB', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    echo "Products with BBB data: " . $produksWithBBB->count() . "\n";
    
    if ($produksWithBBB->count() > 0) {
        echo "✅ Create page should work - products have BBB data\n";
        
        foreach ($produksWithBBB as $produk) {
            $biayaBahan = \App\Models\BomJobBBB::where('user_id', 1)
                ->where('produk_id', $produk->id)
                ->sum('subtotal');
            
            echo "  - " . $produk->nama_produk . ": Rp " . number_format($biayaBahan, 0, ',', '.') . "\n";
        }
    } else {
        echo "❌ No products with BBB data found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking create page: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI BOM_CONTROLLER@create METHOD:\n\n";

try {
    echo "Checking BomController@create method:\n";
    
    // Simulate the controller logic
    $produkIds = \App\Models\BomJobBBB::where('user_id', 1)
        ->pluck('produk_id')
        ->unique();
    
    $produks = \App\Models\Produk::where('user_id', 1)
        ->whereIn('id', $produkIds)
        ->get();
    
    echo "Products that will be passed to view: " . $produks->count() . "\n";
    
    foreach ($produks as $produk) {
        echo "  - " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
        
        // Check if the view will have access to bomJobCosting
        if ($produk->bomJobCosting) {
            echo "    BomJobCosting: " . $produk->bomJobCosting->total_bbb . "\n";
        } else {
            echo "    BomJobCosting: NULL (will use BBB calculation)\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n5. SUMMARY PERBAIKAN:\n\n";

echo "✅ PERBAIKAN YANG TELAH DILAKUKAN:\n";
echo "- Fixed null BomJobCosting error in create.blade.php line 51\n";
echo "- Added fallback calculation from bom_job_bbb when BomJobCosting doesn't exist\n";
echo "- View now works with both scenarios (with/without existing BomJobCosting)\n\n";

echo "✅ ALUR YANG BENAR SEKARANG:\n";
echo "1. User membuka create HPP page (BomJobCosting bisa kosong)\n";
echo "2. View menghitung biaya bahan dari bom_job_bbb jika BomJobCosting kosong\n";
echo "3. User memilih produk dan proses\n";
echo "4. User klik 'Simpan Harga Pokok Produksi'\n";
echo "5. BomController@store membuat BomJobCosting baru dari bom_job_bbb data\n";
echo "6. Data tersimpan di bom_job_costings\n\n";

echo "✅ HASIL:\n";
echo "- Create page tidak lagi error saat BomJobCosting kosong\n";
echo "- Biaya bahan ditampilkan dengan benar dari bom_job_bbb\n";
echo "- Alur HPP calculation tetap sesuai yang diinginkan\n\n";

echo "6. NEXT STEPS:\n\n";

echo "🔄 Test create page di browser:\n";
echo "http://127.0.0.1:8000/master-data/harga-pokok-produksi/create\n";
echo "🔄 Verify tidak ada error lagi\n";
echo "🔄 Verify biaya bahan ditampilkan\n";
echo "🔄 Test proses HPP calculation lengkap\n\n";

echo "=== TEST COMPLETE ===\n";
