<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== EXISTING USERS ===\n";

$users = DB::table('users')->select('id', 'name', 'email', 'role')->limit(5)->get();

foreach ($users as $user) {
    echo "ID: {$user->id} - {$user->name} ({$user->email}) - Role: " . ($user->role ?? 'N/A') . "\n";
}

echo "\nTotal users: " . DB::table('users')->count() . "\n";