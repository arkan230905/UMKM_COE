<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CREATING MISSING PRODUKSI JOURNALS\n";
echo "===================================\n\n";

// Get the COA we need
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first(); // Pers. Barang Jadi Ayam Goreng Bundo
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first(); // Pers. Barang dalam Proses

if (!$coa1161) {
    echo "ERROR: COA 1161 not found!\n";
    exit;
}

if (!$coa117) {
    echo "ERROR: COA 117 not found!\n";
    exit;
}

echo "Using COA:\n";
echo "- 1161: {$coa1161->nama_akun}\n";
echo "- 117: {$coa117->nama_akun}\n\n";

// Check if produksi journals already exist
$existingJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->whereDate('tanggal', '2026-04-12')
    ->get();

if ($existingJournals->count() > 0) {
    echo "Found {$existingJournals->count()} existing produksi journals on 2026-04-12:\n";
    foreach ($existingJournals as $journal) {
        echo "  - {$journal->referensi} | D: {$journal->debit} | K: {$journal->kredit}\n";
    }
    echo "\n";
}

// Create the missing produksi journal for Ayam Goreng Bundo
echo "Creating produksi journal for Ayam Goreng Bundo:\n";
echo "Tanggal: 2026-04-12\n";
echo "Nominal: Rp 3.368.960\n";
echo "Keterangan: Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n\n";

try {
    \DB::beginTransaction();
    
    // Create debit journal (Pers. Barang Jadi Ayam Goreng Bundo)
    $debitJournal = [
        'coa_id' => $coa1161->id,
        'tanggal' => '2026-04-12',
        'keterangan' => 'Transfer WIP ke Barang Jadi - Ayam Goreng Bundo',
        'debit' => 3368960,
        'kredit' => 0,
        'referensi' => 'PROD-20260412-001',
        'tipe_referensi' => 'produksi',
        'created_by' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // Create credit journal (Pers. Barang dalam Proses)
    $creditJournal = [
        'coa_id' => $coa117->id,
        'tanggal' => '2026-04-12',
        'keterangan' => 'Transfer WIP ke Barang Jadi - Ayam Goreng Bundo',
        'debit' => 0,
        'kredit' => 3368960,
        'referensi' => 'PROD-20260412-001',
        'tipe_referensi' => 'produksi',
        'created_by' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // Insert both journals
    \App\Models\JurnalUmum::insert([$debitJournal, $creditJournal]);
    
    \DB::commit();
    
    echo "SUCCESS: Created 2 produksi journals\n";
    echo "- Debit: 1161 - Pers. Barang Jadi Ayam Goreng Bundo: Rp 3.368.960\n";
    echo "- Kredit: 117 - Pers. Barang dalam Proses: Rp 3.368.960\n";
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "ERROR: Failed to create journals - " . $e->getMessage() . "\n";
}

echo "\nVerifying created journals:\n";
echo "==========================\n";

$createdJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->whereDate('tanggal', '2026-04-12')
    ->with('coa')
    ->get();

foreach ($createdJournals as $journal) {
    echo "ID: {$journal->id}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "---\n";
}

echo "\nNow the jurnal umum should show:\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Kredit | Rp 3.368.960\n";

echo "\nDone!\n";

?>
