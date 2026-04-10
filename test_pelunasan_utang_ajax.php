<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;

try {
    echo "=== TESTING PELUNASAN UTANG AJAX ENDPOINT ===\n\n";
    
    // Get a purchase that has credit payment method
    $creditPurchases = Pembelian::where('payment_method', 'credit')
        ->with('vendor')
        ->get();
    
    if ($creditPurchases->isEmpty()) {
        echo "❌ No credit purchases found to test with\n";
        
        // Show all purchases and their payment methods
        echo "\nAll purchases:\n";
        $allPurchases = Pembelian::with('vendor')->get();
        foreach ($allPurchases as $purchase) {
            echo "- ID {$purchase->id}: {$purchase->nomor_pembelian} - {$purchase->payment_method} - Rp " . number_format($purchase->total_harga) . "\n";
        }
        exit;
    }
    
    echo "Found " . $creditPurchases->count() . " credit purchases:\n\n";
    
    foreach ($creditPurchases as $purchase) {
        $sisaUtang = ($purchase->total_harga ?? 0) - ($purchase->terbayar ?? 0);
        
        echo "Purchase ID {$purchase->id}: {$purchase->nomor_pembelian}\n";
        echo "- Vendor: {$purchase->vendor->nama_vendor}\n";
        echo "- Total: Rp " . number_format($purchase->total_harga) . "\n";
        echo "- Terbayar: Rp " . number_format($purchase->terbayar ?? 0) . "\n";
        echo "- Sisa Utang: Rp " . number_format($sisaUtang) . "\n";
        echo "- Payment Method: {$purchase->payment_method}\n";
        
        // Simulate the AJAX response
        if ($sisaUtang > 0) {
            $response = [
                'success' => true,
                'data' => [
                    'sisa_utang' => $sisaUtang,
                    'total_pembelian' => $purchase->total_harga ?? 0,
                    'terbayar' => $purchase->terbayar ?? 0,
                    'vendor' => $purchase->vendor->nama_vendor ?? '-',
                    'nomor_pembelian' => $purchase->nomor_pembelian ?? 'PB-' . $purchase->id
                ]
            ];
            
            echo "✅ AJAX Response would be:\n";
            echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ This purchase is already fully paid\n";
        }
        
        echo "---\n\n";
    }
    
    echo "🎯 EXPECTED BEHAVIOR:\n";
    echo "When you select a purchase in the debt payment form:\n";
    echo "1. Total Pembelian should show the full purchase amount\n";
    echo "2. Sisa Utang should show the remaining debt\n";
    echo "3. Vendor name should be populated\n";
    echo "4. Payment amount should auto-fill with remaining debt\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}