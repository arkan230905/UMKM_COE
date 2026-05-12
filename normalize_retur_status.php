<?php

// Normalize existing retur status to new simplified flow
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseReturn;

echo "Normalizing Retur Status to New Flow\n";
echo "===================================\n\n";

$returs = PurchaseReturn::all();
$updated = 0;

foreach ($returs as $retur) {
    $originalStatus = $retur->status;
    $needsUpdate = false;
    
    // Map old status to new status
    switch ($originalStatus) {
        case 'menunggu_acc':
        case 'acc_vendor':
            $retur->status = 'pending';
            $needsUpdate = true;
            break;
        case 'diproses':
            $retur->status = 'disetujui';
            $needsUpdate = true;
            break;
        // 'dikirim' and 'selesai' remain the same
    }
    
    if ($needsUpdate) {
        $retur->save();
        $updated++;
        echo "Updated Retur ID {$retur->id}: '{$originalStatus}' -> '{$retur->status}'\n";
    } else {
        echo "Retur ID {$retur->id}: '{$originalStatus}' (no change needed)\n";
    }
}

echo "\nNormalization complete. Updated {$updated} records.\n";

// Show current status distribution
echo "\nCurrent status distribution:\n";
$statusCounts = PurchaseReturn::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $status) {
    echo "- {$status->status}: {$status->count} records\n";
}