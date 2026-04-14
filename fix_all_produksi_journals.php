<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING ALL PRODUKSI JOURNALS\n";
echo "============================\n\n";

// Get all COA we need
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first(); // Pers. Barang Jadi Ayam Crispi Macdi
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first(); // Pers. Barang Jadi Ayam Goreng Bundo
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first(); // Pers. Barang dalam Proses

echo "Available COA:\n";
echo "- 116: {$coa116->nama_akun}\n";
echo "- 1161: {$coa1161->nama_akun}\n";
echo "- 117: {$coa117->nama_akun}\n\n";

// Find all produksi journals
$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

echo "Found {$produksiJournals->count()} produksi journals:\n\n";

foreach ($produksiJournals as $journal) {
    echo "Journal ID: {$journal->id}\n";
    echo "  Tanggal: {$journal->tanggal}\n";
    echo "  Referensi: {$journal->referensi}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    
    // Check if this journal should use a different COA
    $needsUpdate = false;
    $newCoaId = null;
    $reason = '';
    
    if ($journal->coa_id == 116) {
        // This is using the old COA 116, check what product it should be
        if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
            $needsUpdate = true;
            $newCoaId = $coa1161->id;
            $reason = 'Should use COA 1161 for Ayam Goreng Bundo';
        } elseif (strpos($journal->keterangan, 'Ayam Crispi Macdi') !== false) {
            // This is correct, keep using COA 116
            $reason = 'Correctly using COA 116 for Ayam Crispi Macdi';
        } else {
            // Unknown product, check if we can determine from other sources
            $needsUpdate = true;
            $newCoaId = $coa1161->id;
            $reason = 'Unknown product, using COA 1161 as default';
        }
    }
    
    if ($needsUpdate && $newCoaId) {
        echo "  -> NEEDS UPDATE: {$reason}\n";
        
        try {
            $journal->coa_id = $newCoaId;
            $journal->save();
            
            $newCoa = \App\Models\Coa::find($newCoaId);
            echo "  -> Updated to: {$newCoa->kode_akun} - {$newCoa->nama_akun}\n";
        } catch (\Exception $e) {
            echo "  -> ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  -> OK: {$reason}\n";
    }
    
    echo "---\n";
}

echo "\nCreating missing journals for the amounts user mentioned:\n";
echo "======================================================\n";

// The user mentioned two amounts: 3.864.960 and 3.368.960
$amounts = [3864960, 3368960];

foreach ($amounts as $index => $amount) {
    echo "\nProcessing amount: " . number_format($amount, 0, ',', '.') . "\n";
    
    // Check if this amount already exists
    $existingJournal = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
        ->whereDate('tanggal', '2026-04-12')
        ->where(function($query) use ($amount) {
            $query->where('debit', $amount)->orWhere('kredit', $amount);
        })
        ->first();
    
    if ($existingJournal) {
        echo "  -> Journal already exists: ID {$existingJournal->id}\n";
        continue;
    }
    
    // Determine which product this should be for
    $coaToUse = ($index == 0) ? $coa116 : $coa1161; // First amount for Ayam Crispi, second for Ayam Goreng Bundo
    $productName = ($index == 0) ? 'Ayam Crispi Macdi' : 'Ayam Goreng Bundo';
    
    echo "  -> Creating journals for: {$productName}\n";
    
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
        
        echo "  -> SUCCESS: Created 2 journals for {$productName}\n";
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

echo "\nDone!\n";

?>
