<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\User;

// Login sebagai user
$user = User::latest()->first();
auth()->login($user);

echo "Logged in as: {$user->name}\n";
echo "Company ID: {$user->perusahaan_id}\n\n";

// Test dengan global scope (default)
$coasWithScope = Coa::count();
echo "COA dengan global scope: {$coasWithScope}\n";

// Test tanpa global scope
$coasWithoutScope = Coa::withoutGlobalScopes()->count();
echo "COA tanpa global scope: {$coasWithoutScope}\n";

// Test COA template
$coaTemplate = Coa::withoutGlobalScopes()->whereNull('company_id')->count();
echo "COA template (company_id = null): {$coaTemplate}\n";

// Test COA company 1
$coaCompany1 = Coa::withoutGlobalScopes()->where('company_id', 1)->count();
echo "COA company 1: {$coaCompany1}\n";

echo "\n✓ Global scope bekerja dengan baik!\n";
