<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking User-Company Relationships...\n\n";

// Check users table
$users = \Illuminate\Support\Facades\DB::table('users')->get();
echo "Users in database: {$users->count()}\n";
foreach ($users as $user) {
    echo "  ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Perusahaan ID: " . ($user->perusahaan_id ?? 'NULL') . ", Company ID: " . ($user->company_id ?? 'NULL') . "\n";
}

echo "\nCompanies in database:\n";
$companies = \Illuminate\Support\Facades\DB::table('perusahaan')->get();
foreach ($companies as $company) {
    echo "  ID: {$company->id}, Name: {$company->nama}, Kode: {$company->kode}\n";
}

echo "\nChecking if user_id 1 exists: ";
$user1 = \Illuminate\Support\Facades\DB::table('users')->find(1);
echo $user1 ? "YES" : "NO";

echo "\nChecking if company_id 1 exists: ";
$company1 = \Illuminate\Support\Facades\DB::table('perusahaan')->find(1);
echo $company1 ? "YES" : "NO";

echo "\nUser-Company relationship check completed!\n";
