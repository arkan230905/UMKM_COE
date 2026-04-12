<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test data
$returs = \App\Models\PurchaseReturn::with(['pembelian', 'items'])
    ->withSum('items as calculated_total', 'subtotal')
    ->latest()
    ->get();

echo "Total retur: " . $returs->count() . "\n";

foreach ($returs as $retur) {
    echo "ID: {$retur->id}\n";
    echo "Status: {$retur->status}\n";
    echo "Jenis Retur: " . ($retur->jenis_retur ?? 'NULL') . "\n";
    echo "Action Button: " . ($retur->action_button ? json_encode($retur->action_button) : 'NULL') . "\n";
    echo "Next Status: " . ($retur->next_status ?? 'NULL') . "\n";
    echo "---\n";
}