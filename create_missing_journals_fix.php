<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating missing journal entries...\n\n";

try {
    // Get all pembelian that don't have journal entries
    $pembelians = App\Models\Pembelian::with(['details', 'vendor', 'kasBank'])->get();
    
    echo "Found " . $pembelians->count() . " pembelian records\n";
    
    foreach ($pembelians as $pembelian) {
        echo "\nProcessing Pembelian ID: {$pembelian->id} - {$pembelian->nomor_pembelian}\n";
        
        // Check if journal already exists
        $existingJournal = App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
            ->where('referensi', $pembelian->nomor_pembelian)
            ->exists();
            
        if ($existingJournal) {
            echo "  - Journal already exists, skipping\n";
            continue;
        }
        
        // Create journal entries
        $journalService = new App\Services\PembelianJournalService();
        $journal = $journalService->createJournalFromPembelian($pembelian);
        
        if ($journal) {
            echo "  - Journal created successfully\n";
        } else {
            echo "  - Failed to create journal\n";
        }
    }
    
    echo "\nProcessing Penggajian...\n";
    
    // Get all penggajian that don't have journal entries
    $penggajians = App\Models\Penggajian::get();
    
    echo "Found " . $penggajians->count() . " penggajian records\n";
    
    foreach ($penggajians as $penggajian) {
        echo "\nProcessing Penggajian ID: {$penggajian->id}\n";
        
        // Check if journal already exists
        $existingJournal = App\Models\JurnalUmum::where('tipe_referensi', 'penggajian')
            ->where('referensi', 'GJ-' . $penggajian->id)
            ->exists();
            
        if ($existingJournal) {
            echo "  - Journal already exists, skipping\n";
            continue;
        }
        
        // Create journal entries for penggajian
        try {
            \DB::beginTransaction();
            
            // Debit Beban Gaji
            $jurnalDebit = [
                'coa_id' => 1, // Assuming COA ID 1 for beban gaji
                'tanggal' => $penggajian->tanggal,
                'keterangan' => 'Penggajian karyawan - ' . $penggajian->periode,
                'debit' => $penggajian->total_gaji,
                'kredit' => 0,
                'referensi' => 'GJ-' . $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => 1,
            ];
            
            // Kredit Kas
            $jurnalKredit = [
                'coa_id' => 2, // Assuming COA ID 2 for kas
                'tanggal' => $penggajian->tanggal,
                'keterangan' => 'Penggajian karyawan - ' . $penggajian->periode,
                'debit' => 0,
                'kredit' => $penggajian->total_gaji,
                'referensi' => 'GJ-' . $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => 1,
            ];
            
            App\Models\JurnalUmum::insert([$jurnalDebit, $jurnalKredit]);
            
            \DB::commit();
            echo "  - Journal created successfully\n";
            
        } catch (\Exception $e) {
            \DB::rollBack();
            echo "  - Failed to create journal: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nDone!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
