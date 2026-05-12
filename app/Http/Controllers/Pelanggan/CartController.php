<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with('produk')
            ->where('user_id', auth()->id())
            ->get();

        $total = $carts->sum('subtotal');

        return view('pelanggan.cart', compact('carts', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'required|integer|min:1',
        ]);

        $produk = Produk::findOrFail($request->produk_id);

        // Cek stok
        if ($produk->stok < $request->qty) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        DB::beginTransaction();
        try {
            $cart = Cart::where('user_id', auth()->id())
                ->where('produk_id', $request->produk_id)
                ->first();

            if ($cart) {
                // Update qty
                $newQty = $cart->qty + $request->qty;
                if ($produk->stok < $newQty) {
                    return back()->with('error', 'Stok tidak mencukupi!');
                }
                $cart->update([
                    'qty' => $newQty,
                    'subtotal' => $newQty * $cart->harga,
                ]);
            } else {
                // Create new
                Cart::create([
                    'user_id' => auth()->id(),
                    'produk_id' => $request->produk_id,
                    'qty' => $request->qty,
                    'harga' => $produk->harga_jual,
                    'subtotal' => $request->qty * $produk->harga_jual,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan ke keranjang: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        // Cek ownership
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        // Cek stok
        if ($cart->produk->stok < $request->qty) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart->update([
            'qty' => $request->qty,
            'subtotal' => $request->qty * $cart->harga,
        ]);

        return back()->with('success', 'Keranjang berhasil diupdate!');
    }

    public function destroy(Cart $cart)
    {
        // Cek ownership
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang!');
    }

    public function clear()
    {
        Cart::where('user_id', auth()->id())->delete();

        return back()->with('success', 'Keranjang berhasil dikosongkan!');
    }
}
