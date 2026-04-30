<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Multi-Tenant Data Isolation...\n\n";

// Get all companies
$companies = \Illuminate\Support\Facades\DB::table('perusahaan')->get();
echo "Companies found: {$companies->count()}\n";

foreach ($companies as $company) {
    echo "\n=== COMPANY: {$company->nama} (ID: {$company->id}) ===\n";
    
    // Test COA isolation
    $coaCount = \Illuminate\Support\Facades\DB::table('coas')
        ->where('user_id', $company->id)
        ->count();
    
    $coaSample = \Illuminate\Support\Facades\DB::table('coas')
        ->where('user_id', $company->id)
        ->limit(3)
        ->get();
    
    echo "COA Records: {$coaCount}\n";
    foreach ($coaSample as $coa) {
        echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
    }
    
    // Test Satuan isolation
    $satuanCount = \Illuminate\Support\Facades\DB::table('satuans')
        ->where('user_id', $company->id)
        ->count();
    
    $satuanSample = \Illuminate\Support\Facades\DB::table('satuans')
        ->where('user_id', $company->id)
        ->limit(3)
        ->get();
    
    echo "Satuan Records: {$satuanCount}\n";
    foreach ($satuanSample as $satuan) {
        echo "  {$satuan->kode} - {$satuan->nama}\n";
    }
    
    // Test user association
    $userCount = \Illuminate\Support\Facades\DB::table('users')
        ->where('perusahaan_id', $company->id)
        ->count();
    
    echo "Associated Users: {$userCount}\n";
    
    $users = \Illuminate\Support\Facades\DB::table('users')
        ->where('perusahaan_id', $company->id)
        ->get();
    
    foreach ($users as $user) {
        echo "  {$user->name} ({$user->email})\n";
    }
}

echo "\n=== DATA ISOLATION VERIFICATION ===\n";

// Verify that COA codes are unique per company
$coaConflicts = \Illuminate\Support\Facades\DB::table('coas')
    ->select('kode_akun', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
    ->groupBy('kode_akun')
    ->having('count', '>', 1)
    ->get();

if ($coaConflicts->isEmpty()) {
    echo "COA Isolation: PASSED (No duplicate codes across companies)\n";
} else {
    echo "COA Isolation: FAILED (Found duplicate codes)\n";
    foreach ($coaConflicts as $conflict) {
        echo "  Code {$conflict->kode_akun} appears {$conflict->count} times\n";
    }
}

// Verify that Satuan codes are unique per company
$satuanConflicts = \Illuminate\Support\Facades\DB::table('satuans')
    ->select('kode', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
    ->groupBy('kode')
    ->having('count', '>', 1)
    ->get();

if ($satuanConflicts->isEmpty()) {
    echo "Satuan Isolation: PASSED (No duplicate codes across companies)\n";
} else {
    echo "Satuan Isolation: FAILED (Found duplicate codes)\n";
    foreach ($satuanConflicts as $conflict) {
        echo "  Code {$conflict->kode} appears {$conflict->count} times\n";
    }
}

// Test cross-company data access simulation
echo "\n=== CROSS-COMPANY ACCESS TEST ===\n";

// Simulate user from Company A trying to access Company B data
$companyA = \Illuminate\Support\Facades\DB::table('perusahaan')->find(1);
$companyB = \Illuminate\Support\Facades\DB::table('perusahaan')->find(2);

if ($companyA && $companyB) {
    // Get user from Company A
    $userA = \Illuminate\Support\Facades\DB::table('users')
        ->where('perusahaan_id', $companyA->id)
        ->first();
    
    if ($userA) {
        // Simulate login as user A
        \Illuminate\Support\Facades\Auth::loginUsingId($userA->id);
        
        // Try to access Company B data (should be empty if isolation works)
        $companyBData = \Illuminate\Support\Facades\DB::table('coas')
            ->where('user_id', $companyB->id)
            ->count();
        
        echo "User from Company A accessing Company B COA data: {$companyBData} records (should be 0)\n";
        
        // Try to access own company data (should have records)
        $companyAData = \Illuminate\Support\Facades\DB::table('coas')
            ->where('user_id', $companyA->id)
            ->count();
        
        echo "User from Company A accessing own COA data: {$companyAData} records (should be > 0)\n";
        
        if ($companyBData == 0 && $companyAData > 0) {
            echo "Data Isolation: WORKING CORRECTLY\n";
        } else {
            echo "Data Isolation: NOT WORKING\n";
        }
    }
}

echo "\nMulti-tenant isolation test completed!\n";
