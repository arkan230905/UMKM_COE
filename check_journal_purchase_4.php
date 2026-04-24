<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Checking journal entries for purchase 4:\n\n";
    
    $entries = DB::table('jurnal_umum')
        ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
        ->where('jurnal_umum.tipe_referensi', 'pembelian')
        ->where('jurnal_umum.referensi', 'PB-20260423-0004')
        ->select('coas.kode_akun', 'coas.nama_akun', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'jurnal_umum.keterangan')
        ->get();
    
    if ($entries->isEmpty()) {
        echo "No journal entries found for purchase 4\n";
    } else {
        foreach ($entries as $entry) {
            echo sprintf("%-6s - %-30s: Debit %10s, Credit %10s (%s)\n", 
                $entry->kode_akun, 
                $entry->nama_akun, 
                number_format($entry->debit, 0), 
                number_format($entry->kredit, 0),
                $entry->keterangan
            );
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}