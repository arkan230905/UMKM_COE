<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPREHENSIVE DEBUG BIAYA BAHAN ===\n\n";

echo "1. VERIFY DATABASE DATA EXISTENCE:\n\n";

try {
    $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb')
        ->where('user_id', 1)
        ->where('produk_id', 2)
        ->get();
    
    echo "BBB Data for user_id=1, produk_id=2:\n";
    echo "Count: " . $bbbData->count() . "\n";
    
    foreach ($bbbData as $bbb) {
        echo "  ID: " . $bbb->id . "\n";
        echo "  Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "  Jumlah: " . $bbb->jumlah . "\n";
        echo "  Satuan: " . $bbb->satuan . "\n";
        echo "  Harga Satuan: " . $bbb->harga_satuan . "\n";
        echo "  Subtotal: " . $bbb->subtotal . "\n";
        echo "  Created: " . $bbb->created_at . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}

echo "2. TEST EXACT CONTROLLER EXECUTION:\n\n";

try {
    echo "Executing BiayaBahanController@index logic exactly...\n";
    
    // Step 1: Get products (exact same as controller)
    $user = auth()->user();
    if (!$user) {
        // Simulate logged in user
        $user = (object)['id' => 1];
    }
    
    $query = \App\Models\Produk::query()->where('user_id', $user->id);
    
    // Apply filters (if any)
    // $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
    
    $produks = $query->orderBy('nama_produk')->get();
    
    echo "Products found: " . $produks->count() . "\n";
    
    // Step 2: Initialize produkBiaya (exact same as controller)
    $produkBiaya = [];
    
    // Step 3: Process each product (exact same as controller)
    foreach ($produks as $produk) {
        echo "\nProcessing product: " . $produk->nama_produk . " (ID: " . $produk->id . ")\n";
        
        // Get BomJobCosting (exact same as controller)
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', $user->id)
            ->first();
        
        echo "  BomJobCosting: " . ($bomJobCosting ? "Found (ID: " . $bomJobCosting->id . ")" : "Not found") . "\n";
        
        // Get BBB data (exact same as controller)
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
        
        // Check condition (exact same as controller)
        if ($bbbData->count() > 0 || $bomJobCosting) {
            echo "  ✅ Condition met - processing data\n";
            
            // Process BBB data (exact same as controller)
            $totalBiayaBahanBaku = $bbbData->sum('subtotal') ?? 0;
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
            
            echo "  Total BBB: " . $totalBiayaBahanBaku . "\n";
            echo "  Detail count: " . count($detailBahanBaku) . "\n";
            
            // Other data (exact same as controller)
            $totalBiayaBahanPendukung = 0;
            $detailBahanPendukung = [];
            $totalBiayaBahan = $totalBiayaBahanBaku;
            $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
            
            // Add to produkBiaya (exact same as controller)
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
            echo "  Entry count: " . count($produkBiaya) . "\n";
            
        } else {
            echo "  ❌ Condition NOT met - adding empty entry\n";
            
            // Add empty entry (exact same as controller)
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
            
            echo "  ❌ Added empty entry\n";
        }
    }
    
    echo "\nFinal results:\n";
    echo "Products: " . $produks->count() . "\n";
    echo "ProdukBiaya entries: " . count($produkBiaya) . "\n";
    
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
    echo "Error in controller execution: " . $e->getMessage() . "\n";
}

echo "\n3. CHECK VIEW FILE CURRENT STATE:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\biaya-bahan\index.blade.php';
    $viewContent = file_get_contents($viewFile);
    
    echo "Checking view file content...\n";
    
    // Check foreach pattern
    if (strpos($viewContent, '@forelse($produkBiaya as $data)') !== false) {
        echo "✅ View uses @forelse(\$produkBiaya as \$data)\n";
    } else {
        echo "❌ View does NOT use @forelse(\$produkBiaya as \$data)\n";
        
        // Check what it actually uses
        if (strpos($viewContent, '@forelse($produks as $produk)') !== false) {
            echo "  ⚠️  View still uses @forelse(\$produks as \$produk) - THIS IS THE PROBLEM!\n";
        }
    }
    
    // Check data extraction
    if (strpos($viewContent, '$produk = $data[\'produk\']') !== false) {
        echo "✅ View extracts product correctly\n";
    } else {
        echo "❌ View does NOT extract product correctly\n";
    }
    
    // Check biaya assignment
    if (strpos($viewContent, '$biaya = $data') !== false) {
        echo "✅ View assigns biaya correctly\n";
    } else {
        echo "❌ View does NOT assign biaya correctly\n";
    }
    
    // Check total access
    if (strpos($viewContent, 'total_biaya_bahan') !== false) {
        echo "✅ View accesses total_biaya_bahan\n";
    } else {
        echo "❌ View does NOT access total_biaya_bahan\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n4. SIMULATE EXACT VIEW PROCESSING:\n\n";

try {
    echo "Simulating how view will process the data...\n";
    
    // Use the same data from step 2
    if (isset($produkBiaya) && count($produkBiaya) > 0) {
        foreach ($produkBiaya as $index => $data) {
            echo "\nSimulating view processing for entry " . ($index + 1) . ":\n";
            
            // Simulate view logic
            $produk = $data['produk'] ?? null;
            $biaya = $data;
            $totalBiaya = $biaya['total_biaya'] ?? 0;
            $totalBiayaBahanBaku = $biaya['total_biaya_bahan_baku'] ?? 0;
            $totalBiayaBahanPendukung = $biaya['total_biaya_bahan_pendukung'] ?? 0;
            
            $detailBahanBaku = $biaya['detail_bahan_baku'] ?? [];
            $detailBahanPendukung = $biaya['detail_bahan_pendukung'] ?? [];
            
            $jumlahBahanBaku = collect($detailBahanBaku)->filter(function($item) {
                return ($item['subtotal'] ?? 0) > 0;
            })->count();
            
            echo "  Produk: " . ($produk ? $produk->nama_produk : 'No product') . "\n";
            echo "  Total Biaya: " . $totalBiaya . "\n";
            echo "  Total Biaya Bahan Baku: " . $totalBiayaBahanBaku . "\n";
            echo "  Jumlah Bahan Baku: " . $jumlahBahanBaku . " items\n";
            
            if ($jumlahBahanBaku > 0) {
                echo "  ✅ View will display: " . $jumlahBahanBaku . " item - Rp " . number_format($totalBiayaBahanBaku, 0, ',', '.') . "\n";
                echo "  ✅ Status will be: Valid\n";
            } else {
                echo "  ❌ View will display: 0 item - Rp 0\n";
                echo "  ❌ Status will be: Kosong\n";
            }
        }
    } else {
        echo "❌ No produkBiaya data to simulate\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating view: " . $e->getMessage() . "\n";
}

echo "\n5. IDENTIFY THE ACTUAL PROBLEM:\n\n";

echo "Based on comprehensive debug:\n";
echo "1. Database data: " . (\Illuminate\Support\Facades\DB::table('bom_job_bbb')->where('user_id', 1)->count() > 0 ? "✅ Available" : "❌ Not available") . "\n";
echo "2. Controller logic: " . (isset($produkBiaya) && count($produkBiaya) > 0 ? "✅ Working" : "❌ Not working") . "\n";
echo "3. View pattern: " . (strpos(file_get_contents('c:\UMKM_COE\resources\views\master-data\biaya-bahan\index.blade.php'), '@forelse($produkBiaya as $data)') !== false ? "✅ Fixed" : "❌ Still broken") . "\n";

echo "\n=== COMPREHENSIVE DEBUG COMPLETE ===\n";
