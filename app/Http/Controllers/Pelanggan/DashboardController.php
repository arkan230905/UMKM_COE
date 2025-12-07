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
            if (auth()->check()) {
                $role = auth()->user()->role;
                if (!in_array($role, ['pelanggan', 'admin', 'owner'])) {
                    abort(403, 'Unauthorized. Halaman ini hanya untuk pelanggan.');
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search = trim($request->input('q', ''));

        $produksQuery = Produk::where('stok', '>', 0);
        if ($search !== '') {
            $produksQuery->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $produks = $produksQuery
            ->orderBy('nama_produk')
            ->paginate(12)
            ->withQueryString();

        $cartCount = Cart::where('user_id', auth()->id())->sum('qty');

        $bestSellers = Cache::remember('pelanggan_best_sellers_v2', 60, function () {
            $topAgg = PenjualanDetail::selectRaw('produk_id, SUM(jumlah) as total_terjual')
                ->groupBy('produk_id')
                ->orderByDesc('total_terjual')
                ->limit(6)
                ->get();

            $result = collect();
            if ($topAgg->isNotEmpty()) {
                $produkMap = Produk::whereIn('id', $topAgg->pluck('produk_id'))
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
                $result = Produk::where('stok', '>', 0)
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
            }
            return $result;
        });

        $favoriteIds = [];
        $favoriteProduks = collect();
        if (auth()->check()) {
            $favoriteIds = Favorite::where('user_id', auth()->id())->pluck('produk_id')->all();
            if (!empty($favoriteIds)) {
                $favoriteProduks = Produk::whereIn('id', $favoriteIds)->get();
            }
        }

        $whatsappNumber = config('services.whatsapp.number') ?? env('WHATSAPP_NUMBER');

        return view('pelanggan.dashboard', compact('produks', 'cartCount', 'bestSellers', 'search', 'favoriteIds', 'favoriteProduks', 'whatsappNumber'));
    }
}
