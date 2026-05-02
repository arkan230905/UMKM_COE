<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\Satuan;
use App\Models\User;

echo "==============================================\n";
echo "CEK DATA MASTER\n";
echo "==============================================\n\n";

$coaNull = Coa::whereNull('user_id')->count();
$coaNotNull = Coa::whereNotNull('user_id')->count();
$satuanNull = Satuan::whereNull('user_id')->count();
$satuanNotNull = Satuan::whereNotNull('user_id')->count();

echo "COA:\n";
echo "  - user_id = NULL (master): {$coaNull}\n";
echo "  - user_id != NULL (per user): {$coaNotNull}\n\n";

echo "Satuan:\n";
echo "  - user_id = NULL (master): {$satuanNull}\n";
echo "  - user_id != NULL (per user): {$satuanNotNull}\n\n";

$users = User::all();
echo "Total Users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    $userCoas = Coa::where('user_id', $user->id)->count();
    $userSatuans = Satuan::where('user_id', $user->id)->count();
    
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "  - COA: {$userCoas}\n";
    echo "  - Satuan: {$userSatuans}\n\n";
}

echo "==============================================\n";
