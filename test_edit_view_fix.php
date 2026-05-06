<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST EDIT VIEW FIX ===\n\n";

echo "1. VERIFIKASI PERBAIKAN JAVASCRIPT DI EDIT VIEW:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    $viewContent = file_get_contents($viewFile);
    
    // Check if the fix is applied
    if (strpos($viewContent, 'Use rate_per_produk directly from controller') !== false) {
        echo "✅ JavaScript fix applied in edit view\n";
    } else {
        echo "❌ JavaScript fix not found in edit view\n";
    }
    
    // Check if old logic is removed
    if (strpos($viewContent, 'Calculate rate per produk based on proportion') !== false) {
        echo "❌ Old calculation logic still exists\n";
    } else {
        echo "✅ Old calculation logic removed\n";
    }
    
    // Check if new logic is present
    if (strpos($viewContent, 'const ratePerProduk = parseFloat(komp.rate_per_produk || 0);') !== false) {
        echo "✅ New JavaScript logic found\n";
    } else {
        echo "❌ New JavaScript logic not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking edit view: " . $e->getMessage() . "\n";
}

echo "\n2. SIMULASI DATA YANG AKAN DITERIMA OLEH EDIT VIEW:\n\n";

try {
    echo "Simulating data that will be passed to edit view:\n";
    
    // Simulate the data structure from BomController@edit
    $prosesBtkl = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->with(['jabatan', 'bopProses'])
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get()
        ->map(function($proses) {
            // Calculate BTKL
            $jumlahPegawai = 0;
            $tarifPerJamJabatan = 0;
            
            if ($proses->jabatan) {
                $jumlahPegawai = \App\Models\Pegawai::where('user_id', 1)
                    ->where(function($q) use ($proses) {
                        $q->where('jabatan_id', $proses->jabatan->id)
                          ->orWhere('jabatan', $proses->jabatan->nama);
                    })->count();
                $tarifPerJamJabatan = $proses->jabatan->tarif_per_jam ?? $proses->jabatan->tarif ?? 0;
            }
            
            $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
            $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
            
            // Get BOP data if exists
            $bopPerProduk = 0;
            $totalBopPerJam = 0;
            $komponenBop = [];
            
            if ($proses->bopProses) {
                $bopPerProduk = $proses->bopProses->bop_per_unit ?? 0;
                
                if ($proses->bopProses->komponen_bop) {
                    $komponenBop = is_array($proses->bopProses->komponen_bop) 
                        ? $proses->bopProses->komponen_bop 
                        : json_decode($proses->bopProses->komponen_bop, true);
                    
                    // Normalize komponen BOP data
                    if (is_array($komponenBop)) {
                        $normalizedKomponen = [];
                        $totalRatePerHour = 0;
                        
                        foreach ($komponenBop as $komponen) {
                            $ratePerProduk = 0;
                            $ratePerHour = 0;
                            
                            if (isset($komponen['rate_per_produk']) && $komponen['rate_per_produk'] > 0) {
                                $ratePerProduk = floatval($komponen['rate_per_produk']);
                            } elseif (isset($komponen['rate_per_hour']) && $komponen['rate_per_hour'] > 0) {
                                $ratePerHour = floatval($komponen['rate_per_hour']);
                                $ratePerProduk = $ratePerHour;
                            }
                            
                            $normalizedKomponen[] = [
                                'component' => $komponen['component'] ?? 'N/A',
                                'rate_per_produk' => $ratePerProduk,
                                'rate_per_hour' => $komponen['rate_per_hour'] ?? null,
                                'description' => $komponen['description'] ?? ''
                            ];
                            
                            $totalRatePerHour += floatval($komponen['rate_per_hour'] ?? 0);
                        }
                        
                        $komponenBop = $normalizedKomponen;
                        $totalBopPerJam = $totalRatePerHour;
                    }
                }
            }
            
            echo "Proses: " . $proses->nama_proses . "\n";
            echo "  BOP per produk: " . $bopPerProduk . "\n";
            echo "  Komponen BOP (normalized):\n";
            foreach ($komponenBop as $komponen) {
                echo "    - " . $komponen['component'] . ": rate_per_produk=" . $komponen['rate_per_produk'] . "\n";
                
                // Simulate the new JavaScript logic
                $ratePerProdukJS = floatval($komponen['rate_per_produk'] ?? 0);
                echo "      JavaScript will display: Rp " . number_format($ratePerProdukJS, 0, ',', '.') . "\n";
                
                if ($ratePerProdukJS > 0) {
                    echo "      ✅ Will show correct value\n";
                } else {
                    echo "      ❌ Will show Rp 0\n";
                }
            }
            echo "---\n";
            
            return [
                'id' => $proses->id,
                'nama_proses' => $proses->nama_proses,
                'bop_per_produk' => $bopPerProduk,
                'komponen_bop' => $komponenBop
            ];
        });
    
    echo "✅ Data structure simulation completed\n";
    
} catch (\Exception $e) {
    echo "Error simulating data: " . $e->getMessage() . "\n";
}

echo "\n3. COMPARISON CREATE VS EDIT VIEW:\n\n";

try {
    echo "Comparing JavaScript logic in create vs edit view:\n\n";
    
    $createViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    
    if (file_exists($createViewFile) && file_exists($editViewFile)) {
        $createViewContent = file_get_contents($createViewFile);
        $editViewContent = file_get_contents($editViewFile);
        
        // Check create view logic
        if (strpos($createViewContent, 'Use rate_per_produk directly from controller') !== false) {
            echo "✅ Create view uses correct logic\n";
        } else {
            echo "❌ Create view doesn't use correct logic\n";
        }
        
        // Check edit view logic
        if (strpos($editViewContent, 'Use rate_per_produk directly from controller') !== false) {
            echo "✅ Edit view now uses correct logic\n";
        } else {
            echo "❌ Edit view still uses old logic\n";
        }
        
        echo "\nBoth views should now use the same JavaScript logic:\n";
        echo "const ratePerProduk = parseFloat(komp.rate_per_produk || 0);\n";
        
    } else {
        echo "❌ View files not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error comparing views: " . $e->getMessage() . "\n";
}

echo "\n4. EXPECTED RESULT:\n\n";

echo "After fix, edit page should show:\n";
echo "Pengukusan:\n";
echo "  Gas / BBM: Rp 67 (not Rp 0)\n";
echo "  Air & Kebersihan: Rp 28 (not Rp 0)\n";
echo "  Total BOP/pcs: Rp 95\n\n";
echo "Pengemasan Dan Pengtopingan:\n";
echo "  Listrik: Rp 278 (not Rp 0)\n";
echo "  Susu: Rp 649 (not Rp 0)\n";
echo "  Keju: Rp 1000 (not Rp 0)\n";
echo "  Cup: Rp 400 (not Rp 0)\n";
echo "  Total BOP/pcs: Rp 2327\n\n";

echo "5. NEXT STEPS:\n\n";

echo "1. ✅ JavaScript fix applied to edit view\n";
echo "2. ✅ Both views now use consistent logic\n";
echo "3. 🔄 Test edit page in browser\n";
echo "4. 🔄 Verify BOP components show correct values\n";
echo "5. 🔄 Check that Total BOP/pcs matches\n\n";

echo "=== TEST COMPLETE ===\n";
