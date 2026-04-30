<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h2>Pemeriksaan COA Bahan Pendukung</h2>";
echo "<p>Memeriksa apakah bahan pendukung memiliki COA persediaan yang benar...</p>";

// Get user ID (assuming user ID 1)
$userId = 1;

// Bahan pendukung yang disebutkan user
$bahanPendukungNames = ['Listrik', 'Susu', 'Keju', 'Cup'];

echo "<h3>Status Bahan Pendukung:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th>Nama Bahan</th>";
echo "<th>Kode Bahan</th>";
echo "<th>COA Persediaan ID</th>";
echo "<th>COA Persediaan</th>";
echo "<th>Status</th>";
echo "</tr>";

foreach ($bahanPendukungNames as $namaBahan) {
    $bahan = \App\Models\BahanPendukung::withoutGlobalScopes()
        ->where('user_id', $userId)
        ->where('nama_bahan', 'LIKE', '%' . $namaBahan . '%')
        ->first();
    
    if ($bahan) {
        $coaPersediaan = null;
        if ($bahan->coa_persediaan_id) {
            $coaPersediaan = \App\Models\Coa::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->where('kode_akun', $bahan->coa_persediaan_id)
                ->first();
        }
        
        $status = $coaPersediaan ? '✅ OK' : '❌ COA Persediaan Belum Diset';
        $bgColor = $coaPersediaan ? '#d4edda' : '#f8d7da';
        
        echo "<tr style='background:{$bgColor};'>";
        echo "<td>{$bahan->nama_bahan}</td>";
        echo "<td>{$bahan->kode_bahan}</td>";
        echo "<td>" . ($bahan->coa_persediaan_id ?: '-') . "</td>";
        echo "<td>" . ($coaPersediaan ? "{$coaPersediaan->kode_akun} - {$coaPersediaan->nama_akun}" : '-') . "</td>";
        echo "<td><strong>{$status}</strong></td>";
        echo "</tr>";
    } else {
        echo "<tr style='background:#fff3cd;'>";
        echo "<td colspan='5'>⚠️ Bahan '{$namaBahan}' tidak ditemukan di master data</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Cari semua bahan pendukung yang belum memiliki COA persediaan
echo "<h3>Semua Bahan Pendukung Tanpa COA Persediaan:</h3>";
$bahanTanpaCoa = \App\Models\BahanPendukung::withoutGlobalScopes()
    ->where('user_id', $userId)
    ->whereNull('coa_persediaan_id')
    ->orWhere('coa_persediaan_id', '')
    ->get();

if ($bahanTanpaCoa->count() > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>Kode Bahan</th>";
    echo "<th>Nama Bahan</th>";
    echo "<th>Harga Satuan</th>";
    echo "</tr>";
    
    foreach ($bahanTanpaCoa as $bahan) {
        echo "<tr>";
        echo "<td>{$bahan->kode_bahan}</td>";
        echo "<td>{$bahan->nama_bahan}</td>";
        echo "<td>Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p><strong>Total: {$bahanTanpaCoa->count()} bahan pendukung belum memiliki COA persediaan</strong></p>";
} else {
    echo "<p style='color:green;'>✅ Semua bahan pendukung sudah memiliki COA persediaan</p>";
}

// Cari COA dengan prefix 113 (Pers. Bahan Pendukung)
echo "<h3>COA Persediaan Bahan Pendukung (113):</h3>";
$coaPersediaanBahanPendukung = \App\Models\Coa::withoutGlobalScopes()
    ->where('user_id', $userId)
    ->where('kode_akun', 'LIKE', '113%')
    ->orderBy('kode_akun')
    ->get();

if ($coaPersediaanBahanPendukung->count() > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>Kode Akun</th>";
    echo "<th>Nama Akun</th>";
    echo "<th>Saldo</th>";
    echo "</tr>";
    
    foreach ($coaPersediaanBahanPendukung as $coa) {
        echo "<tr>";
        echo "<td>{$coa->kode_akun}</td>";
        echo "<td>{$coa->nama_akun}</td>";
        echo "<td>Rp " . number_format($coa->saldo ?? 0, 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Tidak ada COA dengan prefix 113</p>";
}

echo "<hr>";
echo "<h3>Rekomendasi:</h3>";
echo "<ol>";
echo "<li>Pastikan setiap bahan pendukung di master data memiliki COA Persediaan yang benar (field <code>coa_persediaan_id</code>)</li>";
echo "<li>COA Persediaan untuk bahan pendukung biasanya menggunakan kode 113 (Pers. Bahan Pendukung) atau 115 (Pers. Bahan Pembantu)</li>";
echo "<li>Setelah COA diset di master data, sistem akan otomatis menggunakan COA tersebut saat posting produksi</li>";
echo "<li>Jika bahan pendukung belum ada COA spesifik, buat COA baru dengan prefix 113 atau 1131, 1132, dst.</li>";
echo "</ol>";
