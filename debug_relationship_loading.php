<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging relationship loading for pembelian details...\n\n";

$pembelian = \App\Models\Pembelian::find(10);
if (!$pembelian) {
    echo "Purchase ID 10 not found!\n";
    exit;
}

echo "Loading details with different methods...\n\n";

// Method 1: Default loading
echo "=== METHOD 1: Default loading ===\n";
$details1 = $pembelian->details;
foreach ($details1 as $detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "  bahanBaku: " . ($detail->bahanBaku ? 'LOADED' : 'NULL') . "\n";
    echo "  bahanPendukung: " . ($detail->bahanPendukung ? 'LOADED' : 'NULL') . "\n";
    
    if ($detail->bahanBaku) {
        echo "  Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
        echo "  COA Pembelian ID: " . ($detail->bahanBaku->coa_pembelian_id ?? 'NULL') . "\n";
        echo "  coaPembelian relation: " . ($detail->bahanBaku->coaPembelian ? 'LOADED' : 'NULL') . "\n";
    }
    echo "\n";
}

// Method 2: Eager loading
echo "=== METHOD 2: Eager loading ===\n";
$pembelian2 = \App\Models\Pembelian::with([
    'details.bahanBaku.coaPembelian',
    'details.bahanPendukung.coaPembelian'
])->find(10);

foreach ($pembelian2->details as $detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "  bahanBaku: " . ($detail->bahanBaku ? 'LOADED' : 'NULL') . "\n";
    echo "  bahanPendukung: " . ($detail->bahanPendukung ? 'LOADED' : 'NULL') . "\n";
    
    if ($detail->bahanBaku) {
        echo "  Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
        echo "  COA Pembelian ID: " . ($detail->bahanBaku->coa_pembelian_id ?? 'NULL') . "\n";
        echo "  coaPembelian relation: " . ($detail->bahanBaku->coaPembelian ? 'LOADED' : 'NULL') . "\n";
        if ($detail->bahanBaku->coaPembelian) {
            echo "  COA Pembelian: {$detail->bahanBaku->coaPembelian->nama_akun} ({$detail->bahanBaku->coaPembelian->kode_akun})\n";
        }
    }
    echo "\n";
}

// Method 3: Manual query
echo "=== METHOD 3: Manual query ===\n";
$detail = \App\Models\PembelianDetail::where('pembelian_id', 10)
    ->with(['bahanBaku.coaPembelian', 'bahanPendukung.coaPembelian'])
    ->first();

if ($detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "  bahan_baku_id: " . ($detail->bahan_baku_id ?? 'NULL') . "\n";
    echo "  bahan_pendukung_id: " . ($detail->bahan_pendukung_id ?? 'NULL') . "\n";
    
    if ($detail->bahan_baku_id) {
        $bahanBaku = \App\Models\BahanBaku::with('coaPembelian')->find($detail->bahan_baku_id);
        echo "  Bahan Baku (manual): {$bahanBaku->nama_bahan}\n";
        echo "  COA Pembelian ID (manual): " . ($bahanBaku->coa_pembelian_id ?? 'NULL') . "\n";
        echo "  coaPembelian relation (manual): " . ($bahanBaku->coaPembelian ? 'LOADED' : 'NULL') . "\n";
        if ($bahanBaku->coaPembelian) {
            echo "  COA Pembelian (manual): {$bahanBaku->coaPembelian->nama_akun} ({$bahanBaku->coaPembelian->kode_akun})\n";
        }
    }
}

echo "\nDone.\n";
