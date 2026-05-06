<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DROP JOURNAL TABLES CORRECTLY ===\n\n";

echo "1. CEK CONSTRAINT YANG ADA:\n\n";

try {
    // Get actual constraint names
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
    
    $actualConstraints = [];
    foreach ($constraints as $constraint) {
        $actualConstraints[] = $constraint->CONSTRAINT_NAME;
        echo "Found constraint: " . $constraint->CONSTRAINT_NAME . "\n";
        echo "  Table: " . $constraint->TABLE_NAME . "\n";
        echo "  Column: " . $constraint->COLUMN_NAME . "\n";
        echo "  References: " . $constraint->REFERENCED_TABLE_NAME . "." . $constraint->REFERENCED_COLUMN_NAME . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking constraints: " . $e->getMessage() . "\n";
}

echo "2. HAPUS CONSTRAINT DENGAN NAMA YANG BENAR:\n\n";

try {
    // Drop constraints with actual names
    foreach ($actualConstraints as $constraintName) {
        try {
            echo "Dropping constraint: " . $constraintName . "\n";
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE journal_lines DROP FOREIGN KEY $constraintName");
            echo "✅ Constraint dropped\n";
        } catch (\Exception $e) {
            echo "❌ Failed to drop $constraintName: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error dropping constraints: " . $e->getMessage() . "\n";
}

echo "\n3. HAPUS TABEL JOURNAL_LINES:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('journal_lines')) {
        $recordCount = \Illuminate\Support\Facades\DB::table('journal_lines')->count();
        
        if ($recordCount == 0) {
            echo "Dropping table: journal_lines\n";
            \Illuminate\Support\Facades\Schema::drop('journal_lines');
            echo "✅ Table journal_lines dropped successfully\n";
        } else {
            echo "⚠️ Table journal_lines has " . $recordCount . " records - skipping\n";
        }
    } else {
        echo "Table journal_lines doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error dropping journal_lines: " . $e->getMessage() . "\n";
}

echo "\n4. HAPUS TABEL JOURNAL_ENTRIES:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('journal_entries')) {
        $recordCount = \Illuminate\Support\Facades\DB::table('journal_entries')->count();
        
        if ($recordCount == 0) {
            echo "Dropping table: journal_entries\n";
            \Illuminate\Support\Facades\Schema::drop('journal_entries');
            echo "✅ Table journal_entries dropped successfully\n";
        } else {
            echo "⚠️ Table journal_entries has " . $recordCount . " records - skipping\n";
        }
    } else {
        echo "Table journal_entries doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error dropping journal_entries: " . $e->getMessage() . "\n";
}

echo "\n5. VERIFIKASI HASIL:\n\n";

try {
    $tablesToCheck = ['journal_entries', 'journal_lines'];
    
    foreach ($tablesToCheck as $table) {
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

echo "\n=== DROP COMPLETE ===\n";
