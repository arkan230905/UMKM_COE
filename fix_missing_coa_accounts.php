<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

echo "=== FIX MISSING COA ACCOUNTS ===\n\n";

// COA accounts that need to be created for penggajian
$requiredCoa = [
    '513' => 'Beban Tunjangan',
    '514' => 'Beban Asuransi',
];

echo "Creating missing COA accounts...\n";

foreach ($requiredCoa as $kode => $nama) {
    echo "Checking COA {$kode} - {$nama}...\n";
    
    $existing = Coa::where('kode_akun', $kode)->first();
    if ($existing) {
        echo "  ✅ Already exists: {$existing->nama_akun}\n";
        continue;
    }
    
    try {
        DB::beginTransaction();
        
        // Create the missing COA account
        $coa = Coa::create([
            'kode_akun' => $kode,
            'nama_akun' => $nama,
            'tipe_akun' => 'Expense',
            'saldo_awal' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "  ✅ Created: {$coa->nama_akun} ({$coa->kode_akun})\n";
        
        DB::commit();
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "  ❌ Error creating {$nama}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== VERIFICATION ===\n";
echo "Checking all required COA accounts:\n";

foreach ($requiredCoa as $kode => $nama) {
    $coa = Coa::where('kode_akun', $kode)->first();
    echo "  {$kode}: " . ($coa ? "✅ {$coa->nama_akun}" : "❌ NOT FOUND") . "\n";
}

echo "\n=== COMPLETE ===\n";
echo "Now postToJournal should work correctly!\n";
