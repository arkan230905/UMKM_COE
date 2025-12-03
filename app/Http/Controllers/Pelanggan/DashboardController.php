<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Cart;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->role !== 'pelanggan') {
                abort(403, 'Unauthorized. Halaman ini hanya untuk pelanggan.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $produks = Produk::where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->paginate(12);

        $cartCount = Cart::where('user_id', auth()->id())->sum('qty');

        return view('pelanggan.dashboard', compact('produks', 'cartCount'));
    }
}
