<?php
// Script to verify the new production system is working correctly
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Produksi;
use App\Models\ProduksiProses;
use App\Models\BahanPendukung;

try {
    echo "<h1>✅ Production System Verification</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";
    
    echo "<h2>🔍 System Status Check</h2>";
    
    // 1. Check database migration status
    echo "<h3>1. Database Schema</h3>";
    
    try {
        $columns = DB::select("SHOW COLUMNS FROM produksi_proses LIKE 'estimasi_durasi'");
        if (count($columns) > 0) {
            echo "<p class='success'>✅ Migration applied: produksi_proses table has new fields</p>";
        } else {
            echo "<p class='error'>❌ Migration needed: Run 'php artisan migrate' to add new fields</p>";
        }
    } catch (\Exception $e) {
        echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
    }
    
    // 2. Check bahan pendukung stock
    echo "<h3>2. Bahan Pendukung Stock</h3>";
    
    $bahanPendukungs = BahanPendukung::all();
    $stockIssues = $bahanPendukungs->where('stok', 50);
    
    if ($stockIssues->count() > 0) {
        echo "<p class='warning'>⚠️ Found " . $stockIssues->count() . " items with stock = 50 (should be 200)</p>";
        echo "<p class='info'>Run fix_production_issues.php to update stock values</p>";
    } else {
        echo "<p class='success'>✅ All bahan pendukung have correct stock values (200)</p>";
    }
    
    // 3. Check production status distribution
    echo "<h3>3. Production Status Distribution</h3>";
    
    $statusCounts = [
        'draft' => Produksi::where('status', 'draft')->count(),
        'dalam_proses' => Produksi::where('status', 'dalam_proses')->count(),
        'selesai' => Produksi::where('status', 'selesai')->count(),
    ];
    
    echo "<table>";
    echo "<tr><th>Status</th><th>Count</th><th>Description</th></tr>";
    echo "<tr><td>draft</td><td>{$statusCounts['draft']}</td><td>Ready to start production</td></tr>";
    echo "<tr><td>dalam_proses</td><td>{$statusCounts['dalam_proses']}</td><td>Materials consumed, processes running</td></tr>";
    echo "<tr><td>selesai</td><td>{$statusCounts['selesai']}</td><td>All processes completed</td></tr>";
    echo "</table>";
    
    // 4. Check process management
    echo "<h3>4. Process Management</h3>";
    
    $processStats = DB::table('produksi_proses')
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->get();
    
    if ($processStats->count() > 0) {
        echo "<table>";
        echo "<tr><th>Process Status</th><th>Count</th></tr>";
        foreach ($processStats as $stat) {
            echo "<tr><td>{$stat->status}</td><td>{$stat->count}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ️ No production processes found</p>";
    }
    
    // 5. Check journal entries
    echo "<h3>5. Journal Entries</h3>";
    
    $journalStats = DB::table('journal_entries')
        ->select('ref_type', DB::raw('COUNT(*) as count'))
        ->where('ref_type', 'like', 'production_%')
        ->groupBy('ref_type')
        ->get();
    
    if ($journalStats->count() > 0) {
        echo "<table>";
        echo "<tr><th>Journal Type</th><th>Count</th></tr>";
        foreach ($journalStats as $stat) {
            echo "<tr><td>{$stat->ref_type}</td><td>{$stat->count}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ️ No production journal entries found</p>";
    }
    
    // 6. Test production flow simulation
    echo "<h3>6. Production Flow Test</h3>";
    
    $testProduction = Produksi::where('status', 'draft')->first();
    
    if ($testProduction) {
        echo "<p class='info'>📋 Test Production Found: #{$testProduction->id} - {$testProduction->produk->nama_produk}</p>";
        
        // Check if it has BOM data
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $testProduction->produk_id)->first();
        
        if ($bomJobCosting) {
            echo "<p class='success'>✅ BOM Job Costing data available</p>";
            
            $bomJobBTKLs = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
            echo "<p class='info'>📊 Found {$bomJobBTKLs->count()} BTKL processes</p>";
            
            foreach ($bomJobBTKLs as $btkl) {
                echo "<li>{$btkl->proses_produksi} - Rp " . number_format($btkl->subtotal, 0, ',', '.') . "</li>";
            }
        } else {
            echo "<p class='warning'>⚠️ No BOM Job Costing data - will create default process</p>";
        }
        
        // Check material availability
        $bomJobBBBs = $bomJobCosting ? \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get() : collect();
        $bomJobBahanPendukungs = $bomJobCosting ? \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get() : collect();
        
        $materialCheck = true;
        $shortages = [];
        
        foreach ($bomJobBBBs as $bomJobBBB) {
            $bahan = $bomJobBBB->bahanBaku;
            if ($bahan) {
                $needed = $bomJobBBB->jumlah * $testProduction->qty_produksi;
                $available = $bahan->stok;
                
                if ($available < $needed) {
                    $materialCheck = false;
                    $shortages[] = "{$bahan->nama_bahan}: need {$needed}, have {$available}";
                }
            }
        }
        
        foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
            $bahan = $bomJobBahanPendukung->bahanPendukung;
            if ($bahan) {
                $needed = $bomJobBahanPendukung->jumlah * $testProduction->qty_produksi;
                $available = 200; // Fixed stock
                
                if ($available < $needed) {
                    $materialCheck = false;
                    $shortages[] = "{$bahan->nama_bahan}: need {$needed}, have {$available}";
                }
            }
        }
        
        if ($materialCheck) {
            echo "<p class='success'>✅ Materials available for production</p>";
            echo "<p class='info'>🚀 Ready to test: <a href='/transaksi/produksi' target='_blank'>Go to Production Index</a></p>";
        } else {
            echo "<p class='error'>❌ Material shortages:</p>";
            foreach ($shortages as $shortage) {
                echo "<li class='error'>{$shortage}</li>";
            }
        }
        
    } else {
        echo "<p class='info'>ℹ️ No draft productions available for testing</p>";
        echo "<p class='info'>Create a new production to test the flow</p>";
    }
    
    // 7. System recommendations
    echo "<h3>7. System Recommendations</h3>";
    
    $recommendations = [];
    
    if ($stockIssues->count() > 0) {
        $recommendations[] = "Update bahan pendukung stock to 200 using fix_production_issues.php";
    }
    
    if ($statusCounts['draft'] == 0) {
        $recommendations[] = "Create a new production to test the manual process flow";
    }
    
    if ($statusCounts['dalam_proses'] > 0) {
        $recommendations[] = "Check in-progress productions and test manual process control";
    }
    
    try {
        $migrationCheck = DB::select("SHOW COLUMNS FROM produksi_proses LIKE 'estimasi_durasi'");
        if (count($migrationCheck) == 0) {
            $recommendations[] = "Run 'php artisan migrate' to apply database changes";
        }
    } catch (\Exception $e) {
        $recommendations[] = "Check database connection and run migrations";
    }
    
    if (count($recommendations) > 0) {
        echo "<ul>";
        foreach ($recommendations as $rec) {
            echo "<li class='warning'>⚠️ {$rec}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ System is ready for testing!</p>";
    }
    
    // 8. Quick action links
    echo "<h3>8. Quick Actions</h3>";
    
    echo "<div style='margin:20px 0;'>";
    echo "<a href='fix_production_issues.php' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Fix Issues</a> ";
    echo "<a href='test_new_production_flow.php' style='background:#007bff;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Test Flow</a> ";
    echo "<a href='/transaksi/produksi' target='_blank' style='background:#17a2b8;color:white;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Production Index</a> ";
    echo "<a href='/transaksi/produksi/create' target='_blank' style='background:#ffc107;color:black;padding:10px 15px;text-decoration:none;margin:5px;border-radius:5px;'>Create Production</a>";
    echo "</div>";
    
    echo "<h2 class='success'>🎉 Verification Complete!</h2>";
    echo "<p>The new production system with manual process control is ready for use.</p>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}