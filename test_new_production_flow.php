<?php
// Script to test new production flow
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
    echo "<h1>🧪 Test New Production Flow</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";
    
    echo "<h2>📋 Current Production Status</h2>";
    
    // Get all productions
    $productions = Produksi::with(['produk', 'proses'])->orderBy('id', 'desc')->take(5)->get();
    
    echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
    echo "<tr><th>ID</th><th>Product</th><th>Status</th><th>Processes</th><th>Progress</th><th>Actions</th></tr>";
    
    foreach ($productions as $produksi) {
        $statusColor = match($produksi->status) {
            'draft' => 'color:blue',
            'dalam_proses' => 'color:orange',
            'selesai' => 'color:green',
            default => 'color:gray'
        };
        
        echo "<tr>";
        echo "<td>{$produksi->id}</td>";
        echo "<td>{$produksi->produk->nama_produk}</td>";
        echo "<td style='{$statusColor}'>{$produksi->status}</td>";
        echo "<td>{$produksi->proses->count()} processes</td>";
        echo "<td>{$produksi->proses_selesai}/{$produksi->total_proses} ({$produksi->progress_percentage}%)</td>";
        
        $actions = [];
        if ($produksi->status === 'draft') {
            $actions[] = "<a href='/transaksi/produksi/mulai-produksi/{$produksi->id}' style='color:green'>Start Production</a>";
        }
        if ($produksi->status === 'dalam_proses') {
            $actions[] = "<a href='/transaksi/produksi/{$produksi->id}/proses' style='color:orange'>Manage Processes</a>";
        }
        $actions[] = "<a href='/transaksi/produksi/{$produksi->id}' style='color:blue'>View Detail</a>";
        
        echo "<td>" . implode(' | ', $actions) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🔄 Production Process Details</h2>";
    
    // Show process details for productions in progress
    $inProgressProductions = Produksi::where('status', 'dalam_proses')->with(['produk', 'proses'])->get();
    
    if ($inProgressProductions->count() > 0) {
        foreach ($inProgressProductions as $produksi) {
            echo "<h3>Production #{$produksi->id} - {$produksi->produk->nama_produk}</h3>";
            
            if ($produksi->proses->count() > 0) {
                echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
                echo "<tr><th>Order</th><th>Process</th><th>Status</th><th>BTKL Cost</th><th>BOP Cost</th><th>Actions</th></tr>";
                
                foreach ($produksi->proses as $proses) {
                    $statusColor = match($proses->status) {
                        'belum_dimulai' => 'color:gray',
                        'sedang_dikerjakan' => 'color:orange',
                        'selesai' => 'color:green',
                        default => 'color:black'
                    };
                    
                    echo "<tr>";
                    echo "<td>{$proses->urutan}</td>";
                    echo "<td>{$proses->nama_proses}</td>";
                    echo "<td style='{$statusColor}'>{$proses->status}</td>";
                    echo "<td>Rp " . number_format($proses->biaya_btkl, 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($proses->biaya_bop, 0, ',', '.') . "</td>";
                    
                    $processActions = [];
                    if ($proses->status === 'belum_dimulai') {
                        $hasRunning = $produksi->proses->where('status', 'sedang_dikerjakan')->count() > 0;
                        if (!$hasRunning) {
                            $processActions[] = "<a href='/transaksi/produksi/proses/{$proses->id}/mulai' style='color:green'>Start</a>";
                        } else {
                            $processActions[] = "<span style='color:gray'>Waiting</span>";
                        }
                    } elseif ($proses->status === 'sedang_dikerjakan') {
                        $processActions[] = "<a href='/transaksi/produksi/proses/{$proses->id}/selesai' style='color:orange'>Finish</a>";
                    } else {
                        $processActions[] = "<span style='color:green'>Completed</span>";
                    }
                    
                    echo "<td>" . implode(' | ', $processActions) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ No processes found for this production</p>";
            }
        }
    } else {
        echo "<p class='info'>ℹ️ No productions currently in progress</p>";
    }
    
    echo "<h2>📊 System Status Summary</h2>";
    
    $draftCount = Produksi::where('status', 'draft')->count();
    $inProgressCount = Produksi::where('status', 'dalam_proses')->count();
    $completedCount = Produksi::where('status', 'selesai')->count();
    
    echo "<ul>";
    echo "<li><strong>Draft Productions:</strong> {$draftCount} (ready to start)</li>";
    echo "<li><strong>In Progress:</strong> {$inProgressCount} (materials consumed, processes running)</li>";
    echo "<li><strong>Completed:</strong> {$completedCount} (finished goods added to inventory)</li>";
    echo "</ul>";
    
    echo "<h2>🎯 New Production Flow</h2>";
    echo "<ol>";
    echo "<li><strong>Create Production:</strong> Save as 'draft' status</li>";
    echo "<li><strong>Start Production:</strong> Click 'Mulai Produksi' → Consume materials → Create processes → Status becomes 'dalam_proses'</li>";
    echo "<li><strong>Execute Processes:</strong> User manually starts each process in sequence</li>";
    echo "<li><strong>Complete Production:</strong> When all processes finished → Add finished goods to inventory → Status becomes 'selesai'</li>";
    echo "</ol>";
    
    echo "<h2>🔗 Quick Links</h2>";
    echo "<ul>";
    echo "<li><a href='/transaksi/produksi' target='_blank'>Production Index</a></li>";
    echo "<li><a href='/transaksi/produksi/create' target='_blank'>Create New Production</a></li>";
    echo "<li><a href='/laporan/stok?tipe=bahan_pendukung' target='_blank'>Stock Report</a></li>";
    echo "</ul>";
    
    echo "<h2>⚠️ Important Notes</h2>";
    echo "<ul>";
    echo "<li>Materials are consumed when 'Mulai Produksi' is clicked, not when production is created</li>";
    echo "<li>Each process must be started manually by the user</li>";
    echo "<li>Only one process can run at a time per production</li>";
    echo "<li>Finished goods are only added to inventory when ALL processes are completed</li>";
    echo "<li>Journal entries are created at different stages: materials when started, labor/overhead when completed</li>";
    echo "</ul>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}