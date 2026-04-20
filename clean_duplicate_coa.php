<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CLEANING DUPLICATE COA RECORDS ===\n\n";

// Check if there are any foreign key references that might be affected
echo "=== STEP 1: CHECKING FOREIGN KEY REFERENCES ===\n";

$tables_to_check = [
    'journal_lines' => 'coa_id',
    'jurnal_umum' => 'coa_id'
];

$referenced_coa_ids = [];
foreach ($tables_to_check as $table => $column) {
    $references = DB::select("
        SELECT DISTINCT {$column} as coa_id, COUNT(*) as count
        FROM {$table} 
        WHERE {$column} IS NOT NULL
        GROUP BY {$column}
        ORDER BY {$column}
    ");
    
    if (!empty($references)) {
        echo "Table '{$table}' has references to COA IDs:\n";
        foreach ($references as $ref) {
            echo "  COA ID {$ref->coa_id}: {$ref->count} references\n";
            $referenced_coa_ids[] = $ref->coa_id;
        }
    }
}

$referenced_coa_ids = array_unique($referenced_coa_ids);
echo "\nTotal referenced COA IDs: " . count($referenced_coa_ids) . "\n\n";

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
$safe_to_delete = [];
$unsafe_to_delete = [];

foreach ($duplicates as $dup) {
    $ids_array = explode(',', $dup->all_ids);
    $keep_id = $dup->keep_id;
    $delete_ids = array_filter($ids_array, function($id) use ($keep_id) {
        return $id != $keep_id;
    });
    
    // Check if any of the delete_ids are referenced
    $has_references = false;
    foreach ($delete_ids as $id) {
        if (in_array($id, $referenced_coa_ids)) {
            $has_references = true;
            break;
        }
    }
    
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
    
    $plan = [
        'kode_akun' => $dup->kode_akun,
        'nama_akun' => $dup->nama_akun,
        'keep_id' => $keep_id,
        'delete_ids' => $delete_ids,
        'correct_saldo_awal' => $correct_saldo_awal,
        'has_references' => $has_references
    ];
    
    if ($has_references) {
        $unsafe_to_delete[] = $plan;
    } else {
        $safe_to_delete[] = $plan;
    }
    
    $cleanup_plan[] = $plan;
}

echo "Safe to delete (no references): " . count($safe_to_delete) . " accounts\n";
echo "Unsafe to delete (has references): " . count($unsafe_to_delete) . " accounts\n\n";

if (!empty($unsafe_to_delete)) {
    echo "⚠️  ACCOUNTS WITH REFERENCES (will update references first):\n";
    foreach ($unsafe_to_delete as $plan) {
        echo "  {$plan['kode_akun']} ({$plan['nama_akun']})\n";
    }
    echo "\n";
}

echo "=== STEP 3: EXECUTING CLEANUP ===\n";

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
        
        // If there are references, update them to point to the kept record
        if ($plan['has_references']) {
            foreach ($plan['delete_ids'] as $delete_id) {
                if (in_array($delete_id, $referenced_coa_ids)) {
                    // Update journal_lines references
                    $jl_updated = DB::table('journal_lines')
                        ->where('coa_id', $delete_id)
                        ->update(['coa_id' => $plan['keep_id']]);
                    
                    // Update jurnal_umum references
                    $ju_updated = DB::table('jurnal_umum')
                        ->where('coa_id', $delete_id)
                        ->update(['coa_id' => $plan['keep_id']]);
                    
                    $total_references_updated += ($jl_updated + $ju_updated);
                    
                    if ($jl_updated > 0 || $ju_updated > 0) {
                        echo "  🔄 Updated {$jl_updated} journal_lines + {$ju_updated} jurnal_umum references from ID {$delete_id} to {$plan['keep_id']}\n";
                    }
                }
            }
        }
        
        // Delete duplicate records
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

echo "\n=== CLEANUP PROCESS COMPLETED ===\n";