<?php
// Script to test the fixed production system
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Produksi;
use App\Models\ProduksiProses;

try {
    echo "<h1>🧪 Test Fixed Production System</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";
    
    echo "<h2>🔍 System Status Check</h2>";
    
    // 1. Check status ENUM values
    echo "<h3>1. Status Column Check</h3>";
    
    $statusColumn = DB::select("SHOW COLUMNS FROM produksi_proses WHERE Field = 'status'");
    
    if (count($statusColumn) > 0) {
        echo "<p class='info'>📋 Status column type: {$statusColumn[0]->Type}</p>";
        
        if (strpos($statusColumn[0]->Type, 'enum') !== false) {
            preg_match("/^enum\((.+)\)$/", $statusColumn[0]->Type, $matches);
            if (isset($matches[1])) {
                $enumValues = str_getcsv($matches[1], ',', "'");
                echo "<p class='info'>📝 Available ENUM values: " . implode(', ', $enumValues) . "</p>";
                
                if (in_array('pending', $enumValues)) {
                    echo "<p class='success'>✅ 'pending' status is available</p>";
                } else {
                    echo "<p class='error'>❌ 'pending' status is not available</p>";
                }
            }
        }
    }
    
    // 2. Test process creation
    echo "<h3>2. Process Creation Test</h3>";
    
    $testProduction = Produksi::where('status', 'draft')->first();
    
    if ($testProduction) {
        echo "<p class='info'>📋 Test production: #{$testProduction->id} - {$testProduction->produk->nama_produk}</p>";
        
        // Clean up any existing test processes
        ProduksiProses::where('produksi_id', $testProduction->id)
            ->where('nama_proses', 'LIKE', 'Test Process%')
            ->delete();
        
        try {
            // Test creating a process with 'pending' status
            $testProcess = ProduksiProses::create([
                'produksi_id' => $testProduction->id,
                'nama_proses' => 'Test Process - Compatibility Check',
                'urutan' => 999,
                'status' => 'pending',
                'biaya_btkl' => 100,
                'biaya_bop' => 50,
                'total_biaya_proses' => 150,
            ]);
            
            echo "<p class='success'>✅ Process creation successful with ID: {$testProcess->id}</p>";
            echo "<p class='info'>📊 Process details:</p>";
            echo "<ul>";
            echo "<li>Name: {$testProcess->nama_proses}</li>";
            echo "<li>Status: {$testProcess->status}</li>";
            echo "<li>Order: {$testProcess->urutan}</li>";
            echo "<li>BTKL Cost: Rp " . number_format($testProcess->biaya_btkl, 0, ',', '.') . "</li>";
            echo "</ul>";
            
            // Test status badge
            echo "<p class='info'>📋 Status badge: {$testProcess->status_badge}</p>";
            
            // Clean up test process
            $testProcess->delete();
            echo "<p class='info'>🧹 Test process cleaned up</p>";
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Process creation failed: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p class='warning'>⚠️ No draft productions found for testing</p>";
        echo "<p class='info'>Create a new production to test the system</p>";
    }
    
    // 3. Check existing processes
    echo "<h3>3. Existing Processes Check</h3>";
    
    $existingProcesses = ProduksiProses::with('produksi.produk')->take(5)->get();
    
    if ($existingProcesses->count() > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Production</th><th>Process</th><th>Status</th><th>Order</th><th>BTKL</th></tr>";
        
        foreach ($existingProcesses as $process) {
            echo "<tr>";
            echo "<td>{$process->id}</td>";
            echo "<td>#{$process->produksi->id} - {$process->produksi->produk->nama_produk}</td>";
            echo "<td>{$process->nama_proses}</td>";
            echo "<td>{$process->status}</td>";
            echo "<td>{$process->urutan}</td>";
            echo "<td>Rp " . number_format($process->biaya_btkl, 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ️ No existing processes found</p>";
    }
    
    // 4. Production flow simulation
    echo "<h3>4. Production Flow Simulation</h3>";
    
    if ($testProduction) {
        echo "<div style='background:#e7f3ff;padding:15px;border-left:4px solid #007bff;margin:10px 0;'>";
        echo "<h4>🎯 Expected Flow:</h4>";
        echo "<ol>";
        echo "<li><strong>Create Production:</strong> Status = 'draft'</li>";
        echo "<li><strong>Click 'Mulai Produksi':</strong> Consume materials → Create processes with status = 'pending' → Status = 'dalam_proses'</li>";
        echo "<li><strong>Manual Process Control:</strong> User clicks 'Mulai' on each process → Status = 'sedang_dikerjakan'</li>";
        echo "<li><strong>Complete Process:</strong> User clicks 'Selesaikan' → Status = 'selesai'</li>";
        echo "<li><strong>All Processes Done:</strong> Add finished goods → Status = 'selesai'</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<p class='success'>🚀 System is ready for testing!</p>";
        echo "<p><a href='/transaksi/produksi' target='_blank' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Test Production System</a></p>";
        
    }
    
    // 5. Status mapping reference
    echo "<h3>5. Status Mapping Reference</h3>";
    
    echo "<table>";
    echo "<tr><th>Original Status</th><th>Current Status</th><th>Description</th></tr>";
    echo "<tr><td>belum_dimulai</td><td>pending</td><td>Process is ready to start</td></tr>";
    echo "<tr><td>sedang_dikerjakan</td><td>sedang_dikerjakan</td><td>Process is currently running</td></tr>";
    echo "<tr><td>selesai</td><td>selesai</td><td>Process is completed</td></tr>";
    echo "</table>";
    
    // 6. Troubleshooting
    echo "<h3>6. Troubleshooting</h3>";
    
    echo "<div style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;margin:10px 0;'>";
    echo "<h4>⚠️ If Still Having Issues:</h4>";
    echo "<ul>";
    echo "<li><strong>ENUM Error:</strong> Run fix_status_enum_error.php to add missing status values</li>";
    echo "<li><strong>Column Error:</strong> Run fix_database_error.php to add missing columns</li>";
    echo "<li><strong>Permission Error:</strong> Check database user permissions</li>";
    echo "<li><strong>Connection Error:</strong> Verify database connection settings</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🔗 Quick Actions:</h3>";
    echo "<div style='margin:20px 0;'>";
    echo "<a href='/transaksi/produksi' target='_blank' style='background:#17a2b8;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Production Index</a> ";
    echo "<a href='/transaksi/produksi/create' target='_blank' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Create Production</a> ";
    echo "<a href='fix_status_enum_error.php' style='background:#dc3545;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Fix Status Error</a> ";
    echo "<a href='verify_production_system.php' style='background:#6f42c1;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Verify System</a>";
    echo "</div>";
    
    echo "<h2 class='success'>🎉 System Test Complete!</h2>";
    echo "<p>The production system has been updated to use compatible status values. Try clicking 'Mulai Produksi' now!</p>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}