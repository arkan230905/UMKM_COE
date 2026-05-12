<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG HPP SAVE REDIRECT ISSUE ===\n\n";

echo "1. CEK LARAVEL LOGS:\n\n";

try {
    $logFile = 'c:\UMKM_COE\storage\logs\laravel.log';
    
    if (file_exists($logFile)) {
        echo "✅ Laravel log file found\n";
        
        // Read last 50 lines of log
        $lines = file($logFile);
        $lastLines = array_slice($lines, -50);
        
        echo "Last 50 lines of Laravel log:\n";
        foreach ($lastLines as $line) {
            echo trim($line) . "\n";
        }
    } else {
        echo "❌ Laravel log file not found at: " . $logFile . "\n";
        echo "Checking alternative log locations...\n";
        
        $possibleLogFiles = [
            'c:\UMKM_COE\storage\logs\app.log',
            'c:\UMKM_COE\storage\logs\error.log',
            'c:\UMKM_COE\storage\logs\debug.log'
        ];
        
        foreach ($possibleLogFiles as $log) {
            if (file_exists($log)) {
                echo "✅ Found log file: " . $log . "\n";
                $lines = file($log);
                $lastLines = array_slice($lines, -20);
                foreach ($lastLines as $line) {
                    echo trim($line) . "\n";
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking logs: " . $e->getMessage() . "\n";
}

echo "\n2. CEK CURRENT DATA DI bom_job_costings:\n\n";

try {
    $jobCostings = \App\Models\BomJobCosting::all();
    
    echo "Data saat ini di bom_job_costings:\n";
    foreach ($jobCostings as $jc) {
        echo "ID: " . $jc->id . "\n";
        echo "Produk ID: " . $jc->produk_id . "\n";
        echo "User ID: " . $jc->user_id . "\n";
        echo "Total BBB: " . $jc->total_bbb . "\n";
        echo "Total BTKL: " . $jc->total_btkl . "\n";
        echo "Total BOP: " . $jc->total_bop . "\n";
        echo "Total HPP: " . $jc->total_hpp . "\n";
        echo "HPP per unit: " . $jc->hpp_per_unit . "\n";
        echo "Updated: " . $jc->updated_at . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking job costings: " . $e->getMessage() . "\n";
}

echo "\n3. CEK CURRENT DATA DI produks:\n\n";

try {
    $produks = \App\Models\Produk::all();
    
    echo "Data saat ini di produks:\n";
    foreach ($produks as $produk) {
        echo "ID: " . $produk->id . "\n";
        echo "Nama: " . $produk->nama_produk . "\n";
        echo "User ID: " . $produk->user_id . "\n";
        echo "Harga Pokok: " . $produk->harga_pokok . "\n";
        echo "Updated: " . $produk->updated_at . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking products: " . $e->getMessage() . "\n";
}

echo "\n4. SIMULASI PENYIMPANAN DENGAN ERROR HANDLING:\n\n";

try {
    echo "Simulating BomController@store with error handling:\n";
    
    // Simulate the exact process
    $validated = [
        'produk_id' => 2,
        'proses_ids' => [1],
        'biaya_bahan' => 2500,
        'total_btkl' => 166.67,
        'total_bop' => 95,
        'total_hpp' => 2761.67
    ];
    
    echo "Step 1: Start transaction\n";
    DB::beginTransaction();
    echo "✅ Transaction started\n";
    
    echo "Step 2: Get BomJobCosting\n";
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $validated['produk_id'])
        ->where('user_id', 1)
        ->first();
    
    if (!$bomJobCosting) {
        echo "❌ BomJobCosting not found - would return error\n";
        DB::rollBack();
        return;
    }
    
    echo "✅ Found BomJobCosting ID: " . $bomJobCosting->id . "\n";
    
    echo "Step 3: Calculate totals\n";
    $totalBtkl = 0;
    $totalBop = 0;
    
    foreach ($validated['proses_ids'] as $prosesId) {
        $proses = \App\Models\ProsesProduksi::with(['jabatan', 'bopProses'])->find($prosesId);
        
        if (!$proses) continue;
        
        // Calculate BTKL
        $jumlahPegawai = \App\Models\Pegawai::where('user_id', 1)
            ->where(function($q) use ($proses) {
                $q->where('jabatan_id', $proses->jabatan->id)
                  ->orWhere('jabatan', $proses->jabatan->nama);
            })->count();
        $tarifPerJamJabatan = $proses->jabatan->tarif_per_jam ?? $proses->jabatan->tarif ?? 0;
        
        $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
        $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
        
        $totalBtkl += $btklPerProduk;
        
        // Add BOP if exists
        if ($proses->bopProses) {
            $bopPerProduk = $proses->bopProses->bop_per_unit ?? 0;
            $totalBop += $bopPerProduk;
        }
    }
    
    echo "✅ Calculated totals:\n";
    echo "  Total BTKL: " . $totalBtkl . "\n";
    echo "  Total BOP: " . $totalBop . "\n";
    
    echo "Step 4: Update BomJobCosting\n";
    $bomJobCosting->total_btkl = $totalBtkl;
    $bomJobCosting->total_bop = $totalBop;
    $bomJobCosting->total_hpp = $validated['total_hpp'];
    $bomJobCosting->hpp_per_unit = $validated['total_hpp'];
    
    try {
        $bomJobCosting->save();
        echo "✅ BomJobCosting saved\n";
    } catch (\Exception $e) {
        echo "❌ Error saving BomJobCosting: " . $e->getMessage() . "\n";
        DB::rollBack();
        return;
    }
    
    echo "Step 5: Update product\n";
    $produk = \App\Models\Produk::find($validated['produk_id']);
    
    if (!$produk) {
        echo "❌ Product not found\n";
        DB::rollBack();
        return;
    }
    
    try {
        $produk->harga_pokok = $validated['total_hpp'];
        $produk->save();
        echo "✅ Product saved\n";
    } catch (\Exception $e) {
        echo "❌ Error saving product: " . $e->getMessage() . "\n";
        DB::rollBack();
        return;
    }
    
    echo "Step 6: Commit transaction\n";
    DB::commit();
    echo "✅ Transaction committed\n";
    
    echo "✅ Simulation completed successfully\n";
    
} catch (\Exception $e) {
    echo "❌ Simulation failed: " . $e->getMessage() . "\n";
    DB::rollBack();
}

echo "\n5. CEK POTENTIAL MASALAH LAIN:\n\n";

try {
    echo "Potential issues that could cause redirect without save:\n";
    echo "1. ❌ Exception in BomJobCosting->save()\n";
    echo "2. ❌ Exception in Produk->save()\n";
    echo "3. ❌ Validation failure (but redirect happens)\n";
    echo "4. ❌ Missing required fields\n";
    echo "5. ❌ Foreign key constraints\n";
    echo "6. ❌ Data type mismatch\n";
    echo "7. ❌ Database connection issues\n\n";
    
    // Check data types
    echo "Checking data types:\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_costings');
    echo "bom_job_costings columns:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nproduks columns:\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produks');
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking potential issues: " . $e->getMessage() . "\n";
}

echo "\n6. RECOMMENDATIONS:\n\n";

echo "Based on analysis:\n";
echo "1. Check Laravel logs for specific error messages\n";
echo "2. Verify all required fields are present\n";
echo "3. Check data types match database schema\n";
echo "4. Test with smaller data values\n";
echo "5. Add more detailed error logging\n\n";

echo "=== DEBUG COMPLETE ===\n";
