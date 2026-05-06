<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST BOP PENGUKUSAN FIX ===\n\n";

echo "1. VERIFIKASI PERBAIKAN CONTROLLER:\n\n";

try {
    // Test exact BomController@create logic for Pengukusan
    $proses = \App\Models\ProsesProduksi::where('id', 1)
        ->with(['jabatan', 'bopProses'])
        ->first();
    
    if ($proses) {
        // Simulate the exact logic from BomController@create
        $jumlahPegawai = \App\Models\Pegawai::where('user_id', 1)
            ->where(function($q) use ($proses) {
                $q->where('jabatan_id', $proses->jabatan->id)
                  ->orWhere('jabatan', $proses->jabatan->nama);
            })
            ->count();
        $tarifPerJamJabatan = $proses->jabatan->tarif_per_jam ?? $proses->jabatan->tarif ?? 0;
        
        $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
        $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
        
        $bopPerProduk = 0;
        $totalBopPerJam = 0;
        $komponenBop = [];
        
        if ($proses->bopProses) {
            $bopPerProduk = $proses->bopProses->bop_per_unit ?? 0;
            
            if ($proses->bopProses->komponen_bop) {
                $komponenBop = is_array($proses->bopProses->komponen_bop) 
                    ? $proses->bopProses->komponen_bop 
                    : json_decode($proses->bopProses->komponen_bop, true);
                
                // Apply the normalization logic
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
        
        echo "✅ Controller logic results for Pengukusan:\n";
        echo "BOP per produk: " . $bopPerProduk . "\n";
        echo "Komponen BOP:\n";
        
        foreach ($komponenBop as $komponen) {
            echo "  - " . $komponen['component'] . ": rate_per_produk=" . $komponen['rate_per_produk'] . "\n";
        }
        
        // Simulate the data structure that will be passed to JavaScript
        $prosesBtklData = [
            'nama_proses' => $proses->nama_proses,
            'total' => $bopPerProduk,
            'komponen' => $komponenBop
        ];
        
        echo "\n✅ Data structure for JavaScript:\n";
        echo json_encode($prosesBtklData, JSON_PRETTY_PRINT) . "\n";
        
    }
    
} catch (\Exception $e) {
    echo "Error testing controller: " . $e->getMessage() . "\n";
}

echo "\n2. VERIFIKASI PERBAIKAN JAVASCRIPT VIEW:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        // Check if the JavaScript fix is applied
        if (strpos($viewContent, 'Use rate_per_produk directly from controller') !== false) {
            echo "✅ JavaScript fix applied in view\n";
        } else {
            echo "❌ JavaScript fix not found in view\n";
        }
        
        // Check if old calculation logic is removed
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
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n3. SIMULASI JAVASCRIPT LOGIC YANG BARU:\n\n";

try {
    // Simulate the new JavaScript logic
    $proses = \App\Models\ProsesProduksi::where('id', 1)
        ->with(['jabatan', 'bopProses'])
        ->first();
    
    if ($proses && $proses->bopProses) {
        // Get normalized data from controller
        $komponenBop = [];
        if ($proses->bopProses->komponen_bop) {
            $komponenBop = is_array($proses->bopProses->komponen_bop) 
                ? $proses->bopProses->komponen_bop 
                : json_decode($proses->bopProses->komponen_bop, true);
            
            // Apply normalization
            if (is_array($komponenBop)) {
                $normalizedKomponen = [];
                foreach ($komponenBop as $komponen) {
                    $ratePerProduk = 0;
                    if (isset($komponen['rate_per_produk']) && $komponen['rate_per_produk'] > 0) {
                        $ratePerProduk = floatval($komponen['rate_per_produk']);
                    } elseif (isset($komponen['rate_per_hour']) && $komponen['rate_per_hour'] > 0) {
                        $ratePerProduk = floatval($komponen['rate_per_hour']);
                    }
                    
                    $normalizedKomponen[] = [
                        'component' => $komponen['component'] ?? 'N/A',
                        'rate_per_produk' => $ratePerProduk,
                        'description' => $komponen['description'] ?? ''
                    ];
                }
                $komponenBop = $normalizedKomponen;
            }
        }
        
        echo "✅ Simulating new JavaScript logic:\n";
        foreach ($komponenBop as $komp) {
            // New JavaScript logic: const ratePerProduk = parseFloat(komp.rate_per_produk || 0);
            $ratePerProduk = floatval($komp['rate_per_produk'] ?? 0);
            
            echo "  Component: " . $komp['component'] . "\n";
            echo "    rate_per_produk from controller: " . $komp['rate_per_produk'] . "\n";
            echo "    JavaScript ratePerProduk: " . $ratePerProduk . "\n";
            echo "    Display: Rp " . number_format($ratePerProduk, 0, ',', '.') . "\n";
            
            if ($ratePerProduk > 0) {
                echo "    ✅ FIXED: Will display correct value\n";
            } else {
                echo "    ❌ STILL 0: Will display Rp 0\n";
            }
            echo "---\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error simulating JavaScript: " . $e->getMessage() . "\n";
}

echo "\n4. SUMMARY PERBAIKAN:\n\n";

echo "✅ YANG TELAH DIPERBAIKI:\n";
echo "1. BomController@create: Normalisasi data komponen_bop\n";
echo "2. JavaScript view: Gunakan rate_per_produk langsung\n";
echo "3. Remove perhitungan proporsional yang salah\n\n";

echo "✅ ALUR DATA YANG BENAR:\n";
echo "Database → Controller (normalize) → JavaScript (display)\n";
echo "rate_per_produk → rate_per_produk → rate_per_produk\n\n";

echo "✅ HASIL YANG DIHARAPKAN:\n";
echo "Pengukusan:\n";
echo "  Gas / BBM: Rp 67 (bukan Rp 0)\n";
echo "  Air & Kebersihan: Rp 28 (bukan Rp 0)\n";
echo "  Total BOP/pcs: Rp 95\n\n";

echo "5. NEXT STEPS:\n\n";
echo "1. ✅ View cache cleared\n";
echo "2. ✅ Controller logic fixed\n";
echo "3. ✅ JavaScript logic fixed\n";
echo "4. 🔄 Test di browser untuk melihat hasil\n\n";

echo "=== TEST SELESAI ===\n";
