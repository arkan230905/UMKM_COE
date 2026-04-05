<?php
// Script to check production status and journal entries
// Place this in your Laravel root directory and access via browser

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Produksi;

try {
    echo "<h1>🔍 Production Status Check</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";
    
    $produksiId = 8;
    
    // Check production details
    $produksi = Produksi::with(['produk', 'details.bahanBaku', 'details.bahanPendukung'])->find($produksiId);
    
    if (!$produksi) {
        echo "<p class='error'>❌ Production ID {$produksiId} not found!</p>";
        exit;
    }
    
    echo "<h2>📊 Production Information</h2>";
    echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>ID</td><td>{$produksi->id}</td></tr>";
    echo "<tr><td>Product</td><td>{$produksi->produk->nama_produk}</td></tr>";
    echo "<tr><td>Status</td><td>{$produksi->status}</td></tr>";
    echo "<tr><td>Quantity</td><td>{$produksi->qty_produksi}</td></tr>";
    echo "<tr><td>Total Cost</td><td>Rp " . number_format($produksi->total_biaya, 0, ',', '.') . "</td></tr>";
    echo "<tr><td>Material Cost</td><td>Rp " . number_format($produksi->total_bahan, 0, ',', '.') . "</td></tr>";
    echo "<tr><td>Labor Cost</td><td>Rp " . number_format($produksi->total_btkl, 0, ',', '.') . "</td></tr>";
    echo "<tr><td>Overhead Cost</td><td>Rp " . number_format($produksi->total_bop, 0, ',', '.') . "</td></tr>";
    echo "<tr><td>Details Count</td><td>" . $produksi->details->count() . "</td></tr>";
    echo "</table>";
    
    // Check production details
    echo "<h2>📋 Production Details</h2>";
    if ($produksi->details->count() > 0) {
        echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
        echo "<tr><th>Type</th><th>Material</th><th>Qty Recipe</th><th>Unit</th><th>Subtotal</th></tr>";
        
        foreach ($produksi->details as $detail) {
            if ($detail->bahan_baku_id) {
                echo "<tr>";
                echo "<td>Bahan Baku</td>";
                echo "<td>{$detail->bahanBaku->nama_bahan}</td>";
                echo "<td>{$detail->qty_resep}</td>";
                echo "<td>{$detail->satuan_resep}</td>";
                echo "<td>Rp " . number_format($detail->subtotal, 0, ',', '.') . "</td>";
                echo "</tr>";
            }
            
            if ($detail->bahan_pendukung_id) {
                echo "<tr>";
                echo "<td>Bahan Pendukung</td>";
                echo "<td>{$detail->bahanPendukung->nama_bahan}</td>";
                echo "<td>{$detail->qty_resep}</td>";
                echo "<td>{$detail->satuan_resep}</td>";
                echo "<td>Rp " . number_format($detail->subtotal, 0, ',', '.') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No production details found. This means 'Mulai Produksi' hasn't been clicked yet.</p>";
        echo "<p class='info'>Production details are created when materials are consumed during production start.</p>";
    }
    
    // Check journal entries
    echo "<h2>📝 Journal Entries</h2>";
    
    $journalTypes = ['production_material', 'production_labor_overhead', 'production_finish'];
    
    foreach ($journalTypes as $type) {
        echo "<h3>{$type}</h3>";
        
        $entries = DB::table('journal_entries')
            ->where('ref_type', $type)
            ->where('ref_id', $produksiId)
            ->get();
            
        if ($entries->count() > 0) {
            echo "<p class='success'>✅ Found " . $entries->count() . " journal entries</p>";
            
            foreach ($entries as $entry) {
                echo "<p><strong>Entry ID {$entry->id}</strong> - {$entry->tanggal} - {$entry->memo}</p>";
                
                $lines = DB::table('journal_lines as jl')
                    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
                    ->where('jl.journal_entry_id', $entry->id)
                    ->select('jl.*', 'coas.kode_akun', 'coas.nama_akun')
                    ->get();
                    
                if ($lines->count() > 0) {
                    echo "<table border='1' style='border-collapse:collapse; margin:10px 0; margin-left:20px;'>";
                    echo "<tr><th>COA Code</th><th>Account Name</th><th>Debit</th><th>Credit</th><th>Memo</th></tr>";
                    
                    foreach ($lines as $line) {
                        echo "<tr>";
                        echo "<td>{$line->kode_akun}</td>";
                        echo "<td>{$line->nama_akun}</td>";
                        echo "<td>Rp " . number_format($line->debit, 0, ',', '.') . "</td>";
                        echo "<td>Rp " . number_format($line->credit, 0, ',', '.') . "</td>";
                        echo "<td>{$line->memo}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='warning'>⚠️ No journal lines found for this entry</p>";
                }
            }
        } else {
            echo "<p class='error'>❌ No journal entries found for {$type}</p>";
        }
    }
    
    // Check bahan pendukung stock
    echo "<h2>📦 Bahan Pendukung Stock Status</h2>";
    
    $bahanPendukungs = DB::table('bahan_pendukungs')
        ->select('id', 'nama_bahan', 'stok', 'harga_satuan')
        ->orderBy('id')
        ->get();
        
    echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
    echo "<tr><th>ID</th><th>Nama Bahan</th><th>Stock</th><th>Price</th><th>Status</th></tr>";
    
    foreach ($bahanPendukungs as $bahan) {
        $status = $bahan->stok == 200 ? "<span class='success'>✅ Updated</span>" : "<span class='error'>❌ Still 50</span>";
        echo "<tr>";
        echo "<td>{$bahan->id}</td>";
        echo "<td>{$bahan->nama_bahan}</td>";
        echo "<td>{$bahan->stok}</td>";
        echo "<td>Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🎯 Summary & Recommendations</h2>";
    
    if ($produksi->details->count() == 0) {
        echo "<p class='warning'>⚠️ <strong>Main Issue:</strong> Production details are empty because 'Mulai Produksi' hasn't been clicked.</p>";
        echo "<p class='info'><strong>Solution:</strong> Go to production index and click 'Mulai Produksi' button to start the actual production process.</p>";
    }
    
    $hasStock50 = $bahanPendukungs->where('stok', 50)->count() > 0;
    if ($hasStock50) {
        echo "<p class='warning'>⚠️ <strong>Stock Issue:</strong> Some bahan pendukung still have stock = 50.</p>";
        echo "<p class='info'><strong>Solution:</strong> Run the fix_production_issues.php script to update stock to 200.</p>";
    }
    
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='fix_production_issues.php' target='_blank'>Run Fix Script</a> to update stock and create journals</li>";
    echo "<li><a href='/transaksi/produksi' target='_blank'>Go to Production Index</a></li>";
    echo "<li>Click 'Mulai Produksi' if production is still in draft status</li>";
    echo "<li><a href='/transaksi/produksi/{$produksiId}' target='_blank'>Check Production Detail</a></li>";
    echo "<li>Click journal buttons to verify entries are created</li>";
    echo "</ol>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}