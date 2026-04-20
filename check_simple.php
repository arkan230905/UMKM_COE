<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$penggajian = App\Models\Penggajian::all(['id', 'tanggal_penggajian', 'status_pembayaran', 'total_gaji']);
echo "=== PENGGAJIAN DATA ===\n";
foreach($penggajian as $p) {
    echo "ID: {$p->id}, Date: {$p->tanggal_penggajian}, Status: {$p->status_pembayaran}, Total: {$p->total_gaji}\n";
}