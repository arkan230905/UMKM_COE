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
        $userId = auth('pelanggan')->id();
        
        // Get all favorite products for this user
        $favoriteProduks = Produk::withoutGlobalScopes()
            ->whereHas('favorites', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('satuan', 'kategori')
            ->orderBy('nama_produk')
            ->paginate(12);

        $cartCount = 0;
        if ($userId) {
            $cartCount = \App\Models\Cart::where('user_id', $userId)->sum('qty');
        }

        $kategoris = \App\Models\KategoriProduk::withoutGlobalScopes()
            ->whereHas('produks', function($query) {
                $query->withoutGlobalScopes();
            })
            ->withCount(['produks' => function($query) {
                $query->withoutGlobalScopes();
            }])
            ->orderBy('nama')
            ->get();

        $favoriteIds = Favorite::where('user_id', $userId)->pluck('produk_id')->toArray();

        return view('pelanggan.favorites', compact('favoriteProduks', 'cartCount', 'kategoris', 'favoriteIds'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id'
        ]);

        $userId = auth('pelanggan')->id();
        $produkId = $request->produk_id;

        $favorite = Favorite::where('user_id', $userId)
            ->where('produk_id', $produkId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
        } else {
            Favorite::create([
                'user_id' => $userId,
                'produk_id' => $produkId
            ]);
            $isFavorite = true;
        }

        return response()->json(['success' => true, 'is_favorite' => $isFavorite]);
    }
}
