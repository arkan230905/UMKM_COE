<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PRESENSI CONTROLLER OUTPUT ===\n\n";

// Simulate controller logic
$presensis = \App\Models\Presensi::with('pegawai')
    ->orderBy('tgl_presensi', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(10);

// Transform seperti di controller
$presensis->getCollection()->transform(function ($presensi) {
    if ($presensi->pegawai) {
        $presensi->pegawai->nama_display = $presensi->pegawai->nama ?: $presensi->pegawai->nomor_induk_pegawai;
    }
    return $presensi;
});

echo "Total presensi: " . $presensis->count() . "\n\n";

foreach ($presensis as $presensi) {
    echo "Presensi ID: {$presensi->id}\n";
    echo "Pegawai NIP: {$presensi->pegawai->nomor_induk_pegawai}\n";
    echo "Pegawai Nama: {$presensi->pegawai->nama}\n";
    echo "Nama Display: {$presensi->pegawai->nama_display}\n";
    
    // Simulate blade output
    $output = $presensi->pegawai->nama_display ?? $presensi->pegawai->nama;
    echo "Yang akan tampil di blade: '{$output}'\n";
    echo "---\n";
}

// Generate HTML preview
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Preview Presensi</title>
    <style>
        body { background: #1b1b28; color: white; font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #333; text-align: left; }
        th { background: #6c63ff; }
        .nama { color: #ffffff; font-weight: 600; font-size: 15px; }
        .nip { color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Preview Nama Pegawai di Presensi</h1>
    <table>
        <tr>
            <th>NAMA PEGAWAI</th>
            <th>TANGGAL</th>
            <th>STATUS</th>
        </tr>';

foreach ($presensis as $presensi) {
    $nama = $presensi->pegawai->nama_display ?? $presensi->pegawai->nama;
    $nip = $presensi->pegawai->nomor_induk_pegawai;
    $tanggal = \Carbon\Carbon::parse($presensi->tgl_presensi)->isoFormat('dddd, D MMMM YYYY');
    
    $html .= "<tr>
        <td>
            <div class='nama'>{$nama}</div>
            <div class='nip'>{$nip}</div>
        </td>
        <td>{$tanggal}</td>
        <td><span style='background: #28a745; padding: 4px 8px; border-radius: 4px;'>{$presensi->status}</span></td>
    </tr>";
}

$html .= '</table></body></html>';

file_put_contents('preview_presensi.html', $html);

echo "\n✅ HTML preview created: preview_presensi.html\n";
echo "Buka file ini di browser untuk melihat preview!\n";
