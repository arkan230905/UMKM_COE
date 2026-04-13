<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\Coa;
use App\Observers\PembelianObserver;

echo "=== TESTING SPECIFIC PERSEDIAAN COA ===\n";

// Check available persediaan COAs
echo "Available Persediaan COAs:\n";
$persediaanCoas = Coa::where('tipe_akun', 'Asset')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%pers%bahan%')
              ->orWhere('nama_akun', 'like', '%persediaan%');
    })
    ->orderBy('kode_akun')
    ->get();

foreach ($persediaanCoas as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Get purchase with details
$pembelian = Pembelian::with([
    'details.bahanBaku',
    'details.bahanPendukung'
])->find(3);

if ($pembelian) {
    echo "\nPurchase Details:\n";
    foreach ($pembelian->details as $detail) {
        if ($detail->bahanBaku) {
            echo "- Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
            
            // Test finding specific COA
            $specificCoa = Coa::where('tipe_akun', 'Asset')
                ->where('nama_akun', 'like', '%pers%bahan%baku%')
                ->where('nama_akun', 'like', '%' . $detail->bahanBaku->nama_bahan . '%')
                ->first();
                
            if ($specificCoa) {
                echo "  Found specific COA: {$specificCoa->kode_akun} - {$specificCoa->nama_akun}\n";
            } else {
                echo "  No specific COA found, will use general 114\n";
            }
        }
        
        if ($detail->bahanPendukung) {
            echo "- Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}\n";
            
            // Test finding specific COA
            $specificCoa = Coa::where('tipe_akun', 'Asset')
                ->where('nama_akun', 'like', '%pers%bahan%pendukung%')
                ->where('nama_akun', 'like', '%' . $detail->bahanPendukung->nama_bahan . '%')
                ->first();
                
            if ($specificCoa) {
                echo "  Found specific COA: {$specificCoa->kode_akun} - {$specificCoa->nama_akun}\n";
            } else {
                echo "  No specific COA found, will use general 115\n";
            }
        }
    }
    
    // Delete existing journal and recreate
    echo "\n🔄 Recreating journal with new logic...\n";
    
    $existingJournal = JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', $pembelian->id)
        ->first();
    
    if ($existingJournal) {
        $existingJournal->lines()->delete();
        $existingJournal->delete();
        echo "Deleted existing journal\n";
    }
    
    // Create new journal
    $observer = new PembelianObserver();
    $observer->created($pembelian);
    
    echo "✅ New journal created with specific COA logic!\n";
    echo "Check: /akuntansi/jurnal-umum?ref_type=purchase&ref_id={$pembelian->id}\n";
}

echo "\n✅ Test complete!\n";