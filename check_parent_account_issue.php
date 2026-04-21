<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Parent Account Issue ===" . PHP_EOL;

// Current behavior vs expected behavior
echo PHP_EOL . "Current vs Expected Behavior:" . PHP_EOL;
echo "Account 114 (Parent):" . PHP_EOL;
echo "  Current: Rp 5.900.000 (sum of all bahan baku)" . PHP_EOL;
echo "  Expected: Rp 0 (not used directly)" . PHP_EOL;
echo PHP_EOL;
echo "Account 115 (Parent):" . PHP_EOL;
echo "  Current: Rp 86.800.000 (sum of all bahan pendukung)" . PHP_EOL;
echo "  Expected: Rp 0 (not used directly)" . PHP_EOL;

// Check what specific accounts exist
echo PHP_EOL . "=== Specific Accounts That Should Have Saldo ===" . PHP_EOL;

// Bahan Baku specific accounts
$bahanBakuSpecific = DB::table('bahan_bakus')
    ->select('coa_persediaan_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(saldo_awal * harga_satuan) as total'))
    ->where('saldo_awal', '>', 0)
    ->whereIn('coa_persediaan_id', ['1141', '1142', '1143'])
    ->groupBy('coa_persediaan_id')
    ->get();

echo "Bahan Baku Specific Accounts:" . PHP_EOL;
foreach ($bahanBakuSpecific as $item) {
    echo "  COA " . $item->coa_persediaan_id . ": Rp " . number_format($item->total, 0) . PHP_EOL;
}

// Bahan Pendukung specific accounts
$bahanPendukungSpecific = DB::table('bahan_pendukungs')
    ->select('coa_persediaan_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(saldo_awal * harga_satuan) as total'))
    ->where('saldo_awal', '>', 0)
    ->whereIn('coa_persediaan_id', ['1152', '1153', '1154', '1155', '1156'])
    ->groupBy('coa_persediaan_id')
    ->get();

echo PHP_EOL . "Bahan Pendukung Specific Accounts:" . PHP_EOL;
foreach ($bahanPendukungSpecific as $item) {
    echo "  COA " . $item->coa_persediaan_id . ": Rp " . number_format($item->total, 0) . PHP_EOL;
}

echo PHP_EOL . "=== Solution ===" . PHP_EOL;
echo "Need to modify getInventorySaldoAwal to:" . PHP_EOL;
echo "1. Return 0 for parent accounts (114, 115)" . PHP_EOL;
echo "2. Only calculate saldo for specific accounts (1141, 1142, 1143, 1152, etc.)" . PHP_EOL;
echo "3. Parent accounts should not aggregate child accounts" . PHP_EOL;
