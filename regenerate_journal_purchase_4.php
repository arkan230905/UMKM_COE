<?php

require_once 'vendor/autoload.php';

use App\Services\PembelianJournalService;
use App\Models\Pembelian;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = new PembelianJournalService();
    $pembelian = Pembelian::find(4);
    
    if (!$pembelian) {
        echo "Purchase 4 not found\n";
        exit(1);
    }
    
    echo "Current payment method: " . $pembelian->payment_method . "\n";
    echo "Current bank_id: " . ($pembelian->bank_id ?? 'NULL') . "\n";
    
    $result = $service->createJournalFromPembelian($pembelian);
    
    if ($result) {
        echo "Journal regenerated successfully for purchase 4\n";
    } else {
        echo "Failed to regenerate journal for purchase 4\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}