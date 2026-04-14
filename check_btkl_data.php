<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking BTKL Data ===\n\n";

try {
    // Check BTKL table
    $btklCount = DB::table('btkls')->count();
    echo "BTKL Records: {$btklCount}\n";

    if ($btklCount > 0) {
        $btkls = DB::table('btkls')->get();
        foreach ($btkls as $b) {
            echo "ID: {$b->id}, Name: {$b->nama_btkl}, Tarif: {$b->tarif_per_jam}, Kapasitas: {$b->kapasitas_per_jam}\n";
        }
    }

    echo "\n=== Checking ProsesProduksi Data ===\n";
    
    // Check ProsesProduksi table
    $prosesCount = DB::table('proses_produksis')->count();
    echo "ProsesProduksi Records: {$prosesCount}\n";

    if ($prosesCount > 0) {
        $proses = DB::table('proses_produksis')->get();
        foreach ($proses as $p) {
            echo "ID: {$p->id}, Name: {$p->nama_proses}, Tarif: {$p->tarif_btkl}, Kapasitas: {$p->kapasitas_per_jam}, Jabatan ID: {$p->jabatan_id}\n";
        }
    }

    echo "\n=== Checking BOP Proses Data ===\n";
    
    // Check BOP Proses table
    $bopCount = DB::table('bop_proses')->count();
    echo "BOP Proses Records: {$bopCount}\n";

    if ($bopCount > 0) {
        $bop = DB::table('bop_proses')->get();
        foreach ($bop as $b) {
            echo "ID: {$b->id}, Proses ID: {$b->proses_produksi_id}, Kapasitas: {$b->kapasitas_per_jam}, BOP/Unit: {$b->bop_per_unit}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
