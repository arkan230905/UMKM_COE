<?php
/**
 * Script untuk verify semua foreign key constraints
 * Jalankan: php verify_foreign_keys.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFICATION: Foreign Key Constraints\n";
echo str_repeat("=", 100) . "\n\n";

// Tabel yang harus memiliki foreign key ke accounts table
$tablesWithAccountsFk = [
    'pembayaran_beban' => ['akun_beban_id', 'akun_kas_id'],
    'pelunasan_utang' => ['akun_kas_id', 'coa_pelunasan_id'],
    'retur_kompensasi' => ['akun_id'],
    'jurnal_umum' => ['coa_id'],
    'asets' => ['coa_id', 'depr_expense_coa_id', 'depr_accum_coa_id'],
    'produksis' => ['coa_persediaan_barang_jadi_id'],
    'beban_operasional' => ['coa_id'],
    'bop_budgets' => ['coa_id'],
];

$allValid = true;

foreach ($tablesWithAccountsFk as $table => $columns) {
    echo "Checking table: {$table}\n";
    
    // Get foreign keys for this table
    $fks = DB::select("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = ? AND TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ", [$table]);
    
    foreach ($columns as $column) {
        $found = false;
        $isValid = false;
        
        foreach ($fks as $fk) {
            if ($fk->COLUMN_NAME === $column) {
                $found = true;
                $isValid = ($fk->REFERENCED_TABLE_NAME === 'accounts');
                
                $status = $isValid ? '✅' : '❌';
                echo "  {$status} {$column} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
                
                if (!$isValid) {
                    $allValid = false;
                    echo "     ERROR: Should reference 'accounts' table, not '{$fk->REFERENCED_TABLE_NAME}'\n";
                }
                break;
            }
        }
        
        if (!$found) {
            echo "  ⚠️  {$column} - NO FOREIGN KEY FOUND\n";
        }
    }
    
    echo "\n";
}

// Check if Account table has data
echo "Checking Account table data:\n";
$accountCount = DB::table('accounts')->count();
echo "  Total accounts: {$accountCount}\n";

if ($accountCount === 0) {
    echo "  ❌ WARNING: No accounts found in database!\n";
    $allValid = false;
} else {
    echo "  ✅ Accounts table has data\n";
}

echo "\n";

// Check sample data integrity
echo "Checking data integrity:\n";

$tables = [
    'pembayaran_beban' => ['akun_beban_id', 'akun_kas_id'],
    'pelunasan_utang' => ['akun_kas_id', 'coa_pelunasan_id'],
    'retur_kompensasi' => ['akun_id'],
];

foreach ($tables as $table => $columns) {
    $count = DB::table($table)->count();
    
    if ($count > 0) {
        echo "  Checking {$table} ({$count} records):\n";
        
        foreach ($columns as $column) {
            $orphaned = DB::table($table)
                ->leftJoin('accounts', 'accounts.id', '=', "{$table}.{$column}")
                ->whereNull('accounts.id')
                ->where("{$table}.{$column}", '!=', null)
                ->count();
            
            if ($orphaned > 0) {
                echo "    ❌ {$column}: {$orphaned} orphaned records found!\n";
                $allValid = false;
            } else {
                echo "    ✅ {$column}: All records valid\n";
            }
        }
    } else {
        echo "  {$table}: No data\n";
    }
}

echo "\n" . str_repeat("=", 100) . "\n";

if ($allValid) {
    echo "✅ ALL CHECKS PASSED - Foreign keys are correctly configured!\n";
} else {
    echo "❌ SOME CHECKS FAILED - Please review the errors above\n";
}

echo str_repeat("=", 100) . "\n\n";
