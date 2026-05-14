<?php
// Test if global scope is affecting Pegawai count
echo 'Testing without global scope:' . PHP_EOL;

// Temporarily disable global scope by calling without auth
\Illuminate\Support\Facades\Auth::logout();

// Test direct count
$count = \App\Models\Pegawai::withoutGlobalScope('user_id')->count();
echo 'Pegawai count without user scope: ' . $count . PHP_EOL;

// Test with auth
$user = \App\Models\User::find(1);
\Illuminate\Support\Facades\Auth::login($user);
$countWithAuth = \App\Models\Pegawai::count();
echo 'Pegawai count with auth: ' . $countWithAuth . PHP_EOL;
?>
