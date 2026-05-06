<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX JOURNAL CONSTRAINTS ===\n\n";

echo "1. CEK FOREIGN KEY CONSTRAINTS:\n\n";

try {
    // Check foreign key constraints on journal_entries
    $constraints = \Illuminate\Support\Facades\DB::select("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            information_schema.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME IN ('journal_entries', 'journal_lines')
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "Foreign key constraints found:\n";
    foreach ($constraints as $constraint) {
        echo "  Constraint: " . $constraint->CONSTRAINT_NAME . "\n";
        echo "  Table: " . $constraint->TABLE_NAME . "\n";
        echo "  Column: " . $constraint->COLUMN_NAME . "\n";
        echo "  References: " . $constraint->REFERENCED_TABLE_NAME . "." . $constraint->REFERENCED_COLUMN_NAME . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking constraints: " . $e->getMessage() . "\n";
}

echo "\n2. HAPUS FOREIGN KEY CONSTRAINTS:\n\n";

try {
    // Drop foreign key constraints
    $constraintsToDrop = [
        'journal_entries' => ['journal_entries_user_id_foreign', 'journal_entries_created_by_foreign'],
        'journal_lines' => ['journal_lines_journal_entry_id_foreign', 'journal_lines_coa_id_foreign']
    ];
    
    foreach ($constraintsToDrop as $table => $constraintNames) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            foreach ($constraintNames as $constraintName) {
                try {
                    echo "Dropping constraint: " . $constraintName . " from " . $table . "\n";
                    \Illuminate\Support\Facades\Schema::table($table, function ($table) use ($constraintName) {
                        $table->dropForeign($constraintName);
                    });
                    echo "✅ Constraint " . $constraintName . " dropped\n";
                } catch (\Exception $e) {
                    echo "❌ Failed to drop " . $constraintName . ": " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error dropping constraints: " . $e->getMessage() . "\n";
}

echo "\n3. HAPUS TABEL JOURNAL:\n\n";

try {
    $tablesToDrop = ['journal_entries', 'journal_lines'];
    
    foreach ($tablesToDrop as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $recordCount = \Illuminate\Support\Facades\DB::table($table)->count();
            
            if ($recordCount == 0) {
                echo "Dropping table: " . $table . "\n";
                \Illuminate\Support\Facades\Schema::drop($table);
                echo "✅ Table " . $table . " dropped successfully\n";
            } else {
                echo "⚠️ Table " . $table . " has " . $recordCount . " records - skipping\n";
            }
        } else {
            echo "Table " . $table . " doesn't exist\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error dropping tables: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI HASIL:\n\n";

try {
    $remainingTables = ['journal_entries', 'journal_lines'];
    
    foreach ($remainingTables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "❌ Table " . $table . " still exists\n";
        } else {
            echo "✅ Table " . $table . " successfully removed\n";
        }
    }
    
    // Verify jurnal_umum still exists
    if (\Illuminate\Support\Facades\Schema::hasTable('jurnal_umum')) {
        echo "✅ jurnal_umum table still exists\n";
    } else {
        echo "❌ jurnal_umum table missing\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying results: " . $e->getMessage() . "\n";
}

echo "\n=== CONSTRAINT FIX COMPLETE ===\n";
