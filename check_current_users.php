<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CURRENT USERS IN DATABASE ===\n\n";

$users = DB::table('users')
    ->select('id', 'name', 'email', 'role', 'created_at')
    ->orderBy('id')
    ->get();

if ($users->isEmpty()) {
    echo "❌ No users found in database\n";
    exit(1);
}

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Role: {$user->role}\n";
    echo "  Created: {$user->created_at}\n";
    
    // Check COA count
    $coaCount = DB::table('coas')->where('user_id', $user->id)->count();
    echo "  COA Count: {$coaCount}\n";
    
    if ($coaCount > 0) {
        // Check specific important COAs
        $importantCoas = DB::table('coas')
            ->where('user_id', $user->id)
            ->whereIn('kode_akun', ['1141', '1161', '1171', '1172', '1173', '211', '550'])
            ->pluck('nama_akun', 'kode_akun');
        
        if ($importantCoas->isNotEmpty()) {
            echo "  Important COAs:\n";
            foreach ($importantCoas as $kode => $nama) {
                echo "    - {$kode}: {$nama}\n";
            }
        }
    } else {
        echo "  ⚠️  WARNING: User has NO COA!\n";
    }
    
    // Check Satuan count
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    echo "  Satuan Count: {$satuanCount}\n";
    
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Total Users: " . $users->count() . "\n";
echo "Users with COA: " . $users->filter(function($u) {
    return DB::table('coas')->where('user_id', $u->id)->exists();
})->count() . "\n";
echo "Users without COA: " . $users->filter(function($u) {
    return !DB::table('coas')->where('user_id', $u->id)->exists();
})->count() . "\n";

echo "\n✅ Check complete!\n";
