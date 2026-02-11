<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Btkl;
use App\Models\Jabatan;

echo "=== CEK DATA BTKL PERBUMBUAN ===\n\n";

// Cek data BTKL yang ada
$btklData = Btkl::with('jabatan')->get();

echo "Total data BTKL: " . $btklData->count() . "\n\n";

foreach ($btklData as $btkl) {
    echo "ID: {$btkl->id}\n";
    echo "  Kode Proses: {$btkl->kode_proses}\n";
    echo "  Nama BTKL: {$btkl->nama_btkl}\n";
    echo "  Jabatan: " . ($btkl->jabatan->nama ?? 'N/A') . "\n";
    echo "  Tarif per Jam: Rp " . number_format($btkl->tarif_per_jam, 0, ',', '.') . "\n";
    echo "  Kapasitas per Jam: " . ($btkl->kapasitas_per_jam ?? 0) . " pcs\n";
    echo "  Status: " . ($btkl->is_active ? 'Aktif' : 'Tidak Aktif') . "\n";
    echo "  ---\n";
}

// Cek khusus untuk Perbumbuan
echo "\n=== FOKUS PADA PERBUMBUAN ===\n";
$perbumbuanBtkl = Btkl::where('nama_btkl', 'like', '%perbumbuan%')
    ->orWhereHas('jabatan', function($q) {
        $q->where('nama', 'like', '%perbumbuan%');
    })
    ->get();

if ($perbumbuanBtkl->count() > 0) {
    foreach ($perbumbuanBtkl as $btkl) {
        echo "BTKL Perbumbuan ditemukan:\n";
        echo "  ID: {$btkl->id}\n";
        echo "  Nama: {$btkl->nama_btkl}\n";
        echo "  Kapasitas: " . ($btkl->kapasitas_per_jam ?? 0) . " pcs/jam\n";
        echo "  Tarif: Rp " . number_format($btkl->tarif_per_jam, 0, ',', '.') . "/jam\n";
    }
} else {
    echo "Tidak ada data BTKL untuk Perbumbuan\n";
}

echo "\n✅ SELESAI\n";