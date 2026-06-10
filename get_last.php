<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$penjualan = \App\Models\Penjualan::with('details')->orderBy('id', 'desc')->first();
echo json_encode($penjualan->toArray(), JSON_PRETTY_PRINT);
