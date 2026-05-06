<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG JABATAN API ===\n\n";

// Check all users
$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id}, Role: {$user->role})\n";
    
    $jabatans = \App\Models\Jabatan::where('user_id', $user->id)->get();
    echo "  Total Jabatan: " . $jabatans->count() . "\n";
    
    if ($jabatans->count() > 0) {
        foreach ($jabatans as $jab) {
            echo "    - {$jab->nama} (Kategori: {$jab->kategori})\n";
        }
    }
    
    // Test API for BTKL
    $btklJabatans = \App\Models\Jabatan::where('user_id', $user->id)
        ->where('kategori', 'btkl')
        ->get();
    echo "  Jabatan BTKL: " . $btklJabatans->count() . "\n";
    
    // Test API for BTKTL
    $btktlJabatans = \App\Models\Jabatan::where('user_id', $user->id)
        ->where('kategori', 'btktl')
        ->get();
    echo "  Jabatan BTKTL: " . $btktlJabatans->count() . "\n";
    
    echo "\n";
}

// Check if there are any Jabatan without user_id
$orphanJabatans = \App\Models\Jabatan::whereNull('user_id')->orWhere('user_id', 0)->get();
echo "Jabatan without user_id: " . $orphanJabatans->count() . "\n";
if ($orphanJabatans->count() > 0) {
    foreach ($orphanJabatans as $jab) {
        echo "  - {$jab->nama} (Kategori: {$jab->kategori}, user_id: {$jab->user_id})\n";
    }
}
