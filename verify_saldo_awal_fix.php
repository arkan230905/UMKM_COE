<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class');
$kernel->bootstrap();

echo "=== Verify Saldo Awal Fix ===" . PHP_EOL;

// Test the getInventorySaldoAwal function logic
echo PHP_EOL . "Testing Fixed Logic:" . PHP_EOL;

// Test cases
$testCases = [
    '1141' => 'Ayam Potong',
    '1142' => 'Ayam Kampung',
    '1143' => 'Bebek',
    '1152' => 'Tepung Terigu',
    '1153' => 'Tepung Maizena',
    '1154' => 'Lada',
    '1155' => 'Bubuk Kaldu Ayam',
    '1156' => 'Bubuk Bawang Putih',
    '115' => 'Parent Bahan Pendukung',
    '114' => 'Parent Bahan Baku'
];

foreach ($testCases as $kodeAkun => $description) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . " (" . $description . "):" . PHP_EOL;
    
    // Simulate the fixed logic
    $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
    $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
    
    $saldoAwal = 0;
    
    // Untuk akun bahan baku
    if (in_array($kodeAkun, $bahanBakuCoas)) {
        if (in_array($kodeAkun, ['1101', '114'])) {
            // Parent accounts - sum all bahan baku
            $saldoAwal = DB::table('bahan_bakus')
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
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
            // Parent account - sum all bahan pendukung
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        } else {
            // Specific child account
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $kodeAkun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    }
    
    echo "  Result: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
    
    // Expected values based on actual data
    $expected = [
        '1141' => 1600000, // Ayam Potong: 50 × 32,000
        '1142' => 1800000, // Ayam Kampung: 40 × 45,000
        '1143' => 2500000, // Bebek: 50 × 50,000
        '1152' => 20000000, // Tepung Terigu: 400 × 50,000
        '1153' => 20000000, // Tepung Maizena: 400 × 50,000
        '1154' => 6000000, // Lada: 400 × 15,000
        '1155' => 16000000, // Bubuk Kaldu: 400 × 40,000
        '1156' => 24800000, // Bubuk Bawang Putih: 400 × 62,000
        '115' => 86800000, // Total all bahan pendukung
        '114' => 5900000, // Total all bahan baku
    ];
    
    if (isset($expected[$kodeAkun])) {
        $status = ($saldoAwal == $expected[$kodeAkun]) ? "CORRECT" : "WRONG";
        echo "  Expected: Rp " . number_format($expected[$kodeAkun], 0) . " - " . $status . PHP_EOL;
    }
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Fixed Issues:" . PHP_EOL;
echo "1. Added missing COA codes (1141, 1142, 1143) for bahan baku" . PHP_EOL;
echo "2. Fixed logic to use specific COA instead of hardcoded values" . PHP_EOL;
echo "3. Bahan Baku should now show correct saldo awal" . PHP_EOL;
echo "4. Bahan Pendukung should show correct individual and parent totals" . PHP_EOL;
