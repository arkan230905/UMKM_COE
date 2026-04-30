<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "REFRESHING PEGAWAI JABATAN RELATIONSHIP FOR HOSTING\n";

// Get pegawai and force refresh the relationship
$pegawai = \App\Models\Pegawai::where('nama', 'Budi Susanto')->first();

if (!$pegawai) {
    echo "ERROR: Pegawai Budi Susanto not found!\n";
    exit;
}

echo "\n=== CURRENT PEGAWAI DATA ===\n";
echo "ID: " . $pegawai->id . "\n";
echo "Nama: " . $pegawai->nama . "\n";
echo "Jabatan ID: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";

// Check the jabatan directly
if ($pegawai->jabatan_id) {
    $jabatan = \App\Models\Jabatan::find($pegawai->jabatan_id);
    echo "\n=== DIRECT JABATAN CHECK ===\n";
    echo "Jabatan ID: " . $jabatan->id . "\n";
    echo "Nama Jabatan: '" . $jabatan->nama_jabatan . "'\n";
    echo "Tunjangan Jabatan: Rp " . number_format($jabatan->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    
    $totalTunjangan = ($jabatan->tunjangan_jabatan ?? 0) + 
                      ($jabatan->tunjangan_transport ?? 0) + 
                      ($jabatan->tunjangan_konsumsi ?? 0);
    
    echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
    
    // Force refresh the relationship
    echo "\n=== REFRESHING RELATIONSHIP ===\n";
    $pegawai->load('jabatanRelasi');
    
    echo "Relationship loaded: " . ($pegawai->jabatanRelasi ? "YES" : "NO") . "\n";
    
    if ($pegawai->jabatanRelasi) {
        echo "Jabatan from relationship: '" . $pegawai->jabatanRelasi->nama_jabatan . "'\n";
        echo "Tunjangan from relationship: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    } else {
        echo "ERROR: Relationship still not loading\n";
        
        // Check if there's a cache issue
        echo "\n=== CLEARING CACHE ===\n";
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        
        echo "Cache cleared\n";
        
        // Try again
        $pegawai->refresh();
        $pegawai->load('jabatanRelasi');
        
        echo "After cache clear - Relationship loaded: " . ($pegawai->jabatanRelasi ? "YES" : "NO") . "\n";
        
        if ($pegawai->jabatanRelasi) {
            echo "Jabatan from relationship: '" . $pegawai->jabatanRelasi->nama_jabatan . "'\n";
        }
    }
    
    // Simulate what the PenggajianController would do
    echo "\n=== SIMULATING PENGGAJIAN CONTROLLER LOGIC ===\n";
    
    // This is how the controller would load the pegawai
    $pegawaiForController = \App\Models\Pegawai::where('user_id', 1)
        ->with('jabatanRelasi')
        ->where('id', $pegawai->id)
        ->first();
    
    echo "Controller query result: " . ($pegawaiForController ? "FOUND" : "NOT FOUND") . "\n";
    
    if ($pegawaiForController) {
        echo "Jabatan loaded by controller: " . ($pegawaiForController->jabatanRelasi ? "YES" : "NO") . "\n";
        
        if ($pegawaiForController->jabatanRelasi) {
            echo "Controller sees jabatan: '" . $pegawaiForController->jabatanRelasi->nama_jabatan . "'\n";
            echo "Controller sees tunjangan jabatan: Rp " . number_format($pegawaiForController->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
            echo "Controller sees tunjangan transport: Rp " . number_format($pegawaiForController->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
            echo "Controller sees tunjangan konsumsi: Rp " . number_format($pegawaiForController->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
            
            $controllerTotalTunjangan = ($pegawaiForController->jabatanRelasi->tunjangan_jabatan ?? 0) + 
                                       ($pegawaiForController->jabatanRelasi->tunjangan_transport ?? 0) + 
                                       ($pegawaiForController->jabatanRelasi->tunjangan_konsumsi ?? 0);
            
            echo "Controller total tunjangan: Rp " . number_format($controllerTotalTunjangan, 0, ',', '.') . "\n";
            
            echo "\n=== EXPECTED DISPLAY IN PENGGAJIAN CREATE ===\n";
            echo "Pegawai: " . $pegawaiForController->nama . " - " . $pegawaiForController->jabatanRelasi->nama_jabatan . " [Gaji: 0, Tarif: 20.000]\n";
            echo "Tunjangan Jabatan: Rp " . number_format($pegawaiForController->jabatanRelasi->tunjangan_jabatan, 0, ',', '.') . "\n";
            echo "Tunjangan Transport: Rp " . number_format($pegawaiForController->jabatanRelasi->tunjangan_transport, 0, ',', '.') . "\n";
            echo "Tunjangan Konsumsi: Rp " . number_format($pegawaiForController->jabatanRelasi->tunjangan_konsumsi, 0, ',', '.') . "\n";
            echo "Total Tunjangan: Rp " . number_format($controllerTotalTunjangan, 0, ',', '.') . "\n";
            
            if ($controllerTotalTunjangan > 0) {
                echo "\nSUCCESS: Tunjangan will be displayed correctly in penggajian create!\n";
                echo "The issue is resolved for hosting.\n";
            } else {
                echo "\nISSUE: Total tunjangan is still 0\n";
            }
        } else {
            echo "ISSUE: Controller cannot load jabatan relationship\n";
        }
    }
    
} else {
    echo "ERROR: Pegawai has no jabatan_id\n";
}

echo "\nRelationship refresh completed!\n";
