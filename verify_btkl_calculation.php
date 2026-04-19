<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFIKASI PERHITUNGAN BTKL ===\n\n";

// Simulasi data yang akan dikirim ke view BTKL create/edit
$jabatanBtkl = \App\Models\Jabatan::where('kategori', 'btkl')
    ->with('pegawais')
    ->orderBy('nama')
    ->get();

echo "Data yang akan dikirim ke view:\n";
echo str_repeat("=", 80) . "\n\n";

// Simulasi employeeData yang di-map di controller
$employeeData = $jabatanBtkl->map(function($jabatan) {
    return [
        'id' => $jabatan->id,
        'nama' => $jabatan->nama,
        'pegawai_count' => $jabatan->pegawais->count(),
        'tarif' => $jabatan->tarif ?? 0
    ];
});

echo "employeeData (yang akan digunakan JavaScript):\n";
echo json_encode($employeeData, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "PERHITUNGAN TARIF BTKL:\n";
echo str_repeat("-", 80) . "\n\n";

foreach ($employeeData as $data) {
    $tarifBtkl = $data['tarif'] * $data['pegawai_count'];
    
    echo "Jabatan: {$data['nama']}\n";
    echo "  Tarif per Jam: Rp " . number_format($data['tarif'], 0, ',', '.') . "\n";
    echo "  Jumlah Pegawai: {$data['pegawai_count']}\n";
    echo "  Tarif BTKL: Rp " . number_format($data['tarif'], 0, ',', '.') . " × {$data['pegawai_count']} = Rp " . number_format($tarifBtkl, 0, ',', '.') . "\n";
    echo "\n";
}

echo str_repeat("=", 80) . "\n\n";

echo "CONTOH PERHITUNGAN BIAYA PER PRODUK:\n";
echo str_repeat("-", 80) . "\n\n";

// Contoh dengan kapasitas 100 pcs/jam
$kapasitas = 100;

foreach ($employeeData as $data) {
    $tarifBtkl = $data['tarif'] * $data['pegawai_count'];
    $biayaPerProduk = $kapasitas > 0 ? $tarifBtkl / $kapasitas : 0;
    
    echo "Jabatan: {$data['nama']}\n";
    echo "  Tarif BTKL: Rp " . number_format($tarifBtkl, 0, ',', '.') . "/jam\n";
    echo "  Kapasitas: {$kapasitas} pcs/jam\n";
    echo "  Biaya per Produk: Rp " . number_format($tarifBtkl, 0, ',', '.') . " ÷ {$kapasitas} = Rp " . number_format($biayaPerProduk, 0, ',', '.') . "/pcs\n";
    echo "\n";
}

echo "=== SELESAI ===\n";
