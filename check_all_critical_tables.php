<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking all critical tables for user_id columns...\n\n";

$criticalTables = [
    'produks' => 'Products',
    'penjualans' => 'Sales',
    'pembelians' => 'Purchases',
    'produksis' => 'Production',
    'presensis' => 'Attendance',
    'penggajians' => 'Payroll',
    'asets' => 'Assets',
    'jurnal_umum' => 'General Ledger',
    'penjualan_details' => 'Sales Details',
    'pembelian_details' => 'Purchase Details',
    'produksi_details' => 'Production Details',
    'boms' => 'Bill of Materials',
    'bom_details' => 'BOM Details',
    'stock_movements' => 'Stock Movements',
    'stock_layers' => 'Stock Layers',
    'returs' => 'Returns',
    'retur_penjualans' => 'Sales Returns',
    'detail_retur_penjualans' => 'Sales Return Details',
    'purchase_returns' => 'Purchase Returns',
    'purchase_return_items' => 'Purchase Return Items',
    'pelunasan_utangs' => 'Debt Payments',
    'pembayaran_bebans' => 'Expense Payments',
    'beban_operasional' => 'Operational Expenses',
    'catalog_photos' => 'Catalog Photos',
    'catalog_sections' => 'Catalog Sections',
    'favorites' => 'Favorites',
    'paket_menus' => 'Package Menus',
    'ongkir_settings' => 'Shipping Settings',
    'bukti_pembayaran' => 'Payment Proofs',
    'pelanggans' => 'Customers',
    'kartu_stok' => 'Stock Cards',
    'pegawais' => 'Employees'
];

$issuesFound = [];

foreach ($criticalTables as $table => $description) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        $hasUserId = \Illuminate\Support\Facades\Schema::hasColumn($table, 'user_id');
        $hasCompanyId = \Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id');
        
        $status = "OK";
        if (!$hasUserId && !$hasCompanyId) {
            $status = "MISSING";
            $issuesFound[] = $table;
        }
        
        echo sprintf("%-25s | %-25s | User: %s | Company: %s | %s\n", 
            $table, 
            $description, 
            $hasUserId ? "YES" : "NO",
            $hasCompanyId ? "YES" : "NO",
            $status
        );
    } else {
        echo sprintf("%-25s | %-25s | TABLE NOT FOUND\n", $table, $description);
        $issuesFound[] = $table;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total tables checked: " . count($criticalTables) . "\n";
echo "Issues found: " . count($issuesFound) . "\n";

if (!empty($issuesFound)) {
    echo "\nTables with issues:\n";
    foreach ($issuesFound as $table) {
        echo "  - {$table}\n";
    }
} else {
    echo "\nAll critical tables are properly configured for multi-tenant!\n";
}

echo "\nCritical tables check completed!\n";
