<?php
// Script to create journal entries for existing production
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Produksi;
use App\Services\JournalService;

try {
    echo "<h1>🔧 Create Production Journal Entries</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
    
    $produksiId = 8; // Production ID 8
    
    echo "<p class='info'>Creating journal entries for Production ID: {$produksiId}</p>";
    
    // Get production data
    $produksi = Produksi::with(['produk', 'details.bahanBaku', 'details.bahanPendukung'])->find($produksiId);
    
    if (!$produksi) {
        echo "<p class='error'>❌ Production ID {$produksiId} not found!</p>";
        exit;
    }
    
    echo "<p class='info'>📊 Production Details:</p>";
    echo "<ul>";
    echo "<li>Product: {$produksi->produk->nama_produk}</li>";
    echo "<li>Quantity: {$produksi->qty_produksi}</li>";
    echo "<li>Total Cost: Rp " . number_format($produksi->total_biaya, 0, ',', '.') . "</li>";
    echo "<li>Material Cost: Rp " . number_format($produksi->total_bahan, 0, ',', '.') . "</li>";
    echo "<li>Labor Cost: Rp " . number_format($produksi->total_btkl, 0, ',', '.') . "</li>";
    echo "<li>Overhead Cost: Rp " . number_format($produksi->total_bop, 0, ',', '.') . "</li>";
    echo "<li>Status: {$produksi->status}</li>";
    echo "</ul>";
    
    // Initialize journal service
    $journal = app(\App\Services\JournalService::class);
    
    // Delete existing journal entries for this production
    echo "<p class='info'>🗑️ Deleting existing journal entries...</p>";
    $journal->deleteByRef('production_material', (int)$produksiId);
    $journal->deleteByRef('production_labor_overhead', (int)$produksiId);
    $journal->deleteByRef('production_finish', (int)$produksiId);
    
    $tanggal = $produksi->tanggal;
    
    // 1. Create Material Journal Entries
    echo "<p class='info'>📝 Creating material journal entries...</p>";
    $materialEntries = [];
    $totalMaterialCost = 0;
    
    foreach ($produksi->details as $detail) {
        if ($detail->bahan_baku_id && $detail->bahanBaku) {
            $bahan = $detail->bahanBaku;
            $coaCode = '1101'; // Default COA for raw materials
            
            $materialEntries[] = [
                'code' => $coaCode,
                'debit' => 0,
                'credit' => $detail->subtotal,
                'memo' => "Konsumsi {$bahan->nama_bahan}"
            ];
            $totalMaterialCost += $detail->subtotal;
            echo "<li>- {$bahan->nama_bahan}: Rp " . number_format($detail->subtotal, 0, ',', '.') . "</li>";
        }
        
        if ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
            $bahan = $detail->bahanPendukung;
            $coaCode = '1150'; // Default COA for supporting materials
            
            $materialEntries[] = [
                'code' => $coaCode,
                'debit' => 0,
                'credit' => $detail->subtotal,
                'memo' => "Konsumsi {$bahan->nama_bahan}"
            ];
            $totalMaterialCost += $detail->subtotal;
            echo "<li>- {$bahan->nama_bahan}: Rp " . number_format($detail->subtotal, 0, ',', '.') . "</li>";
        }
    }
    
    // Add WIP debit entry
    if ($totalMaterialCost > 0) {
        array_unshift($materialEntries, [
            'code' => '1301', // WIP
            'debit' => $totalMaterialCost,
            'credit' => 0,
            'memo' => 'Transfer material ke WIP'
        ]);
        
        $journal->post($tanggal, 'production_material', (int)$produksiId, 'Konsumsi Material untuk Produksi', $materialEntries);
        echo "<p class='success'>✅ Material journal created: Rp " . number_format($totalMaterialCost, 0, ',', '.') . "</p>";
    }
    
    // 2. Create Labor & Overhead Journal Entries
    echo "<p class='info'>📝 Creating labor & overhead journal entries...</p>";
    $laborOverheadEntries = [];
    $totalLaborOverhead = $produksi->total_btkl + $produksi->total_bop;
    
    if ($totalLaborOverhead > 0) {
        $laborOverheadEntries = [
            [
                'code' => '1301', // WIP
                'debit' => $totalLaborOverhead,
                'credit' => 0,
                'memo' => 'Transfer BTKL & BOP ke WIP'
            ],
            [
                'code' => '5201', // Labor Cost
                'debit' => 0,
                'credit' => $produksi->total_btkl,
                'memo' => 'Alokasi BTKL ke produksi'
            ],
            [
                'code' => '5301', // Overhead Cost
                'debit' => 0,
                'credit' => $produksi->total_bop,
                'memo' => 'Alokasi BOP ke produksi'
            ]
        ];
        
        $journal->post($tanggal, 'production_labor_overhead', (int)$produksiId, 'Alokasi BTKL & BOP ke Produksi', $laborOverheadEntries);
        echo "<p class='success'>✅ Labor & overhead journal created: Rp " . number_format($totalLaborOverhead, 0, ',', '.') . "</p>";
    }
    
    // 3. Create Finished Goods Journal Entries
    echo "<p class='info'>📝 Creating finished goods journal entries...</p>";
    $totalProductionCost = $produksi->total_biaya;
    
    if ($totalProductionCost > 0) {
        $finishedGoodsEntries = [
            [
                'code' => '1201', // Finished Goods
                'debit' => $totalProductionCost,
                'credit' => 0,
                'memo' => 'Transfer ke Barang Jadi'
            ],
            [
                'code' => '1301', // WIP
                'debit' => 0,
                'credit' => $totalProductionCost,
                'memo' => 'Selesai produksi'
            ]
        ];
        
        $journal->post($tanggal, 'production_finish', (int)$produksiId, 'Transfer WIP ke Barang Jadi', $finishedGoodsEntries);
        echo "<p class='success'>✅ Finished goods journal created: Rp " . number_format($totalProductionCost, 0, ',', '.') . "</p>";
    }
    
    echo "<h2 class='success'>🎉 All journal entries created successfully!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='/transaksi/produksi/{$produksiId}' target='_blank'>Go back to Production Detail</a></li>";
    echo "<li>Click on the journal buttons to see the entries</li>";
    echo "<li>The journal pages should now show the accounting entries</li>";
    echo "</ol>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}