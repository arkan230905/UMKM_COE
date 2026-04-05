<?php
// Add this route to your routes/web.php file
// Or create a temporary route file

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/update-bahan-pendukung-stock', function() {
    try {
        // Check current stock
        $currentStock = DB::table('bahan_pendukungs')
            ->select('id', 'nama_bahan', 'stok', 'harga_satuan')
            ->get();
        
        $html = '<h1>🔧 Update Bahan Pendukung Stock</h1>';
        $html .= '<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f2f2f2;} .btn{padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;display:inline-block;margin:10px 0;}</style>';
        
        $html .= '<h3>📊 Current Stock:</h3>';
        $html .= '<table><tr><th>ID</th><th>Nama Bahan</th><th>Stock</th><th>Harga Satuan</th></tr>';
        
        $needsUpdate = false;
        foreach ($currentStock as $item) {
            $stockColor = $item->stok == 50 ? 'color:red;font-weight:bold;' : 'color:green;font-weight:bold;';
            $html .= "<tr><td>{$item->id}</td><td>{$item->nama_bahan}</td><td style='{$stockColor}'>{$item->stok}</td><td>Rp " . number_format($item->harga_satuan, 0, ',', '.') . "</td></tr>";
            if ($item->stok == 50) $needsUpdate = true;
        }
        $html .= '</table>';
        
        if ($needsUpdate) {
            $html .= '<p style="color:red;">⚠️ Some items still have stock = 50</p>';
            $html .= '<a href="/update-bahan-pendukung-stock?action=update" class="btn" onclick="return confirm(\'Update all stock to 200?\')">🔄 Update All Stock to 200</a>';
        } else {
            $html .= '<p style="color:green;">✅ All stock is already 200!</p>';
        }
        
        return $html;
        
    } catch (\Exception $e) {
        return '<h1 style="color:red;">❌ Error: ' . $e->getMessage() . '</h1>';
    }
});

Route::get('/update-bahan-pendukung-stock', function() {
    $action = request('action');
    
    if ($action === 'update') {
        try {
            // Update all bahan pendukung stock to 200
            $updated = DB::table('bahan_pendukungs')->update(['stok' => 200]);
            
            $html = '<h1 style="color:green;">✅ Success!</h1>';
            $html .= "<p>Updated {$updated} bahan pendukung records to stock = 200</p>";
            $html .= '<p><strong>Next steps:</strong></p>';
            $html .= '<ol>';
            $html .= '<li>Go to your stock report: <a href="/laporan/stok?tipe=bahan_pendukung&item_id=13">Stock Report</a></li>';
            $html .= '<li>Refresh the page (Ctrl+F5)</li>';
            $html .= '<li>Stock should now show 200.00 Liter</li>';
            $html .= '</ol>';
            
            return $html;
            
        } catch (\Exception $e) {
            return '<h1 style="color:red;">❌ Error updating stock: ' . $e->getMessage() . '</h1>';
        }
    }
    
    // Show current stock and update button (same as above)
    // ... rest of the code
});