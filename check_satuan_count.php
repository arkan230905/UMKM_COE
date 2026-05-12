<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "📊 Satuan Count Per User:\n";
echo "==========================\n\n";

$results = DB::table('satuans')
    ->select('user_id', DB::raw('COUNT(*) as total'))
    ->groupBy('user_id')
    ->get();

foreach ($results as $row) {
    $userId = $row->user_id ?? 'NULL';
    echo "User {$userId}: {$row->total} units\n";
}

echo "\n";
echo "Expected: All users should have 16 units\n";
