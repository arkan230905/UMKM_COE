<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Creating missing journals for existing pembelian transactions...\n\n";

// Get pembelian transactions without journals
$pembelianIds = [12, 13, 14]; // IDs that need journals

foreach ($pembelianIds as $pembelianId) {
    echo "=== Processing Pembelian ID: {$pembelianId} ===\n";
    
    $pembelian = \App\Models\Pembelian::with([
        'details.bahanBaku.coaPembelian',
        'details.bahanPendukung.coaPembelian',
        'vendor'
    ])->find($pembelianId);
    
    if (!$pembelian) {
        echo "Pembelian ID {$pembelianId} not found!\n\n";
        continue;
    }
    
    echo "Nomor: {$pembelian->nomor_pembelian}\n";
    echo "Total: " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
    echo "Payment Method: {$pembelian->payment_method}\n";
    echo "Details: " . $pembelian->details->count() . "\n\n";
    
    // Create journal entries using the same logic as observer
    $entries = [];
    $coaGroups = [];
    
    foreach($pembelian->details as $detail) {
        $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
        
        // Get COA pembelian from master data
        $coaPembelian = null;
        if ($detail->bahanBaku && $detail->bahanBaku->coa_pembelian_id) {
            $coaPembelian = $detail->bahanBaku->coaPembelian;
        } elseif ($detail->bahanPendukung && $detail->bahanPendukung->coa_pembelian_id) {
            $coaPembelian = $detail->bahanPendukung->coaPembelian;
        }
        
        if ($coaPembelian) {
            $coaCode = $coaPembelian->kode_akun;
            if (!isset($coaGroups[$coaCode])) {
                $coaGroups[$coaCode] = [
                    'coa' => $coaPembelian,
                    'total' => 0,
                    'items' => []
                ];
            }
            $coaGroups[$coaCode]['total'] += $subtotal;
            $coaGroups[$coaCode]['items'][] = $detail->bahanBaku->nama_bahan ?? $detail->bahanPendukung->nama_bahan ?? 'Unknown';
        } else {
            echo "WARNING: COA pembelian not found for detail ID {$detail->id}\n";
        }
    }
    
    // Create debit entries for each COA group
    foreach ($coaGroups as $coaCode => $group) {
        $entries[] = [
            'code' => $coaCode, 
            'debit' => $group['total'], 
            'credit' => 0,
            'memo' => 'Pembelian ' . implode(', ', array_unique($group['items']))
        ];
        echo "Debit entry: {$coaCode} = " . number_format($group['total'], 0, ',', '.') . "\n";
    }
    
    // Add PPN Masukan if exists
    if (($pembelian->ppn_nominal ?? 0) > 0) {
        $entries[] = [
            'code' => '127', // PPN Masukkan
            'debit' => $pembelian->ppn_nominal, 
            'credit' => 0,
            'memo' => 'PPN Masukan ' . ($pembelian->ppn_persen ?? 10) . '%'
        ];
        echo "PPN entry: 127 = " . number_format($pembelian->ppn_nominal, 0, ',', '.') . "\n";
    }
    
    // Add credit entry
    $totalAmount = $pembelian->total_harga;
    if ($pembelian->payment_method === 'credit') {
        // Credit Hutang Usaha
        $entries[] = [
            'code' => '210', // Hutang Usaha
            'debit' => 0, 
            'credit' => $totalAmount,
            'memo' => 'Hutang pembelian kredit'
        ];
        echo "Credit entry (Hutang): 210 = " . number_format($totalAmount, 0, ',', '.') . "\n";
    } else {
        // Credit Kas/Bank
        if ($pembelian->bank_id) {
            $bankCoa = \App\Models\Coa::find($pembelian->bank_id);
            if ($bankCoa) {
                $entries[] = [
                    'code' => $bankCoa->kode_akun, 
                    'debit' => 0, 
                    'credit' => $totalAmount,
                    'memo' => 'Pembayaran ' . ($pembelian->payment_method === 'cash' ? 'tunai' : 'transfer') . ' pembelian'
                ];
                echo "Credit entry (Bank): {$bankCoa->kode_akun} = " . number_format($totalAmount, 0, ',', '.') . "\n";
            }
        }
    }
    
    // Calculate totals to verify balance
    $totalDebit = 0;
    $totalCredit = 0;
    foreach ($entries as $entry) {
        $totalDebit += $entry['debit'];
        $totalCredit += $entry['credit'];
    }
    
    echo "Total Debit: " . number_format($totalDebit, 0, ',', '.') . "\n";
    echo "Total Credit: " . number_format($totalCredit, 0, ',', '.') . "\n";
    echo "Balanced: " . ($totalDebit == $totalCredit ? 'YES' : 'NO') . "\n";
    
    if ($totalDebit == $totalCredit) {
        try {
            $journalService = new \App\Services\JournalService();
            
            $journalService->post(
                $pembelian->tanggal->format('Y-m-d'),
                'purchase',
                $pembelian->id,
                'Pembelian ' . ($pembelian->vendor->nama_vendor ?? '') . ' - ' . ($pembelian->nomor_pembelian ?? $pembelian->id),
                $entries
            );
            
            echo "✓ Journal created successfully!\n\n";
        } catch (\Exception $e) {
            echo "✗ Error creating journal: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "✗ Journal not balanced - skipping\n\n";
    }
}

echo "\nDone.\n";
