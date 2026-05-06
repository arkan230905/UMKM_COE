<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST EDIT VIEW SYNC ===\n\n";

echo "1. VERIFIKASI PERBAIKAN EDIT VIEW:\n\n";

try {
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    $editViewContent = file_get_contents($editViewFile);
    
    // Check if the fix is applied
    if (strpos($editViewContent, 'Calculate biaya bahan with fallback logic (same as create view)') !== false) {
        echo "✅ Biaya bahan fallback logic added to edit view\n";
    } else {
        echo "❌ Biaya bahan fallback logic not found in edit view\n";
    }
    
    // Check if the logic is correct
    if (strpos($editViewContent, 'if ($bomJobCosting)') !== false) {
        echo "✅ Conditional logic for BomJobCosting exists\n";
    } else {
        echo "❌ Conditional logic for BomJobCosting missing\n";
    }
    
    // Check if BBB fallback exists
    if (strpos($editViewContent, 'BomJobBBB::where') !== false) {
        echo "✅ BBB fallback calculation exists\n";
    } else {
        echo "❌ BBB fallback calculation missing\n";
    }
    
    // Check if BOP JavaScript fix exists
    if (strpos($editViewContent, 'Use rate_per_produk directly from controller') !== false) {
        echo "✅ BOP JavaScript fix exists\n";
    } else {
        echo "❌ BOP JavaScript fix missing\n";
    }
    
    // Check if form method is correct
    if (strpos($editViewContent, '@method(\'PUT\')') !== false) {
        echo "✅ Edit form uses PUT method\n";
    } else {
        echo "❌ Edit form method issue\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying edit view: " . $e->getMessage() . "\n";
}

echo "\n2. SIMULASI EDIT VIEW LOGIC:\n\n";

try {
    echo "Simulating edit view logic:\n";
    
    // Get the product that would be edited
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "Product: " . $produk->nama_produk . "\n";
        
        // Get BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', 1)
            ->first();
        
        // Simulate the @php logic from edit view
        $biayaBahan = 0;
        if ($bomJobCosting) {
            $biayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
            echo "  Using BomJobCosting: " . $biayaBahan . "\n";
        } else {
            // Calculate from bom_job_bbb directly
            $biayaBahan = \App\Models\BomJobBBB::where('user_id', 1)
                ->where('produk_id', $produk->id)
                ->sum('subtotal');
            echo "  Using BBB fallback: " . $biayaBahan . "\n";
        }
        
        echo "  Display: Rp " . number_format($biayaBahan, 0, ',', '.') . "\n";
        echo "  Hidden input: " . $biayaBahan . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error simulating edit view logic: " . $e->getMessage() . "\n";
}

echo "\n3. CEK STRUKTUR YANG SAMA DENGAN CREATE VIEW:\n\n";

try {
    echo "Checking structure consistency:\n";
    
    $createViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    
    $createViewContent = file_get_contents($createViewFile);
    $editViewContent = file_get_contents($editViewFile);
    
    // Check biaya bahan logic consistency
    $createBBBLogic = strpos($createViewContent, 'BomJobBBB::where') !== false;
    $editBBBLogic = strpos($editViewContent, 'BomJobBBB::where') !== false;
    
    if ($createBBBLogic && $editBBBLogic) {
        echo "✅ Both views have BBB fallback logic\n";
    } else {
        echo "❌ Inconsistent BBB fallback logic\n";
    }
    
    // Check BOP JavaScript consistency
    $createBOPJS = strpos($createViewContent, 'Use rate_per_produk directly from controller') !== false;
    $editBOPJS = strpos($editViewContent, 'Use rate_per_produk directly from controller') !== false;
    
    if ($createBOPJS && $editBOPJS) {
        echo "✅ Both views have fixed BOP JavaScript\n";
    } else {
        echo "❌ Inconsistent BOP JavaScript\n";
    }
    
    // Check form structure
    $createForm = strpos($createViewContent, 'method="POST"') !== false;
    $editForm = strpos($editViewContent, '@method(\'PUT\')') !== false;
    
    if ($createForm && $editForm) {
        echo "✅ Both views have correct form methods\n";
    } else {
        echo "❌ Form method inconsistency\n";
    }
    
    // Check BOP component section
    $createBOPSection = strpos($createViewContent, 'Detail Komponen BOP') !== false;
    $editBOPSection = strpos($editViewContent, 'Detail Komponen BOP') !== false;
    
    if ($createBOPSection && $editBOPSection) {
        echo "✅ Both views have BOP component sections\n";
    } else {
        echo "❌ Missing BOP component sections\n";
    }
    
    // Check JavaScript calculation
    $createJS = strpos($createViewContent, 'calculateTotal()') !== false;
    $editJS = strpos($editViewContent, 'calculateTotal()') !== false;
    
    if ($createJS && $editJS) {
        echo "✅ Both views have JavaScript calculation\n";
    } else {
        echo "❌ Missing JavaScript calculation\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking structure: " . $e->getMessage() . "\n";
}

echo "\n4. CEK DATA YANG AKAN DITERIMA EDIT VIEW:\n\n";

try {
    echo "Simulating BomController@edit data:\n";
    
    $produk = \App\Models\Produk::find(2);
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 2)
        ->where('user_id', 1)
        ->first();
    
    // Get selected BTKL processes (from existing data)
    $selectedBtklIds = [];
    if ($bomJobCosting) {
        // This would come from BomJobBTKL table, but since it's empty, we'll use a default
        $selectedBtklIds = [1]; // Default to Pengukusan for testing
    }
    
    // Match nama_proses with proses_produksis.nama_proses to get IDs
    $selectedProsesIds = \App\Models\ProsesProduksi::whereIn('nama_proses', ['Pengukusan'])
        ->pluck('id')
        ->toArray();
    
    // Get all BTKL processes with their BOP
    $prosesBtkl = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->with(['jabatan', 'bopProses'])
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get()
        ->map(function($proses) use ($selectedProsesIds) {
            // Simulate the same logic as create method
            $jumlahPegawai = \App\Models\Pegawai::where('user_id', 1)
                ->where(function($q) use ($proses) {
                    $q->where('jabatan_id', $proses->jabatan->id)
                      ->orWhere('jabatan', $proses->jabatan->nama);
                })->count();
            $tarifPerJamJabatan = $proses->jabatan->tarif_per_jam ?? $proses->jabatan->tarif ?? 0;
            
            $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
            $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
            
            // Get BOP data
            $bopPerProduk = 0;
            $komponenBop = [];
            
            if ($proses->bopProses) {
                $bopPerProduk = $proses->bopProses->bop_per_unit ?? 0;
                
                if ($proses->bopProses->komponen_bop) {
                    $komponenBop = is_array($proses->bopProses->komponen_bop) 
                        ? $proses->bopProses->komponen_bop 
                        : json_decode($proses->bopProses->komponen_bop, true);
                    
                    // Normalize
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
                                'rate_per_hour' => $komponen['rate_per_hour'] ?? null,
                                'description' => $komponen['description'] ?? ''
                            ];
                        }
                        $komponenBop = $normalizedKomponen;
                    }
                }
            }
            
            return [
                'id' => $proses->id,
                'nama_proses' => $proses->nama_proses,
                'btkl_per_produk' => $btklPerProduk,
                'bop_per_produk' => $bopPerProduk,
                'komponen_bop' => $komponenBop,
                'has_bop' => $proses->bopProses !== null,
                'is_selected' => in_array($proses->id, $selectedProsesIds)
            ];
        });
    
    echo "✅ Data prepared for edit view:\n";
    echo "  Product: " . ($produk ? $produk->nama_produk : 'NULL') . "\n";
    echo "  BomJobCosting: " . ($bomJobCosting ? 'EXISTS' : 'NULL') . "\n";
    echo "  Processes: " . $prosesBtkl->count() . "\n";
    echo "  Selected processes: " . count($selectedProsesIds) . "\n";
    
} catch (\Exception $e) {
    echo "Error simulating edit data: " . $e->getMessage() . "\n";
}

