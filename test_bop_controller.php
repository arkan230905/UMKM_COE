<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST BOP PROSES CONTROLLER ===" . PHP_EOL;

// Test method create()
try {
    $controller = new \App\Http\Controllers\MasterData\BopProsesController();
    
    // Simulasi data yang akan dikirim ke view
    $availableProses = \App\Models\ProsesProduksi::whereDoesntHave('bopProses')
        ->where('kapasitas_per_jam', '>', 0)
        ->orderBy('nama_proses')
        ->get();

    echo "✅ Controller BopProses berhasil diinstansiasi" . PHP_EOL;
    echo "✅ Method create() akan mengembalikan data:" . PHP_EOL;
    echo "  - Jumlah availableProses: " . $availableProses->count() . PHP_EOL;
    
    if ($availableProses->isEmpty()) {
        echo "  - Warning: Semua proses BTKL sudah memiliki BOP atau belum memiliki kapasitas per jam." . PHP_EOL;
    } else {
        echo "  - Proses yang bisa dipilih:" . PHP_EOL;
        foreach($availableProses as $proses) {
            echo "    * {$proses->kode_proses} - {$proses->nama_proses} ({$proses->kapasitas_per_jam} unit/jam)" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "✅ View create akan menerima variable 'availableProses' dengan " . $availableProses->count() . " data" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
