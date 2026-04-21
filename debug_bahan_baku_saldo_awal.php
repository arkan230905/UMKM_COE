<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Bahan Baku Saldo Awal Issue ===" . PHP_EOL;

// Check what the neracaSaldo method is calling
echo PHP_EOL . "=== Testing getInventorySaldoAwal for Specific COA ===" . PHP_EOL;

$testCoas = ['1141', '1142', '1143', '114'];

foreach ($testCoas as $kodeAkun) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . ":" . PHP_EOL;
    
    // Test the getInventorySaldoAwal function directly
    try {
        // Simulate the function logic
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
        
        echo "  Result: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
        
        // Check what data exists for this COA
        if (in_array($kodeAkun, ['1141', '1142', '1143'])) {
            $items = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $kodeAkun)
                ->get();
            
            echo "  Items with COA " . $kodeAkun . ":" . PHP_EOL;
            foreach ($items as $item) {
                $total = $item->saldo_awal * $item->harga_satuan;
                echo "    " . $item->nama_bahan . ": " . $item->saldo_awal . " × " . $item->harga_satuan . " = " . $total . PHP_EOL;
            }
        }
        
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . PHP_EOL;
    }
}

// Check what the neracaSaldo method actually does
echo PHP_EOL . "=== Check neracaSaldo Method Logic ===" . PHP_EOL;

// Get the actual COA data
$coas = DB::table('coas')
    ->whereIn('kode_akun', ['1141', '1142', '1143', '114'])
    ->orderBy('kode_akun')
    ->get();

echo "COA Data in Database:" . PHP_EOL;
foreach ($coas as $coa) {
    echo "  " . $coa->kode_akun . " - " . $coa->nama_akun . PHP_EOL;
    echo "    Saldo Awal: " . $coa->saldo_awal . PHP_EOL;
    echo "    Tipe Akun: " . $coa->tipe_akun . PHP_EOL;
    echo "    Kategori: " . $coa->kategori_akun . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check if the issue is in the neracaSaldo method itself
echo PHP_EOL . "=== Check neracaSaldo Processing ===" . PHP_EOL;

// Simulate what neracaSaldo does
$allCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->orderByRaw('kode_akun asc, RPAD(kode_akun, 10, "0"), LENGTH(kode_akun)')
    ->get();

foreach ($allCoas as $coa) {
    if (in_array($coa->kode_akun, ['1141', '1142', '1143', '114'])) {
        echo "Processing COA " . $coa->kode_akun . ":" . PHP_EOL;
        
        // Check if this is an inventory account
        if (in_array($coa->kategori_akun, ['Persediaan Bahan Baku', 'Persediaan Bahan Pendukung', 'Persediaan Barang Jadi'])) {
            echo "  Is inventory account - calling getInventorySaldoAwal" . PHP_EOL;
            
            // This should call getInventorySaldoAwal
            $saldoAwal = $this->getInventorySaldoAwal($coa->kode_akun);
            echo "  getInventorySaldoAwal result: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
        } else {
            echo "  Not inventory account - using coa.saldo_awal: " . $coa->saldo_awal . PHP_EOL;
        }
        echo "---" . PHP_EOL;
    }
}
