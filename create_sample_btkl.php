<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATE SAMPLE BTKL ===\n";

$jabatan = \App\Models\Jabatan::where('nama', 'Penggorengan')->first();
if ($jabatan) {
    $btkl = \App\Models\Btkl::create([
        'kode_proses' => 'PROC-001',
        'jabatan_id' => $jabatan->id,
        'tarif_per_jam' => 15000,
        'satuan' => 'Jam',
        'kapasitas_per_jam' => 100,
        'deskripsi_proses' => 'Proses penggorengan produk',
        'is_active' => true
    ]);
    echo "BTKL created: " . $btkl->kode_proses . " - " . $btkl->jabatan->nama . "\n";
    echo "Employee count: " . $btkl->jabatan->pegawais->count() . "\n";
} else {
    echo "Jabatan Penggorengan not found\n";
}

echo "\n=== TEST CONTROLLER AGAIN ===\n";
$controller = new \App\Http\Controllers\MasterData\BtklController();
$result = $controller->index();
$data = $result->getData();
echo "BTKL count: " . $data['btkls']->count() . "\n";

if ($data['btkls']->count() > 0) {
    $btkl = $data['btkls']->first();
    echo "Sample BTKL:\n";
    echo "- Kode: " . $btkl->kode_proses . "\n";
    echo "- Jabatan: " . ($btkl->jabatan->nama ?? 'No jabatan') . "\n";
    echo "- Employee count: " . ($btkl->jabatan->pegawais->count() ?? 0) . "\n";
}
