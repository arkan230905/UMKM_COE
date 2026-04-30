<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Catalog Company Data...\n\n";

// Check current user
echo "=== CURRENT USER ===\n";
if (\Illuminate\Support\Facades\Auth::check()) {
    $user = \Illuminate\Support\Facades\Auth::user();
    echo "User ID: {$user->id}\n";
    echo "User Name: {$user->name}\n";
    echo "User Email: {$user->email}\n";
    echo "User Perusahaan ID: " . ($user->perusahaan_id ?? 'NULL') . "\n";
    echo "User Role: " . ($user->role ?? 'NULL') . "\n";
} else {
    echo "No authenticated user found\n";
    // Simulate login with user ID 1
    \Illuminate\Support\Facades\Auth::loginUsingId(1);
    $user = \Illuminate\Support\Facades\Auth::user();
    echo "Logged in as User ID: {$user->id}\n";
    echo "User Perusahaan ID: " . ($user->perusahaan_id ?? 'NULL') . "\n";
}

echo "\n=== PERUSAHAAN DATA ===\n";
$perusahaan = \App\Models\Perusahaan::find($user->perusahaan_id);
if ($perusahaan) {
    echo "Perusahaan ID: {$perusahaan->id}\n";
    echo "Nama: {$perusahaan->nama}\n";
    echo "Email: {$perusahaan->email}\n";
    echo "Telepon: {$perusahaan->telepon}\n";
    echo "Alamat: {$perusahaan->alamat}\n";
    echo "Catalog Description: " . ($perusahaan->catalog_description ?? 'NULL') . "\n";
} else {
    echo "Perusahaan NOT FOUND for perusahaan_id: " . ($user->perusahaan_id ?? 'NULL') . "\n";
}

echo "\n=== ALL PERUSAHAAN ===\n";
$allPerusahaan = \App\Models\Perusahaan::all();
echo "Total Perusahaan: {$allPerusahaan->count()}\n";
foreach ($allPerusahaan as $p) {
    echo "  ID: {$p->id} - {$p->nama}\n";
}

echo "\n=== CHECK ABOUT PAGE COMPANY ===\n";
// Check if there's company data from about page
$aboutCompany = \App\Models\Perusahaan::first();
if ($aboutCompany) {
    echo "About Page Company Found:\n";
    echo "  ID: {$aboutCompany->id}\n";
    echo "  Nama: {$aboutCompany->nama}\n";
    echo "  Email: {$aboutCompany->email}\n";
    echo "  Telepon: {$aboutCompany->telepon}\n";
    echo "  Alamat: {$aboutCompany->alamat}\n";
    echo "  Catalog Description: " . ($aboutCompany->catalog_description ?? 'NULL') . "\n";
    
    // Check if this company can be used for catalog
    if ($user->perusahaan_id != $aboutCompany->id) {
        echo "  SUGGESTION: Update user perusahaan_id to {$aboutCompany->id}\n";
    }
} else {
    echo "No company data found in database\n";
}

echo "\n=== CATALOG SECTIONS ===\n";
if ($perusahaan) {
    $catalogSections = \Illuminate\Support\Facades\DB::table('catalog_sections')
        ->where('perusahaan_id', $perusahaan->id)
        ->get();
    
    echo "Catalog Sections for Perusahaan ID {$perusahaan->id}: {$catalogSections->count()}\n";
    foreach ($catalogSections as $section) {
        echo "  {$section->section_type}: {$section->order}\n";
    }
}

echo "\nCatalog company data check completed!\n";
