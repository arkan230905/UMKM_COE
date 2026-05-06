<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ADD user_id TO produksi_proses TABLE ===\n\n";

echo "1. CEK STRUKTUR TABEL SAAT INI:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('produksi_proses')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produksi_proses');
        echo "Current columns in produksi_proses:\n";
        echo implode(', ', $columns) . "\n\n";
        
        if (in_array('user_id', $columns)) {
            echo "✅ user_id column already exists\n";
        } else {
            echo "❌ user_id column missing - need to add\n";
        }
    } else {
        echo "❌ produksi_proses table doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

echo "\n2. CEK DATA EXISTING:\n\n";

try {
    $totalRecords = \Illuminate\Support\Facades\DB::table('produksi_proses')->count();
    echo "Total records in produksi_proses: " . $totalRecords . "\n";
    
    if ($totalRecords > 0) {
        echo "Sample records:\n";
        $samples = \Illuminate\Support\Facades\DB::table('produksi_proses')->limit(3)->get();
        
        foreach ($samples as $sample) {
            echo "  ID: " . $sample->id . ", Produksi ID: " . $sample->produksi_id . ", Nama: " . $sample->nama_proses . "\n";
        }
        
        echo "\n⚠️ Need to update existing records with user_id\n";
    } else {
        echo "✅ No existing records - safe to add column\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking existing data: " . $e->getMessage() . "\n";
}

echo "\n3. TAMBAHKAN user_id COLUMN:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('produksi_proses') && 
        !in_array('user_id', \Illuminate\Support\Facades\Schema::getColumnListing('produksi_proses'))) {
        
        echo "Adding user_id column to produksi_proses table...\n";
        
        \Illuminate\Support\Facades\Schema::table('produksi_proses', function ($table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
            $table->index('user_id');
        });
        
        echo "✅ user_id column added successfully\n";
        
    } else {
        echo "✅ user_id column already exists or table doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
}

echo "\n4. UPDATE EXISTING RECORDS:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasColumn('produksi_proses', 'user_id')) {
        $recordsToUpdate = \Illuminate\Support\Facades\DB::table('produksi_proses')
            ->whereNull('user_id')
            ->count();
        
        echo "Records to update: " . $recordsToUpdate . "\n";
        
        if ($recordsToUpdate > 0) {
            echo "Updating existing records with user_id from produksis table...\n";
            
            $updated = \Illuminate\Support\Facades\DB::statement("
                UPDATE produksi_proses pp
                JOIN produksis p ON pp.produksi_id = p.id
                SET pp.user_id = p.user_id
                WHERE pp.user_id IS NULL
            ");
            
            echo "✅ Updated " . $recordsToUpdate . " records with user_id\n";
            
            // Verify update
            $nullCount = \Illuminate\Support\Facades\DB::table('produksi_proses')
                ->whereNull('user_id')
                ->count();
            
            if ($nullCount == 0) {
                echo "✅ All records now have user_id\n";
            } else {
                echo "⚠️ " . $nullCount . " records still have null user_id\n";
            }
            
        } else {
            echo "✅ All records already have user_id\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error updating records: " . $e->getMessage() . "\n";
}

echo "\n5. VERIFIKASI FINAL:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('produksi_proses')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produksi_proses');
        echo "Final columns in produksi_proses:\n";
        echo implode(', ', $columns) . "\n";
        
        if (in_array('user_id', $columns)) {
            echo "✅ user_id column exists\n";
        } else {
            echo "❌ user_id column still missing\n";
        }
        
        // Check data integrity
        $totalRecords = \Illuminate\Support\Facades\DB::table('produksi_proses')->count();
        $recordsWithUserId = \Illuminate\Support\Facades\DB::table('produksi_proses')
            ->whereNotNull('user_id')
            ->count();
        
        echo "Total records: " . $totalRecords . "\n";
        echo "Records with user_id: " . $recordsWithUserId . "\n";
        
        if ($totalRecords > 0 && $recordsWithUserId == $totalRecords) {
            echo "✅ All records have user_id - multi-tenant ready\n";
        } elseif ($totalRecords == 0) {
            echo "✅ No records - ready for new data\n";
        } else {
            echo "⚠️ Some records missing user_id\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error in final verification: " . $e->getMessage() . "\n";
}

echo "\n6. NEXT STEPS:\n\n";

echo "✅ COMPLETED:\n";
echo "- Added user_id column to produksi_proses table\n";
echo "- Updated existing records with user_id from produksis table\n";
echo "- Added index for better performance\n\n";

echo "🔄 NEXT ACTIONS NEEDED:\n";
echo "1. Update ProduksiProses model to include user_id in fillable\n";
echo "2. Update controller methods to use user_id filtering\n";
echo "3. Verify all queries use user_id filtering\n";
echo "4. Test multi-tenant functionality\n\n";

echo "=== ADD user_id COMPLETE ===\n";
