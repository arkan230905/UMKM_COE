<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = DB::table('jabatans')->count();

echo "=== VERIFY JABATAN TABLE ===\n\n";
echo "Total Jabatan in database: {$count}\n\n";

if ($count === 0) {
    echo "✅ Database is clean - No jabatan data\n";
    echo "✅ Users can now create their own jabatan\n";
} else {
    echo "⚠️  Still have {$count} jabatan data\n";
    
    $byUser = DB::table('jabatans')
        ->select('user_id', DB::raw('COUNT(*) as count'))
        ->groupBy('user_id')
        ->get();
    
    echo "\nBreakdown:\n";
    foreach ($byUser as $row) {
        echo "  User {$row->user_id}: {$row->count} jabatan\n";
    }
}
