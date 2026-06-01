<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JenisAset;

$count = JenisAset::where('nama', '!=', 'Aset Tetap')->count();
echo 'Count to delete: ' . $count . PHP_EOL;

if ($count > 0) {
    JenisAset::where('nama', '!=', 'Aset Tetap')->delete();
    echo 'Deleted successfully.' . PHP_EOL;
}
