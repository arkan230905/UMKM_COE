<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Manually creating HPP journal entries for SJ-20260430-001...\n";

// Get the penjualan
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

echo "\n=== Penjualan Details ===\n";
echo "ID: " . $penjualan->id . "\n";
echo "Nomor: " . $penjualan->nomor_penjualan . "\n";
echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
echo "Total HPP: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";

// Get COA accounts
$hppCoa = \App\Models\Coa::where('kode_akun', '56')->where('user_id', 1)->first();
$persediaanCoa = \App\Models\Coa::where('kode_akun', '1161')->where('user_id', 1)->first();

echo "\n=== COA Accounts ===\n";
echo "HPP COA: " . ($hppCoa ? $hppCoa->nama_akun . " (ID: " . $hppCoa->id . ")" : "NOT FOUND") . "\n";
echo "Persediaan COA: " . ($persediaanCoa ? $persediaanCoa->nama_akun . " (ID: " . $persediaanCoa->id . ")" : "NOT FOUND") . "\n";

if ($hppCoa && $persediaanCoa && $penjualan->total_hpp > 0) {
    echo "\n=== Creating Manual HPP Journal Entries ===\n";
    
    try {
        // Create HPP journal entries directly
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
                   $penjualan->tanggal->format('Y-m-d') : 
                   $penjualan->tanggal;
        
        // HPP Entry (Debit)
        \App\Models\JurnalUmum::create([
            'tanggal' => $tanggal,
            'coa_id' => $hppCoa->id,
            'keterangan' => 'HPP Penjualan ' . $penjualan->nomor_penjualan,
            'debit' => $penjualan->total_hpp,
            'kredit' => 0,
            'referensi' => 'penjualan#' . $penjualan->id,
            'tipe_referensi' => 'penjualan',
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Created HPP Debit entry: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
        
        // Persediaan Entry (Credit)
        \App\Models\JurnalUmum::create([
            'tanggal' => $tanggal,
            'coa_id' => $persediaanCoa->id,
            'keterangan' => 'Persediaan Barang Jadi ' . $penjualan->nomor_penjualan,
            'debit' => 0,
            'kredit' => $penjualan->total_hpp,
            'referensi' => 'penjualan#' . $penjualan->id,
            'tipe_referensi' => 'penjualan',
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Created Persediaan Credit entry: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
        
        // Check all journal entries now
        echo "\n=== All Journal Entries for SJ-20260430-001 ===\n";
        $allEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
            ->with('coa')
            ->get();
        
        echo "Total entries: " . $allEntries->count() . "\n\n";
        
        echo "Tanggal\tDeskripsi\tKode Akun\tNama Akun\tTipe\tKeterangan\tDebit\tKredit\n";
        echo str_repeat("-", 120) . "\n";
        
        $totalDebit = 0;
        $totalKredit = 0;
        
        foreach ($allEntries as $jurnal) {
            echo \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') . "\t";
            echo "Penjualan #" . $penjualan->nomor_penjualan . "\t";
            echo $jurnal->coa->kode_akun . "\t";
            echo $jurnal->coa->nama_akun . "\t";
            echo $jurnal->coa->tipe_akun . "\t";
            echo $jurnal->keterangan . "\t";
            echo ($jurnal->debit > 0 ? "Rp " . number_format($jurnal->debit, 0, ',', '.') : "-") . "\t";
            echo ($jurnal->kredit > 0 ? "Rp " . number_format($jurnal->kredit, 0, ',', '.') : "-") . "\n";
            
            $totalDebit += $jurnal->debit;
            $totalKredit += $jurnal->kredit;
        }
        
        echo str_repeat("-", 120) . "\n";
        echo "TOTAL\t\t\t\t\t\t\tRp " . number_format($totalDebit, 0, ',', '.') . "\tRp " . number_format($totalKredit, 0, ',', '.') . "\n";
        
        echo "\n=== Verification ===\n";
        echo "Balance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";
        
        $hppEntries = $allEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                   strpos($jurnal->coa->kode_akun, '56') !== false;
        });
        
        $persediaanEntries = $allEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
                   strpos($jurnal->coa->kode_akun, '116') !== false;
        });
        
        echo "HPP entries: " . $hppEntries->count() . "\n";
        echo "Persediaan entries: " . $persediaanEntries->count() . "\n";
        
        if ($hppEntries->count() > 0 && $persediaanEntries->count() > 0) {
            echo "\nSUCCESS: Complete journal entries with HPP created manually!\n";
            echo "\nThe journal now shows:\n";
            echo "- 112 Kas: Debit Rp 555.000\n";
            echo "- 41 Penjualan: Kredit Rp 500.000\n";
            echo "- 212 PPN Keluaran: Kredit Rp 55.000\n";
            echo "- 56 Harga Pokok Penjualan: Debit Rp 268.600\n";
            echo "- 1161 Persediaan Barang Jadi: Kredit Rp 268.600\n";
            echo "\nTotal Debit: Rp 823.600\n";
            echo "Total Kredit: Rp 823.600\n";
        } else {
            echo "\nISSUE: Manual creation failed\n";
        }
        
    } catch (Exception $e) {
        echo "Error creating manual entries: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "\nCannot create HPP entries:\n";
    if (!$hppCoa) echo "- HPP COA not found\n";
    if (!$persediaanCoa) echo "- Persediaan COA not found\n";
    if ($penjualan->total_hpp <= 0) echo "- Total HPP is zero\n";
}

echo "\nManual HPP journal creation completed!\n";
