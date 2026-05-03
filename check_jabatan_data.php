<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Jabatan data...\n";
echo "Total Jabatan records: " . \App\Models\Jabatan::count() . "\n";

$kategoris = \App\Models\Jabatan::whereNotNull('kategori')
    ->where('kategori', '!=', '')
    ->distinct()
    ->pluck('kategori');

echo "Unique kategoris found: " . $kategoris->count() . "\n";
echo "Kategoris: " . $kategoris->implode(', ') . "\n";

// Check by user
$users = \App\Models\User::where('role', 'owner')->get();
foreach ($users as $user) {
    $count = \App\Models\Jabatan::where('user_id', $user->id)->count();
    $userKategoris = \App\Models\Jabatan::where('user_id', $user->id)
        ->whereNotNull('kategori')
        ->where('kategori', '!=', '')
        ->distinct()
        ->pluck('kategori');
    echo "\nUser: {$user->name} (ID: {$user->id})\n";
    echo "  Jabatan count: {$count}\n";
    echo "  Kategoris: " . $userKategoris->implode(', ') . "\n";
}
