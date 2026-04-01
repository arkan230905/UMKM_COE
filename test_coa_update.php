<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $coa = App\Models\Coa::find(83);
    if ($coa) {
        echo "Current kode_akun: " . $coa->kode_akun . "\n";
        $coa->kode_akun = '539';
        $coa->save();
        echo "COA updated successfully to: " . $coa->kode_akun . "\n";
    } else {
        echo "COA not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}