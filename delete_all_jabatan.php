<?php

/**
 * DELETE ALL JABATAN
 * 
 * Hapus semua data jabatan dari database
 * HATI-HATI: Ini akan menghapus SEMUA jabatan untuk SEMUA user!
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DELETE ALL JABATAN ===\n\n";

// Count current jabatan
$totalJabatan = DB::table('jabatans')->count();

if ($totalJabatan === 0) {
    echo "✅ No jabatan found. Database is already clean.\n";
    exit(0);
}

echo "⚠️  WARNING: This will delete ALL jabatan data!\n";
echo "Total jabatan to delete: {$totalJabatan}\n\n";

// Show breakdown by user
$byUser = DB::table('jabatans')
    ->select('user_id', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id')
    ->get();

echo "Breakdown by user:\n";
foreach ($byUser as $row) {
    if ($row->user_id) {
        $user = DB::table('users')->where('id', $row->user_id)->first();
        $userName = $user ? $user->name : 'UNKNOWN';
        echo "  User {$row->user_id} ({$userName}): {$row->count} jabatan\n";
    } else {
        echo "  User NULL: {$row->count} jabatan\n";
    }
}

echo "\nAre you sure you want to delete ALL jabatan? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if ($line !== 'yes') {
    echo "\nAborted. No data deleted.\n";
    exit(0);
}

// Delete all jabatan
echo "\nDeleting all jabatan...\n";
$deleted = DB::table('jabatans')->delete();

echo "✅ Deleted {$deleted} jabatan records\n\n";

// Verify
$remaining = DB::table('jabatans')->count();

if ($remaining === 0) {
    echo "✅ All jabatan deleted successfully!\n";
    echo "Database is now clean. Users can create their own jabatan.\n";
} else {
    echo "⚠️  WARNING: Still have {$remaining} jabatan remaining\n";
}
