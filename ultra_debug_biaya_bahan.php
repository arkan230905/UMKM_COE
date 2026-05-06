<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ULTRA DEBUG BIAYA BAHAN ===\n\n";

echo "1. VERIFY DATABASE DATA EXACTLY:\n\n";

try {
    // Check exact data that should be found
    $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
        ->where('user_id', 1)
        ->where('produk_id', 2)
        ->get();
    
    echo "BBB Data for user_id=1, produk_id=2:\n";
    echo "Count: " . $bbbData->count() . "\n";
    
    if ($bbbData->count() > 0) {
        foreach ($bbbData as $bbb) {
            echo "  Record " . $bbb->id . ":\n";
            echo "    user_id: " . $bbb->user_id . "\n";
            echo "    produk_id: " . $bbb->produk_id . "\n";
            echo "    bahan_baku_id: " . $bbb->bahan_baku_id . "\n";
            echo "    jumlah: " . $bbb->jumlah . "\n";
            echo "    satuan: " . $bbb->satuan . "\n";
            echo "    harga_satuan: " . $bbb->harga_satuan . "\n";
            echo "    subtotal: " . $bbb->subtotal . "\n";
            echo "    created_at: " . $bbb->created_at . "\n";
        }
    } else {
        echo "❌ NO DATA FOUND - This is the problem!\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}

echo "\n2. TEST CONTROLLER QUERY STEP BY STEP:\n\n";

try {
    echo "Testing exact controller query...\n";
    
    // Step 1: Get products
    $produks = \App\Models\Produk::where('user_id', 1)->get();
    echo "Products for user_id=1: " . $produks->count() . "\n";
    
    foreach ($produks as $produk) {
        echo "  - " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
    }
    
    // Step 2: Test BBB query for each product
    foreach ($produks as $produk) {
        echo "\nTesting BBB query for product: " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
        
        // This is the EXACT query from controller
        $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb as bbb')
            ->leftJoin('bahan_bakus as bb', 'bbb.bahan_baku_id', '=', 'bb.id')
            ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
            ->where('bbb.user_id', 1)  // This should be auth()->id()
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
        
        echo "  BBB records found: " . $bbbData->count() . "\n";
        
        if ($bbbData->count() > 0) {
            foreach ($bbbData as $bbb) {
                echo "    - " . $bbb->nama_bahan . ": " . $bbb->subtotal . "\n";
            }
            
            $total = $bbbData->sum('subtotal');
            echo "  Total from query: " . $total . "\n";
        } else {
            echo "  ❌ NO RECORDS FOUND - This is the problem!\n";
            
            // Let's check why
            echo "  Debugging query conditions:\n";
            echo "    user_id condition: bbb.user_id = 1\n";
            echo "    produk_id condition: bbb.produk_id = " . $produk->id . "\n";
            
            // Check if data exists without user_id filter
            $bbbDataNoFilter = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
                ->where('produk_id', $produk->id)
                ->get();
            
            echo "    Records without user_id filter: " . $bbbDataNoFilter->count() . "\n";
            
            if ($bbbDataNoFilter->count() > 0) {
                foreach ($bbbDataNoFilter as $bbb) {
                    echo "      - Record " . $bbb->id . " has user_id: " . $bbb->user_id . "\n";
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error testing controller query: " . $e->getMessage() . "\n";
}

echo "\n3. SIMULATE FULL CONTROLLER LOGIC:\n\n";

try {
    echo "Simulating full BiayaBahanController@index...\n";
    
    // Simulate logged in user
    $user = (object)['id' => 1];
    
    // Step 1: Get products
    $query = \App\Models\Produk::query()->where('user_id', $user->id);
    $produks = $query->orderBy('nama_produk')->get();
    
    echo "Step 1 - Products: " . $produks->count() . "\n";
    
    // Step 2: Initialize produkBiaya
    $produkBiaya = [];
    echo "Step 2 - Initialized produkBiaya: " . count($produkBiaya) . " entries\n";
    
    // Step 3: Process each product
    foreach ($produks as $index => $produk) {
        echo "\nStep 3." . ($index + 1) . " - Processing: " . $produk->nama_produk . "\n";
        
        // Get BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', $user->id)
            ->first();
        
        echo "  BomJobCosting: " . ($bomJobCosting ? "Found" : "Not found") . "\n";
        
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
        
        echo "  BBB query result: " . $bbbData->count() . " records\n";
        
        // Check the condition
        $conditionMet = ($bbbData->count() > 0 || $bomJobCosting);
        echo "  Condition (BBB > 0 || BomJobCosting): " . ($conditionMet ? "TRUE" : "FALSE") . "\n";
        
        if ($conditionMet) {
            echo "  ✅ Processing data...\n";
            
            $totalBiayaBahanBaku = $bbbData->sum('subtotal') ?? 0;
            echo "    Total BBB: " . $totalBiayaBahanBaku . "\n";
            
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
            
            echo "    Detail count: " . count($detailBahanBaku) . "\n";
            
            // Add to produkBiaya
            $produkBiaya[] = [
                'produk' => $produk,
                'total_biaya' => $totalBiayaBahanBaku,
                'total_biaya_bahan_baku' => $totalBiayaBahanBaku,
                'total_biaya_bahan_pendukung' => 0,
                'detail_bahan' => $detailBahanBaku,
                'detail_bahan_baku' => $detailBahanBaku,
                'detail_bahan_pendukung' => [],
                'total_biaya_bahan' => $totalBiayaBahanBaku,
                'bom_job_costing' => $bomJobCosting
            ];
            
            echo "  ✅ Added to produkBiaya. Total entries: " . count($produkBiaya) . "\n";
            
        } else {
            echo "  ❌ Adding empty entry...\n";
            
            // Add empty entry
            $produkBiaya[] = [
                'produk' => $produk,
                'total_biaya' => 0,
                'total_biaya_bahan_baku' => 0,
                'total_biaya_bahan_pendukung' => 0,
                'detail_bahan' => [],
                'detail_bahan_baku' => [],
                'detail_bahan_pendukung' => [],
                'total_biaya_bahan' => 0,
                'bom_job_costing' => null
            ];
            
            echo "  ❌ Added empty entry. Total entries: " . count($produkBiaya) . "\n";
        }
    }
    
    echo "\nStep 4 - Final Results:\n";
    echo "Total produkBiaya entries: " . count($produkBiaya) . "\n";
    
    foreach ($produkBiaya as $index => $entry) {
        echo "\nEntry " . ($index + 1) . ":\n";
        echo "  Produk: " . $entry['produk']->nama_produk . "\n";
        echo "  Total Biaya Bahan: " . $entry['total_biaya_bahan'] . "\n";
        echo "  Total Biaya Bahan Baku: " . $entry['total_biaya_bahan_baku'] . "\n";
        echo "  Detail Count: " . count($entry['detail_bahan_baku']) . "\n";
        
        if (count($entry['detail_bahan_baku']) > 0) {
            foreach ($entry['detail_bahan_baku'] as $detail) {
                echo "    - " . $detail['nama_bahan'] . ": " . $detail['subtotal'] . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error simulating controller: " . $e->getMessage() . "\n";
}

echo "\n4. CHECK WHAT VIEW WILL RECEIVE:\n\n";

try {
    if (isset($produkBiaya)) {
        echo "Controller will send to view:\n";
        echo "- \$produks: " . (isset($produks) ? $produks->count() . " products" : "not set") . "\n";
        echo "- \$produkBiaya: " . count($produkBiaya) . " entries\n";
        
        // Simulate view processing
        foreach ($produkBiaya as $index => $data) {
            echo "\nView will process entry " . ($index + 1) . ":\n";
            
            $produk = $data['produk'] ?? null;
            $biaya = $data;
            $totalBiayaBahanBaku = $biaya['total_biaya_bahan_baku'] ?? 0;
            
            $detailBahanBaku = $biaya['detail_bahan_baku'] ?? [];
            $jumlahBahanBaku = collect($detailBahanBaku)->filter(function($item) {
                return ($item['subtotal'] ?? 0) > 0;
            })->count();
            
            echo "  Produk: " . ($produk ? $produk->nama_produk : 'No product') . "\n";
            echo "  Total Biaya Bahan Baku: " . $totalBiayaBahanBaku . "\n";
            echo "  Jumlah Bahan Baku: " . $jumlahBahanBaku . " items\n";
            echo "  Status: " . ($jumlahBahanBaku > 0 ? "Valid" : "Kosong") . "\n";
            echo "  Display: " . ($jumlahBahanBaku . " item - Rp " . number_format($totalBiayaBahanBaku, 0, ',', '.')) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking view data: " . $e->getMessage() . "\n";
}

echo "\n5. IDENTIFY THE EXACT PROBLEM:\n\n";

echo "Based on ultra debug:\n";
echo "1. Database data exists: " . (\Illuminate\Support\Facades\DB::table('bom_job_bbb')->where('user_id', 1)->count() > 0 ? "✅" : "❌") . "\n";
echo "2. Controller query works: " . (isset($produkBiaya) && count($produkBiaya) > 0 ? "✅" : "❌") . "\n";
echo "3. Data flows to view: " . (isset($produkBiaya) ? "✅" : "❌") . "\n";

echo "\n=== ULTRA DEBUG COMPLETE ===\n";
