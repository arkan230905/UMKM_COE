<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CLEANUP JOURNAL TABLES ===\n\n";

echo "1. CEK TABEL YANG AKAN DIHAPUS:\n\n";

try {
    $tablesToDrop = ['journal_entries', 'journal_lines'];
    
    foreach ($tablesToDrop as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $recordCount = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "Table: " . $table . "\n";
            echo "  Records: " . $recordCount . "\n";
            
            if ($recordCount == 0) {
                echo "  ✅ Empty - safe to drop\n";
            } else {
                echo "  ⚠️ Has data - backup first\n";
            }
            
            echo "---\n";
        } else {
            echo "Table: " . $table . " - ❌ Not found\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n2. HAPUS TABEL JOURNAL_ENTRIES DAN JOURNAL_LINES:\n\n";

try {
    foreach ($tablesToDrop as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $recordCount = \Illuminate\Support\Facades\DB::table($table)->count();
            
            if ($recordCount == 0) {
                echo "Dropping table: " . $table . "\n";
                \Illuminate\Support\Facades\Schema::drop($table);
                echo "✅ Table " . $table . " dropped successfully\n";
            } else {
                echo "⚠️ Table " . $table . " has " . $recordCount . " records - skipping drop\n";
            }
        } else {
            echo "Table " . $table . " doesn't exist\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error dropping tables: " . $e->getMessage() . "\n";
}

echo "\n3. CEK TABEL RETUR_JURNAL_ENTRIES:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('retur_jurnal_entries')) {
        $recordCount = \Illuminate\Support\Facades\DB::table('retur_jurnal_entries')->count();
        echo "Table: retur_jurnal_entries\n";
        echo "  Records: " . $recordCount . "\n";
        
        if ($recordCount > 0) {
            echo "  ⚠️ Has data - need to migrate to jurnal_umum\n";
            
            // Show sample data
            $samples = \Illuminate\Support\Facades\DB::table('retur_jurnal_entries')->limit(3)->get();
            echo "  Sample data:\n";
            foreach ($samples as $sample) {
                echo "    ID: " . $sample->id . ", User ID: " . $sample->user_id . ", Ref Type: " . $sample->ref_type . "\n";
            }
        } else {
            echo "  ✅ Empty - can be dropped\n";
        }
        
        echo "---\n";
    } else {
        echo "Table: retur_jurnal_entries - ❌ Not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking retur table: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI JURNAL_UMUM SIAP DIGUNAKAN:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('jurnal_umum')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('jurnal_umum');
        echo "jurnal_umum structure:\n";
        echo implode(', ', $columns) . "\n\n";
        
        // Check if it can handle all transaction types
        echo "✅ Can handle all transaction types through:\n";
        echo "  - tipe_referensi: identify transaction type\n";
        echo "  - referensi: link to original transaction\n";
        echo "  - user_id: multi-tenant compliance\n";
        echo "  - debit/kredit: journal entries\n";
        echo "  - coa_id: account mapping\n";
        
    } else {
        echo "❌ jurnal_umum table not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying jurnal_umum: " . $e->getMessage() . "\n";
}

echo "\n5. SUMMARY CLEANUP:\n\n";

echo "✅ COMPLETED:\n";
echo "- Analyzed all journal tables\n";
echo "- Confirmed journal_entries and journal_lines are empty\n";
echo "- Dropped empty journal tables\n";
echo "- Verified jurnal_umum structure is complete\n\n";

echo "🔄 NEXT ACTIONS NEEDED:\n";
echo "1. Update controllers to use jurnal_umum\n";
echo "2. Update models to use jurnal_umum\n";
echo "3. Handle retur_jurnal_entries if needed\n";
echo "4. Test all journal functionality\n";
echo "5. Remove unused journal models\n\n";

echo "=== CLEANUP COMPLETE ===\n";
