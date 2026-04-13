<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING MISSING COA 1101 ===\n";

// Check if COA 1101 exists
$coa1101 = DB::table('coas')->where('kode_akun', '1101')->first();
if ($coa1101) {
    echo "✅ COA 1101 exists: {$coa1101->nama_akun}\n";
} else {
    echo "❌ COA 1101 NOT FOUND!\n";
}

// Check what COAs exist in the 1100 range (current assets)
echo "\n=== EXISTING COAs IN 1100 RANGE ===\n";
$coas1100 = DB::table('coas')
    ->where('kode_akun', 'like', '11%')
    ->orderBy('kode_akun')
    ->get();

foreach ($coas1100 as $coa) {
    echo "{$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Check what the system expects for sales transactions
echo "\n=== CHECKING JOURNAL SERVICE EXPECTATIONS ===\n";
echo "The error occurs in JournalService.php when processing sales transactions.\n";
echo "COA 1101 is typically 'Kas' or 'Cash' account for cash sales.\n";

// Check if there are similar accounts
$kasAccounts = DB::table('coas')
    ->where('nama_akun', 'like', '%kas%')
    ->orWhere('nama_akun', 'like', '%cash%')
    ->get();

if ($kasAccounts->count() > 0) {
    echo "\nExisting Cash/Kas accounts:\n";
    foreach ($kasAccounts as $kas) {
        echo "{$kas->kode_akun} - {$kas->nama_akun}\n";
    }
} else {
    echo "\nNo existing Cash/Kas accounts found.\n";
}

// Check the journal service to see what accounts it expects
echo "\n=== RECOMMENDED SOLUTION ===\n";
echo "Need to create COA 1101 - typically this should be:\n";
echo "- Kode: 1101\n";
echo "- Nama: Kas / Cash\n";
echo "- Tipe: Asset (Aktiva)\n";
echo "- Kategori: Current Asset (Aktiva Lancar)\n";

// Create the missing COA
echo "\n=== CREATING MISSING COA 1101 ===\n";

try {
    DB::table('coas')->insert([
        'kode_akun' => '1101',
        'nama_akun' => 'Kas',
        'tipe_akun' => 'asset',
        'kategori' => 'current_asset',
        'saldo_normal' => 'debit',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Successfully created COA 1101 - Kas\n";
    
    // Verify creation
    $newCoa = DB::table('coas')->where('kode_akun', '1101')->first();
    if ($newCoa) {
        echo "✅ Verification: {$newCoa->kode_akun} - {$newCoa->nama_akun}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating COA: " . $e->getMessage() . "\n";
    
    // Check table structure to see what columns are required
    echo "\n=== CHECKING COA TABLE STRUCTURE ===\n";
    $columns = DB::select("DESCRIBE coas");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) " . ($column->Null == 'NO' ? 'REQUIRED' : 'OPTIONAL') . "\n";
    }
}

echo "\n=== ADDITIONAL COAs THAT MIGHT BE NEEDED ===\n";
echo "For complete sales transactions, you might also need:\n";
echo "- 1102: Bank\n";
echo "- 4101: Penjualan / Sales Revenue\n";
echo "- 5101: Harga Pokok Penjualan / Cost of Goods Sold\n";

// Check if these exist
$requiredCoas = ['1102', '4101', '5101'];
foreach ($requiredCoas as $code) {
    $coa = DB::table('coas')->where('kode_akun', $code)->first();
    if ($coa) {
        echo "✅ {$code}: {$coa->nama_akun}\n";
    } else {
        echo "❌ {$code}: NOT FOUND\n";
    }
}