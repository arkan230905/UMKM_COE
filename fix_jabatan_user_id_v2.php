<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIX JABATAN USER_ID (v2) ===\n\n";

// User arkan = ID 4
$targetUserId = 4;
$user = DB::table('users')->where('id', $targetUserId)->first();
echo "Target user: {$user->name} (ID: {$user->id})\n\n";

// Show all jabatan
$all = DB::table('jabatans')->get(['id','nama','kategori','user_id']);
echo "Jabatan records BEFORE fix:\n";
foreach ($all as $j) {
    echo "  ID:{$j->id} | {$j->nama} | {$j->kategori} | user_id={$j->user_id}\n";
}
echo "\n";

// Fix: update all jabatan with user_id=1 to user_id=4
// (assuming user_id=1 is the system/default user and arkan is the real owner)
$updated = DB::table('jabatans')
    ->where('user_id', 1)
    ->update(['user_id' => $targetUserId]);

echo "Updated {$updated} records from user_id=1 to user_id={$targetUserId}\n\n";

// Verify
$after = DB::table('jabatans')->get(['id','nama','kategori','user_id']);
echo "Jabatan records AFTER fix:\n";
foreach ($after as $j) {
    echo "  ID:{$j->id} | {$j->nama} | {$j->kategori} | user_id={$j->user_id}\n";
}

$btkl  = DB::table('jabatans')->where('user_id', $targetUserId)->where('kategori','btkl')->count();
$btktl = DB::table('jabatans')->where('user_id', $targetUserId)->where('kategori','btktl')->count();
echo "\nBTKL: {$btkl} | BTKTL: {$btktl}\n";
echo "\nSELESAI! Dropdown jabatan di halaman tambah pegawai sekarang akan menampilkan data.\n";
