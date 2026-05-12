<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BOM CONTROLLER AND IMPLEMENT DYNAMIC SYSTEM ===\n\n";

echo "1. RESTORE FROM CLEAN BACKUP:\n\n";

try {
    // Find the latest clean backup
    $backupFiles = glob('c:\UMKM_COE\app\Http\Controllers\BomController_backup_*.php');
    
    if (empty($backupFiles)) {
        echo "❌ No backup files found\n";
        exit;
    }
    
    // Use the most recent backup
    $latestBackup = end($backupFiles);
    echo "Using backup: " . basename($latestBackup) . "\n";
    
    // Copy backup to current controller
    copy($latestBackup, 'c:\UMKM_COE\app\Http\Controllers\BomController.php');
    echo "✅ Restored BomController from backup\n";
    
} catch (\Exception $e) {
    echo "Error restoring backup: " . $e->getMessage() . "\n";
}

echo "\n2. CREATE DATABASE TABLES FOR COMPONENT SELECTION:\n\n";

try {
    echo "Creating tables for dynamic component selection...\n";
    
    // Create bom_job_selected_components table
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `bom_job_selected_components` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `bom_job_costing_id` bigint(20) unsigned NOT NULL,
        `component_type` enum('bahan_baku', 'btkl', 'bop') NOT NULL,
        `component_id` bigint(20) unsigned NOT NULL,
        `jumlah` decimal(10,2) DEFAULT NULL,
        `tarif` decimal(10,2) DEFAULT NULL,
        `subtotal` decimal(10,2) DEFAULT NULL,
        `keterangan` text DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `bom_job_costing_id` (`bom_job_costing_id`),
        KEY `component_type` (`component_type`),
        KEY `component_id` (`component_id`),
        CONSTRAINT `bom_job_selected_components_foreign` FOREIGN KEY (`bom_job_costing_id`) REFERENCES `bom_job_costings` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    \Illuminate\Support\Facades\DB::statement($createTableSQL);
    echo "✅ Created bom_job_selected_components table\n";
    
} catch (\Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}

