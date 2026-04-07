<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing process name typo...\n";

$proses = \App\Models\ProduksiProses::where('produksi_id', 8)->where('nama_proses', 'Perbumbuan')->first();

if ($proses) {
    $proses->update(['nama_proses' => 'Pembumbuan']);
    echo "✅ Fixed process name: 'Perbumbuan' → 'Pembumbuan'\n";
} else {
    echo "❌ Process not found\n";
}

echo "🎉 Done!\n";