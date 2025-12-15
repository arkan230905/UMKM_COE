<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Produk;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with('produk')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $cartCount = \App\Models\Cart::where('user_id', auth()->id())->sum('qty');

        return view('pelanggan.favorites', compact('favorites', 'cartCount'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'produk_id' => ['required', 'exists:produks,id'],
        ]);

        $fav = Favorite::where('user_id', auth()->id())
            ->where('produk_id', $request->produk_id)
            ->first();

        if ($fav) {
            $fav->delete();
            return back()->with('success', 'Dihapus dari favorit');
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'produk_id' => $request->produk_id,
        ]);

        return back()->with('success', 'Ditambahkan ke favorit');
    }
}
