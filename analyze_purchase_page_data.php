<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Analyze Purchase Page Data ===" . PHP_EOL;

// Check what the user actually sees on pembelian/1 page
echo "Checking what user sees on pembelian/1 page..." . PHP_EOL;

// Get pembelian with details
$pembelian = DB::table('pembelians')->where('id', 1)->first();
$pembelianDetails = DB::table('pembelian_details')
    ->where('pembelian_id', 1)
    ->where('bahan_baku_id', 1)
    ->first();

if (!$pembelianDetails) {
    echo "No purchase details found for Ayam Potong in pembelian/1" . PHP_EOL;
    exit;
}

echo "Purchase Details from pembelian/1:" . PHP_EOL;
echo "Jumlah: " . $pembelianDetails->jumlah . PHP_EOL;
echo "Satuan ID: " . $pembelianDetails->satuan . PHP_EOL;

// Get satuan details
$satuan = DB::table('satuans')->where('id', $pembelianDetails->satuan)->first();
echo "Satuan Name: " . $satuan->nama . PHP_EOL;

// Check if there's any conversion logic in the purchase
echo PHP_EOL . "=== Purchase Conversion Analysis ===" . PHP_EOL;

// The user says: "kilogram itu harusnya 40 kilogram dan potong itu harusnya masuknya 120 potong karena 1 kilogtamnya di pembelian ayam potong itu di konversinya menjadi 1 kg = 3 potong"

echo "User's statement analysis:" . PHP_EOL;
echo "- Expected Kilogram: 40 kg" . PHP_EOL;
echo "- Expected Potong: 120 Potong" . PHP_EOL;
echo "- Conversion ratio: 1 kg = 3 Potong" . PHP_EOL;
echo PHP_EOL;

// If expected is 120 Potong with 1 kg = 3 Potong, then:
$expectedKgFromPotong = 120 / 3; // 40 kg
echo "120 Potong ÷ 3 = " . $expectedKgFromPotong . " kg" . PHP_EOL;

// But actual purchase shows:
echo PHP_EOL . "Actual purchase data:" . PHP_EOL;
echo "Jumlah: " . $pembelianDetails->jumlah . " " . $satuan->nama . PHP_EOL;

if ($satuan->nama === 'Kilogram') {
    echo "Purchase is in Kilogram: " . $pembelianDetails->jumlah . " kg" . PHP_EOL;
    echo "With conversion 1 kg = 3 Potong: " . ($pembelianDetails->jumlah * 3) . " Potong" . PHP_EOL;
} elseif ($satuan->nama === 'Potong') {
    echo "Purchase is in Potong: " . $pembelianDetails->jumlah . " Potong" . PHP_EOL;
    echo "With conversion 1 kg = 3 Potong: " . ($pembelianDetails->jumlah / 3) . " kg" . PHP_EOL;
}

echo PHP_EOL . "=== Possible Scenarios ===" . PHP_EOL;
echo "Scenario 1: User expects different quantity than actual purchase" . PHP_EOL;
echo "  - Actual: 50 kg = 150 Potong" . PHP_EOL;
echo "  - Expected: 40 kg = 120 Potong" . PHP_EOL;
echo "  - Difference: 10 kg = 30 Potong" . PHP_EOL;
echo PHP_EOL;
echo "Scenario 2: User wants to correct the purchase data" . PHP_EOL;
echo "  - Change purchase from 50 kg to 40 kg" . PHP_EOL;
echo "  - This would make it 40 kg = 120 Potong" . PHP_EOL;
echo PHP_EOL;
echo "Scenario 3: User is referring to a different purchase transaction" . PHP_EOL;
echo "  - Maybe there's another purchase with 40 kg" . PHP_EOL;

// Check if there are other purchases for Ayam Potong
echo PHP_EOL . "=== Other Purchases for Ayam Potong ===" . PHP_EOL;
$otherPurchases = DB::table('pembelian_details')
    ->join('pembelians', 'pembelian_details.pembelian_id', '=', 'pembelians.id')
    ->where('pembelian_details.bahan_baku_id', 1)
    ->where('pembelian_details.pembelian_id', '!=', 1)
    ->select('pembelian_details.*', 'pembelians.tanggal')
    ->get();

echo "Found " . $otherPurchases->count() . " other purchases:" . PHP_EOL;
foreach ($otherPurchases as $purchase) {
    $satuan = DB::table('satuans')->where('id', $purchase->satuan)->first();
    echo "  ID " . $purchase->pembelian_id . ": " . $purchase->jumlah . " " . $satuan->nama . " on " . $purchase->tanggal . PHP_EOL;
}
