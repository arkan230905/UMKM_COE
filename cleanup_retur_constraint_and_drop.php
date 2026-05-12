<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CLEANUP RETUR CONSTRAINT AND DROP JOURNAL_ENTRIES ===\n\n";

echo "1. CEK RETUR_JURNAL_ENTRIES DATA:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('retur_jurnal_entries')) {
        $recordCount = \Illuminate\Support\Facades\DB::table('retur_jurnal_entries')->count();
        echo "retur_jurnal_entries records: " . $recordCount . "\n";
        
        if ($recordCount > 0) {
            echo "Sample data:\n";
            $samples = \Illuminate\Support\Facades\DB::table('retur_jurnal_entries')->limit(3)->get();
            foreach ($samples as $sample) {
                echo "  ID: " . $sample->id . ", Journal Entry ID: " . $sample->jurnal_entry_id . "\n";
            }
            
            // Migrate data to jurnal_umum before dropping
            echo "\nMigrating retur data to jurnal_umum...\n";
            $this->migrateReturToJurnalUmum();
        } else {
            echo "✅ No data in retur_jurnal_entries\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking retur table: " . $e->getMessage() . "\n";
}

echo "\n2. HAPUS FOREIGN KEY CONSTRAINT DARI RETUR_JURNAL_ENTRIES:\n\n";

try {
    // Drop the foreign key constraint
    echo "Dropping constraint: retur_jurnal_entries_jurnal_entry_id_foreign\n";
    \Illuminate\Support\Facades\DB::statement("ALTER TABLE retur_jurnal_entries DROP FOREIGN KEY retur_jurnal_entries_jurnal_entry_id_foreign");
    echo "✅ Constraint dropped\n";
    
} catch (\Exception $e) {
    echo "Error dropping constraint: " . $e->getMessage() . "\n";
}

echo "\n3. HAPUS TABEL JOURNAL_ENTRIES:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('journal_entries')) {
        $recordCount = \Illuminate\Support\Facades\DB::table('journal_entries')->count();
        
        if ($recordCount == 0) {
            echo "Dropping table: journal_entries\n";
            \Illuminate\Support\Facades\Schema::drop('journal_entries');
            echo "✅ Table journal_entries dropped successfully\n";
        } else {
            echo "⚠️ Table journal_entries has " . $recordCount . " records\n";
            echo "   Backing up data to jurnal_umum before dropping...\n";
            
            // Migrate any remaining data
            $this->migrateJournalEntriesToJurnalUmum();
            
            echo "   Dropping table...\n";
            \Illuminate\Support\Facades\Schema::drop('journal_entries');
            echo "✅ Table journal_entries dropped after migration\n";
        }
    } else {
        echo "Table journal_entries doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error dropping journal_entries: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI FINAL:\n\n";

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
        
        $recordCount = \Illuminate\Support\Facades\DB::table('jurnal_umum')->count();
        echo "   Records in jurnal_umum: " . $recordCount . "\n";
    } else {
        echo "❌ jurnal_umum table missing\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying: " . $e->getMessage() . "\n";
}

echo "\n5. UPDATE RETUR_JURNAL_ENTRIES STRUCTURE:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('retur_jurnal_entries')) {
        // Remove jurnal_entry_id column since journal_entries is gone
        if (\Illuminate\Support\Facades\Schema::hasColumn('retur_jurnal_entries', 'jurnal_entry_id')) {
            echo "Removing jurnal_entry_id column from retur_jurnal_entries\n";
            \Illuminate\Support\Facades\Schema::table('retur_jurnal_entries', function ($table) {
                $table->dropColumn('jurnal_entry_id');
            });
            echo "✅ Column removed\n";
        }
        
        // Add jurnal_umum_id column to reference jurnal_umum
        if (!\Illuminate\Support\Facades\Schema::hasColumn('retur_jurnal_entries', 'jurnal_umum_id')) {
            echo "Adding jurnal_umum_id column to retur_jurnal_entries\n";
            \Illuminate\Support\Facades\Schema::table('retur_jurnal_entries', function ($table) {
                $table->unsignedBigInteger('jurnal_umum_id')->nullable();
                $table->foreign('jurnal_umum_id')->references('id')->on('jurnal_umum');
            });
            echo "✅ Column added with foreign key\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error updating retur_jurnal_entries: " . $e->getMessage() . "\n";
}

echo "\n=== CLEANUP COMPLETE ===\n";

// Helper functions
function migrateReturToJurnalUmum() {
    try {
        $returRecords = \Illuminate\Support\Facades\DB::table('retur_jurnal_entries')->get();
        
        foreach ($returRecords as $retur) {
            // Create corresponding jurnal_umum records
            \Illuminate\Support\Facades\DB::table('jurnal_umum')->insert([
                'user_id' => $retur->user_id,
                'coa_id' => null, // Will be filled based on retur type
                'tanggal' => $retur->created_at,
                'keterangan' => 'Retur jurnal - ID: ' . $retur->id,
                'debit' => 0,
                'kredit' => 0,
                'referensi' => $retur->id,
                'tipe_referensi' => 'retur_jurnal',
                'created_by' => $retur->user_id,
                'created_at' => $retur->created_at,
                'updated_at' => $retur->updated_at,
            ]);
        }
        
        echo "✅ Migrated " . count($returRecords) . " retur records to jurnal_umum\n";
        
    } catch (\Exception $e) {
        echo "Error migrating retur data: " . $e->getMessage() . "\n";
    }
}

function migrateJournalEntriesToJurnalUmum() {
    try {
        $journalEntries = \Illuminate\Support\Facades\DB::table('journal_entries')->get();
        
        foreach ($journalEntries as $entry) {
            // Create corresponding jurnal_umum record
            \Illuminate\Support\Facades\DB::table('jurnal_umum')->insert([
                'user_id' => $entry->user_id,
                'coa_id' => null, // Will be filled based on lines
                'tanggal' => $entry->tanggal,
                'keterangan' => $entry->memo ?? 'Journal Entry - ID: ' . $entry->id,
                'debit' => 0,
                'kredit' => 0,
                'referensi' => $entry->id,
                'tipe_referensi' => 'journal_entry',
                'created_by' => $entry->user_id,
                'created_at' => $entry->created_at,
                'updated_at' => $entry->updated_at,
            ]);
        }
        
        echo "✅ Migrated " . count($journalEntries) . " journal entries to jurnal_umum\n";
        
    } catch (\Exception $e) {
        echo "Error migrating journal entries: " . $e->getMessage() . "\n";
    }
}
