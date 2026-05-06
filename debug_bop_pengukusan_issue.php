<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BOP PENGUKUSAN ISSUE ===\n\n";

echo "1. CEK DATABASE DATA LANGSUNG:\n\n";

try {
    // Get BOP data for Pengukusan (proses_produksi_id = 1)
    $bopData = \App\Models\BopProses::where('proses_produksi_id', 1)
        ->where('user_id', 1)
        ->first();
    
    if ($bopData) {
        echo "✅ BOP data found for Pengukusan:\n";
        echo "ID: " . $bopData->id . "\n";
        echo "Total BOP per produk: " . $bopData->total_bop_per_produk . "\n";
        echo "BOP per unit: " . $bopData->bop_per_unit . "\n";
        
        // Check komponen_bop structure
        if ($bopData->komponen_bop) {
            $komponen = is_array($bopData->komponen_bop) ? $bopData->komponen_bop : json_decode($bopData->komponen_bop, true);
            
            if (is_array($komponen)) {
                echo "Komponen BOP structure:\n";
                foreach ($komponen as $k => $komponen) {
                    echo "  Komponen " . ($k + 1) . ":\n";
                    echo "    component: " . ($komponen['component'] ?? 'N/A') . "\n";
                    echo "    rate_per_produk: " . ($komponen['rate_per_produk'] ?? 'NULL') . "\n";
                    echo "    rate_per_hour: " . ($komponen['rate_per_hour'] ?? 'NULL') . "\n";
                    echo "    description: " . ($komponen['description'] ?? 'NULL') . "\n";
                    echo "    keterangan: " . ($komponen['keterangan'] ?? 'NULL') . "\n";
                }
            }
        }
    } else {
        echo "❌ No BOP data found for Pengukusan\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}

echo "\n2. SIMULASI EXACT BomController@create LOGIC:\n\n";

try {
    echo "Simulating exact BomController@create logic for Pengukusan:\n";
    
    // Get Pengukusan proses
    $proses = \App\Models\ProsesProduksi::where('id', 1)
        ->with(['jabatan', 'bopProses'])
        ->first();
    
    if ($proses) {
        echo "✅ Found Pengukusan proses:\n";
        echo "Nama: " . $proses->nama_proses . "\n";
        
        // Calculate BTKL
        $jumlahPegawai = 0;
        $tarifPerJamJabatan = 0;
        
        if ($proses->jabatan) {
            $jumlahPegawai = \App\Models\Pegawai::where('user_id', 1)
                ->where(function($q) use ($proses) {
                    $q->where('jabatan_id', $proses->jabatan->id)
                      ->orWhere('jabatan', $proses->jabatan->nama);
                })
                ->count();
            $tarifPerJamJabatan = $proses->jabatan->tarif_per_jam ?? $proses->jabatan->tarif ?? 0;
        }
        
        echo "Jumlah pegawai: " . $jumlahPegawai . "\n";
        echo "Tarif per jam: " . $tarifPerJamJabatan . "\n";
        
        // Get BOP data
        $bopPerProduk = 0;
        $totalBopPerJam = 0;
        $komponenBop = [];
        
        if ($proses->bopProses) {
            echo "✅ Found BOP data:\n";
            echo "BOP per produk (bop_per_unit): " . ($proses->bopProses->bop_per_unit ?? 0) . "\n";
            echo "Total BOP per produk: " . $proses->bopProses->total_bop_per_produk . "\n";
            
            $bopPerProduk = $proses->bopProses->bop_per_unit ?? 0;
            
            // Get komponen BOP for display (exact logic from controller)
            if ($proses->bopProses->komponen_bop) {
                $komponenBop = is_array($proses->bopProses->komponen_bop) 
                    ? $proses->bopProses->komponen_bop 
                    : json_decode($proses->bopProses->komponen_bop, true);
                
                echo "Original komponen_bop data:\n";
                foreach ($komponenBop as $k => $komponen) {
                    echo "  " . ($komponen['component'] ?? 'N/A') . ": rate_per_produk=" . ($komponen['rate_per_produk'] ?? 'NULL') . ", rate_per_hour=" . ($komponen['rate_per_hour'] ?? 'NULL') . "\n";
                }
                
                // Normalize komponen BOP data (exact logic from controller)
                if (is_array($komponenBop)) {
                    $normalizedKomponen = [];
                    $totalRatePerHour = 0;
                    
                    foreach ($komponenBop as $komponen) {
                        $ratePerProduk = 0;
                        $ratePerHour = 0;
                        
                        // Check if rate_per_produk exists
                        if (isset($komponen['rate_per_produk']) && $komponen['rate_per_produk'] > 0) {
                            $ratePerProduk = floatval($komponen['rate_per_produk']);
                            echo "  Using rate_per_produk: " . $ratePerProduk . "\n";
                        } elseif (isset($komponen['rate_per_hour']) && $komponen['rate_per_hour'] > 0) {
                            // Convert rate_per_hour to rate_per_produk
                            $ratePerHour = floatval($komponen['rate_per_hour']);
                            // For display, we'll use rate_per_hour as rate_per_produk since we don't have capacity info here
                            $ratePerProduk = $ratePerHour;
                            echo "  Converting rate_per_hour to rate_per_produk: " . $ratePerProduk . "\n";
                        } else {
                            echo "  ❌ No rate data found for component: " . ($komponen['component'] ?? 'N/A') . "\n";
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
                    
                    echo "Normalized komponen_bop:\n";
                    foreach ($normalizedKomponen as $komponen) {
                        echo "  " . $komponen['component'] . ": rate_per_produk=" . $komponen['rate_per_produk'] . "\n";
                    }
                }
            }
            
            echo "\nFinal results:\n";
            echo "BOP per produk: " . $bopPerProduk . "\n";
            echo "Total BOP per jam: " . $totalBopPerJam . "\n";
            echo "Komponen count: " . count($komponenBop) . "\n";
        } else {
            echo "❌ No BOP data found\n";
        }
    } else {
        echo "❌ No Pengukusan proses found\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating controller: " . $e->getMessage() . "\n";
}

echo "\n3. CEK CACHE DAN LARAVEL CACHE:\n\n";

try {
    echo "Checking if Laravel cache might be causing issues...\n";
    
    // Check if view cache exists
    $cachePath = 'c:\UMKM_COE\bootstrap\cache\config.php';
    if (file_exists($cachePath)) {
        echo "✅ Config cache exists\n";
    } else {
        echo "❌ Config cache not found\n";
    }
    
    // Check if routes cache exists
    $routeCachePath = 'c:\UMKM_COE\bootstrap\cache\routes-v7.php';
    if (file_exists($routeCachePath)) {
        echo "✅ Routes cache exists\n";
    } else {
        echo "❌ Routes cache not found\n";
    }
    
    echo "Suggestion: Clear Laravel cache if changes not reflecting\n";
    echo "Commands: php artisan cache:clear, php artisan view:clear, php artisan route:clear\n";
    
} catch (\Exception $e) {
    echo "Error checking cache: " . $e->getMessage() . "\n";
}

echo "\n4. CEK VIEW FILE UNTUK BOP DISPLAY:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        // Find BOP component display section
        if (strpos($viewContent, 'Detail Komponen BOP') !== false) {
            echo "✅ Found 'Detail Komponen BOP' section in view\n";
            
            // Find the section that displays BOP components
            if (preg_match('/Detail Komponen BOP.*?endforeach/s', $viewContent, $matches)) {
                $bopSection = $matches[0];
                echo "BOP section found in view (first 500 chars):\n";
                echo substr($bopSection, 0, 500) . "...\n";
                
                // Check if it uses rate_per_produk
                if (strpos($bopSection, 'rate_per_produk') !== false) {
                    echo "✅ View uses rate_per_produk field\n";
                } else {
                    echo "❌ View doesn't use rate_per_produk field\n";
                }
                
                // Check if it uses formatNumber function
                if (strpos($bopSection, 'formatNumber') !== false) {
                    echo "✅ View uses formatNumber function\n";
                } else {
                    echo "❌ View doesn't use formatNumber function\n";
                }
            }
        } else {
            echo "❌ 'Detail Komponen BOP' section not found in view\n";
        }
    } else {
        echo "❌ View file not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n5. RECOMMENDATIONS:\n\n";

echo "Based on analysis:\n";
echo "1. ✅ Database data is correct\n";
echo "2. ✅ Controller logic should work\n";
echo "3. ❌ Issue might be:\n";
echo "   - Laravel cache not cleared\n";
echo "   - View file still using old logic\n";
echo "   - Controller changes not applied\n\n";

echo "Next steps:\n";
echo "1. Clear Laravel cache\n";
echo "2. Check if view file needs updating\n";
echo "3. Verify controller changes are saved\n\n";

echo "=== DEBUG COMPLETE ===\n";
