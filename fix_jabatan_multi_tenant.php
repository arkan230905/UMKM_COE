<?php

/**
 * FIX JABATAN MULTI-TENANT
 * 
 * 1. Delete jabatan with wrong user_id or NULL user_id
 * 2. Seed default jabatan for all users
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX JABATAN MULTI-TENANT ===\n\n";

// Step 1: Check current jabatan
echo "Step 1: Checking current jabatan...\n";
$allJabatan = DB::table('jabatans')->get(['id', 'user_id', 'kode_jabatan', 'nama', 'kategori']);

echo "Total jabatan in database: " . $allJabatan->count() . "\n\n";

// Group by user_id
$byUser = $allJabatan->groupBy('user_id');

foreach ($byUser as $userId => $jabatans) {
    if ($userId) {
        $user = DB::table('users')->where('id', $userId)->first();
        $userName = $user ? $user->name : 'USER NOT FOUND';
        echo "User {$userId} ({$userName}): {$jabatans->count()} jabatan\n";
    } else {
        echo "User NULL: {$jabatans->count()} jabatan (ORPHANED)\n";
    }
}

echo "\n";

// Step 2: Delete orphaned or wrong jabatan
echo "Step 2: Cleaning up jabatan...\n";

// Get all valid user IDs
$validUserIds = DB::table('users')->pluck('id')->toArray();

// Delete jabatan with invalid user_id
$deleted = DB::table('jabatans')
    ->where(function($query) use ($validUserIds) {
        $query->whereNull('user_id')
              ->orWhereNotIn('user_id', $validUserIds);
    })
    ->delete();

if ($deleted > 0) {
    echo "✅ Deleted {$deleted} orphaned/invalid jabatan\n\n";
} else {
    echo "ℹ️  No orphaned jabatan found\n\n";
}

// Step 3: Seed jabatan for all users
echo "Step 3: Seeding jabatan for all users...\n";

$users = DB::table('users')->get(['id', 'name', 'email']);

$seeded = 0;
$skipped = 0;

foreach ($users as $user) {
    echo "User {$user->id} ({$user->name}):\n";
    
    $jabatanCount = DB::table('jabatans')->where('user_id', $user->id)->count();
    
    if ($jabatanCount > 0) {
        echo "  ⏭️  Already has {$jabatanCount} jabatan - SKIPPED\n\n";
        $skipped++;
        continue;
    }
    
    try {
        $seeder = new \Database\Seeders\DefaultJabatanSeeder();
        $seeder->run($user->id);
        
        $newCount = DB::table('jabatans')->where('user_id', $user->id)->count();
        echo "  ✅ Created {$newCount} jabatan\n\n";
        $seeded++;
        
    } catch (\Exception $e) {
        echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "=== SUMMARY ===\n";
echo "Deleted orphaned: {$deleted}\n";
echo "Users seeded: {$seeded}\n";
echo "Users skipped: {$skipped}\n\n";

// Step 4: Verify
echo "=== VERIFICATION ===\n";

foreach ($users as $user) {
    $jabatanCount = DB::table('jabatans')->where('user_id', $user->id)->count();
    
    if ($jabatanCount > 0) {
        // Count by kategori
        $btklCount = DB::table('jabatans')
            ->where('user_id', $user->id)
            ->where('kategori', 'btkl')
            ->count();
        
        $btktlCount = DB::table('jabatans')
            ->where('user_id', $user->id)
            ->where('kategori', 'btktl')
            ->count();
        
        echo "✅ User {$user->id} ({$user->name}): {$jabatanCount} jabatan (BTKL: {$btklCount}, BTKTL: {$btktlCount})\n";
    } else {
        echo "❌ User {$user->id} ({$user->name}): NO jabatan\n";
    }
}

// Check for orphaned jabatan
$orphaned = DB::table('jabatans as j')
    ->leftJoin('users as u', 'j.user_id', '=', 'u.id')
    ->whereNull('u.id')
    ->count();

if ($orphaned > 0) {
    echo "\n⚠️  WARNING: Still have {$orphaned} orphaned jabatan\n";
} else {
    echo "\n✅ No orphaned jabatan\n";
}

echo "\n✅ Fix complete!\n";
