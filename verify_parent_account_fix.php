<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Parent Account Fix ===" . PHP_EOL;

// Test the fixed getInventorySaldoAwal logic
echo PHP_EOL . "Testing Fixed getInventorySaldoAwal:" . PHP_EOL;

$testCoas = ['114', '1141', '1142', '1143', '115', '1152', '1153', '1154', '1155', '1156'];

foreach ($testCoas as $kodeAkun) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . ":" . PHP_EOL;
    
    // Simulate the fixed getInventorySaldoAwal logic
    $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
    $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
    
    $saldoAwal = 0;
    
    // Untuk akun bahan baku
    if (in_array($kodeAkun, $bahanBakuCoas)) {
        if (in_array($kodeAkun, ['1101', '114'])) {
            // Parent accounts - return 0 (not used directly)
            $saldoAwal = 0;
        } else {
            // Specific child account
            $saldoAwal = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $kodeAkun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    }
    
    // Untuk akun bahan pendukung
    if (in_array($kodeAkun, $bahanPendukungCoas)) {
        if ($kodeAkun === '115') {
            // Parent account - return 0 (not used directly)
            $saldoAwal = 0;
        } else {
            // Specific child account
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $kodeAkun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    }
    
    echo "  Result: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
    
    // Expected results
    $expected = [
        '114' => 0, // Parent - should be 0
        '1141' => 1600000, // Ayam Potong
        '1142' => 1800000, // Ayam Kampung
        '1143' => 2500000, // Bebek
        '115' => 0, // Parent - should be 0
        '1152' => 20000000, // Tepung Terigu
        '1153' => 20000000, // Tepung Maizena
        '1154' => 6000000, // Lada
        '1155' => 16000000, // Bubuk Kaldu
        '1156' => 24800000, // Bubuk Bawang Putih
    ];
    
    if (isset($expected[$kodeAkun])) {
        $status = ($saldoAwal == $expected[$kodeAkun]) ? "CORRECT" : "WRONG";
        echo "  Expected: Rp " . number_format($expected[$kodeAkun], 0) . " - " . $status . PHP_EOL;
    }
}

echo PHP_EOL . "=== Expected Neraca Saldo Results ===" . PHP_EOL;
echo "Parent Accounts (should be 0):" . PHP_EOL;
echo "- 114 Persediaan Bahan Baku: Rp 0" . PHP_EOL;
echo "- 115 Persediaan Bahan Pendukung: Rp 0" . PHP_EOL;
echo PHP_EOL;
echo "Specific Accounts (should show values):" . PHP_EOL;
echo "- 1141 Persediaan Bahan Baku Ayam Potong: Rp 1.600.000" . PHP_EOL;
echo "- 1142 Persediaan Bahan Baku Ayam Kampung: Rp 1.800.000" . PHP_EOL;
echo "- 1143 Persediaan Bahan Baku Bebek: Rp 2.500.000" . PHP_EOL;
echo "- 1152 Persediaan Tepung Terigu: Rp 20.000.000" . PHP_EOL;
echo "- 1153 Persediaan Tepung Maizena: Rp 20.000.000" . PHP_EOL;
echo "- 1154 Persediaan Lada: Rp 6.000.000" . PHP_EOL;
echo "- 1155 Persediaan Bubuk Kaldu: Rp 16.000.000" . PHP_EOL;
echo "- 1156 Persediaan Bubuk Bawang Putih: Rp 24.800.000" . PHP_EOL;
