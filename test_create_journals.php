<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing journal creation for existing transactions...\n\n";

// Test Pembelian
echo "=== TESTING PEMBELIAN ===\n";
$pembelian = \App\Models\Pembelian::where('user_id', 20)->orderBy('created_at', 'desc')->first();

if ($pembelian) {
    echo "Found pembelian: {$pembelian->nomor_pembelian}\n";
    echo "Total: {$pembelian->total}\n";
    echo "Payment method: {$pembelian->payment_method}\n";
    echo "Details count: " . $pembelian->details->count() . "\n\n";
    
    try {
        $service = new \App\Services\PembelianJournalService();
        $result = $service->createJournalFromPembelian($pembelian);
        
        if ($result) {
            echo "✓ Journal created successfully!\n";
            
            // Check if journal entries were created
            $journalCount = DB::table('jurnal_umum')
                ->where('tipe_referensi', 'pembelian')
                ->where('referensi', $pembelian->nomor_pembelian)
                ->count();
            
            echo "Journal entries created: {$journalCount}\n";
        } else {
            echo "✗ Journal creation returned null\n";
        }
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No pembelian found for user_id 20\n";
}

echo "\n=== TESTING PENJUALAN ===\n";
$penjualan = \App\Models\Penjualan::where('user_id', 20)->orderBy('created_at', 'desc')->first();

if ($penjualan) {
    echo "Found penjualan: {$penjualan->nomor_penjualan}\n";
    echo "Total: {$penjualan->grand_total}\n";
    echo "Payment method: {$penjualan->payment_method}\n";
    echo "Details count: " . $penjualan->details->count() . "\n\n";
    
    try {
        \App\Services\JournalService::createJournalFromPenjualan($penjualan);
        
        echo "✓ Journal created successfully!\n";
        
        // Check if journal entries were created
        $journalCount = DB::table('jurnal_umum')
            ->where('tipe_referensi', 'sale')
            ->where('referensi', $penjualan->id)
            ->count();
        
        echo "Journal entries created: {$journalCount}\n";
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No penjualan found for user_id 20\n";
}

echo "\n=== FINAL CHECK ===\n";
$totalJournals = DB::table('jurnal_umum')->where('user_id', 20)->count();
echo "Total journal entries for user 20: {$totalJournals}\n";
