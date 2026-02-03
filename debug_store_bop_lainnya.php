<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BopLainnya;
use App\Models\Coa;

echo "=== DEBUG: Test Store BOP Lainnya ===" . PHP_EOL;

// Simulate data yang akan disimpan
$testData = [
    'kode_akun' => '503',
    'nama_akun' => 'Beban Sewa',
    'budget' => 5000000,
    'kuantitas_per_jam' => 1,
    'aktual' => 0,
    'periode' => '2026-02',
    'metode_pembebanan' => 'jam_mesin',
    'keterangan' => 'Test penyimpanan BOP Lainnya',
    'is_active' => true
];

echo "Data yang akan disimpan:" . PHP_EOL;
foreach ($testData as $key => $value) {
    echo "- {$key}: {$value}" . PHP_EOL;
}

echo PHP_EOL . "=== Cek COA ===" . PHP_EOL;
$coa = Coa::where('kode_akun', $testData['kode_akun'])->first();
if ($coa) {
    echo "✅ COA ditemukan: {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
} else {
    echo "❌ COA tidak ditemukan untuk kode: {$testData['kode_akun']}" . PHP_EOL;
}

echo PHP_EOL . "=== Cek apakah kode akun dimulai dengan 5 ===" . PHP_EOL;
if (str_starts_with($testData['kode_akun'], '5')) {
    echo "✅ Kode akun valid (dimulai dengan 5)" . PHP_EOL;
} else {
    echo "❌ Kode akun tidak valid (harus dimulai dengan 5)" . PHP_EOL;
}

echo PHP_EOL . "=== Test Validasi ===" . PHP_EOL;
$validator = validator($testData, [
    'kode_akun' => 'required|string|exists:coas,kode_akun',
    'budget' => 'required|numeric|min:0',
    'kuantitas_per_jam' => 'required|integer|min:1',
    'periode' => 'required|string',
    'keterangan' => 'nullable|string'
]);

if ($validator->fails()) {
    echo "❌ Validasi gagal:" . PHP_EOL;
    foreach ($validator->errors()->all() as $error) {
        echo "- {$error}" . PHP_EOL;
    }
} else {
    echo "✅ Validasi berhasil" . PHP_EOL;
}

echo PHP_EOL . "=== Test Penyimpanan ===" . PHP_EOL;
try {
    $bopLainnya = BopLainnya::create($testData);
    echo "✅ Berhasil menyimpan BOP Lainnya dengan ID: {$bopLainnya->id}" . PHP_EOL;
    
    // Hapus data test
    $bopLainnya->delete();
    echo "✅ Data test berhasil dihapus" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Gagal menyimpan: {$e->getMessage()}" . PHP_EOL;
    echo "Error details: {$e->getTraceAsString()}" . PHP_EOL;
}
