<?php
// Create BomJobCosting for existing BOM
echo "Creating BomJobCosting for existing BOM..." . PHP_EOL;

$bom = DB::table('boms')->where('id', 1)->first();

if ($bom) {
    echo "Found BOM: " . $bom->kode_bom . " for produk_id: " . $bom->produk_id . PHP_EOL;
    
    // Check if BomJobCosting already exists
    $existing = DB::table('bom_job_costings')->where('produk_id', $bom->produk_id)->first();
    
    if (!$existing) {
        echo "Creating BomJobCosting..." . PHP_EOL;
        
        // Create BomJobCosting
        $bomJobCostingId = DB::table('bom_job_costings')->insertGetId([
            'produk_id' => $bom->produk_id,
            'jumlah_produk' => 1,
            'total_bbb' => 0,
            'total_btkl' => 0,
            'total_bahan_pendukung' => 0,
            'total_bop' => 0,
            'total_hpp' => 0,
            'hpp_per_unit' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "BomJobCosting created with ID: " . $bomJobCostingId . PHP_EOL;
        
        // Sync BTKL data
        $btklProcesses = DB::table('btkls')->get();
        
        foreach ($btklProcesses as $btkl) {
            echo "Processing BTKL: " . $btkl->nama_btkl . PHP_EOL;
            
            // Check if already linked
            $existing = DB::table('bom_job_btkl')
                ->where('bom_job_costing_id', $bomJobCostingId)
                ->where('btkl_id', $btkl->id)
                ->first();
            
            if (!$existing) {
                // Calculate biaya per produk
                $biayaPerProduk = $btkl->kapasitas_per_jam > 0 ? 
                    $btkl->tarif_per_jam / $btkl->kapasitas_per_jam : 0;
                
                // Insert into bom_job_btkl
                DB::table('bom_job_btkl')->insert([
                    'bom_job_costing_id' => $bomJobCostingId,
                    'btkl_id' => $btkl->id,
                    'nama_proses' => $btkl->nama_btkl,
                    'durasi_jam' => 1,
                    'tarif_per_jam' => $btkl->tarif_per_jam,
                    'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                    'biaya_per_produk' => $biayaPerProduk,
                    'subtotal' => $biayaPerProduk * 1,
                    'keterangan' => 'Auto-sync from BTKL master data',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                echo "  - Linked to bom_job_btkl" . PHP_EOL;
            } else {
                echo "  - Already linked" . PHP_EOL;
            }
        }
        
        echo "BOM Job Costing setup completed!" . PHP_EOL;
        
    } else {
        echo "BomJobCosting already exists for produk_id: " . $bom->produk_id . PHP_EOL;
    }
    
} else {
    echo "No BOM found with id = 1" . PHP_EOL;
}
