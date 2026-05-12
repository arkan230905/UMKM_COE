<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG EDIT BOP ISSUE ===\n\n";

echo "1. CEK BomController@edit METHOD:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Find the edit method
    if (preg_match('/public function edit\(\$id\)(.*?)^}/sm', $controllerContent, $matches)) {
        $editMethod = $matches[0];
        
        echo "✅ Found BomController@edit method\n";
        echo "Method length: " . strlen($editMethod) . " characters\n";
        
        // Check for BOP data loading
        if (strpos($editMethod, 'bop') !== false || strpos($editMethod, 'BOP') !== false) {
            echo "✅ Method contains BOP references\n";
        } else {
            echo "❌ Method doesn't contain BOP references\n";
        }
        
        // Check for prosesBtkl data
        if (strpos($editMethod, 'prosesBtkl') !== false) {
            echo "✅ Method contains prosesBtkl references\n";
        } else {
            echo "❌ Method doesn't contain prosesBtkl references\n";
        }
        
        // Extract key parts
        if (preg_match('/\$prosesBtkl.*?=.*?;/s', $editMethod, $prosesMatches)) {
            echo "Found prosesBtkl assignment:\n";
            echo substr($prosesMatches[0], 0, 200) . "...\n";
        }
        
    } else {
        echo "❌ BomController@edit method not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n2. SIMULASI EDIT METHOD LOGIC:\n\n";

try {
    echo "Simulating BomController@edit for ID 2:\n";
    
    // Find product
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "✅ Found product: " . $produk->nama_produk . "\n";
        
        // Find BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)
            ->where('user_id', 1)
            ->first();
        
        if ($bomJobCosting) {
            echo "✅ Found BomJobCosting:\n";
            echo "  Total BBB: " . $bomJobCosting->total_bbb . "\n";
            echo "  Total BTKL: " . $bomJobCosting->total_btkl . "\n";
            echo "  Total BOP: " . $bomJobCosting->total_bop . "\n";
            echo "  Total HPP: " . $bomJobCosting->total_hpp . "\n";
            
            // Check if there are existing BTKL/BOP records
            $btklRecords = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
            echo "  Existing BTKL records: " . $btklRecords->count() . "\n";
            
            $bopRecords = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
            echo "  Existing BOP records: " . $bopRecords->count() . "\n";
            
            foreach ($bopRecords as $bop) {
                echo "    - " . $bop->nama_bop . ": " . $bop->tarif . "\n";
            }
            
        } else {
            echo "❌ No BomJobCosting found\n";
        }
        
        // Check if edit method uses same logic as create
        echo "\nChecking if edit method uses same prosesBtkl logic as create...\n";
        
        // Simulate the create logic for prosesBtkl
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
                echo "  Komponen BOP count: " . count($komponenBop) . "\n";
                foreach ($komponenBop as $komponen) {
                    echo "    - " . $komponen['component'] . ": rate_per_produk=" . $komponen['rate_per_produk'] . "\n";
                }
                echo "---\n";
                
                return [
                    'id' => $proses->id,
                    'kode_proses' => $proses->kode_proses,
                    'nama_proses' => $proses->nama_proses,
                    'nama_jabatan' => $proses->jabatan->nama ?? '-',
                    'jumlah_pegawai' => $jumlahPegawai,
                    'tarif_per_jam_jabatan' => $tarifPerJamJabatan,
                    'tarif_btkl' => $tarifBtkl,
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'btkl_per_produk' => $btklPerProduk,
                    'total_bop_per_jam' => $totalBopPerJam,
                    'bop_per_produk' => $bopPerProduk,
                    'komponen_bop' => $komponenBop,
                    'has_bop' => $proses->bopProses !== null
                ];
            });
        
        echo "✅ Simulated prosesBtkl data created\n";
        
    } else {
        echo "❌ Product not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating edit logic: " . $e->getMessage() . "\n";
}

echo "\n3. CEK EDIT VIEW FILE:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        // Check if view uses same BOP component logic as create
        if (strpos($viewContent, 'komponen_bop') !== false) {
            echo "✅ Edit view contains komponen_bop references\n";
        } else {
            echo "❌ Edit view doesn't contain komponen_bop references\n";
        }
        
        // Check for BOP detail section
        if (strpos($viewContent, 'Detail Komponen BOP') !== false) {
            echo "✅ Edit view contains BOP detail section\n";
        } else {
            echo "❌ Edit view doesn't contain BOP detail section\n";
        }
        
        // Check JavaScript for BOP handling
        if (strpos($viewContent, 'rate_per_produk') !== false) {
            echo "✅ Edit view contains rate_per_produk references\n";
        } else {
            echo "❌ Edit view doesn't contain rate_per_produk references\n";
        }
        
    } else {
        echo "❌ Edit view file not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking edit view: " . $e->getMessage() . "\n";
}

echo "\n4. IDENTIFY ISSUE:\n\n";

echo "Based on analysis:\n";
echo "1. Check if edit method uses same prosesBtkl logic as create\n";
echo "2. Check if edit view uses same JavaScript logic as create\n";
echo "3. Check if BOP data is properly normalized in edit\n";
echo "4. Check if edit view has same BOP component display logic\n\n";

echo "=== DEBUG COMPLETE ===\n";
