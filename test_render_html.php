<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST RENDER HTML ===\n\n";

$presensis = \App\Models\Presensi::with('pegawai')->take(3)->get();

foreach ($presensis as $presensi) {
    // Simulasi output HTML yang akan di-render
    $html = '<td class="fw-bold text-white" data-update="v2">' . "\n";
    $html .= '    <i class="bi bi-person-circle fs-5 me-2"></i>' . "\n";
    $html .= '    <span style="color: #ffffff !important; font-weight: bold !important; font-size: 16px !important; display: inline-block !important;">' . "\n";
    $html .= '        ' . $presensi->pegawai->nama . "\n";
    $html .= '    </span>' . "\n";
    $html .= '    <br>' . "\n";
    $html .= '    <small class="text-muted" style="font-size: 12px;">' . $presensi->pegawai->nomor_induk_pegawai . '</small>' . "\n";
    $html .= '</td>' . "\n";
    
    echo "Presensi ID: {$presensi->id}\n";
    echo "HTML Output:\n";
    echo $html;
    echo "\n---\n\n";
}

// Save to file
file_put_contents('test_output.html', '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Output</title>
    <style>
        body { background: #1b1b28; color: white; font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 15px; border: 1px solid #333; }
        .text-muted { color: #999; }
    </style>
</head>
<body>
    <h1>Test Render Nama Pegawai</h1>
    <table>
        <tr>
            <th>NAMA PEGAWAI</th>
        </tr>
');

foreach ($presensis as $presensi) {
    $html = '<tr><td class="fw-bold text-white" data-update="v2">';
    $html .= '<i class="bi bi-person-circle fs-5 me-2"></i>';
    $html .= '<span style="color: #ffffff !important; font-weight: bold !important; font-size: 16px !important; display: inline-block !important;">';
    $html .= $presensi->pegawai->nama;
    $html .= '</span>';
    $html .= '<br>';
    $html .= '<small class="text-muted" style="font-size: 12px;">' . $presensi->pegawai->nomor_induk_pegawai . '</small>';
    $html .= '</td></tr>';
    
    file_put_contents('test_output.html', $html . "\n", FILE_APPEND);
}

file_put_contents('test_output.html', '</table></body></html>', FILE_APPEND);

echo "\n✅ HTML test file created: test_output.html\n";
echo "Buka file ini di browser untuk melihat hasil render yang benar.\n";
