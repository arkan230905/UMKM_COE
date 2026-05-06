<?php
// Script to fix NULL user_id in jabatans table
// Run: php fix_jabatan_user_id.php

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIX JABATAN USER_ID ===\n\n";

// Find user arkan
$user = DB::table('users')->where('email', 'arkan@gmail.com')->first();
if (!$user) {
    // Try to find any owner user
    $user = DB::table('users')->where('role', 'owner')->first();
}

if (!$user) {
    echo "ERROR: No user found!\n";
    exit(1);
}

echo "Target user: {$user->name} (ID: {$user->id})\n\n";

// Show current state
$all = DB::table('jabatans')->get(['id','nama','kategori','user_id']);
echo "Current jabatan records:\n";
foreach ($all as $j) {
    echo "  ID:{$j->id} | {$j->nama} | {$j->kategori} | user_id=" . ($j->user_id ?? 'NULL') . "\n";
}
echo "\n";

// Fix NULL user_id
$nullCount = DB::table('jabatans')->whereNull('user_id')->count();
echo "Records with NULL user_id: {$nullCount}\n";

if ($nullCount > 0) {
    $updated = DB::table('jabatans')
        ->whereNull('user_id')
        ->update(['user_id' => $user->id]);
    echo "Fixed {$updated} records -> user_id = {$user->id}\n\n";
} else {
    echo "No NULL records to fix.\n\n";
}

// Verify
$fixed = DB::table('jabatans')->where('user_id', $user->id)->get(['id','nama','kategori','user_id']);
echo "After fix - jabatan for user {$user->id}:\n";
foreach ($fixed as $j) {
    echo "  ID:{$j->id} | {$j->nama} | {$j->kategori} | user_id={$j->user_id}\n";
}

$btkl = DB::table('jabatans')->where('user_id', $user->id)->where('kategori','btkl')->count();
$btktl = DB::table('jabatans')->where('user_id', $user->id)->where('kategori','btktl')->count();
echo "\nBTKL: {$btkl} | BTKTL: {$btktl}\n";
echo "\nDone! Dropdown jabatan seharusnya sudah berfungsi.\n";
