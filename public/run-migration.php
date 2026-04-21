<?php
// Run migration via web request

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

echo "<pre>";
$status = $kernel->call('migrate', ['--step' => true]);
echo "</pre>";

echo $status === 0 ? '<h2 style="color: green;">✓ Migration berhasil dijalankan!</h2>' : '<h2 style="color: red;">✗ Migration gagal</h2>';
