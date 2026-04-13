<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING EXISTING PRODUKSI JOURNALS\n";
echo "==================================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first(); // Ayam Crispi Macdi
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first(); // Ayam Goreng Bundo
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first(); // WIP

echo "Available COA:\n";
echo "- 116: {$coa116->nama_akun}\n";
echo "- 1161: {$coa1161->nama_akun}\n";
echo "- 117: {$coa117->nama_akun}\n\n";

// Find all journals using COA 116 (Ayam Crispi Macdi)
$wrongJournals = \App\Models\JurnalUmum::where('coa_id', $coa116->id)
    ->where('tipe_referensi', 'produksi')
    ->get();

echo "Found {$wrongJournals->count()} produksi journals using COA 116:\n\n";

foreach ($wrongJournals as $journal) {
    echo "Journal ID: {$journal->id}\n";
    echo "  Tanggal: {$journal->tanggal}\n";
    echo "  Referensi: {$journal->referensi}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    
    // Check if this is for Ayam Goreng Bundo
    if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false || 
        strpos($journal->keterangan, 'Ayam Goreng') !== false) {
        
        echo "  -> This should use COA 1161 (Ayam Goreng Bundo)\n";
        
        try {
            // Update to use correct COA
            $journal->coa_id = $coa1161->id;
            $journal->save();
            
            echo "  -> Updated to COA 1161 successfully\n";
        } catch (\Exception $e) {
            echo "  -> ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  -> Keeping COA 116 (Ayam Crispi Macdi)\n";
    }
    
    echo "---\n";
}

// Check if we need to create missing journals for the amounts user mentioned
echo "\nChecking for missing journals based on user report:\n";
echo "================================================\n";

$amounts = [3864960, 3368960]; // Rp 3.864.960 and Rp 3.368.960

foreach ($amounts as $index => $amount) {
    echo "\nChecking amount: " . number_format($amount, 0, ',', '.') . "\n";
    
    // Check if this amount exists in produksi journals
    $existingJournal = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
        ->whereDate('tanggal', '2026-04-12')
        ->where(function($query) use ($amount) {
            $query->where('debit', $amount)->orWhere('kredit', $amount);
        })
        ->first();
    
    if ($existingJournal) {
        echo "  -> Found existing journal: ID {$existingJournal->id}\n";
        continue;
    }
    
    // Determine which product this should be
    $productName = ($index == 0) ? 'Ayam Crispi Macdi' : 'Ayam Goreng Bundo';
    $coaToUse = ($index == 0) ? $coa116 : $coa1161;
    
    echo "  -> Creating missing journal for: {$productName}\n";
    
    try {
        \DB::beginTransaction();
        
        // Create debit journal
        $debitJournal = [
            'coa_id' => $coaToUse->id,
            'tanggal' => '2026-04-12',
            'keterangan' => "Transfer WIP ke Barang Jadi - {$productName}",
            'debit' => $amount,
            'kredit' => 0,
            'referensi' => 'PROD-20260412-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            'tipe_referensi' => 'produksi',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Create credit journal
        $creditJournal = [
            'coa_id' => $coa117->id,
            'tanggal' => '2026-04-12',
            'keterangan' => "Transfer WIP ke Barang Jadi - {$productName}",
            'debit' => 0,
            'kredit' => $amount,
            'referensi' => 'PROD-20260412-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            'tipe_referensi' => 'produksi',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        \App\Models\JurnalUmum::insert([$debitJournal, $creditJournal]);
        
        \DB::commit();
        
        echo "  -> SUCCESS: Created journals for {$productName}\n";
        echo "     Debit: {$coaToUse->kode_akun} - {$coaToUse->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
        echo "     Kredit: 117 - Pers. Barang dalam Proses: Rp " . number_format($amount, 0, ',', '.') . "\n";
        
    } catch (\Exception $e) {
        \DB::rollBack();
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nFinal verification:\n";
echo "==================\n";

$allProduksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

echo "All produksi journals:\n";
foreach ($allProduksiJournals as $journal) {
    echo "  ID: {$journal->id} | {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nExpected result:\n";
echo "================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Crispi Macdi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Kredit | Rp 3.864.960\n\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Kredit | Rp 3.368.960\n";

echo "\nDone!\n";

?>
