<?php
/**
 * Script to fix orphaned data (records without user_id)
 * This script finds all records in bahan_bakus and bahan_pendukungs
 * that don't have a user_id and reports them.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING FOR ORPHANED DATA ===\n\n";

// Check bahan_bakus
$orphanedBahanBaku = DB::table('bahan_bakus')
    ->whereNull('user_id')
    ->get();

echo "Bahan Baku without user_id: " . $orphanedBahanBaku->count() . "\n";
if ($orphanedBahanBaku->count() > 0) {
    echo "Records:\n";
    foreach ($orphanedBahanBaku as $item) {
        echo "  - ID: {$item->id}, Nama: {$item->nama_bahan}, Created: {$item->created_at}\n";
    }
}
echo "\n";

// Check bahan_pendukungs
$orphanedBahanPendukung = DB::table('bahan_pendukungs')
    ->whereNull('user_id')
    ->get();

echo "Bahan Pendukung without user_id: " . $orphanedBahanPendukung->count() . "\n";
if ($orphanedBahanPendukung->count() > 0) {
    echo "Records:\n";
    foreach ($orphanedBahanPendukung as $item) {
        echo "  - ID: {$item->id}, Nama: {$item->nama_bahan}, Created: {$item->created_at}\n";
    }
}
echo "\n";

// Get all users to help determine which user should own the orphaned data
$users = DB::table('users')->select('id', 'name', 'email', 'created_at')->get();
echo "Available users:\n";
foreach ($users as $user) {
    echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Created: {$user->created_at}\n";
}
echo "\n";

echo "=== ANALYSIS COMPLETE ===\n";
echo "To fix this data, we need to determine which user should own each orphaned record.\n";
echo "This can be based on:\n";
echo "1. Creation timestamp (assign to user created around the same time)\n";
echo "2. Manual assignment (if you know which user created what)\n";
echo "3. Assign all to a specific user ID\n";
