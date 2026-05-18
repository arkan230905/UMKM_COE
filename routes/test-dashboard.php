<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-dashboard-debug', function() {
    $bestSellers = \Illuminate\Support\Facades\Cache::remember('pelanggan_best_sellers_v2', 60, function () {
        $topAgg = \App\Models\PenjualanDetail::selectRaw('produk_id, SUM(jumlah) as total_terjual')
            ->groupBy('produk_id')
            ->orderByDesc('total_terjual')
            ->limit(6)
            ->get();

        $result = collect();
        if ($topAgg->isNotEmpty()) {
            $produkMap = \App\Models\Produk::withoutGlobalScopes()->whereIn('id', $topAgg->pluck('produk_id'))
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
            $allProducts = \App\Models\Produk::withoutGlobalScopes()->orderByDesc('created_at')->limit(6)->get();
            
            // Gunakan stok langsung dari kolom produks
            $result = $allProducts->filter(function($p) {
                $p->stok_tersedia = max(0, $p->stok);
                return $p->stok_tersedia > 0; // Hanya tampilkan produk yang ada stoknya
            });
        }
        return $result;
    });

    return response()->json([
        'bestSellers_count' => $bestSellers->count(),
        'bestSellers' => $bestSellers->map(function($p) {
            return [
                'id' => $p->id,
                'nama_produk' => $p->nama_produk,
                'harga_jual' => $p->harga_jual,
                'stok' => $p->stok,
                'foto' => $p->foto,
                'total_terjual' => $p->total_terjual ?? 0
            ];
        })
    ]);
});

Route::get('/test-dashboard-view', function() {
    $bestSellers = \Illuminate\Support\Facades\Cache::remember('pelanggan_best_sellers_v2', 60, function () {
        $topAgg = \App\Models\PenjualanDetail::selectRaw('produk_id, SUM(jumlah) as total_terjual')
            ->groupBy('produk_id')
            ->orderByDesc('total_terjual')
            ->limit(6)
            ->get();

        $result = collect();
        if ($topAgg->isNotEmpty()) {
            $produkMap = \App\Models\Produk::withoutGlobalScopes()->whereIn('id', $topAgg->pluck('produk_id'))
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
        
        // Fallback: Jika belum ada penjualan, tampilkan produk random dengan stok > 0
        if ($result->isEmpty()) {
            $allProducts = \App\Models\Produk::withoutGlobalScopes()
                ->where('stok', '>', 0)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            
            $result = $allProducts->map(function($p) {
                $p->stok_tersedia = max(0, $p->stok);
                $p->total_terjual = 0;
                return $p;
            });
        }
        
        return $result;
    });

    $produks = collect();
    $cartCount = 0;
    $search = '';
    $favoriteIds = [];
    $favoriteProduks = collect();
    $whatsappNumber = '';
    $kategoris = collect();
    $kategoriFilter = '';

    return view('pelanggan.dashboard-test', compact('bestSellers'));
});

Route::get('/test-carousel-simple', function() {
    $bestSellers = \App\Models\Produk::withoutGlobalScopes()
        ->where('stok', '>', 0)
        ->limit(3)
        ->get();

    $html = '<!DOCTYPE html><html><head><title>Test Carousel</title><style>body{font-family:Arial;padding:20px}.carousel{background:#e0f2fe;padding:20px;border-radius:10px;max-width:500px}.product{background:white;padding:20px;border-radius:10px;margin:10px 0}.product h3{margin:0}.product p{color:#666}</style></head><body>';
    $html .= '<h1>Test Carousel - Best Sellers</h1>';
    $html .= '<p>Total Products: ' . $bestSellers->count() . '</p>';
    $html .= '<div class="carousel"><h2>Carousel Products:</h2>';
    
    foreach ($bestSellers as $p) {
        $html .= '<div class="product">';
        $html .= '<h3>' . $p->nama_produk . '</h3>';
        $html .= '<p>Harga: Rp ' . number_format($p->harga_jual, 0, ',', '.') . '</p>';
        $html .= '<p>Stok: ' . $p->stok . '</p>';
        $html .= '</div>';
    }
    
    $html .= '</div></body></html>';
    
    return response($html)->header('Content-Type', 'text/html');
});
