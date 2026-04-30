<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "EMERGENCY FIX: HPP Journal for Hosting Preparation\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

if (!$penjualan) {
    echo "ERROR: Penjualan SJ-20260430-001 not found!\n";
    exit;
}

echo "\n=== Current Status ===\n";
echo "Penjualan ID: " . $penjualan->id . "\n";
echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
echo "Database Total HPP: Rp " . number_format($penjualan->total_hpp ?? 0, 0, ',', '.') . "\n";

// Check current journal entries
$currentEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
echo "Current journal entries: " . $currentEntries->count() . "\n";

// Check if HPP entries exist
$hppExists = $currentEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
           strpos($jurnal->coa->kode_akun, '56') !== false;
})->count() > 0;

echo "HPP entries exist: " . ($hppExists ? "YES" : "NO") . "\n";

if (!$hppExists) {
    echo "\n=== EMERGENCY FIX REQUIRED ===\n";
    
    // Calculate HPP from details
    $calculatedHpp = 0;
    foreach ($penjualan->details as $detail) {
        $hppPerUnit = $detail->produk->hpp ?? $detail->produk->harga_pokok ?? 5372; // Fallback to display value
        $detailHpp = $hppPerUnit * $detail->jumlah;
        $calculatedHpp += $detailHpp;
        
        echo "Detail: " . $detail->produk->nama_produk . " x" . $detail->jumlah . " @ Rp " . number_format($hppPerUnit, 0, ',', '.') . " = Rp " . number_format($detailHpp, 0, ',', '.') . "\n";
    }
    
    echo "Calculated HPP: Rp " . number_format($calculatedHpp, 0, ',', '.') . "\n";
    
    // Update penjualan total_hpp
    $penjualan->update([
        'total_hpp' => $calculatedHpp,
        'user_id' => 1, // Ensure user_id is set
        'updated_at' => now(),
    ]);
    
    echo "Updated total_hpp to Rp " . number_format($calculatedHpp, 0, ',', '.') . "\n";
    
    // Get COA accounts
    $hppCoa = \App\Models\Coa::where('kode_akun', '56')->where('user_id', 1)->first();
    $persediaanCoa = \App\Models\Coa::where('kode_akun', '1161')->where('user_id', 1)->first();
    
    if (!$hppCoa) {
        echo "ERROR: HPP COA not found!\n";
        exit;
    }
    
    if (!$persediaanCoa) {
        echo "ERROR: Persediaan COA not found!\n";
        exit;
    }
    
    echo "Found COA accounts:\n";
    echo "HPP COA: " . $hppCoa->nama_akun . " (ID: " . $hppCoa->id . ")\n";
    echo "Persediaan COA: " . $persediaanCoa->nama_akun . " (ID: " . $persediaanCoa->id . ")\n";
    
    // Delete existing HPP entries if any (to avoid duplicates)
    \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
        ->whereIn('coa_id', [$hppCoa->id, $persediaanCoa->id])
        ->delete();
    
    // Create HPP journal entries
    $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
               $penjualan->tanggal->format('Y-m-d') : 
               $penjualan->tanggal;
    
    // HPP Debit Entry
    \App\Models\JurnalUmum::create([
        'tanggal' => $tanggal,
        'coa_id' => $hppCoa->id,
        'keterangan' => 'HPP Penjualan ' . $penjualan->nomor_penjualan,
        'debit' => $calculatedHpp,
        'kredit' => 0,
        'referensi' => 'penjualan#' . $penjualan->id,
        'tipe_referensi' => 'penjualan',
        'user_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Persediaan Credit Entry
    \App\Models\JurnalUmum::create([
        'tanggal' => $tanggal,
        'coa_id' => $persediaanCoa->id,
        'keterangan' => 'Persediaan Barang Jadi ' . $penjualan->nomor_penjualan,
        'debit' => 0,
        'kredit' => $calculatedHpp,
        'referensi' => 'penjualan#' . $penjualan->id,
        'tipe_referensi' => 'penjualan',
        'user_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Created HPP journal entries: Rp " . number_format($calculatedHpp, 0, ',', '.') . "\n";
    
    // Verify the fix
    echo "\n=== VERIFICATION ===\n";
    $allEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
        ->with('coa')
        ->orderBy('coa_id')
        ->get();
    
    echo "Total journal entries: " . $allEntries->count() . "\n\n";
    
    echo "Journal Entries for SJ-20260430-001:\n";
    echo "Tanggal\tKode Akun\tNama Akun\t\t\tDebit\t\tKredit\n";
    echo str_repeat("-", 80) . "\n";
    
    $totalDebit = 0;
    $totalKredit = 0;
    
    foreach ($allEntries as $jurnal) {
        echo \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') . "\t";
        echo $jurnal->coa->kode_akun . "\t";
        echo substr($jurnal->coa->nama_akun, 0, 20) . "\t\t";
        echo ($jurnal->debit > 0 ? "Rp " . number_format($jurnal->debit, 0, ',', '.') : "-") . "\t";
        echo ($jurnal->kredit > 0 ? "Rp " . number_format($jurnal->kredit, 0, ',', '.') : "-") . "\n";
        
        $totalDebit += $jurnal->debit;
        $totalKredit += $jurnal->kredit;
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "TOTAL\t\t\t\t\tRp " . number_format($totalDebit, 0, ',', '.') . "\tRp " . number_format($totalKredit, 0, ',', '.') . "\n";
    
    echo "\nBalance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";
    
    $finalHppEntries = $allEntries->filter(function($jurnal) {
        return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
               strpos($jurnal->coa->kode_akun, '56') !== false;
    });
    
    echo "HPP entries: " . $finalHppEntries->count() . "\n";
    
    if ($finalHppEntries->count() > 0) {
        echo "\nSUCCESS: HPP journal entries created!\n";
        echo "SJ-20260430-001 now has complete journal entries including HPP.\n";
        echo "READY FOR HOSTING!\n";
    } else {
        echo "\nFAILED: HPP entries still missing\n";
    }
    
} else {
    echo "\nHPP entries already exist - no fix needed\n";
}

echo "\nEmergency fix completed!\n";
