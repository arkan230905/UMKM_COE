<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEEP DEBUG BIAYA BAHAN ===\n\n";

echo "1. CHECK ACTUAL CONTROLLER CODE:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Check if our fixes are actually there
    echo "Checking for our fixes:\n";
    
    if (strpos($controllerContent, '$produkBiaya[] = [') !== false) {
        echo "✅ Indexed array assignment found\n";
    } else {
        echo "❌ Indexed array assignment NOT found\n";
    }
    
    if (strpos($controllerContent, "'produk' => \$produk,") !== false) {
        echo "✅ Product field assignment found\n";
    } else {
        echo "❌ Product field assignment NOT found\n";
    }
    
    if (strpos($controllerContent, 'bom_job_bbb as bbb') !== false) {
        echo "✅ Direct BBB query found\n";
    } else {
        echo "❌ Direct BBB query NOT found\n";
    }
    
    if (strpos($controllerContent, 'master-data.biaya-bahan.index') !== false) {
        echo "✅ Correct view name found\n";
    } else {
        echo "❌ Correct view name NOT found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n2. CHECK VIEW FILE STRUCTURE:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\biaya-bahan\index.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        echo "Checking view structure:\n";
        
        // Check what the view actually expects
        if (strpos($viewContent, '@foreach ($produkBiaya as $index => $data)') !== false) {
            echo "✅ View uses indexed array with index\n";
        } elseif (strpos($viewContent, '@foreach ($produkBiaya as $data)') !== false) {
            echo "✅ View uses indexed array without index\n";
        } else {
            echo "❌ View foreach pattern not found\n";
        }
        
        // Check how view accesses data
        if (strpos($viewContent, '$data[\'produk\']') !== false) {
            echo "✅ View accesses \$data['produk']\n";
        } elseif (strpos($viewContent, '$data->produk') !== false) {
            echo "✅ View accesses \$data->produk\n";
        } else {
            echo "❌ View does not access product correctly\n";
        }
        
        // Check total field access
        if (strpos($viewContent, 'total_biaya_bahan') !== false) {
            echo "✅ View accesses total_biaya_bahan\n";
        } else {
            echo "❌ View does NOT access total_biaya_bahan\n";
        }
        
        // Check if view shows 0 by default
        if (strpos($viewContent, 'Rp 0') !== false) {
            echo "⚠️  View has hardcoded Rp 0 values\n";
        }
        
    } else {
        echo "❌ View file not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n3. STEP BY STEP CONTROLLER EXECUTION:\n\n";

try {
    echo "Simulating exact BiayaBahanController@index execution...\n";
    
    // Step 1: Get products
    $user = (object)['id' => 1];
    $query = \App\Models\Produk::query()->where('user_id', $user->id);
    $produks = $query->orderBy('nama_produk')->get();
    
    echo "Step 1 - Products found: " . $produks->count() . "\n";
    
    // Step 2: Initialize produkBiaya
    $produkBiaya = [];
    echo "Step 2 - Initialized empty produkBiaya array\n";
    
    // Step 3: Process each product
    foreach ($produks as $index => $produk) {
        echo "\nStep 3." . ($index + 1) . " - Processing: " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
        
        // Get BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', $user->id)
            ->first();
        
        echo "  BomJobCosting: " . ($bomJobCosting ? "Found (ID: " . $bomJobCosting->id . ")" : "Not found") . "\n";
        
        // Get BBB data
        $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb as bbb')
            ->leftJoin('bahan_bakus as bb', 'bbb.bahan_baku_id', '=', 'bb.id')
            ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
            ->where('bbb.user_id', $user->id)
            ->where('bbb.produk_id', $produk->id)
            ->select(
                'bbb.id',
                'bb.nama_bahan',
                'bbb.jumlah as qty',
                'bbb.satuan',
                'bbb.harga_satuan',
                'bbb.subtotal',
                's.nama as satuan_nama'
            )
            ->get();
        
        echo "  BBB records: " . $bbbData->count() . "\n";
        
        if ($bbbData->count() > 0) {
            foreach ($bbbData as $bbb) {
                echo "    - " . $bbb->nama_bahan . ": " . $bbb->subtotal . "\n";
            }
        }
        
        $totalBiayaBahanBaku = $bbbData->sum('subtotal');
        echo "  Total BBB: " . $totalBiayaBahanBaku . "\n";
        
        // Step 4: Check condition for adding to produkBiaya
        if ($bbbData->count() > 0 || $bomJobCosting) {
            echo "  ✅ Condition met - adding to produkBiaya\n";
            
            $detailBahanBaku = $bbbData->map(function($detail) {
                return [
                    'nama_bahan' => $detail->nama_bahan ?? 'Unknown',
                    'qty' => $detail->qty ?? 0,
                    'satuan' => $detail->satuan_nama ?? $detail->satuan ?? 'unit',
                    'harga_satuan' => $detail->harga_satuan ?? 0,
                    'subtotal' => $detail->subtotal ?? 0,
                    'tipe' => 'Bahan Baku',
                    'status' => 'aktif'
                ];
            })->toArray() ?? [];
            
            $totalBiayaBahanPendukung = 0;
            $detailBahanPendukung = [];
            $totalBiayaBahan = $totalBiayaBahanBaku;
            $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
            
            // This is where we add to produkBiaya
            $produkBiaya[] = [
                'produk' => $produk,
                'total_biaya' => $totalBiayaBahan,
                'total_biaya_bahan_baku' => $totalBiayaBahanBaku,
                'total_biaya_bahan_pendukung' => $totalBiayaBahanPendukung,
                'detail_bahan' => $allDetails,
                'detail_bahan_baku' => $detailBahanBaku,
                'detail_bahan_pendukung' => $detailBahanPendukung,
                'total_biaya_bahan' => $totalBiayaBahan,
                'bom_job_costing' => $bomJobCosting
            ];
            
            echo "  ✅ Added to produkBiaya array\n";
            echo "  Entry count now: " . count($produkBiaya) . "\n";
            
        } else {
            echo "  ❌ Condition NOT met - NOT adding to produkBiaya\n";
        }
    }
    
    echo "\nStep 5 - Final produkBiaya array:\n";
    echo "Total entries: " . count($produkBiaya) . "\n";
    
    foreach ($produkBiaya as $index => $entry) {
        echo "Entry " . ($index + 1) . ":\n";
        echo "  Produk: " . $entry['produk']->nama_produk . "\n";
        echo "  Total Biaya Bahan: " . $entry['total_biaya_bahan'] . "\n";
        echo "  Total Biaya Bahan Baku: " . $entry['total_biaya_bahan_baku'] . "\n";
        echo "  Detail Count: " . count($entry['detail_bahan_baku']) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error in step-by-step execution: " . $e->getMessage() . "\n";
}

echo "\n4. CHECK WHAT VIEW RECEIVES:\n\n";

try {
    echo "Variables passed to view:\n";
    echo "- \$produks: " . (isset($produks) ? $produks->count() . " products" : "not set") . "\n";
    echo "- \$produkBiaya: " . (isset($produkBiaya) ? count($produkBiaya) . " entries" : "not set") . "\n";
    
    if (isset($produkBiaya) && count($produkBiaya) > 0) {
        $firstEntry = $produkBiaya[0];
        echo "\nFirst entry structure:\n";
        foreach ($firstEntry as $key => $value) {
            if (is_object($value)) {
                echo "  $key: Object (" . get_class($value) . ")\n";
            } elseif (is_array($value)) {
                echo "  $key: Array (" . count($value) . " items)\n";
            } else {
                echo "  $key: $value\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking view variables: " . $e->getMessage() . "\n";
}

echo "\n5. IDENTIFY THE ACTUAL PROBLEM:\n\n";

echo "Based on deep debug:\n";
echo "1. Controller fixes status: " . (strpos(file_get_contents('c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php'), '$produkBiaya[] = [') !== false ? "✅ Applied" : "❌ Not applied") . "\n";
echo "2. Data availability: " . (\Illuminate\Support\Facades\DB::table('bom_job_bbb')->where('user_id', 1)->count() > 0 ? "✅ Available" : "❌ Not available") . "\n";
echo "3. Logic execution: " . (isset($produkBiaya) && count($produkBiaya) > 0 ? "✅ Working" : "❌ Not working") . "\n";

echo "\n=== DEEP DEBUG COMPLETE ===\n";