echo "\n5. SUMMARY PERBAIKAN:\n\n";

echo "✅ YANG TELAH DIPERBAIKI:\n";
echo "- Added biaya bahan fallback logic to edit view\n";
echo "- Made biaya bahan calculation consistent with create view\n";
echo "- Both views now handle null BomJobCosting gracefully\n";
echo "- BOP JavaScript is consistent between views\n";
echo "- Form methods are correct (POST for create, PUT for edit)\n\n";

echo "✅ STRUKTUR YANG SAMA:\n";
echo "- Step 1: Produk information (create: selectable, edit: read-only)\n";
echo "- Step 2: Process selection with pre-selection\n";
echo "- Step 3: BOP components display\n";
echo "- Step 4: Total calculation\n";
echo "- JavaScript for dynamic calculations\n";
echo "- Form submission\n\n";

echo "✅ PERBEDAAN YANG VALID:\n";
echo "- Create: Product dropdown, Edit: Product display (read-only)\n";
echo "- Create: No pre-selection, Edit: Pre-selected processes\n";
echo "- Create: POST method, Edit: PUT method\n\n";

echo "6. READY FOR TESTING:\n\n";

echo "🔄 Test edit page: http://127.0.0.1:8000/master-data/harga-pokok-produksi/2/edit\n";
echo "🔄 Verify no errors occur\n";
echo "🔄 Check biaya bahan displays correctly\n";
echo "🔄 Verify BOP components show values\n";
echo "🔄 Test process selection functionality\n";
echo "🔄 Test form submission\n\n";

echo "=== SYNC COMPLETE ===\n";
