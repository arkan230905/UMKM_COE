<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGATE BOP DATA LOSS ===\n\n";

echo "1. CEK STRUKTUR TABEL bop_proses:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bop_proses');
    echo "Columns di bop_proses:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nTotal records: " . \App\Models\BopProses::count() . "\n";
    
} catch (\Exception $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

echo "\n2. CEK LARAVEL LOGS UNTUK BOP DELETION:\n\n";

try {
    $logFile = 'c:\UMKM_COE\storage\logs\laravel.log';
    
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $recentLines = array_slice($lines, -100); // Last 100 lines
        
        echo "Searching for BOP deletion logs...\n";
        foreach ($recentLines as $line) {
            if (strpos($line, 'bop') !== false || strpos($line, 'BOP') !== false) {
                echo trim($line) . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking logs: " . $e->getMessage() . "\n";
}

echo "\n3. CEK APAKAH ADA DATA BOP DI TABEL LAIN:\n\n";

try {
    echo "Checking for BOP data in other tables:\n";
    
    // Check bom_job_bop
    $bomJobBopCount = \App\Models\BomJobBOP::count();
    echo "bom_job_bop records: " . $bomJobBopCount . "\n";
    
    if ($bomJobBopCount > 0) {
        echo "Sample bom_job_bop data:\n";
        $samples = \App\Models\BomJobBOP::limit(5)->get();
        foreach ($samples as $sample) {
            echo "  - " . $sample->nama_bop . ": " . $sample->tarif . "\n";
        }
    }
    
    // Check if there's any backup or migration
    echo "\nChecking for BOP-related migrations:\n";
    $migrationPath = 'c:\UMKM_COE\database\migrations';
    if (is_dir($migrationPath)) {
        $migrations = scandir($migrationPath);
        foreach ($migrations as $migration) {
            if (strpos($migration, 'bop') !== false) {
                echo "  Found: " . $migration . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking other tables: " . $e->getMessage() . "\n";
}

echo "\n4. CEK PROSES_PRODUKSI YANG SEHARUSNYA PUNYA BOP:\n\n";

try {
    echo "Checking processes that should have BOP:\n";
    
    $processes = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    foreach ($processes as $process) {
        echo "Proses: " . $process->nama_proses . " (ID: " . $process->id . ")\n";
        echo "  Kapasitas: " . $process->kapasitas_per_jam . "\n";
        echo "  Jabatan: " . ($process->jabatan->nama ?? 'N/A') . "\n";
        
        // Check if there's existing BOP data in bom_job_bop for this process
        $bopCount = \App\Models\BomJobBOP::where('nama_bop', 'like', $process->nama_proses . '%')->count();
        echo "  BOP records in bom_job_bop: " . $bopCount . "\n";
        
        if ($bopCount > 0) {
            $bopRecords = \App\Models\BomJobBOP::where('nama_bop', 'like', $process->nama_proses . '%')->get();
            foreach ($bopRecords as $bop) {
                echo "    - " . $bop->nama_bop . ": " . $bop->tarif . "\n";
            }
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking processes: " . $e->getMessage() . "\n";
}

echo "\n5. REKONSTRUKSI DATA BOP DARI bom_job_bop:\n\n";

try {
    echo "Attempting to reconstruct BOP data from bom_job_bop:\n";
    
    $processes = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    foreach ($processes as $process) {
        echo "Process: " . $process->nama_proses . "\n";
        
        // Get BOP data from bom_job_bop
        $bopRecords = \App\Models\BomJobBOP::where('nama_bop', 'like', $process->nama_proses . '%')->get();
        
        if ($bopRecords->count() > 0) {
            echo "  Found " . $bopRecords->count() . " BOP records\n";
            
            // Reconstruct komponen_bop structure
            $komponenBop = [];
            $totalBopPerUnit = 0;
            
            foreach ($bopRecords as $bop) {
                // Extract component name from nama_bop
                $componentName = str_replace($process->nama_proses . ' - ', '', $bop->nama_bop);
                
                $komponenBop[] = [
                    'component' => $componentName,
                    'rate_per_produk' => $bop->tarif,
                    'rate_per_hour' => null,
                    'description' => ''
                ];
                
                $totalBopPerUnit += $bop->tarif;
            }
            
            echo "  Reconstructed BOP data:\n";
            foreach ($komponenBop as $komponen) {
                echo "    - " . $komponen['component'] . ": rate_per_produk=" . $komponen['rate_per_produk'] . "\n";
            }
            
            echo "  Total BOP per unit: " . $totalBopPerUnit . "\n";
            
            // Create bop_proses record
            $bopProses = new \App\Models\BopProses();
            $bopProses->user_id = 1;
            $bopProses->proses_produksi_id = $process->id;
            $bopProses->komponen_bop = json_encode($komponenBop);
            $bopProses->total_bop_per_produk = $totalBopPerUnit;
            $bopProses->bop_per_unit = $totalBopPerUnit;
            $bopProses->total_bop_per_jam = 0; // Not used anymore
            $bopProses->kapasitas_per_jam = $process->kapasitas_per_jam;
            $bopProses->is_active = 1;
            
            try {
                $bopProses->save();
                echo "  ✅ BOP data restored for " . $process->nama_proses . "\n";
            } catch (\Exception $e) {
                echo "  ❌ Failed to restore BOP data: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "  No BOP records found in bom_job_bop\n";
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error reconstructing BOP data: " . $e->getMessage() . "\n";
}

echo "\n6. VERIFIKASI RESTORASI:\n\n";

try {
    echo "Verifying BOP data restoration:\n";
    
    $bopProsesCount = \App\Models\BopProses::count();
    echo "Total bop_proses records after restoration: " . $bopProsesCount . "\n";
    
    if ($bopProsesCount > 0) {
        echo "Restored BOP data:\n";
        $bopRecords = \App\Models\BopProses::all();
        foreach ($bopRecords as $bop) {
            $process = \App\Models\ProsesProduksi::find($bop->proses_produksi_id);
            echo "  - " . ($process->nama_proses ?? 'Unknown') . ": " . $bop->bop_per_unit . "\n";
            
            if ($bop->komponen_bop) {
                $komponen = json_decode($bop->komponen_bop, true);
                if (is_array($komponen)) {
                    foreach ($komponen as $k) {
                        echo "    * " . ($k['component'] ?? 'N/A') . ": " . ($k['rate_per_produk'] ?? 0) . "\n";
                    }
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error verifying restoration: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";
