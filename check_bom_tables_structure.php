<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK BOM TABLES STRUCTURE ===\n\n";

echo "1. CEK STRUKTUR SEMUA TABEL BOM_JOB:\n\n";

try {
    $bomTables = ['bom_job_bbb', 'bom_job_btkl', 'bom_job_bahan_pendukung', 'bom_job_bop'];
    
    foreach ($bomTables as $table) {
        echo "Table: $table\n";
        
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            echo "  Columns: " . implode(', ', $columns) . "\n";
            
            // Check key columns
            if (in_array('user_id', $columns)) {
                echo "  ✅ user_id column exists\n";
            } else {
                echo "  ❌ user_id column missing\n";
            }
            
            if (in_array('produk_id', $columns)) {
                echo "  ✅ produk_id column exists\n";
            } else {
                echo "  ❌ produk_id column missing\n";
            }
            
            if (in_array('bom_job_costing_id', $columns)) {
                echo "  ✅ bom_job_costing_id column exists\n";
            } else {
                echo "  ❌ bom_job_costing_id column missing\n";
            }
            
            // Check data count
            $recordCount = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "  Records: $recordCount\n";
            
            // Check user 1 data
            $user1Count = \Illuminate\Support\Facades\DB::table($table)->where('user_id', 1)->count();
            echo "  User 1 records: $user1Count\n";
            
        } else {
            echo "  ❌ Table doesn't exist\n";
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n2. ANALISIS RELATIONSHIP YANG DIGUNAKAN:\n\n";

try {
    echo "Based on table structures:\n";
    
    // Check which tables have which foreign keys
    $tablesWithProdukId = [];
    $tablesWithBomJobCostingId = [];
    
    $bomTables = ['bom_job_bbb', 'bom_job_btkl', 'bom_job_bahan_pendukung', 'bom_job_bop'];
    
    foreach ($bomTables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            
            if (in_array('produk_id', $columns)) {
                $tablesWithProdukId[] = $table;
            }
            
            if (in_array('bom_job_costing_id', $columns)) {
                $tablesWithBomJobCostingId[] = $table;
            }
        }
    }
    
    echo "Tables with produk_id: " . implode(', ', $tablesWithProdukId) . "\n";
    echo "Tables with bom_job_costing_id: " . implode(', ', $tablesWithBomJobCostingId) . "\n\n";
    
    if (count($tablesWithProdukId) > 0) {
        echo "✅ Some tables use produk_id relationship\n";
        echo "   Query should be: ->where('user_id', auth()->id())->where('produk_id', \$bomJobCosting->produk_id)\n";
    }
    
    if (count($tablesWithBomJobCostingId) > 0) {
        echo "✅ Some tables use bom_job_costing_id relationship\n";
        echo "   Query should be: ->where('bom_job_costing_id', \$bomJobCosting->id)\n";
    }
    
} catch (\Exception $e) {
    echo "Error analyzing relationships: " . $e->getMessage() . "\n";
}

echo "\n3. CEK DATA UNTUK PRODUK 2 (JASUKE):\n\n";

try {
    $bomTables = ['bom_job_bbb', 'bom_job_btkl', 'bom_job_bahan_pendukung', 'bom_job_bop'];
    
    foreach ($bomTables as $table) {
        echo "Checking $table for produk_id = 2:\n";
        
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            
            if (in_array('produk_id', $columns)) {
                $records = \Illuminate\Support\Facades\DB::table($table)
                    ->where('produk_id', 2)
                    ->where('user_id', 1)
                    ->get();
                
                echo "  Found " . $records->count() . " records\n";
                
                foreach ($records as $record) {
                    echo "    ID: " . $record->id . ", Subtotal: " . ($record->subtotal ?? $record->total ?? 0) . "\n";
                }
            } elseif (in_array('bom_job_costing_id', $columns)) {
                // Get bom_job_costing id for produk 2
                $bomJobCosting = \Illuminate\Support\Facades\DB::table('bom_job_costings')
                    ->where('produk_id', 2)
                    ->where('user_id', 1)
                    ->first();
                
                if ($bomJobCosting) {
                    $records = \Illuminate\Support\Facades\DB::table($table)
                        ->where('bom_job_costing_id', $bomJobCosting->id)
                        ->get();
                    
                    echo "  Found " . $records->count() . " records (using bom_job_costing_id)\n";
                    
                    foreach ($records as $record) {
                        echo "    ID: " . $record->id . ", Subtotal: " . ($record->subtotal ?? $record->total ?? 0) . "\n";
                    }
                } else {
                    echo "  No bom_job_costing found for produk 2\n";
                }
            } else {
                echo "  No clear relationship found\n";
            }
        }
        
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

echo "\n4. REKOMENDASI PERBAIKAN QUERY:\n\n";

try {
    echo "Based on analysis, here's the correct approach:\n\n";
    
    $bomTables = ['bom_job_bbb', 'bom_job_btkl', 'bom_job_bahan_pendukung', 'bom_job_bop'];
    
    foreach ($bomTables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            
            echo "For $table:\n";
            
            if (in_array('produk_id', $columns)) {
                echo "  Use: ->where('user_id', auth()->id())->where('produk_id', \$bomJobCosting->produk_id)\n";
            } elseif (in_array('bom_job_costing_id', $columns)) {
                echo "  Use: ->where('bom_job_costing_id', \$bomJobCosting->id)\n";
            } else {
                echo "  ❌ No clear relationship - need investigation\n";
            }
            
            echo "---\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error providing recommendations: " . $e->getMessage() . "\n";
}

echo "\n=== STRUCTURE CHECK COMPLETE ===\n";
