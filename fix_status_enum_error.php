<?php
// Script to fix status ENUM error in produksi_proses table
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "<h1>🔧 Fix Status ENUM Error</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;} pre{background:#f1f1f1;padding:10px;border-radius:5px;}</style>";
    
    echo "<h2>🔍 Checking Status Column</h2>";
    
    // Check the current structure of status column
    $columns = DB::select("SHOW COLUMNS FROM produksi_proses WHERE Field = 'status'");
    
    if (count($columns) > 0) {
        $statusColumn = $columns[0];
        echo "<p class='info'>📋 Current status column definition:</p>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        echo "<tr>";
        echo "<td>{$statusColumn->Field}</td>";
        echo "<td>{$statusColumn->Type}</td>";
        echo "<td>{$statusColumn->Null}</td>";
        echo "<td>{$statusColumn->Key}</td>";
        echo "<td>{$statusColumn->Default}</td>";
        echo "</tr>";
        echo "</table>";
        
        // Check if it's an ENUM
        if (strpos($statusColumn->Type, 'enum') !== false) {
            echo "<p class='warning'>⚠️ Status column is using ENUM type: {$statusColumn->Type}</p>";
            
            // Extract ENUM values
            preg_match("/^enum\((.+)\)$/", $statusColumn->Type, $matches);
            if (isset($matches[1])) {
                $enumValues = str_getcsv($matches[1], ',', "'");
                echo "<p class='info'>📝 Current ENUM values:</p>";
                echo "<ul>";
                foreach ($enumValues as $value) {
                    echo "<li>{$value}</li>";
                }
                echo "</ul>";
                
                // Check if 'belum_dimulai' is in the ENUM
                if (!in_array('belum_dimulai', $enumValues)) {
                    echo "<p class='error'>❌ 'belum_dimulai' is NOT in the ENUM values!</p>";
                    
                    // Suggest solutions
                    echo "<h3>🔧 Solution Options:</h3>";
                    
                    echo "<div style='background:#d4edda;padding:15px;border-left:4px solid #28a745;margin:10px 0;'>";
                    echo "<h4>Option 1: Add 'belum_dimulai' to ENUM (Recommended)</h4>";
                    echo "<p>Run this SQL to add the missing value:</p>";
                    echo "<pre>";
                    $newEnumValues = array_merge($enumValues, ['belum_dimulai']);
                    $enumString = "'" . implode("','", $newEnumValues) . "'";
                    echo "ALTER TABLE produksi_proses \nMODIFY COLUMN status ENUM({$enumString}) NOT NULL DEFAULT 'pending';";
                    echo "</pre>";
                    echo "</div>";
                    
                    echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;margin:10px 0;'>";
                    echo "<h4>Option 2: Use Existing ENUM Value</h4>";
                    echo "<p>Change the code to use an existing ENUM value instead of 'belum_dimulai'.</p>";
                    echo "<p>Available values: " . implode(', ', $enumValues) . "</p>";
                    echo "</div>";
                    
                    // Try automatic fix
                    echo "<h3>🚀 Automatic Fix Attempt:</h3>";
                    
                    try {
                        $newEnumValues = array_merge($enumValues, ['belum_dimulai']);
                        $enumString = "'" . implode("','", $newEnumValues) . "'";
                        
                        DB::statement("ALTER TABLE produksi_proses MODIFY COLUMN status ENUM({$enumString}) NOT NULL DEFAULT 'pending'");
                        echo "<p class='success'>✅ Successfully added 'belum_dimulai' to status ENUM!</p>";
                        
                        // Verify the change
                        $updatedColumns = DB::select("SHOW COLUMNS FROM produksi_proses WHERE Field = 'status'");
                        if (count($updatedColumns) > 0) {
                            echo "<p class='info'>📋 Updated status column:</p>";
                            echo "<p><strong>Type:</strong> {$updatedColumns[0]->Type}</p>";
                        }
                        
                    } catch (\Exception $e) {
                        echo "<p class='error'>❌ Automatic fix failed: " . $e->getMessage() . "</p>";
                        echo "<p class='warning'>Please run the SQL command manually in phpMyAdmin.</p>";
                    }
                    
                } else {
                    echo "<p class='success'>✅ 'belum_dimulai' is already in the ENUM values</p>";
                }
            }
        } else {
            echo "<p class='info'>ℹ️ Status column is not using ENUM type</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Status column not found in produksi_proses table!</p>";
    }
    
    // Alternative solution: Update controller to use existing ENUM values
    echo "<h3>🔄 Alternative Solution: Use Compatible Status Values</h3>";
    
    echo "<div style='background:#e7f3ff;padding:15px;border-left:4px solid #007bff;margin:10px 0;'>";
    echo "<h4>Update Controller to Use Existing ENUM Values</h4>";
    echo "<p>If you prefer not to modify the database, we can update the controller to use existing status values.</p>";
    
    // Check what values are available
    if (count($columns) > 0 && strpos($columns[0]->Type, 'enum') !== false) {
        preg_match("/^enum\((.+)\)$/", $columns[0]->Type, $matches);
        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
            echo "<p><strong>Available status values:</strong> " . implode(', ', $enumValues) . "</p>";
            
            // Suggest mapping
            $statusMapping = [
                'belum_dimulai' => 'pending',
                'sedang_dikerjakan' => in_array('in_progress', $enumValues) ? 'in_progress' : (in_array('active', $enumValues) ? 'active' : 'pending'),
                'selesai' => in_array('completed', $enumValues) ? 'completed' : (in_array('done', $enumValues) ? 'done' : 'selesai')
            ];
            
            echo "<p><strong>Suggested status mapping:</strong></p>";
            echo "<ul>";
            foreach ($statusMapping as $new => $existing) {
                echo "<li>{$new} → {$existing}</li>";
            }
            echo "</ul>";
        }
    }
    echo "</div>";
    
    // Test the fix
    echo "<h3>🧪 Test Production System</h3>";
    
    $testProduction = \App\Models\Produksi::where('status', 'draft')->first();
    
    if ($testProduction) {
        echo "<p class='info'>📋 Found test production: #{$testProduction->id} - {$testProduction->produk->nama_produk}</p>";
        
        // Check if we can create a process now
        try {
            // Try to create a test process record
            $testProcess = \App\Models\ProduksiProses::where('produksi_id', $testProduction->id)->first();
            
            if (!$testProcess) {
                echo "<p class='info'>🔧 Testing process creation...</p>";
                
                // Try with 'pending' first (most common ENUM value)
                \App\Models\ProduksiProses::create([
                    'produksi_id' => $testProduction->id,
                    'nama_proses' => 'Test Process',
                    'urutan' => 1,
                    'status' => 'pending',
                    'biaya_btkl' => 0,
                    'biaya_bop' => 0,
                    'total_biaya_proses' => 0,
                ]);
                
                echo "<p class='success'>✅ Test process created successfully with 'pending' status</p>";
                
                // Clean up test record
                \App\Models\ProduksiProses::where('produksi_id', $testProduction->id)
                    ->where('nama_proses', 'Test Process')
                    ->delete();
                    
            } else {
                echo "<p class='info'>ℹ️ Production already has processes</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Process creation test failed: " . $e->getMessage() . "</p>";
        }
        
        echo "<p><a href='/transaksi/produksi' target='_blank' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Test Production Index</a></p>";
        
    } else {
        echo "<p class='info'>ℹ️ No draft productions found for testing</p>";
    }
    
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>If automatic fix worked:</strong> Try clicking 'Mulai Produksi' again</li>";
    echo "<li><strong>If manual fix needed:</strong> Run the SQL command above in phpMyAdmin</li>";
    echo "<li><strong>Alternative:</strong> I can update the controller to use existing ENUM values</li>";
    echo "<li><strong>Test:</strong> Verify the production system works after the fix</li>";
    echo "</ol>";
    
    echo "<h3>🔗 Quick Links:</h3>";
    echo "<div style='margin:20px 0;'>";
    echo "<a href='/transaksi/produksi' target='_blank' style='background:#17a2b8;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Production Index</a> ";
    echo "<a href='fix_database_error.php' style='background:#6c757d;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Database Fix</a> ";
    echo "<a href='verify_production_system.php' style='background:#6f42c1;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Verify System</a>";
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}