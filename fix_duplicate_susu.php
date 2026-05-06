<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StockMovement;

echo "Menghapus duplikat Susu...\n";

// Hapus record dengan ID 3 (duplikat)
$deleted = StockMovement::where('id', 3)->delete();

if ($deleted) {
    echo "✓ Berhasil hapus duplikat (ID 3)\n";
} else {
    echo "✗ Tidak ada record dengan ID 3\n";
}

// Tampilkan sisa data
$remaining = StockMovement::where('item_type', 'support')->where('item_id', 1)->get();
echo "\nSisa stock movements untuk Susu:\n";
foreach ($remaining as $r) {
    echo "- ID {$r->id}: qty={$r->qty}, created={$r->created_at}\n";
}
