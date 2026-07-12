<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Cart;
use App\Models\PenjualanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Favorite;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Allow public access to customer portal
            // Jika user login sebagai owner/admin dan akses link toko online, jangan redirect
            // Biarkan mereka lihat toko mereka sebagai public customer
            // This allows owner to preview their customer portal
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // Get perusahaan from middleware
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            return redirect('/')->with('error', 'Perusahaan tidak ditemukan');
        }

        $search = trim($request->input('q', ''));
        $kategoriFilter = $request->input('kategori', '');

        // Ambil SEMUA produk dari perusahaan ini (tanpa filter stok)
        $produksQuery = Produk::withoutGlobalScopes()
            ->where('user_id', $perusahaan->user_id)
            ->with('satuan', 'kategori');
        
        if ($search !== '') {
            $produksQuery->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter by kategori if selected
        if ($kategoriFilter !== '') {
            $produksQuery->where('kategori_id', $kategoriFilter);
        }

        if ($request->ajax() && $request->has('autocomplete')) {
            $autocompleteProduks = $produksQuery->take(5)->get();
            return response()->json($autocompleteProduks);
        }

        $produks = $produksQuery
            ->orderBy('nama_produk')
            ->paginate(12)
            ->withQueryString();
        
        // Gunakan stok langsung dari kolom produks (bukan dari stock_movements)
        // Ini memastikan konsistensi dengan menu produk
        $produks->getCollection()->transform(function($p) {
            $p->stok_tersedia = max(0, $p->stok);
            return $p;
        });

        $userId = auth('pelanggan')->id();
        
        // Removed manual cartCount calculation so CartComposer handles it
        // Get all categories from KategoriProduk
        // For public access, we need to get all categories (both shared and user-specific)
        // The UserScope will automatically filter by user_id if authenticated
        $kategoris = \App\Models\KategoriProduk::withoutGlobalScopes()
            ->where('user_id', $perusahaan->user_id)
            ->whereHas('produks', function($query) use ($perusahaan) {
                $query->withoutGlobalScopes()->where('user_id', $perusahaan->user_id);
            })
            ->withCount(['produks' => function($query) use ($perusahaan) {
                $query->withoutGlobalScopes()->where('user_id', $perusahaan->user_id);
            }])
            ->orderBy('nama')
            ->get();

        $bestSellers = Cache::remember('pelanggan_best_sellers_v2_' . $perusahaan->id, 60, function () use ($perusahaan) {
            $topAgg = PenjualanDetail::selectRaw('produk_id, SUM(jumlah) as total_terjual')
                ->whereHas('penjualan', function($query) use ($perusahaan) {
                    $query->where('user_id', $perusahaan->user_id);
                })
                ->groupBy('produk_id')
                ->orderByDesc('total_terjual')
                ->limit(1)
                ->get();

            $result = collect();
            if ($topAgg->isNotEmpty()) {
                $produkMap = Produk::withoutGlobalScopes()
                    ->where('user_id', $perusahaan->user_id)
                    ->whereIn('id', $topAgg->pluck('produk_id'))
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
            
            // Fallback: Jika belum ada penjualan, tampilkan produk aktif pertama dengan stok > 0
            if ($result->isEmpty()) {
                $activeProduct = Produk::withoutGlobalScopes()
                    ->where('user_id', $perusahaan->user_id)
                    ->where('stok', '>', 0)
                    ->orderBy('id')
                    ->limit(1)
                    ->get();
                
                $result = $activeProduct->map(function($p) {
                    $p->stok_tersedia = max(0, $p->stok);
                    $p->total_terjual = 0;
                    return $p;
                });
            }
            
            return $result;
        });

        $favoriteIds = [];
        $favoriteProduks = collect();
        if ($userId) {
            $favoriteIds = Favorite::where('user_id', $userId)->pluck('produk_id')->toArray();
            if (!empty($favoriteIds)) {
                $favoriteProduks = Produk::withoutGlobalScopes()
                    ->where('user_id', $perusahaan->user_id)
                    ->whereIn('id', $favoriteIds)
                    ->get();
            }
        }

        $whatsappNumber = config('services.whatsapp.number') ?? env('WHATSAPP_NUMBER');

        // Get slug for URL generation in views
        $perusahaan_slug = $perusahaan->slug ?: strtolower(str_replace(' ', '-', $perusahaan->kode));

        return view('pelanggan.dashboard', compact('produks', 'bestSellers', 'search', 'favoriteIds', 'favoriteProduks', 'whatsappNumber', 'kategoris', 'kategoriFilter', 'perusahaan', 'perusahaan_slug'));
    }
}
