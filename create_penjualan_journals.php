<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating journal entries for existing penjualan...\n\n";

$penjualans = App\Models\Penjualan::with('details')->get();

echo "Found " . $penjualans->count() . " penjualan records\n";

foreach ($penjualans as $penjualan) {
    echo "\nProcessing Penjualan ID: {$penjualan->id} - {$penjualan->nomor_penjualan}\n";
    
    // Check if journal already exists
    $existingJournal = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
        ->where('referensi', $penjualan->nomor_penjualan)
        ->exists();
        
    if ($existingJournal) {
        echo "  - Journal already exists, skipping\n";
        continue;
    }
    
    // Calculate total from details
    $totalFromDetails = $penjualan->details->sum('subtotal');
    
    // Skip if total is 0 or null
    if (!$totalFromDetails || $totalFromDetails <= 0) {
        echo "  - Total from details is 0 or null, skipping\n";
        continue;
    }
    
    try {
        \DB::beginTransaction();
        
        // Find COA accounts
        $kasCoa = null;
        if ($penjualan->payment_method === 'cash' || $penjualan->payment_method === 'transfer') {
            $kasCoa = \App\Models\Coa::where('kode_akun', $penjualan->sumber_dana)->first();
        }
        if (!$kasCoa) {
            $kasCoa = \App\Models\Coa::where('kode_akun', '112')->first(); // Default Kas
        }
        
        $penjualanCoa = \App\Models\Coa::where('kode_akun', '411')->first(); // Penjualan
        $hppCoa = \App\Models\Coa::where('kode_akun', '511')->first(); // HPP
        $persediaanCoa = \App\Models\Coa::where('kode_akun', '114')->first(); // Persediaan Barang Jadi
        
        // Calculate HPP (simplified - using 70% of total as example)
        $hpp = $totalFromDetails * 0.7;
        
        // Prepare journal entries
        $journalData = [];
        
        // 1. Debit Kas
        if ($kasCoa) {
            $journalData[] = [
                'coa_id' => $kasCoa->id,
                'tanggal' => $penjualan->tanggal,
                'keterangan' => 'Penjualan Produk - ' . $penjualan->nomor_penjualan,
                'debit' => $totalFromDetails,
                'kredit' => 0,
                'referensi' => $penjualan->nomor_penjualan,
                'tipe_referensi' => 'penjualan',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // 2. Credit Penjualan
        if ($penjualanCoa) {
            $journalData[] = [
                'coa_id' => $penjualanCoa->id,
                'tanggal' => $penjualan->tanggal,
                'keterangan' => 'Penjualan Produk - ' . $penjualan->nomor_penjualan,
                'debit' => 0,
                'kredit' => $penjualan->total_harga,
                'referensi' => $penjualan->nomor_penjualan,
                'tipe_referensi' => 'penjualan',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // 3. HPP entries (if HPP > 0)
        if ($hpp > 0 && $hppCoa && $persediaanCoa) {
            $journalData[] = [
                'coa_id' => $hppCoa->id,
                'tanggal' => $penjualan->tanggal,
                'keterangan' => 'HPP Penjualan - ' . $penjualan->nomor_penjualan,
                'debit' => $hpp,
                'kredit' => 0,
                'referensi' => $penjualan->nomor_penjualan,
                'tipe_referensi' => 'penjualan',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $journalData[] = [
                'coa_id' => $persediaanCoa->id,
                'tanggal' => $penjualan->tanggal,
                'keterangan' => 'Persediaan Barang Jadi - ' . $penjualan->nomor_penjualan,
                'debit' => 0,
                'kredit' => $hpp,
                'referensi' => $penjualan->nomor_penjualan,
                'tipe_referensi' => 'penjualan',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert all journal entries
        if (!empty($journalData)) {
            \App\Models\JurnalUmum::insert($journalData);
            echo "  - " . count($journalData) . " journal entries created successfully\n";
        } else {
            echo "  - No journal entries created (missing COA)\n";
        }
        
        \DB::commit();
        
    } catch (\Exception $e) {
        \DB::rollBack();
        echo "  - Failed to create journal: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
?>
