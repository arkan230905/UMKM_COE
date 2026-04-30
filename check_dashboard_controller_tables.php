<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking DashboardController table requirements...\n";

// Tables used in DashboardController
$dashboardTables = [
    'pegawais' => 'Pegawai',
    'presensis' => 'Presensi', 
    'produks' => 'Produk',
    'vendors' => 'Vendor',
    'bahan_bakus' => 'BahanBaku',
    'satuans' => 'Satuan',
    'pelanggans' => 'Pelanggan',
    'bahan_pendukungs' => 'BahanPendukung',
    'asets' => 'Aset',
    'jabatans' => 'Jabatan',
    'proses_produksis' => 'ProsesProduksi',
    'bop_proses' => 'BopProses',
    'coas' => 'Coa'
];

echo "Checking table structures for user_id columns:\n";
$issuesFound = [];

foreach ($dashboardTables as $table => $model) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        $hasUserId = \Illuminate\Support\Facades\Schema::hasColumn($table, 'user_id');
        $hasCompanyId = \Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id');
        
        $status = "OK";
        if (!$hasUserId && !$hasCompanyId) {
            $status = "MISSING USER_ID";
            $issuesFound[] = $table;
        }
        
        echo sprintf("%-20s | %-15s | User: %s | Company: %s | %s\n", 
            $table, 
            $model, 
            $hasUserId ? "YES" : "NO",
            $hasCompanyId ? "YES" : "NO",
            $status
        );
    } else {
        echo sprintf("%-20s | %-15s | TABLE NOT FOUND\n", $table, $model);
    }
}

echo "\n=== SUMMARY ===\n";
echo "Tables checked: " . count($dashboardTables) . "\n";
echo "Issues found: " . count($issuesFound) . "\n";

if (!empty($issuesFound)) {
    echo "\nTables missing user_id column:\n";
    foreach ($issuesFound as $table) {
        echo "  - {$table}\n";
    }
} else {
    echo "\nAll DashboardController tables are properly configured!\n";
}

echo "\nDashboardController tables check completed!\n";
