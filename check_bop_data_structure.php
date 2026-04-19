<?php
/**
 * Script untuk cek struktur data BOP di database
 * 
 * Jalankan: php artisan tinker
 * Lalu copy-paste code ini
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BopProses;

echo "=== CEK STRUKTUR DATA BOP PROSES ===\n\n";

// Get all BOP Proses
$bopProses = BopProses::with('prosesProduksi')->get();

echo "Total BOP Proses: " . $bopProses->count() . "\n\n";

foreach ($bopProses as $bop) {
    echo "--- BOP ID: {$bop->id} ---\n";
    echo "Proses: {$bop->prosesProduksi->nama_proses}\n";
    echo "Total BOP per Jam: {$bop->total_bop_per_jam}\n";
    echo "BOP per Unit: {$bop->bop_per_unit}\n";
    
    echo "\nKomponen BOP (Raw):\n";
    echo "Type: " . gettype($bop->komponen_bop) . "\n";
    
    if (is_string($bop->komponen_bop)) {
        echo "String value: " . $bop->komponen_bop . "\n";
        $decoded = json_decode($bop->komponen_bop, true);
        echo "Decoded:\n";
        print_r($decoded);
    } elseif (is_array($bop->komponen_bop)) {
        echo "Array value:\n";
        print_r($bop->komponen_bop);
    } else {
        echo "Value: ";
        var_dump($bop->komponen_bop);
    }
    
    echo "\n";
    
    // Check structure
    if (is_array($bop->komponen_bop) || (is_string($bop->komponen_bop) && json_decode($bop->komponen_bop))) {
        $komponenArray = is_string($bop->komponen_bop) ? json_decode($bop->komponen_bop, true) : $bop->komponen_bop;
        
        echo "Struktur Komponen:\n";
        foreach ($komponenArray as $index => $komponen) {
            echo "  [$index] => ";
            if (isset($komponen['component'])) {
                echo "component: {$komponen['component']}, ";
            }
            if (isset($komponen['rate_per_hour'])) {
                echo "rate_per_hour: {$komponen['rate_per_hour']}";
            }
            echo "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "\n=== EXPECTED STRUCTURE ===\n";
echo "Komponen BOP should be array of:\n";
echo "[\n";
echo "  0 => ['component' => 'Listrik Mixer', 'rate_per_hour' => 1000],\n";
echo "  1 => ['component' => 'Mesin Ringan', 'rate_per_hour' => 500],\n";
echo "  ...\n";
echo "]\n";
