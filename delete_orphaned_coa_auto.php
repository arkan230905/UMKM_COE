<?php

/**
 * DELETE ORPHANED COA AUTOMATICALLY
 * 
 * Remove COA records that don't have valid user_id
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DELETE ORPHANED COA ===\n\n";

// Find all COA with invalid user_id
$orphanedCoas = DB::table('coas as c')
    ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
    ->whereNull('u.id')
    ->select('c.id', 'c.user_id', 'c.kode_akun', 'c.nama_akun')
    ->get();

if ($orphanedCoas->isEmpty()) {
    echo "✅ No orphaned COA found. Database is clean!\n";
    exit(0);
}

echo "Found " . $orphanedCoas->count() . " orphaned COA records:\n\n";

foreach ($orphanedCoas as $coa) {
    echo "  ID: {$coa->id} | User: {$coa->user_id} | Kode: {$coa->kode_akun} | Nama: {$coa->nama_akun}\n";
}

echo "\nDeleting orphaned records...\n";

// Delete orphaned COA
$orphanedIds = $orphanedCoas->pluck('id')->toArray();
$deleted = DB::table('coas')->whereIn('id', $orphanedIds)->delete();

echo "✅ Deleted {$deleted} orphaned COA records\n\n";

// Verify
$remaining = DB::table('coas as c')
    ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
    ->whereNull('u.id')
    ->count();

if ($remaining === 0) {
    echo "✅ All orphaned COA cleaned up successfully!\n";
} else {
    echo "⚠️  Still have {$remaining} orphaned COA\n";
}
