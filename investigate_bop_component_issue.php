<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGASI BOP COMPONENT ISSUE ===\n\n";

echo "1. CEK DATA BOP_PROSES:\n\n";

try {
    $bopProses = \App\Models\BopProses::all();
    
    echo "Data di bop_proses:\n";
    foreach ($bopProses as $bop) {
        echo "ID: " . $bop->id . "\n";
        echo "Keterangan: " . $bop->keterangan . "\n";
        echo "User ID: " . $bop->user_id . "\n";
        echo "Total BOP per produk: " . $bop->total_bop_per_produk . "\n";
        echo "BOP per unit: " . $bop->bop_per_unit . "\n";
        echo "Komponen BOP: ";
        
        if ($bop->komponen_bop) {
            $komponen = is_array($bop->komponen_bop) ? $bop->komponen_bop : json_decode($bop->komponen_bop, true);
            if (is_array($komponen)) {
                echo count($komponen) . " komponen\n";
                foreach ($komponen as $k) {
                    echo "    - " . ($k['component'] ?? 'N/A') . ": rate_per_produk=" . ($k['rate_per_produk'] ?? 'N/A') . ", rate_per_hour=" . ($k['rate_per_hour'] ?? 'N/A') . "\n";
                }
            } else {
                echo "Format tidak valid\n";
            }
        } else {
            echo "Tidak ada\n";
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking bop_proses: " . $e->getMessage() . "\n";
}

echo "\n2. CEK PROSES BTKL YANG TERHUBUNG DENGAN BOP:\n\n";

try {
    $prosesBtkl = \App\Models\ProsesProduksi::with(['jabatan', 'bopProses'])->get();
    
    echo "Proses BTKL dengan BOP:\n";
    foreach ($prosesBtkl as $proses) {
        if ($proses->bopProses) {
            echo "Proses: " . $proses->nama_proses . "\n";
            echo "  BOP ID: " . $proses->bopProses->id . "\n";
            echo "  Total BOP per produk: " . $proses->bopProses->total_bop_per_produk . "\n";
            echo "  BOP per unit: " . $proses->bopProses->bop_per_unit . "\n";
            
            // Check komponen structure
            if ($proses->bopProses->komponen_bop) {
                $komponen = is_array($proses->bopProses->komponen_bop) ? $proses->bopProses->komponen_bop : json_decode($proses->bopProses->komponen_bop, true);
                if (is_array($komponen)) {
                    foreach ($komponen as $k) {
                        echo "    Komponen: " . ($k['component'] ?? 'N/A') . "\n";
                        echo "      rate_per_produk: " . ($k['rate_per_produk'] ?? 'NULL') . "\n";
                        echo "      rate_per_hour: " . ($k['rate_per_hour'] ?? 'NULL') . "\n";
                    }
                }
            }
            echo "---\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking proses BTKL: " . $e->getMessage() . "\n";
}

echo "\n3. SIMULASI LOGIKA BOMCONTROLLER@create UNTUK BOP:\n\n";

try {
    echo "Simulasi query BomController@create untuk proses BTKL:\n";
    
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
                    })
                    ->count();
                $tarifPerJamJabatan = $proses->jabatan->tarif_per_jam ?? $proses->jabatan->tarif ?? 0;
            }
            
            $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
            $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
            
            // Get BOP data if exists
            $bopPerProduk = 0;
            $totalBopPerJam = 0;
            $komponenBop = [];
            
            if ($proses->bopProses) {
                // Use BOP per produk directly (not calculated from per jam)
                $bopPerProduk = $proses->bopProses->bop_per_unit ?? 0;
                
                // Get komponen BOP for display
                if ($proses->bopProses->komponen_bop) {
                    $komponenBop = is_array($proses->bopProses->komponen_bop) 
                        ? $proses->bopProses->komponen_bop 
                        : json_decode($proses->bopProses->komponen_bop, true);
                    
                    // Calculate total BOP per jam from component rates for display purposes
                    if (is_array($komponenBop)) {
                        foreach ($komponenBop as $komponen) {
                            $totalBopPerJam += floatval($komponen['rate_per_hour'] ?? 0);
                        }
                    }
                }
            }
            
            echo "Proses: " . $proses->nama_proses . "\n";
            echo "  BOP per produk (dari bop_per_unit): " . $bopPerProduk . "\n";
            echo "  Total BOP per jam: " . $totalBopPerJam . "\n";
            echo "  Komponen BOP:\n";
            
            if (is_array($komponenBop)) {
                foreach ($komponenBop as $komponen) {
                    $ratePerProduk = $komponen['rate_per_produk'] ?? 0;
                    $ratePerHour = $komponen['rate_per_hour'] ?? 0;
                    
                    echo "    - " . ($komponen['component'] ?? 'N/A') . "\n";
                    echo "      rate_per_produk: " . $ratePerProduk . "\n";
                    echo "      rate_per_hour: " . $ratePerHour . "\n";
                    
                    // ISSUE: rate_per_produk is 0, but rate_per_hour has value
                    if ($ratePerProduk == 0 && $ratePerHour > 0) {
                        echo "      ⚠️ ISSUE: rate_per_produk = 0 tapi rate_per_hour = " . $ratePerHour . "\n";
                    }
                }
            } else {
                echo "    Tidak ada komponen\n";
            }
            
            echo "---\n";
            
            return [
                'nama_proses' => $proses->nama_proses,
                'bop_per_produk' => $bopPerProduk,
                'komponen_bop' => $komponenBop
            ];
        });
    
} catch (\Exception $e) {
    echo "Error simulating BomController@create: " . $e->getMessage() . "\n";
}

echo "\n4. ANALISIS MASALAH:\n\n";

echo "KEMUNGKINAN MASALAH:\n";
echo "1. Data komponen_bop menggunakan 'rate_per_hour' tapi view mencari 'rate_per_produk'\n";
echo "2. Konversi dari rate_per_hour ke rate_per_produk tidak dilakukan dengan benar\n";
echo "3. Struktur data komponen_bop tidak konsisten\n\n";

echo "5. CEK VIEW YANG MENAMPILKAN BOP:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        if (strpos($viewContent, 'komponen_bop') !== false) {
            echo "✅ Found komponen_bop in create.blade.php\n";
            
            // Find how komponen_bop is displayed
            if (preg_match('/komponen_bop.*?rate_per_produk/s', $viewContent, $matches)) {
                echo "Found rate_per_produk usage in view:\n";
                echo substr($matches[0], 0, 200) . "...\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking view: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGASI SELESAI ===\n";
