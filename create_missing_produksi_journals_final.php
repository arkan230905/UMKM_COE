<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CREATING MISSING PRODUKSI JOURNALS\n";
echo "===================================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first(); // Ayam Crispi Macdi
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first(); // Ayam Goreng Bundo
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first(); // WIP

echo "COA References:\n";
echo "- 116 (ID {$coa116->id}): {$coa116->nama_akun}\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n";
echo "- 117 (ID {$coa117->id}): {$coa117->nama_akun}\n\n";

// Check current jurnal_umum count
$currentCount = \App\Models\JurnalUmum::count();
echo "Current jurnal_umum records: {$currentCount}\n\n";

// Check if produksi journals exist
$produksiCount = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')->count();
echo "Current produksi journals: {$produksiCount}\n\n";

if ($produksiCount == 0) {
    echo "PRODUKSI JOURNALS MISSING! Creating them now...\n\n";
    
    try {
        \DB::beginTransaction();
        
        // Create Ayam Crispi Macdi journals
        echo "Creating Ayam Crispi Macdi journals:\n";
        $crispiJournals = [
            [
                'coa_id' => $coa116->id,
                'tanggal' => '2026-04-12',
                'keterangan' => 'Transfer WIP ke Barang Jadi - Ayam Crispi Macdi',
                'debit' => 3864960,
                'kredit' => 0,
                'referensi' => 'PROD-20260412-001',
                'tipe_referensi' => 'produksi',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'coa_id' => $coa117->id,
                'tanggal' => '2026-04-12',
                'keterangan' => 'Transfer WIP ke Barang Jadi - Ayam Crispi Macdi',
                'debit' => 0,
                'kredit' => 3864960,
                'referensi' => 'PROD-20260412-001',
                'tipe_referensi' => 'produksi',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        
        \App\Models\JurnalUmum::insert($crispiJournals);
        echo "SUCCESS: Created Ayam Crispi Macdi journals\n";
        
        // Create Ayam Goreng Bundo journals
        echo "\nCreating Ayam Goreng Bundo journals:\n";
        $bundoJournals = [
            [
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
            ],
            [
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
            ]
        ];
        
        \App\Models\JurnalUmum::insert($bundoJournals);
        echo "SUCCESS: Created Ayam Goreng Bundo journals\n";
        
        \DB::commit();
        echo "\nSUCCESS: All produksi journals created!\n";
        
    } catch (\Exception $e) {
        \DB::rollBack();
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "Produksi journals already exist!\n";
}

echo "\nFinal verification:\n";
echo "==================\n";

$finalCount = \App\Models\JurnalUmum::count();
$finalProduksiCount = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')->count();

echo "Total jurnal_umum records: {$finalCount}\n";
echo "Total produksi journals: {$finalProduksiCount}\n\n";

echo "Produksi journals in database:\n";
$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nExpected UI display:\n";
echo "===================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nDone! Refresh your browser to see the changes.\n";

?>
