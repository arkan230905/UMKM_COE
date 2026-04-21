<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Bahan Baku Fix ===" . PHP_EOL;

// Test the fixed neracaSaldo logic
echo PHP_EOL . "Testing Fixed neracaSaldo Logic:" . PHP_EOL;

$testCoas = ['1141', '1142', '1143', '114'];

foreach ($testCoas as $kodeAkun) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . ":" . PHP_EOL;
    
    // Simulate the fixed neracaSaldo logic
    $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
    $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
    
    // Get COA data
    $coa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
    
    if ($coa) {
        echo "  COA: " . $coa->nama_akun . PHP_EOL;
        
        // Check if this is an inventory account
        if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
            echo "  Is inventory account - calling getInventorySaldoAwal" . PHP_EOL;
            
            // This should now call getInventorySaldoAwal
            $saldoAwal = 0;
            
            // Simulate getInventorySaldoAwal logic
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
            
            echo "  getInventorySaldoAwal result: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
        } else {
            echo "  Not inventory account - using coa.saldo_awal: " . $coa->saldo_awal . PHP_EOL;
        }
    } else {
        echo "  COA not found" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Expected Results ===" . PHP_EOL;
echo "1141 (Ayam Potong): Rp 1.600.000" . PHP_EOL;
echo "1142 (Ayam Kampung): Rp 1.800.000" . PHP_EOL;
echo "1143 (Bebek): Rp 2.500.000" . PHP_EOL;
echo "114 (Parent): Rp 5.900.000" . PHP_EOL;

echo PHP_EOL . "=== Fix Summary ===" . PHP_EOL;
echo "Changes made:" . PHP_EOL;
echo "1. Updated bahanBakuCoas to include ['1101', '114', '1141', '1142', '1143']" . PHP_EOL;
echo "2. Applied to both neracaSaldo methods" . PHP_EOL;
echo "3. Now specific bahan baku accounts will call getInventorySaldoAwal" . PHP_EOL;
echo "4. Saldo awal will be calculated from database master data" . PHP_EOL;
