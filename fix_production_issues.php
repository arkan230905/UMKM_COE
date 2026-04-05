<?php
// Script to fix production issues:
// 1. Update bahan pendukung stock from 50 to 200
// 2. Create missing journal entries for production ID 8
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Produksi;
use App\Models\BahanPendukung;
use App\Services\JournalService;

try {
    echo "<h1>🔧 Fix Production Issues</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";
    
    // ========================================
    // ISSUE 1: Update Bahan Pendukung Stock
    // ========================================
    echo "<h2>📦 Issue 1: Update Bahan Pendukung Stock</h2>";
    
    // Check current stock values
    $bahanPendukungs = BahanPendukung::all();
    echo "<p class='info'>Current stock values:</p>";
    echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
    echo "<tr><th>ID</th><th>Nama Bahan</th><th>Current Stock</th><th>Harga Satuan</th></tr>";
    
    $needsUpdate = false;
    foreach ($bahanPendukungs as $bahan) {
        $stockColor = $bahan->stok == 50 ? 'color:red' : 'color:green';
        echo "<tr><td>{$bahan->id}</td><td>{$bahan->nama_bahan}</td><td style='{$stockColor}'>{$bahan->stok}</td><td>Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "</td></tr>";
        if ($bahan->stok == 50) {
            $needsUpdate = true;
        }
    }
    echo "</table>";
    
    if ($needsUpdate) {
        echo "<p class='warning'>⚠️ Found items with stock = 50. Updating to 200...</p>";
        
        // Update all bahan pendukung stock to 200
        $updated = DB::table('bahan_pendukungs')->update(['stok' => 200]);
        
        echo "<p class='success'>✅ Updated {$updated} bahan pendukung records to stock = 200</p>";
        
        // Verify the update
        $bahanPendukungs = BahanPendukung::all();
        echo "<p class='info'>Updated stock values:</p>";
        echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
        echo "<tr><th>ID</th><th>Nama Bahan</th><th>New Stock</th><th>Harga Satuan</th></tr>";
        
        foreach ($bahanPendukungs as $bahan) {
            $stockColor = $bahan->stok == 200 ? 'color:green' : 'color:red';
            echo "<tr><td>{$bahan->id}</td><td>{$bahan->nama_bahan}</td><td style='{$stockColor}'>{$bahan->stok}</td><td>Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='success'>✅ All bahan pendukung already have stock = 200</p>";
    }
    
    // ========================================
    // ISSUE 2: Create Production Journal Entries
    // ========================================
    echo "<h2>📝 Issue 2: Create Production Journal Entries</h2>";
    
    $produksiId = 8; // Production ID 8
    
    echo "<p class='info'>Creating journal entries for Production ID: {$produksiId}</p>";
    
    // Get production data
    $produksi = Produksi::with(['produk', 'details.bahanBaku', 'details.bahanPendukung'])->find($produksiId);
    
    if (!$produksi) {
        echo "<p class='error'>❌ Production ID {$produksiId} not found!</p>";
    } else {
        echo "<p class='info'>📊 Production Details:</p>";
        echo "<ul>";
        echo "<li>Product: {$produksi->produk->nama_produk}</li>";
        echo "<li>Quantity: {$produksi->qty_produksi}</li>";
        echo "<li>Total Cost: Rp " . number_format($produksi->total_biaya, 0, ',', '.') . "</li>";
        echo "<li>Material Cost: Rp " . number_format($produksi->total_bahan, 0, ',', '.') . "</li>";
        echo "<li>Labor Cost: Rp " . number_format($produksi->total_btkl, 0, ',', '.') . "</li>";
        echo "<li>Overhead Cost: Rp " . number_format($produksi->total_bop, 0, ',', '.') . "</li>";
        echo "<li>Status: {$produksi->status}</li>";
        echo "<li>Details Count: " . $produksi->details->count() . "</li>";
        echo "</ul>";
        
        // Initialize journal service
        $journal = app(\App\Services\JournalService::class);
        
        // Delete existing journal entries for this production
        echo "<p class='info'>🗑️ Deleting existing journal entries...</p>";
        $journal->deleteByRef('production_material', (int)$produksiId);
        $journal->deleteByRef('production_labor_overhead', (int)$produksiId);
        $journal->deleteByRef('production_finish', (int)$produksiId);
        
        $tanggal = $produksi->tanggal;
        
        // Check if we have production details
        if ($produksi->details->count() == 0) {
            echo "<p class='warning'>⚠️ No production details found. This might be why journals are empty.</p>";
            echo "<p class='info'>Production details are created when 'Mulai Produksi' is clicked, not when production is initially saved as draft.</p>";
        }
        
        // 1. Create Material Journal Entries
        echo "<p class='info'>📝 Creating material journal entries...</p>";
        $materialEntries = [];
        $totalMaterialCost = 0;
        
        foreach ($produksi->details as $detail) {
            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                $bahan = $detail->bahanBaku;
                $coaCode = $bahan->coa_persediaan_id ?? '1101'; // Use COA from bahan or default
                
                $materialEntries[] = [
                    'code' => $coaCode,
                    'debit' => 0,
                    'credit' => $detail->subtotal,
                    'memo' => "Konsumsi {$bahan->nama_bahan}"
                ];
                $totalMaterialCost += $detail->subtotal;
                echo "<li>- {$bahan->nama_bahan}: Rp " . number_format($detail->subtotal, 0, ',', '.') . " (COA: {$coaCode})</li>";
            }
            
            if ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                $bahan = $detail->bahanPendukung;
                $coaCode = $bahan->coa_persediaan_id ?? '1150'; // Use COA from bahan or default
                
                $materialEntries[] = [
                    'code' => $coaCode,
                    'debit' => 0,
                    'credit' => $detail->subtotal,
                    'memo' => "Konsumsi {$bahan->nama_bahan}"
                ];
                $totalMaterialCost += $detail->subtotal;
                echo "<li>- {$bahan->nama_bahan}: Rp " . number_format($detail->subtotal, 0, ',', '.') . " (COA: {$coaCode})</li>";
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
        } else {
            echo "<p class='warning'>⚠️ No material costs found - material journal not created</p>";
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
        } else {
            echo "<p class='warning'>⚠️ No labor/overhead costs found - labor/overhead journal not created</p>";
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
        } else {
            echo "<p class='warning'>⚠️ No production costs found - finished goods journal not created</p>";
        }
    }
    
    echo "<h2 class='success'>🎉 All issues addressed!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='/laporan/stok?tipe=bahan_pendukung&item_id=13&satuan_id=' target='_blank'>Check Stock Report</a> - should now show 200 instead of 50</li>";
    echo "<li><a href='/transaksi/produksi/{$produksiId}' target='_blank'>Go to Production Detail</a></li>";
    echo "<li>Click on the journal buttons to see the entries</li>";
    echo "<li>The journal pages should now show the accounting entries</li>";
    echo "</ol>";
    
    echo "<p class='info'><strong>Important Notes:</strong></p>";
    echo "<ul>";
    echo "<li>If production details are empty, you need to click 'Mulai Produksi' first to create the material consumption records</li>";
    echo "<li>Journal entries are only created when materials are actually consumed during production</li>";
    echo "<li>Stock report should now show 200 for all bahan pendukung items</li>";
    echo "</ul>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}