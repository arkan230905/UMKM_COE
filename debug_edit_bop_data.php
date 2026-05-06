<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG EDIT BOP DATA ===\n\n";

echo "1. CEK BOP_PROSES DATA LANGSUNG:\n\n";

try {
    $bopProses = \App\Models\BopProses::all();
    
    echo "Data di bop_proses:\n";
    foreach ($bopProses as $bop) {
        echo "ID: " . $bop->id . "\n";
        echo "Proses ID: " . $bop->proses_produksi_id . "\n";
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

echo "\n2. CEK PROSES_PRODUKSI DENGAN BOP:\n\n";

try {
    $prosesWithBop = \App\Models\ProsesProduksi::with(['bopProses'])
        ->where('kapasitas_per_jam', '>', 0)
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    echo "Proses dengan BOP:\n";
    foreach ($prosesWithBop as $proses) {
        echo "Proses: " . $proses->nama_proses . " (ID: " . $proses->id . ")\n";
        
        if ($proses->bopProses) {
            echo "  BOP ID: " . $proses->bopProses->id . "\n";
            echo "  BOP per unit: " . $proses->bopProses->bop_per_unit . "\n";
            echo "  Komponen: ";
            
            if ($proses->bopProses->komponen_bop) {
                $komponen = is_array($proses->bopProses->komponen_bop) 
                    ? $proses->bopProses->komponen_bop 
                    : json_decode($proses->bopProses->komponen_bop, true);
                    
                if (is_array($komponen)) {
                    echo count($komponen) . " komponen\n";
                    foreach ($komponen as $k) {
                        echo "    - " . ($k['component'] ?? 'N/A') . ": rate_per_produk=" . ($k['rate_per_produk'] ?? 'N/A') . "\n";
                    }
                } else {
                    echo "Format tidak valid\n";
                }
            } else {
                echo "Tidak ada\n";
            }
        } else {
            echo "  ❌ Tidak ada BOP data\n";
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking proses with BOP: " . $e->getMessage() . "\n";
}

echo "\n3. CEK RELASI PROSES_PRODUKSI -> BOP_PROSES:\n\n";

try {
    echo "Checking relationship configuration:\n";
    
    // Check if the relationship is properly defined
    $proses = \App\Models\ProsesProduksi::find(1);
    if ($proses) {
        echo "Proses Pengukusan (ID: 1):\n";
        echo "  Kapasitas per jam: " . $proses->kapasitas_per_jam . "\n";
        echo "  Jabatan: " . ($proses->jabatan->nama ?? 'N/A') . "\n";
        
        // Check bopProses relationship
        $bopProses = $proses->bopProses;
        if ($bopProses) {
            echo "  ✅ BOP relationship works\n";
            echo "  BOP ID: " . $bopProses->id . "\n";
            echo "  BOP per unit: " . $bopProses->bop_per_unit . "\n";
        } else {
            echo "  ❌ BOP relationship not working\n";
        }
    }
    
    $proses2 = \App\Models\ProsesProduksi::find(2);
    if ($proses2) {
        echo "\nProses Pengemasan (ID: 2):\n";
        echo "  Kapasitas per jam: " . $proses2->kapasitas_per_jam . "\n";
        echo "  Jabatan: " . ($proses2->jabatan->nama ?? 'N/A') . "\n";
        
        // Check bopProses relationship
        $bopProses2 = $proses2->bopProses;
        if ($bopProses2) {
            echo "  ✅ BOP relationship works\n";
            echo "  BOP ID: " . $bopProses2->id . "\n";
            echo "  BOP per unit: " . $bopProses2->bop_per_unit . "\n";
        } else {
            echo "  ❌ BOP relationship not working\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking relationship: " . $e->getMessage() . "\n";
}

echo "\n4. DEBUG LANGSUNG EDIT METHOD:\n\n";

try {
    echo "Debugging exact edit method logic:\n";
    
    // Simulate exact query from edit method
    $prosesBtkl = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->with(['jabatan', 'bopProses'])
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    echo "Found " . $prosesBtkl->count() . " processes\n";
    
    foreach ($prosesBtkl as $proses) {
        echo "\nProses: " . $proses->nama_proses . "\n";
        echo "  Jabatan: " . ($proses->jabatan->nama ?? 'N/A') . "\n";
        echo "  Kapasitas: " . $proses->kapasitas_per_jam . "\n";
        
        // Check if bopProses is loaded
        if ($proses->bopProses) {
            echo "  ✅ BOP loaded: ID " . $proses->bopProses->id . "\n";
            echo "  BOP per unit: " . $proses->bopProses->bop_per_unit . "\n";
            
            // Check komponen_bop
            if ($proses->bopProses->komponen_bop) {
                $komponen = is_array($proses->bopProses->komponen_bop) 
                    ? $proses->bopProses->komponen_bop 
                    : json_decode($proses->bopProses->komponen_bop, true);
                
                if (is_array($komponen) && !empty($komponen)) {
                    echo "  ✅ Komponen found: " . count($komponen) . " items\n";
                    foreach ($komponen as $k) {
                        echo "    - " . ($k['component'] ?? 'N/A') . ": rate_per_produk=" . ($k['rate_per_produk'] ?? 'N/A') . "\n";
                    }
                } else {
                    echo "  ❌ Komponen empty or invalid\n";
                }
            } else {
                echo "  ❌ No komponen_bop data\n";
            }
        } else {
            echo "  ❌ No BOP data loaded\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error debugging edit method: " . $e->getMessage() . "\n";
}

echo "\n5. IDENTIFY MASALAH:\n\n";

echo "Based on analysis:\n";
echo "1. Check if bopProses relationship is working\n";
echo "2. Check if komponen_bop data exists\n";
echo "3. Check if data is properly loaded in query\n";
echo "4. Check if normalization logic is applied correctly\n\n";

echo "=== DEBUG COMPLETE ===\n";
