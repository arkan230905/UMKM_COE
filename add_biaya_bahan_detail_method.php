<?php

echo "=== ADD BIAYA BAHAN DETAIL METHOD ===\n\n";

echo "Adding detail method to BiayaBahanController...\n";

$controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';
$controllerContent = file_get_contents($controllerFile);

// Add detail method before the last closing brace
$detailMethod = '
    /**
     * Show detail biaya bahan for a product
     */
    public function detail($id)
    {
        try {
            // Get product with user filtering
            $produk = Produk::where(\'id\', $id)->where(\'user_id\', auth()->id())->firstOrFail();
            
            // Get BBB data directly from bom_job_bbb table
            $bbbData = DB::table(\'bom_job_bbb as bbb\')
                ->leftJoin(\'bahan_bakus as bb\', \'bbb.bahan_baku_id\', \'=\', \'bb.id\')
                ->leftJoin(\'satuans as s\', \'bb.satuan_id\', \'=\', \'s.id\')
                ->where(\'bbb.user_id\', auth()->id())
                ->where(\'bbb.produk_id\', $id)
                ->select(
                    \'bbb.id\',
                    \'bb.nama_bahan\',
                    \'bbb.jumlah as qty\',
                    \'bbb.satuan\',
                    \'bbb.harga_satuan\',
                    \'bbb.subtotal\',
                    \'bbb.keterangan\',
                    \'bbb.created_at\',
                    \'s.nama as satuan_nama\'
                )
                ->orderBy(\'bbb.created_at\', \'desc\')
                ->get();
            
            // Calculate totals
            $totalJumlah = $bbbData->sum(\'qty\');
            $totalSubtotal = $bbbData->sum(\'subtotal\');
            
            return view(\'master-data.biaya-bahan.detail\', compact(
                \'produk\',
                \'bbbData\',
                \'totalJumlah\',
                \'totalSubtotal\'
            ));
            
        } catch (\Exception $e) {
            Log::error("Error in BiayaBahanController@detail: " . $e->getMessage());
            return back()->withError(\'Error loading detail biaya bahan: \' . $e->getMessage());
        }
    }

';

// Find the position before the last closing brace
$lastBracePosition = strrpos($controllerContent, '}');
if ($lastBracePosition !== false) {
    // Insert the detail method before the last closing brace
    $newControllerContent = substr_replace($controllerContent, $detailMethod . "\n}", $lastBracePosition, 1);
    
    file_put_contents($controllerFile, $newControllerContent);
    echo "✅ Added detail method to BiayaBahanController\n";
} else {
    echo "❌ Could not find insertion point in controller\n";
}

echo "\n=== METHOD ADDED ===\n";
