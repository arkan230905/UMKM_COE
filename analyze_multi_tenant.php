<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Analyzing Multi-Tenant Implementation...\n\n";

// Check current multi-tenant setup
echo "=== CURRENT MULTI-TENANT SETUP ===\n";

// Check if models have company/user_id relationships
$modelsToCheck = [
    'Coa' => 'Chart of Accounts',
    'Produk' => 'Products',
    'Penjualan' => 'Sales',
    'Pembelian' => 'Purchases',
    'BahanBaku' => 'Raw Materials',
    'BahanPendukung' => 'Supporting Materials',
    'Produksi' => 'Production',
    'Pegawai' => 'Employees',
    'Presensi' => 'Attendance',
    'Penggajian' => 'Payroll',
    'Aset' => 'Assets',
    'JurnalUmum' => 'General Ledger',
    'Perusahaan' => 'Company'
];

echo "Model Company/User ID Analysis:\n";
echo "===============================\n";

foreach ($modelsToCheck as $model => $description) {
    $modelClass = "App\\Models\\{$model}";
    if (class_exists($modelClass)) {
        $modelInstance = new $modelClass();
        $fillable = $modelInstance->getFillable();
        
        $hasUserId = in_array('user_id', $fillable);
        $hasCompanyId = in_array('company_id', $fillable);
        $hasPerusahaanId = in_array('perusahaan_id', $fillable);
        
        echo sprintf("%-20s | %-25s | User: %s | Company: %s | Perusahaan: %s\n", 
            $model, 
            $description, 
            $hasUserId ? "YES" : "NO",
            $hasCompanyId ? "YES" : "NO", 
            $hasPerusahaanId ? "YES" : "NO"
        );
    } else {
        echo sprintf("%-20s | %-25s | MODEL NOT FOUND\n", $model, $description);
    }
}

echo "\n=== CURRENT DATA ISOLATION CHECK ===\n";

// Check if data is properly isolated
echo "Checking data isolation issues...\n";

// Check COA data
$coaWithoutUser = \Illuminate\Support\Facades\DB::table('coas')
    ->whereNull('user_id')
    ->count();

$coaWithUser = \Illuminate\Support\Facades\DB::table('coas')
    ->whereNotNull('user_id')
    ->count();

echo "COA Records:\n";
echo "  Without user_id: {$coaWithoutUser}\n";
echo "  With user_id: {$coaWithUser}\n";

// Check products data
$produkWithoutUser = \Illuminate\Support\Facades\DB::table('produks')
    ->whereNull('user_id')
    ->count();

$produkWithUser = \Illuminate\Support\Facades\DB::table('produks')
    ->whereNotNull('user_id')
    ->count();

echo "\nProducts Records:\n";
echo "  Without user_id: {$produkWithoutUser}\n";
echo "  With user_id: {$produkWithUser}\n";

// Check journal data
$journalWithoutUser = \Illuminate\Support\Facades\DB::table('jurnal_umum')
    ->whereNull('user_id')
    ->count();

$journalWithUser = \Illuminate\Support\Facades\DB::table('jurnal_umum')
    ->whereNotNull('user_id')
    ->count();

echo "\nJournal Records:\n";
echo "  Without user_id: {$journalWithoutUser}\n";
echo "  With user_id: {$journalWithUser}\n";

echo "\n=== USER COMPANY RELATIONSHIPS ===\n";

// Check user-perusahaan relationships
$usersWithPerusahaan = \Illuminate\Support\Facades\DB::table('users')
    ->whereNotNull('perusahaan_id')
    ->count();

$usersWithoutPerusahaan = \Illuminate\Support\Facades\DB::table('users')
    ->whereNull('perusahaan_id')
    ->count();

echo "Users:\n";
echo "  With perusahaan_id: {$usersWithPerusahaan}\n";
echo "  Without perusahaan_id: {$usersWithoutPerusahaan}\n";

// Check perusahaan data
$perusahaanCount = \Illuminate\Support\Facades\DB::table('perusahaan')
    ->count();

echo "\nCompanies: {$perusahaanCount}\n";

if ($perusahaanCount > 0) {
    $companies = \Illuminate\Support\Facades\DB::table('perusahaan')->get();
    foreach ($companies as $company) {
        $userCount = \Illuminate\Support\Facades\DB::table('users')
            ->where('perusahaan_id', $company->id)
            ->count();
        
        echo "  Company {$company->id} ({$company->nama}): {$userCount} users\n";
    }
}

echo "\n=== GLOBAL SCOPES CHECK ===\n";

// Check if models use global scopes for multi-tenant
echo "Checking global scopes implementation...\n";

// Check Coa model
try {
    $coaModel = new \App\Models\Coa();
    $globalScopes = method_exists($coaModel, 'booted') ? get_class_methods($coaModel) : [];
    echo "COA Model methods: " . implode(', ', array_slice($globalScopes, 0, 5)) . "...\n";
} catch (Exception $e) {
    echo "COA Model: Error checking - " . $e->getMessage() . "\n";
}

// Check if there are any queries that don't use company filtering
echo "\n=== POTENTIAL ISOLATION ISSUES ===\n";

// Check for queries that might not filter by company
echo "Checking for potential data leakage...\n";

// This would need to be done by reviewing controller methods
// For now, let's check if there are any records without proper company association

$problematicTables = [
    'coas' => 'COA records without user_id',
    'produks' => 'Products without user_id', 
    'jurnal_umum' => 'Journal entries without user_id',
    'penjualans' => 'Sales without user_id',
    'pembelians' => 'Purchases without user_id'
];

foreach ($problematicTables as $table => $description) {
    $count = \Illuminate\Support\Facades\DB::table($table)
        ->whereNull('user_id')
        ->count();
    
    if ($count > 0) {
        echo "ISSUE: {$table} has {$count} records without user_id - {$description}\n";
    }
}

echo "\nMulti-tenant analysis completed!\n";
