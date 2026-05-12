<?php
// Script to fix database error for production system
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "<h1>🔧 Fix Database Error</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";
    
    echo "<h2>🔍 Database Schema Check</h2>";
    
    // Check if produksi_proses table exists
    if (!Schema::hasTable('produksi_proses')) {
        echo "<p class='error'>❌ Table 'produksi_proses' does not exist!</p>";
        echo "<p class='info'>You need to create this table first.</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Table 'produksi_proses' exists</p>";
    
    // Check current columns
    $columns = DB::select("SHOW COLUMNS FROM produksi_proses");
    
    echo "<h3>Current Table Structure:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column->Field;
        echo "<tr>";
        echo "<td>{$column->Field}</td>";
        echo "<td>{$column->Type}</td>";
        echo "<td>{$column->Null}</td>";
        echo "<td>{$column->Key}</td>";
        echo "<td>{$column->Default}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for missing columns
    $requiredColumns = ['estimasi_durasi', 'kapasitas_per_jam', 'tarif_per_jam'];
    $missingColumns = array_diff($requiredColumns, $existingColumns);
    
    if (count($missingColumns) > 0) {
        echo "<h3>Missing Columns:</h3>";
        echo "<ul>";
        foreach ($missingColumns as $column) {
            echo "<li class='error'>❌ {$column}</li>";
        }
        echo "</ul>";
        
        echo "<h3>🔧 Fix Options:</h3>";
        echo "<div style='background:#f8f9fa;padding:15px;border-left:4px solid #007bff;margin:10px 0;'>";
        echo "<h4>Option 1: Add Missing Columns (Recommended)</h4>";
        echo "<p>Run this SQL in phpMyAdmin or your database tool:</p>";
        echo "<pre style='background:#f1f1f1;padding:10px;border-radius:5px;'>";
        echo "ALTER TABLE produksi_proses \n";
        echo "ADD COLUMN estimasi_durasi DECIMAL(8,2) NULL AFTER status,\n";
        echo "ADD COLUMN kapasitas_per_jam DECIMAL(8,2) NULL AFTER estimasi_durasi,\n";
        echo "ADD COLUMN tarif_per_jam DECIMAL(10,2) NULL AFTER kapasitas_per_jam;";
        echo "</pre>";
        echo "</div>";
        
        echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;margin:10px 0;'>";
        echo "<h4>Option 2: Use Compatibility Mode (Temporary)</h4>";
        echo "<p>The system has been updated to work without these columns temporarily.</p>";
        echo "<p>You can now try clicking 'Mulai Produksi' again.</p>";
        echo "</div>";
        
        // Try to add columns automatically
        echo "<h3>🚀 Automatic Fix Attempt:</h3>";
        
        try {
            DB::statement("ALTER TABLE produksi_proses ADD COLUMN estimasi_durasi DECIMAL(8,2) NULL AFTER status");
            echo "<p class='success'>✅ Added column: estimasi_durasi</p>";
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p class='info'>ℹ️ Column estimasi_durasi already exists</p>";
            } else {
                echo "<p class='error'>❌ Failed to add estimasi_durasi: " . $e->getMessage() . "</p>";
            }
        }
        
        try {
            DB::statement("ALTER TABLE produksi_proses ADD COLUMN kapasitas_per_jam DECIMAL(8,2) NULL AFTER estimasi_durasi");
            echo "<p class='success'>✅ Added column: kapasitas_per_jam</p>";
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p class='info'>ℹ️ Column kapasitas_per_jam already exists</p>";
            } else {
                echo "<p class='error'>❌ Failed to add kapasitas_per_jam: " . $e->getMessage() . "</p>";
            }
        }
        
        try {
            DB::statement("ALTER TABLE produksi_proses ADD COLUMN tarif_per_jam DECIMAL(10,2) NULL AFTER kapasitas_per_jam");
            echo "<p class='success'>✅ Added column: tarif_per_jam</p>";
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p class='info'>ℹ️ Column tarif_per_jam already exists</p>";
            } else {
                echo "<p class='error'>❌ Failed to add tarif_per_jam: " . $e->getMessage() . "</p>";
            }
        }
        
        // Check again after adding columns
        $columnsAfter = DB::select("SHOW COLUMNS FROM produksi_proses");
        $existingColumnsAfter = [];
        foreach ($columnsAfter as $column) {
            $existingColumnsAfter[] = $column->Field;
        }
        
        $stillMissing = array_diff($requiredColumns, $existingColumnsAfter);
        
        if (count($stillMissing) == 0) {
            echo "<p class='success'>🎉 All required columns have been added successfully!</p>";
            echo "<p class='info'>You can now try clicking 'Mulai Produksi' again.</p>";
        } else {
            echo "<p class='warning'>⚠️ Some columns are still missing. Please add them manually using the SQL above.</p>";
        }
        
    } else {
        echo "<p class='success'>✅ All required columns exist</p>";
    }
    
    // Test production system
    echo "<h3>🧪 Production System Test</h3>";
    
    $testProduction = \App\Models\Produksi::where('status', 'draft')->first();
    
    if ($testProduction) {
        echo "<p class='info'>📋 Found test production: #{$testProduction->id} - {$testProduction->produk->nama_produk}</p>";
        echo "<p class='success'>✅ Ready to test production flow</p>";
        echo "<p><a href='/transaksi/produksi' target='_blank' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Go to Production Index</a></p>";
    } else {
        echo "<p class='info'>ℹ️ No draft productions found. Create a new production to test.</p>";
        echo "<p><a href='/transaksi/produksi/create' target='_blank' style='background:#007bff;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Create New Production</a></p>";
    }
    
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>If columns were added successfully, try clicking 'Mulai Produksi' again</li>";
    echo "<li>If still having issues, run the SQL commands manually in phpMyAdmin</li>";
    echo "<li>Check the production index page to see if the system is working</li>";
    echo "<li>Test the manual process control by starting individual processes</li>";
    echo "</ol>";
    
    echo "<h3>🔗 Quick Links:</h3>";
    echo "<div style='margin:20px 0;'>";
    echo "<a href='/transaksi/produksi' target='_blank' style='background:#17a2b8;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Production Index</a> ";
    echo "<a href='verify_production_system.php' style='background:#6f42c1;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Verify System</a> ";
    echo "<a href='test_new_production_flow.php' style='background:#fd7e14;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Test Flow</a>";
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}