<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FORCE remove old vendor unique constraint for production server
     * Replace with new constraint that includes kategori
     */
    public function up(): void
    {
        if (!Schema::hasTable('vendors')) {
            echo "Table vendors does not exist. Skipping.\n";
            return;
        }

        echo "Checking vendors table constraints...\n";
        
        // Get all indexes
        $indexes = DB::select("SHOW INDEX FROM vendors WHERE Non_unique = 0");
        $hasOldConstraint = false;
        $hasNewConstraint = false;
        
        foreach ($indexes as $index) {
            if ($index->Key_name === 'vendors_user_id_nama_vendor_unique') {
                $hasOldConstraint = true;
                echo "Found OLD constraint: vendors_user_id_nama_vendor_unique\n";
            }
            if ($index->Key_name === 'vendors_user_id_nama_vendor_kategori_unique') {
                $hasNewConstraint = true;
                echo "Found NEW constraint: vendors_user_id_nama_vendor_kategori_unique\n";
            }
        }
        
        // Step 1: Remove old constraint if exists
        if ($hasOldConstraint) {
            echo "Dropping OLD constraint: vendors_user_id_nama_vendor_unique\n";
            
            try {
                DB::statement('ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_unique');
                echo "✓ Successfully dropped old constraint\n";
            } catch (\Exception $e) {
                echo "Failed to drop old constraint: " . $e->getMessage() . "\n";
                
                // Try alternative method
                try {
                    DB::statement('ALTER TABLE vendors DROP KEY vendors_user_id_nama_vendor_unique');
                    echo "✓ Successfully dropped old constraint (alternative method)\n";
                } catch (\Exception $e2) {
                    echo "Failed alternative method: " . $e2->getMessage() . "\n";
                    throw new \Exception('Cannot drop old constraint. Please run manually: ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_unique;');
                }
            }
        } else {
            echo "Old constraint not found (already removed or never existed)\n";
        }
        
        // Step 2: Add new constraint if not exists
        if (!$hasNewConstraint) {
            echo "Adding NEW constraint: (user_id, nama_vendor, kategori)\n";
            
            try {
                DB::statement('
                    ALTER TABLE vendors 
                    ADD UNIQUE KEY vendors_user_id_nama_vendor_kategori_unique (user_id, nama_vendor, kategori)
                ');
                echo "✓ Successfully added new constraint\n";
            } catch (\Exception $e) {
                // Check if constraint already exists with different name
                $existingIndexes = DB::select("
                    SELECT DISTINCT INDEX_NAME 
                    FROM INFORMATION_SCHEMA.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'vendors' 
                    AND COLUMN_NAME IN ('user_id', 'nama_vendor', 'kategori')
                    AND NON_UNIQUE = 0
                ");
                
                if (count($existingIndexes) > 0) {
                    echo "Constraint with these columns might already exist under different name\n";
                    foreach ($existingIndexes as $idx) {
                        echo "  - {$idx->INDEX_NAME}\n";
                    }
                } else {
                    throw new \Exception('Cannot add new constraint: ' . $e->getMessage());
                }
            }
        } else {
            echo "New constraint already exists\n";
        }
        
        // Step 3: Verify final state
        echo "\n=== VERIFICATION ===\n";
        
        $finalIndexes = DB::select("SHOW INDEX FROM vendors WHERE Non_unique = 0 AND Key_name != 'PRIMARY'");
        
        if (empty($finalIndexes)) {
            echo "No unique constraints found on vendors table!\n";
        } else {
            echo "Current unique constraints:\n";
            $constraintMap = [];
            
            foreach ($finalIndexes as $idx) {
                if (!isset($constraintMap[$idx->Key_name])) {
                    $constraintMap[$idx->Key_name] = [];
                }
                $constraintMap[$idx->Key_name][] = $idx->Column_name;
            }
            
            foreach ($constraintMap as $name => $columns) {
                echo "  - {$name}: (" . implode(', ', $columns) . ")\n";
            }
        }
        
        echo "\n✅ Migration completed!\n";
        echo "Vendor dengan nama sama sekarang bisa dibuat jika kategorinya berbeda.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "Rolling back vendor constraint changes...\n";
        
        // Remove new constraint
        try {
            DB::statement('ALTER TABLE vendors DROP INDEX IF EXISTS vendors_user_id_nama_vendor_kategori_unique');
            echo "✓ Removed new constraint\n";
        } catch (\Exception $e) {
            echo "Failed to remove new constraint: " . $e->getMessage() . "\n";
        }
        
        // Restore old constraint
        try {
            DB::statement('ALTER TABLE vendors ADD UNIQUE KEY vendors_user_id_nama_vendor_unique (user_id, nama_vendor)');
            echo "✓ Restored old constraint\n";
        } catch (\Exception $e) {
            echo "Failed to restore old constraint: " . $e->getMessage() . "\n";
        }
    }
};
