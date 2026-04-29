<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Penggajian Tunjangan Data...\n\n";

// Get all pegawais to test
$pegawais = \App\Models\Pegawai::with('jabatanRelasi')->get();

echo "Found {$pegawais->count()} pegawais\n\n";

foreach ($pegawais->take(3) as $pegawai) {
    echo "Pegawai: {$pegawai->nama}\n";
    echo "ID: {$pegawai->id}\n";
    echo "Jabatan: " . ($pegawai->jabatan_nama ?? 'Tidak ada') . "\n";
    
    // Check jabatan relation
    $jabatan = $pegawai->jabatanRelasi;
    if ($jabatan) {
        echo "Jabatan Relation Found:\n";
        echo "  Nama Jabatan: {$jabatan->nama}\n";
        echo "  Gaji Pokok: " . number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.') . "\n";
        echo "  Tarif per Jam: " . number_format($jabatan->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
        echo "  Tunjangan: " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
        echo "  Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "  Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        echo "  Asuransi: " . number_format($jabatan->asuransi ?? 0, 0, ',', '.') . "\n";
    } else {
        echo "No Jabatan Relation Found\n";
        echo "  Gaji Pokok (pegawai): " . number_format($pegawai->gaji_pokok ?? 0, 0, ',', '.') . "\n";
        echo "  Tarif per Jam (pegawai): " . number_format($pegawai->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
        echo "  Tunjangan Jabatan (pegawai): " . number_format($pegawai->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
        echo "  Tunjangan Transport (pegawai): " . number_format($pegawai->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "  Tunjangan Konsumsi (pegawai): " . number_format($pegawai->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        echo "  Asuransi (pegawai): " . number_format($pegawai->asuransi ?? 0, 0, ',', '.') . "\n";
    }
    
    // Simulate the API response logic
    echo "\nAPI Response Simulation:\n";
    if ($jabatan) {
        $gajiPokok = $jabatan->gaji_pokok ?? $pegawai->gaji_pokok ?? 0;
        $tarif = $jabatan->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0;
        $tunjanganJabatan = $jabatan->tunjangan ?? 0;
        $tunjanganTransport = $jabatan->tunjangan_transport ?? 0;
        $tunjanganKonsumsi = $jabatan->tunjangan_konsumsi ?? 0;
        $asuransi = $jabatan->asuransi ?? 0;
    } else {
        $gajiPokok = $pegawai->gaji_pokok ?? 0;
        $tarif = $pegawai->tarif_per_jam ?? 0;
        $tunjanganJabatan = $pegawai->tunjangan_jabatan ?? 0;
        $tunjanganTransport = $pegawai->tunjangan_transport ?? 0;
        $tunjanganKonsumsi = $pegawai->tunjangan_konsumsi ?? 0;
        $asuransi = $pegawai->asuransi ?? 0;
    }
    
    $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
    
    echo "  jenis: " . strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl') . "\n";
    echo "  gaji_pokok: {$gajiPokok}\n";
    echo "  tarif: {$tarif}\n";
    echo "  tunjangan_jabatan: {$tunjanganJabatan}\n";
    echo "  tunjangan_transport: {$tunjanganTransport}\n";
    echo "  tunjangan_konsumsi: {$tunjanganKonsumsi}\n";
    echo "  asuransi: {$asuransi}\n";
    echo "  total_tunjangan: {$totalTunjangan}\n";
    
    echo "\nExpected Display:\n";
    echo "  Tunjangan Jabatan: Rp " . number_format($tunjanganJabatan, 0, ',', '.') . "\n";
    echo "  Tunjangan Transport: Rp " . number_format($tunjanganTransport, 0, ',', '.') . "\n";
    echo "  Tunjangan Konsumsi: Rp " . number_format($tunjanganKonsumsi, 0, ',', '.') . "\n";
    echo "  Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "Penggajian tunjangan test completed!\n";
