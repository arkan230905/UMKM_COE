<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking retur_details table structure ===" . PHP_EOL;
$columns = \DB::select('DESCRIBE retur_details');
foreach ($columns as $column) {
    echo "- {$column->Field}: {$column->Type}" . PHP_EOL;
}

echo PHP_EOL . "=== Checking retur_details data ===" . PHP_EOL;
$returDetails = \DB::table('retur_details')->get();
echo "retur_details count: " . $returDetails->count() . PHP_EOL;

foreach ($returDetails as $detail) {
    echo "ID: {$detail->id}";
    foreach (get_object_vars($detail) as $key => $value) {
        if ($key !== 'id') {
            echo ", $key: $value";
        }
    }
    echo PHP_EOL;
}

echo PHP_EOL . "=== Checking Sale ID 3 relationships ===" . PHP_EOL;
$sale = \App\Models\Penjualan::find(3);
if ($sale) {
    echo "Sale found: {$sale->nomor_penjualan}" . PHP_EOL;
    
    // Check different relationship methods
    try {
        $returs = $sale->returs;
        echo "returs relationship count: " . $returs->count() . PHP_EOL;
    } catch (Exception $e) {
        echo "Error with returs relationship: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Checking all return-related models ===" . PHP_EOL;
$models = [
    'SalesReturn' => \App\Models\SalesReturn::class,
    'ReturPenjualan' => \App\Models\ReturPenjualan::class,
    'Retur' => \App\Models\Retur::class,
    'ReturDetail' => \App\Models\ReturDetail::class,
    'DetailReturPenjualan' => \App\Models\DetailReturPenjualan::class,
    'SalesReturnItem' => \App\Models\SalesReturnItem::class,
];

foreach ($models as $name => $class) {
    try {
        $count = $class::count();
        echo "$name: $count records" . PHP_EOL;
        
        if ($count > 0 && $count <= 5) {
            $records = $class::all();
            foreach ($records as $record) {
                echo "  - ID: {$record->id}";
                if (isset($record->penjualan_id)) {
                    echo ", penjualan_id: {$record->penjualan_id}";
                }
                if (isset($record->return_date)) {
                    echo ", return_date: {$record->return_date}";
                }
                echo PHP_EOL;
            }
        }
    } catch (Exception $e) {
        echo "$name: Error - " . $e->getMessage() . PHP_EOL;
    }
}
