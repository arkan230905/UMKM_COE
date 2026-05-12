<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Btkl;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\ProsesProduksi;

echo "=== FIX BTKL TARIF RECALCULATION ===\n\n";

$userId = 4; // Muhammad Arkan Abiyyu

// Get all BTKL records
$btkls = Btkl::with('jabatan')->get();
echo "Total BTKL records: " . $btkls->count() . "\n\n";

if ($btkls->isEmpty()) {
    echo "No BTKL records found!\n";
    exit;
}

foreach ($btkls as $btkl) {
    echo "BTKL: {$btkl->kode_proses} - {$btkl->nama_btkl}\n";
    echo "  Jabatan: " . ($btkl->jabatan->nama ?? 'NULL') . "\n";
    echo "  Tarif stored: " . number_format($btkl->tarif_per_jam, 0, ',', '.') . "\n";
    echo "  Kapasitas: {$btkl->kapasitas_per_jam}\n";
    
    if ($btkl->jabatan) {
        // Count pegawai by user_id AND jabatan name/id
        $jumlahPegawai = Pegawai::where('user_id', $userId)
            ->where(function($q) use ($btkl) {
                $q->where('jabatan_id', $btkl->jabatan->id)
                  ->orWhere('jabatan', $btkl->jabatan->nama);
            })
            ->count();
        
        $tarifPerJam = $btkl->jabatan->tarif_per_jam ?? $btkl->jabatan->tarif ?? 0;
        $tarifBtkl = $jumlahPegawai * $tarifPerJam;
        $biayaPerProduk = $btkl->kapasitas_per_jam > 0 ? round($tarifBtkl / $btkl->kapasitas_per_jam) : 0;
        
        echo "  Jumlah Pegawai (user_id={$userId}): {$jumlahPegawai}\n";
        echo "  Tarif/Jam Jabatan: " . number_format($tarifPerJam, 0, ',', '.') . "\n";
        echo "  Tarif BTKL seharusnya: " . number_format($tarifBtkl, 0, ',', '.') . "\n";
        echo "  Biaya/Produk seharusnya: " . number_format($biayaPerProduk, 0, ',', '.') . "\n";
        
        if ($btkl->tarif_per_jam != $tarifBtkl) {
            // Update tarif_per_jam
            $btkl->tarif_per_jam = $tarifBtkl;
            $btkl->save();
            echo "  ✅ UPDATED tarif_per_jam to: " . number_format($tarifBtkl, 0, ',', '.') . "\n";
            
            // Also update proses_produksis
            $proses = ProsesProduksi::where('btkl_id', $btkl->id)->first();
            if ($proses) {
                $proses->tarif_btkl = $tarifBtkl;
                $proses->save();
                echo "  ✅ UPDATED proses_produksis tarif_btkl\n";
            }
        } else {
            echo "  ✓ Tarif already correct\n";
        }
    } else {
        echo "  ⚠️  No jabatan linked!\n";
    }
    echo "\n";
}

echo "=== DONE ===\n";
echo "Refresh halaman HPP create untuk melihat data yang sudah diperbaiki.\n";
