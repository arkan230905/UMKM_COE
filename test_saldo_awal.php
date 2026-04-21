<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Saldo Awal Fix ===" . PHP_EOL;

// Test specific COA codes
$testCoas = ['1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156'];

foreach ($testCoas as $kodeAkun) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . ":" . PHP_EOL;
    
    // Test bahan baku
    if (in_array($kodeAkun, ['1141', '1142', '1143'])) {
        $saldoAwal = DB::table('bahan_bakus')
            ->where('coa_persediaan_id', $kodeAkun)
            ->where('saldo_awal', '>', 0)
            ->sum(DB::raw('saldo_awal * harga_satuan'));
        
        echo "  Bahan Baku - COA " . $kodeAkun . ": Rp " . number_format($saldoAwal, 0) . PHP_EOL;
    }
    
    // Test bahan pendukung
    if (in_array($kodeAkun, ['1152', '1153', '1154', '1155', '1156'])) {
        $saldoAwal = DB::table('bahan_pendukungs')
            ->where('coa_persediaan_id', $kodeAkun)
            ->where('saldo_awal', '>', 0)
            ->sum(DB::raw('saldo_awal * harga_satuan'));
        
        echo "  Bahan Pendukung - COA " . $kodeAkun . ": Rp " . number_format($saldoAwal, 0) . PHP_EOL;
    }
}

echo PHP_EOL . "=== Parent Account Tests ===" . PHP_EOL;

// Test parent COA 114 (all bahan baku)
$totalBahanBaku = DB::table('bahan_bakus')
    ->where('saldo_awal', '>', 0)
    ->sum(DB::raw('saldo_awal * harga_satuan'));
echo "COA 114 (All Bahan Baku): Rp " . number_format($totalBahanBaku, 0) . PHP_EOL;

// Test parent COA 115 (all bahan pendukung)
$totalBahanPendukung = DB::table('bahan_pendukungs')
    ->where('saldo_awal', '>', 0)
    ->sum(DB::raw('saldo_awal * harga_satuan'));
echo "COA 115 (All Bahan Pendukung): Rp " . number_format($totalBahanPendukung, 0) . PHP_EOL;
