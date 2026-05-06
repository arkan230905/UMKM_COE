<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FORCE DROP JOURNAL_ENTRIES ===\n\n";

echo "1. CEK SEMUA CONSTRAINT DI JOURNAL_ENTRIES:\n\n";

try {
    // Get all constraints for journal_entries
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
            AND TABLE_NAME = 'journal_entries'
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "Constraints on journal_entries:\n";
    foreach ($constraints as $constraint) {
        echo "  Constraint: " . $constraint->CONSTRAINT_NAME . "\n";
        echo "  Column: " . $constraint->COLUMN_NAME . "\n";
        echo "  References: " . $constraint->REFERENCED_TABLE_NAME . "." . $constraint->REFERENCED_COLUMN_NAME . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking constraints: " . $e->getMessage() . "\n";
}

echo "\n2. HAPUS SEMUA CONSTRAINT DENGAN SQL LANGSUNG:\n\n";

try {
    // Drop all foreign key constraints
    $constraints = \Illuminate\Support\Facades\DB::select("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'journal_entries'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    foreach ($constraints as $constraint) {
        $constraintName = $constraint->CONSTRAINT_NAME;
        echo "Dropping constraint: " . $constraintName . "\n";
        
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE journal_entries DROP FOREIGN KEY $constraintName");
            echo "✅ Constraint dropped\n";
        } catch (\Exception $e) {
            echo "❌ Failed to drop $constraintName: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error dropping constraints: " . $e->getMessage() . "\n";
}

echo "\n3. CEK APAKAH MASIH ADA YANG MENGACU KE JOURNAL_ENTRIES:\n\n";

try {
    // Check if any table still references journal_entries
    $references = \Illuminate\Support\Facades\DB::select("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME
        FROM 
            information_schema.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = DATABASE() 
            AND REFERENCED_TABLE_NAME = 'journal_entries'
    ");
    
    echo "Tables referencing journal_entries:\n";
    foreach ($references as $ref) {
        echo "  Table: " . $ref->TABLE_NAME . "\n";
        echo "  Column: " . $ref->COLUMN_NAME . "\n";
        echo "  Constraint: " . $ref->CONSTRAINT_NAME . "\n\n";
    }
    
    if (empty($references)) {
        echo "✅ No tables reference journal_entries\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking references: " . $e->getMessage() . "\n";
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
            echo "⚠️ Table journal_entries has " . $recordCount . " records\n";
            echo "   Records will be lost - proceed with caution\n";
            
            // Show sample data before dropping
            $samples = \Illuminate\Support\Facades\DB::table('journal_entries')->limit(3)->get();
            echo "   Sample data:\n";
            foreach ($samples as $sample) {
                echo "     ID: " . $sample->id . ", User: " . $sample->user_id . ", Ref: " . $sample->ref_type . "\n";
            }
            
            // Force drop
            echo "   Force dropping...\n";
            \Illuminate\Support\Facades\Schema::drop('journal_entries');
            echo "✅ Table journal_entries force dropped\n";
        }
    } else {
        echo "Table journal_entries doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error dropping journal_entries: " . $e->getMessage() . "\n";
}

echo "\n5. VERIFIKASI FINAL:\n\n";

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
    echo "Error verifying: " . $e->getMessage() . "\n";
}

echo "\n=== FORCE DROP COMPLETE ===\n";
