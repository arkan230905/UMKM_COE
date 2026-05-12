<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;
use App\Models\StockLayer;
use App\Http\Controllers\LaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyncMasterDataWithStockReport extends Command
{
    protected $signature = 'master-data:sync-with-stock-report {--dry-run}';
    protected $description = 'Sync master data (stok & harga_satuan) with actual stock report calculations';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Syncing master data with stock report...');

        // Sync bahan pendukung
        $this->syncBahanPendukung($dryRun);
        
        // Sync bahan baku
        $this->syncBahanBaku($dryRun);
        
        // Sync produk
        $this->syncProduk($dryRun);

        $this->info('🎉 Master data sync complete!');
    }

    private function syncBahanPendukung($dryRun)
    {
        $this->info("\n📦 SYNCING BAHAN PENDUKUNG...");
        
        $items = BahanPendukung::all();
        
        foreach ($items as $item) {
            $this->line("🔧 Processing: {$item->nama_bahan}");
            
            // Get actual stock data from stock report
            $stockData = $this->getActualStockData('support', $item->id);
            
            if (!$stockData) {
                $this->line("   ❌ No stock data found, skipping");
                continue;
            }
            
            $actualQty = $stockData['qty'];
            $actualPrice = $stockData['price'];
            
            $this->line("   📋 Master Data: Qty={$item->stok}, Price=Rp " . number_format($item->harga_satuan, 2));
            $this->line("   📊 Stock Report: Qty={$actualQty}, Price=Rp " . number_format($actualPrice, 2));
            
            $qtyDiff = abs($item->stok - $actualQty);
            $priceDiff = abs($item->harga_satuan - $actualPrice);
            
            if ($qtyDiff > 0.01 || $priceDiff > 1) {
                if (!$dryRun) {
                    $item->update([
                        'stok' => $actualQty,
                        'harga_satuan' => $actualPrice
                    ]);
                    $this->line("   ✅ Updated!");
                } else {
                    $this->line("   🔍 Would update (dry run)");
                }
            } else {
                $this->line("   ✅ Already synced");
            }
            
            $this->line("");
        }
    }

    private function syncBahanBaku($dryRun)
    {
        $this->info("\n📦 SYNCING BAHAN BAKU...");
        
        $items = BahanBaku::all();
        
        foreach ($items as $item) {
            $this->line("🔧 Processing: {$item->nama_bahan}");
            
            // Get actual stock data from stock report
            $stockData = $this->getActualStockData('material', $item->id);
            
            if (!$stockData) {
                $this->line("   ❌ No stock data found, skipping");
                continue;
            }
            
            $actualQty = $stockData['qty'];
            $actualPrice = $stockData['price'];
            
            $this->line("   📋 Master Data: Qty={$item->stok}, Price=Rp " . number_format($item->harga_satuan, 2));
            $this->line("   📊 Stock Report: Qty={$actualQty}, Price=Rp " . number_format($actualPrice, 2));
            
            $qtyDiff = abs($item->stok - $actualQty);
            $priceDiff = abs($item->harga_satuan - $actualPrice);
            
            if ($qtyDiff > 0.01 || $priceDiff > 1) {
                if (!$dryRun) {
                    $item->update([
                        'stok' => $actualQty,
                        'harga_satuan' => $actualPrice
                    ]);
                    $this->line("   ✅ Updated!");
                } else {
                    $this->line("   🔍 Would update (dry run)");
                }
            } else {
                $this->line("   ✅ Already synced");
            }
            
            $this->line("");
        }
    }

    private function syncProduk($dryRun)
    {
        $this->info("\n📦 SYNCING PRODUK...");
        
        $items = Produk::all();
        
        foreach ($items as $item) {
            $this->line("🔧 Processing: {$item->nama_produk}");
            
            // Get actual stock data from stock report
            $stockData = $this->getActualStockData('product', $item->id);
            
            if (!$stockData) {
                $this->line("   ❌ No stock data found, skipping");
                continue;
            }
            
            $actualQty = $stockData['qty'];
            $actualPrice = $stockData['price'];
            
            $this->line("   📋 Master Data: Qty={$item->stok}, Price=Rp " . number_format($item->harga_satuan ?? 0, 2));
            $this->line("   📊 Stock Report: Qty={$actualQty}, Price=Rp " . number_format($actualPrice, 2));
            
            $qtyDiff = abs(($item->stok ?? 0) - $actualQty);
            $priceDiff = abs(($item->harga_satuan ?? 0) - $actualPrice);
            
            if ($qtyDiff > 0.01 || $priceDiff > 1) {
                if (!$dryRun) {
                    $item->update([
                        'stok' => $actualQty,
                        'harga_satuan' => $actualPrice
                    ]);
                    $this->line("   ✅ Updated!");
                } else {
                    $this->line("   🔍 Would update (dry run)");
                }
            } else {
                $this->line("   ✅ Already synced");
            }
            
            $this->line("");
        }
    }

    private function getActualStockData($itemType, $itemId)
    {
        try {
            $request = new Request([
                'tipe' => $itemType === 'support' ? 'bahan_pendukung' : $itemType,
                'item_id' => $itemId
            ]);

            $controller = new LaporanController();
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('stok');
            $method->setAccessible(true);
            
            $response = $method->invoke($controller, $request);
            $viewData = $response->getData();
            
            if (!isset($viewData['dailyStock']) || empty($viewData['dailyStock'])) {
                return null;
            }

            $dailyStock = $viewData['dailyStock'];
            $lastTransaction = end($dailyStock);
            
            if ($lastTransaction && $lastTransaction['saldo_akhir_qty'] > 0) {
                $qty = $lastTransaction['saldo_akhir_qty'];
                $value = $lastTransaction['saldo_akhir_nilai'];
                $price = $value / $qty;
                
                return [
                    'qty' => $qty,
                    'value' => $value,
                    'price' => $price
                ];
            }

            return null;

        } catch (\Exception $e) {
            $this->error("Error getting stock data for {$itemType} {$itemId}: " . $e->getMessage());
            return null;
        }
    }
}