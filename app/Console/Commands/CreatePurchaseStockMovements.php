<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembelianDetail;
use App\Models\StockMovement;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;

class CreatePurchaseStockMovements extends Command
{
    protected $signature = 'app:create-purchase-stock-movements';
    protected $description = 'Create stock movements for existing purchases';

    public function handle()
    {
        $this->info('Creating Stock Movements for Purchases...');
        
        // Get all purchase details
        $details = PembelianDetail::with(['pembelian'])->get();
        
        $created = 0;
        $skipped = 0;
        
        foreach ($details as $detail) {
            $pembelian = $detail->pembelian;
            
            if (!$pembelian) {
                continue;
            }
            
            // Determine item type and ID
            $itemType = null;
            $itemId = null;
            $itemName = '';
            
            if ($detail->bahan_baku_id) {
                $itemType = 'material';
                $itemId = $detail->bahan_baku_id;
                $item = BahanBaku::find($itemId);
                $itemName = $item ? $item->nama_bahan : 'Unknown';
            } elseif ($detail->bahan_pendukung_id) {
                $itemType = 'support';
                $itemId = $detail->bahan_pendukung_id;
                $item = BahanPendukung::find($itemId);
                $itemName = $item ? $item->nama_bahan : 'Unknown';
            } else {
                continue;
            }
            
            // Check if stock movement already exists
            $existingMovement = StockMovement::where('ref_type', 'purchase')
                ->where('ref_id', $detail->pembelian_id)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->first();
                
            if ($existingMovement) {
                // Update existing movement with manual conversion data if available
                if ($detail->manual_conversion_data) {
                    $manualConversionData = $detail->manual_conversion_data;
                    if (is_string($manualConversionData)) {
                        $manualConversionData = json_decode($manualConversionData, true);
                    }
                    
                    $existingMovement->update([
                        'manual_conversion_data' => json_encode($manualConversionData)
                    ]);
                    
                    $this->line("  Updated movement for {$itemName} with manual conversion data");
                }
                $skipped++;
                continue;
            }
            
            try {
                // Get unit cost and total cost
                $unitCost = (float)($detail->harga_satuan ?? 0);
                $qty = (float)($detail->jumlah_satuan_utama ?? $detail->jumlah ?? 0);
                $totalCost = $qty * $unitCost;
                
                // Get manual conversion data if exists
                $manualConversionData = null;
                if ($detail->manual_conversion_data) {
                    $manualConversionData = $detail->manual_conversion_data;
                    if (is_string($manualConversionData)) {
                        $manualConversionData = json_decode($manualConversionData, true);
                    }
                }
                
                // Create stock movement
                $movement = StockMovement::create([
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'tanggal' => $pembelian->tanggal,
                    'ref_type' => 'purchase',
                    'ref_id' => $detail->pembelian_id,
                    'direction' => 'in',
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'keterangan' => 'Pembelian',
                    'manual_conversion_data' => $manualConversionData ? json_encode($manualConversionData) : null
                ]);
                
                $this->line("  Created movement for {$itemName} - Qty: {$qty} on {$pembelian->tanggal}");
                if ($manualConversionData) {
                    $this->line("    Manual conversion: 1 {$manualConversionData['sub_satuan_nama']} = {$manualConversionData['manual_conversion_factor']} (from master data)");
                }
                $created++;
                
            } catch (\Exception $e) {
                $this->error("  Error creating movement for {$itemName}: " . $e->getMessage());
            }
        }
        
        $this->info('');
        $this->info("Stock movements created: {$created}, skipped: {$skipped}");
        
        return 0;
    }
}
