<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Btkl;
use App\Models\ProsesProduksi;

echo "=== Sinkronisasi BTKL ke ProsesProduksi ===" . PHP_EOL;

$btkls = Btkl::all();
$sinkronisasi = 0;
$skip = 0;

foreach ($btkls as $btkl) {
    // Cek apakah sudah ada di ProsesProduksi
    $existing = ProsesProduksi::where('btkl_id', $btkl->id)->first();
    
    if ($existing) {
        echo "- SKIP: {$btkl->kode_proses} sudah ada di ProsesProduksi (ID: {$existing->id})" . PHP_EOL;
        $skip++;
    } else {
        // Buat baru
        $proses = ProsesProduksi::create([
            'kode_proses' => $btkl->kode_proses,
            'nama_proses' => $btkl->nama_btkl,
            'deskripsi' => $btkl->deskripsi_proses,
            'tarif_btkl' => $btkl->tarif_per_jam,
            'satuan_btkl' => $btkl->satuan,
            'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
            'btkl_id' => $btkl->id,
        ]);
        
        echo "- CREATE: {$btkl->kode_proses} -> ProsesProduksi ID: {$proses->id}" . PHP_EOL;
        $sinkronisasi++;
    }
}

echo PHP_EOL . "=== Hasil Sinkronisasi ===" . PHP_EOL;
echo "Total BTKL: " . $btkls->count() . PHP_EOL;
echo "Dibuat baru: " . $sinkronisasi . PHP_EOL;
echo "Dilewati (sudah ada): " . $skip . PHP_EOL;
