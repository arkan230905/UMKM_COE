<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Models\PenjualanDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DebugBestSellers extends Command
{
    protected $signature = 'debug:best-sellers';
    protected $description = 'Debug best sellers data';

    public function handle()
    {
        $this->info('=== DEBUGGING BEST SELLERS ===');
        
        // Check penjualan_details count
        $totalPenjualan = PenjualanDetail::count();
        $this->info("Total penjualan_details: {$totalPenjualan}");
        
        if ($totalPenjualan === 0) {
            $this->error("❌ Tidak ada data penjualan!");
            return;
        }
        
        // Get top products
        $this->info("\n=== TOP PRODUCTS BY SALES ===");
        $topAgg = PenjualanDetail::selectRaw('produk_id, SUM(jumlah) as total_terjual')
            ->groupBy('produk_id')
            ->orderByDesc('total_terjual')
            ->limit(6)
            ->get();
        
        $this->info("Found {$topAgg->count()} top products");
        
        foreach ($topAgg as $row) {
            $this->line("Produk ID: {$row->produk_id}, Total Terjual: {$row->total_terjual}");
        }
        
        // Get best sellers from cache
        $this->info("\n=== BEST SELLERS FROM CACHE ===");
        $bestSellers = Cache::remember('pelanggan_best_sellers_v2', 60, function () {
            $topAgg = PenjualanDetail::selectRaw('produk_id, SUM(jumlah) as total_terjual')
                ->groupBy('produk_id')
                ->orderByDesc('total_terjual')
                ->limit(6)
                ->get();

            $result = collect();
            if ($topAgg->isNotEmpty()) {
                $produkMap = Produk::withoutGlobalScopes()->whereIn('id', $topAgg->pluck('produk_id'))
                    ->get()
                    ->keyBy('id');

                foreach ($topAgg as $row) {
                    if (isset($produkMap[$row->produk_id])) {
                        $p = $produkMap[$row->produk_id];
                        $p->total_terjual = (int) $row->total_terjual;
                        $result->push($p);
                    }
                }
            }
            // Fallback jika belum ada penjualan, tampilkan produk terbaru
            if ($result->isEmpty()) {
                $allProducts = Produk::withoutGlobalScopes()->orderByDesc('created_at')->limit(6)->get();
                
                // Gunakan stok langsung dari kolom produks
                $result = $allProducts->filter(function($p) {
                    $p->stok_tersedia = max(0, $p->stok);
                    return $p->stok_tersedia > 0; // Hanya tampilkan produk yang ada stoknya
                });
            }
            return $result;
        });
        
        $this->info("Best sellers count: {$bestSellers->count()}");
        
        foreach ($bestSellers as $product) {
            $this->line("ID: {$product->id}, Nama: {$product->nama_produk}, Terjual: {$product->total_terjual}");
        }
    }
}
