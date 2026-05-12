<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SAFE COA CLEANUP - HANDLING ALL FOREIGN KEYS ===\n\n";

// Find all tables that reference coas.id
echo "=== STEP 1: FINDING ALL FOREIGN KEY REFERENCES ===\n";

$foreign_key_tables = [
    'journal_lines' => 'coa_id',
    'jurnal_umum' => 'coa_id',
    'pembayaran_beban' => 'akun_kas_id',
    'pembelians' => 'akun_kas_id',
    'penjualans' => 'akun_kas_id',
    'penggajians' => 'akun_kas_id'
];

$all_referenced_ids = [];
foreach ($foreign_key_tables as $table => $column) {
    try {
        $references = DB::select("
            SELECT DISTINCT {$column} as coa_id, COUNT(*) as count
            FROM {$table} 
            WHERE {$column} IS NOT NULL
            GROUP BY {$column}
            ORDER BY {$column}
        ");
        
        if (!empty($references)) {
            echo "Table '{$table}' references:\n";
            foreach ($references as $ref) {
                echo "  COA ID {$ref->coa_id}: {$ref->count} records\n";
                $all_referenced_ids[] = $ref->coa_id;
            }
        }
    } catch (Exception $e) {
        echo "⚠️  Table '{$table}' check failed: " . $e->getMessage() . "\n";
    }
}

$all_referenced_ids = array_unique($all_referenced_ids);
echo "\nTotal referenced COA IDs: " . count($all_referenced_ids) . "\n\n";

// Get duplicates analysis
echo "=== STEP 2: ANALYZING DUPLICATES ===\n";

$duplicates = DB::select("
    SELECT 
        kode_akun,
        COUNT(*) as count,
        MIN(id) as keep_id,
        GROUP_CONCAT(id ORDER BY id) as all_ids,
        MIN(nama_akun) as nama_akun
    FROM coas 
    GROUP BY kode_akun 
    HAVING COUNT(*) > 1
    ORDER BY kode_akun
");

echo "Found " . count($duplicates) . " accounts with duplicates.\n\n";

// Prepare cleanup plan
$cleanup_plan = [];
foreach ($duplicates as $dup) {
    $ids_array = explode(',', $dup->all_ids);
    $keep_id = $dup->keep_id;
    $delete_ids = array_filter($ids_array, function($id) use ($keep_id) {
        return $id != $keep_id;
    });
    
    // Determine correct saldo_awal based on account
    $correct_saldo_awal = 0;
    switch ($dup->kode_akun) {
        case '111': // Kas Bank
            $correct_saldo_awal = 100000000;
            break;
        case '112': // Kas
            $correct_saldo_awal = 75000000;
            break;
        case '114': // Persediaan Bahan Baku
            $correct_saldo_awal = 6619850;
            break;
        case '115': // Persediaan Bahan Pendukung
            $correct_saldo_awal = 89900000;
            break;
        case '310': // Modal Usaha
            $correct_saldo_awal = 175000000;
            break;
        default:
            $correct_saldo_awal = 0;
    }
    
    $cleanup_plan[] = [
        'kode_akun' => $dup->kode_akun,
        'nama_akun' => $dup->nama_akun,
        'keep_id' => $keep_id,
        'delete_ids' => $delete_ids,
        'correct_saldo_awal' => $correct_saldo_awal
    ];
}

echo "=== STEP 3: EXECUTING SAFE CLEANUP ===\n";

try {
    DB::beginTransaction();
    
    $total_deleted = 0;
    $total_updated = 0;
    $total_references_updated = 0;
    
    foreach ($cleanup_plan as $plan) {
        echo "Processing account {$plan['kode_akun']} ({$plan['nama_akun']})...\n";
        
        // Update the kept record with correct saldo_awal
        $updated = DB::table('coas')
            ->where('id', $plan['keep_id'])
            ->update(['saldo_awal' => $plan['correct_saldo_awal']]);
        
        if ($updated) {
            $total_updated++;
            echo "  ✅ Updated saldo_awal to " . number_format($plan['correct_saldo_awal'], 0, ',', '.') . "\n";
        }
        
        // Update all foreign key references to point to the kept record
        foreach ($plan['delete_ids'] as $delete_id) {
            if (in_array($delete_id, $all_referenced_ids)) {
                echo "  🔄 Updating references from ID {$delete_id} to {$plan['keep_id']}...\n";
                
                foreach ($foreign_key_tables as $table => $column) {
                    try {
                        $updated_refs = DB::table($table)
                            ->where($column, $delete_id)
                            ->update([$column => $plan['keep_id']]);
                        
                        if ($updated_refs > 0) {
                            $total_references_updated += $updated_refs;
                            echo "    - {$table}: {$updated_refs} records\n";
                        }
                    } catch (Exception $e) {
                        echo "    ⚠️  {$table}: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        // Now safely delete duplicate records
        if (!empty($plan['delete_ids'])) {
            $deleted = DB::table('coas')
                ->whereIn('id', $plan['delete_ids'])
                ->delete();
            
            $total_deleted += $deleted;
            echo "  🗑️  Deleted {$deleted} duplicate records\n";
        }
        
        echo "\n";
    }
    
    DB::commit();
    
    echo "=== CLEANUP COMPLETED SUCCESSFULLY ===\n";
    echo "✅ Updated {$total_updated} records with correct saldo_awal\n";
    echo "🔄 Updated {$total_references_updated} foreign key references\n";
    echo "🗑️  Deleted {$total_deleted} duplicate records\n\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ ERROR during cleanup: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
    exit(1);
}

// Verify the results
echo "=== STEP 4: VERIFICATION ===\n";

$remaining_duplicates = DB::select("
    SELECT kode_akun, COUNT(*) as count
    FROM coas 
    GROUP BY kode_akun 
    HAVING COUNT(*) > 1
");

if (empty($remaining_duplicates)) {
    echo "✅ No more duplicate accounts found!\n";
} else {
    echo "⚠️  Still found duplicates:\n";
    foreach ($remaining_duplicates as $dup) {
        echo "  Account {$dup->kode_akun}: {$dup->count} records\n";
    }
}

// Show final saldo_awal values for key accounts
echo "\n=== FINAL SALDO AWAL VALUES ===\n";
$final_values = DB::select("
    SELECT kode_akun, nama_akun, saldo_awal
    FROM coas 
    WHERE kode_akun IN ('111', '112', '113', '114', '115', '310')
    ORDER BY kode_akun
");

foreach ($final_values as $val) {
    echo "Account {$val->kode_akun} ({$val->nama_akun}): " . 
         number_format($val->saldo_awal ?? 0, 0, ',', '.') . "\n";
}

// Test trial balance after cleanup
echo "\n=== TESTING TRIAL BALANCE AFTER CLEANUP ===\n";
$test_accounts = ['111', '112', '114', '115'];

foreach ($test_accounts as $code) {
    $tb_result = DB::select("
        SELECT 
            coa_summary.kode_akun,
            coa_summary.nama_akun,
            coa_summary.saldo_awal
        FROM (
            SELECT 
                c.kode_akun,
                MIN(c.nama_akun) as nama_akun,
                SUM(COALESCE(c.saldo_awal, 0)) as saldo_awal
            FROM coas c
            WHERE c.kode_akun = ?
            GROUP BY c.kode_akun
        ) coa_summary
    ", [$code]);
    
    if (!empty($tb_result)) {
        $result = $tb_result[0];
        echo "Account {$result->kode_akun} ({$result->nama_akun}): " . 
             number_format($result->saldo_awal, 0, ',', '.') . "\n";
    }
}

echo "\n=== CLEANUP PROCESS COMPLETED ===\n";