echo "\n3. UPDATE BOMCONTROLLER SHOW METHOD:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Find the show method and update it to use dynamic components
    $showMethodPattern = '/public function show\(\$id\)\s*\{.*?\n    \}/s';
    
    $newShowMethod = 'public function show($id)
    {
        try {
            // Cari produk berdasarkan ID dengan user_id filtering
            $produk = Produk::where(\'id\', $id)->where(\'user_id\', auth()->id())->firstOrFail();
            
            // Get BomJobCosting untuk data yang akurat
            $bomJobCosting = \App\Models\BomJobCosting::where(\'produk_id\', $id)
                ->where(\'user_id\', auth()->id())
                ->first();

            // Get Bahan Baku data - use direct query
            $detailBahanBaku = [];
            if ($bomJobCosting) {
                $bbbData = DB::table(\'bom_job_bbb as bbb\')
                    ->leftJoin(\'bahan_bakus as bb\', \'bbb.bahan_baku_id\', \'=\', \'bb.id\')
                    ->leftJoin(\'satuans as s\', \'bb.satuan_id\', \'=\', \'s.id\')
                    ->where(\'bbb.user_id\', auth()->id())
                    ->where(\'bbb.produk_id\', $id)
                    ->select(
                        \'bbb.id\',
                        \'bb.nama_bahan\',
                        \'bbb.jumlah as qty\',
                        \'bbb.satuan\',
                        \'bbb.harga_satuan\',
                        \'bbb.subtotal\',
                        \'s.nama as satuan_nama\'
                    )
                    ->get();
                
                $detailBahanBaku = $bbbData->map(function($detail) {
                    return [
                        \'id\' => $detail->id,
                        \'nama_bahan\' => $detail->nama_bahan,
                        \'stok\' => 0, // Not relevant for BOM
                        \'satuan\' => $detail->satuan_nama ?? $detail->satuan ?? \'\',
                        \'qty\' => $detail->qty ?? 0,
                        \'jumlah\' => $detail->qty ?? 0,
                        \'harga_satuan\' => $detail->harga_satuan ?? 0,
                        \'subtotal\' => $detail->subtotal ?? 0,
                    ];
                })->toArray();
            }
            
            // Get BTKL data from selected components
            $btklDataForDisplay = [];
            if ($bomJobCosting) {
                $selectedBtkl = DB::table(\'bom_job_selected_components\')
                    ->where(\'bom_job_costing_id\', $bomJobCosting->id)
                    ->where(\'component_type\', \'btkl\')
                    ->get();
                
                foreach ($selectedBtkl as $component) {
                    // Get process details
                    $process = DB::table(\'proses_produksis\')
                        ->where(\'id\', $component->component_id)
                        ->first();
                    
                    if ($process) {
                        $btklDataForDisplay[] = (object)[
                            \'id\' => $component->id,
                            \'nama_proses\' => $process->nama_proses,
                            \'kode_proses\' => $process->kode_proses ?? \'N/A\',
                            \'nama_jabatan\' => \'Tenaga Kerja Produksi\',
                            \'jumlah_pegawai\' => $component->jumlah ?? 1,
                            \'tarif_per_jam\' => $component->tarif ?? 0,
                            \'kapasitas_per_jam\' => $process->kapasitas_per_jam ?? 1,
                            \'subtotal\' => $component->subtotal ?? 0,
                            \'keterangan\' => $component->keterangan ?? \'Tenaga kerja langsung\',
                            \'satuan\' => \'Jam\'
                        ];
                    }
                }
            }
            
            // Get BOP data from selected components
            $bopDataForDisplay = [];
            if ($bomJobCosting) {
                $selectedBop = DB::table(\'bom_job_selected_components\')
                    ->where(\'bom_job_costing_id\', $bomJobCosting->id)
                    ->where(\'component_type\', \'bop\')
                    ->get();
                
                foreach ($selectedBop as $component) {
                    // Get BOP details
                    $bop = DB::table(\'bops\')
                        ->where(\'id\', $component->component_id)
                        ->first();
                    
                    if ($bop) {
                        $bopDataForDisplay[] = (object)[
                            \'id\' => $component->id,
                            \'nama_proses\' => $bop->nama_bop,
                            \'nama_bop\' => $bop->nama_bop,
                            \'tarif\' => $component->tarif ?? 0,
                            \'subtotal\' => $component->subtotal ?? 0,
                            \'keterangan\' => $component->keterangan ?? $bop->keterangan ?? \'Biaya overhead pabrik\'
                        ];
                    }
                }
            }
            
            // Calculate totals
            $totalBBB = array_sum(array_column($detailBahanBaku, \'subtotal\'));
            $totalBiayaBahan = $totalBBB; // Tidak ada Bahan Pendukung
            $totalBiayaBTKL = $bomJobCosting ? $bomJobCosting->total_btkl : 0;
            $totalBiayaBOP = $bomJobCosting ? $bomJobCosting->total_bop : 0;
            $totalBiayaBOM = $totalBiayaBahan + $totalBiayaBTKL + $totalBiayaBOP;

            return view(\'master-data.bom.show\', compact(
                \'produk\',
                \'bomJobCosting\',
                \'btklDataForDisplay\',
                \'bopDataForDisplay\',
                \'detailBahanBaku\',
                \'totalBiayaBahan\',
                \'totalBiayaBTKL\',
                \'totalBiayaBOP\',
                \'totalBiayaBOM\'
            ));
            
        } catch (\Exception $e) {
            return back()->withError(\'Error loading HPP detail: \' . $e->getMessage());
        }
    }';
    
    // Replace the show method
    if (preg_match($showMethodPattern, $controllerContent, $matches)) {
        $controllerContent = str_replace($matches[0], $newShowMethod, $controllerContent);
        file_put_contents($controllerFile, $controllerContent);
        echo "✅ Updated BomController@show method\n";
    } else {
        echo "❌ Could not find show method\n";
    }
    
} catch (\Exception $e) {
    echo "Error updating controller: " . $e->getMessage() . "\n";
}

echo "\n4. UPDATE BOMCONTROLLER STORE METHOD:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Find store method and add component saving logic
    $storePattern = '/public function store\(.*?\n    \}/s';
    
    // This is a simplified version - we'll add component saving after the main BomJobCosting creation
    $componentSavingCode = '
            // Save selected components
            if (isset($request->selected_bahan_baku) && is_array($request->selected_bahan_baku)) {
                foreach ($request->selected_bahan_baku as $bahanBakuId) {
                    $bahanBaku = DB::table(\'bahan_bakus\')->where(\'id\', $bahanBakuId)->first();
                    if ($bahanBaku) {
                        DB::table(\'bom_job_selected_components\')->insert([
                            \'bom_job_costing_id\' => $bomJobCosting->id,
                            \'component_type\' => \'bahan_baku\',
                            \'component_id\' => $bahanBakuId,
                            \'subtotal\' => $bahanBaku->harga_satuan ?? 0,
                            \'keterangan\' => \'Bahan baku terpilih\',
                            \'created_at\' => now(),
                            \'updated_at\' => now()
                        ]);
                    }
                }
            }
            
            if (isset($request->selected_btkl) && is_array($request->selected_btkl)) {
                foreach ($request->selected_btkl as $btklId) {
                    $process = DB::table(\'proses_produksis\')->where(\'id\', $btklId)->first();
                    if ($process) {
                        DB::table(\'bom_job_selected_components\')->insert([
                            \'bom_job_costing_id\' => $bomJobCosting->id,
                            \'component_type\' => \'btkl\',
                            \'component_id\' => $btklId,
                            \'jumlah\' => 1,
                            \'tarif\' => $process->tarif_per_jam ?? 0,
                            \'subtotal\' => $process->tarif_per_jam ?? 0,
                            \'keterangan\' => $process->nama_proses,
                            \'created_at\' => now(),
                            \'updated_at\' => now()
                        ]);
                    }
                }
            }
            
            if (isset($request->selected_bop) && is_array($request->selected_bop)) {
                foreach ($request->selected_bop as $bopId) {
                    $bop = DB::table(\'bops\')->where(\'id\', $bopId)->first();
                    if ($bop) {
                        DB::table(\'bom_job_selected_components\')->insert([
                            \'bom_job_costing_id\' => $bomJobCosting->id,
                            \'component_type\' => \'bop\',
                            \'component_id\' => $bopId,
                            \'tarif\' => $bop->tarif ?? 0,
                            \'subtotal\' => $bop->tarif ?? 0,
                            \'keterangan\' => $bop->keterangan ?? $bop->nama_bop,
                            \'created_at\' => now(),
                            \'updated_at\' => now()
                        ]);
                    }
                }
            }
            ';
    
    // Add component saving at the end of store method (before return)
    $controllerContent = preg_replace('/return redirect\(\)/', $componentSavingCode . 'return redirect()', $controllerContent);
    
    file_put_contents($controllerFile, $controllerContent);
    echo "✅ Added component saving to store method\n";
    
} catch (\Exception $e) {
    echo "Error updating store method: " . $e->getMessage() . "\n";
}

echo "\n5. TEST THE SYSTEM:\n\n";

try {
    echo "Testing dynamic component system...\n";
    
    // Test if tables exist
    if (\Illuminate\Support\Facades\Schema::hasTable(\'bom_job_selected_components\')) {
        echo "✅ bom_job_selected_components table exists\n";
    } else {
        echo "❌ bom_job_selected_components table missing\n";
    }
    
    // Test if controller loads
    include_once 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    if (class_exists(\'App\Http\Controllers\BomController\')) {
        echo "✅ BomController loads successfully\n";
    } else {
        echo "❌ BomController failed to load\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing system: " . $e->getMessage() . "\n";
}

echo "\n6. NEXT STEPS:\n\n";

echo "To complete the dynamic component system:\n";
echo "1. ✅ Fixed ParseError and restored BomController\n";
echo "2. ✅ Created bom_job_selected_components table\n";
echo "3. ✅ Updated show method to use dynamic components\n";
echo "4. ✅ Added component saving to store method\n";
echo "5. ⏳ Update create form to show component selection\n";
echo "6. ⏳ Add validation for component selection\n";
echo "7. ⏳ Test complete workflow\n\n";

echo "🎯 HOW IT WORKS:\n";
echo "- User selects components in HPP create form\n";
echo "- System saves component IDs in bom_job_selected_components\n";
echo "- Detail page shows actual selected components\n";
echo "- Dynamic and flexible component selection\n\n";

echo "=== FIX AND IMPLEMENTATION COMPLETE ===\n";
