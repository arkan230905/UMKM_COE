<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProsesProduksi;
use App\Models\Jabatan;
use App\Models\Pegawai;

echo "=== FIX PROSES PRODUKSI BTKL TARIF ===\n\n";

$userId = 4;

// Get all proses produksi with jabatan
$proses = ProsesProduksi::with('jabatan')->get();
echo "Total ProsesProduksi: " . $proses->count() . "\n\n";

foreach ($proses as $p) {
    echo "Proses: {$p->kode_proses} - {$p->nama_proses}\n";
    echo "  Jabatan: " . ($p->jabatan->nama ?? 'NULL') . "\n";
    echo "  Tarif stored: " . number_format($p->tarif_btkl ?? 0, 0, ',', '.') . "\n";
    echo "  Kapasitas: " . ($p->kapasitas_per_jam ?? 0) . "\n";
    
    if ($p->jabatan) {
        $jumlahPegawai = Pegawai::where('user_id', $userId)
            ->where(function($q) use ($p) {
                $q->where('jabatan_id', $p->jabatan->id)
                  ->orWhere('jabatan', $p->jabatan->nama);
            })->count();
        
        $tarifPerJam = $p->jabatan->tarif_per_jam ?? $p->jabatan->tarif ?? 0;
        $tarifBtkl = $jumlahPegawai * $tarifPerJam;
        $kapasitas = $p->kapasitas_per_jam ?? 0;
        $biayaPerProduk = $kapasitas > 0 ? round($tarifBtkl / $kapasitas) : 0;
        
        echo "  Jumlah Pegawai: {$jumlahPegawai}\n";
        echo "  Tarif/Jam Jabatan: " . number_format($tarifPerJam, 0, ',', '.') . "\n";
        echo "  Tarif BTKL seharusnya: " . number_format($tarifBtkl, 0, ',', '.') . "\n";
        echo "  Biaya/Produk seharusnya: Rp " . number_format($biayaPerProduk, 0, ',', '.') . "\n";
        
        if (($p->tarif_btkl ?? 0) != $tarifBtkl) {
            $p->tarif_btkl = $tarifBtkl;
            $p->save();
            echo "  ✅ UPDATED tarif_btkl to: " . number_format($tarifBtkl, 0, ',', '.') . "\n";
        } else {
            echo "  ✓ Tarif already correct\n";
        }
    }
    echo "\n";
}

echo "=== SELESAI ===\n";
