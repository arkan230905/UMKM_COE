<?php
// Test if global scope is affecting Pegawai count
require __DIR__.'/vendor/autoload.php';

echo 'Testing without global scope:' . PHP_EOL;

// Test direct count without auth context
$count = \App\Models\Pegawai::withoutGlobalScope('user_id')->count();
echo 'Pegawai count without user scope: ' . $count . PHP_EOL;
?>
