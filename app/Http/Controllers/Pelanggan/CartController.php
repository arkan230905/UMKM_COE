<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected function getCartTotalQty()
    {
        $cartCount = 0;
        if (auth('pelanggan')->check()) {
            $perusahaan = current_perusahaan();
            $query = Cart::where('user_id', auth('pelanggan')->id());
            
            if ($perusahaan) {
                $query->whereHas('produk', function ($q) use ($perusahaan) {
                    $q->withoutGlobalScopes()->where('user_id', $perusahaan->user_id);
                });
            }
            
            $cartCount = $query->sum('qty');
        }
        return (int) $cartCount;
    }

    public function index()
    {
        $perusahaan = current_perusahaan();
        $perusahaan_slug = perusahaan_slug($perusahaan);

        $carts = Cart::with(['produk' => function ($query) {
            $query->withoutGlobalScopes();
        }])
            ->where('user_id', auth('pelanggan')->id())
            ->whereHas('produk', function ($q) use ($perusahaan) {
                if ($perusahaan) {
                    $q->withoutGlobalScopes()->where('user_id', $perusahaan->user_id);
                }
            })
            ->get();

        $total = $carts->sum('subtotal');

        return view('pelanggan.cart', compact('carts', 'total', 'perusahaan_slug'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'required|integer|min:1',
        ]);

        $produk = Produk::withoutGlobalScopes()->findOrFail($request->produk_id);

        // Cek stok
        if ($produk->stok < $request->qty) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        DB::beginTransaction();
        try {
            $cart = Cart::where('user_id', auth('pelanggan')->id())
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
                    'user_id' => auth('pelanggan')->id(),
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

    public function update(Request $request, $perusahaan_slug, Cart $cart)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        // Cek ownership
        if ($cart->user_id !== auth('pelanggan')->id()) {
            abort(403);
        }

        // Load produk dengan withoutGlobalScopes karena produk milik owner lain
        $produk = \App\Models\Produk::withoutGlobalScopes()->find($cart->produk_id);
        
        if (!$produk) {
            return back()->with('error', 'Produk tidak ditemukan!');
        }

        // Cek stok
        if ($produk->stok < $request->qty) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart->update([
            'qty' => $request->qty,
            'subtotal' => $request->qty * $cart->harga,
        ]);

        return back()->with('success', 'Keranjang berhasil diupdate!');
    }

    public function destroy($perusahaan_slug, Cart $cart)
    {
        // Cek ownership
        if ($cart->user_id !== auth('pelanggan')->id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang!');
    }

    public function clear()
    {
        Cart::where('user_id', auth('pelanggan')->id())->delete();

        return back()->with('success', 'Keranjang berhasil dikosongkan!');
    }

    public function storeAjax(Request $request)
    {
        \Log::info('storeAjax called', ['request' => $request->all()]);
        
        if (!auth('pelanggan')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'required|integer|min:1',
        ]);

        $produk = Produk::withoutGlobalScopes()->findOrFail($request->produk_id);

        // Cek stok
        if ($produk->stok < $request->qty) {
            return response()->json(['error' => 'Stok tidak mencukupi!'], 422);
        }

        DB::beginTransaction();
        try {
            $cart = Cart::where('user_id', auth('pelanggan')->id())
                ->where('produk_id', $request->produk_id)
                ->first();

            if ($cart) {
                // Update qty
                $newQty = $cart->qty + $request->qty;
                if ($produk->stok < $newQty) {
                    return response()->json(['error' => 'Stok tidak mencukupi!'], 422);
                }
                $cart->update([
                    'qty' => $newQty,
                    'subtotal' => $newQty * $cart->harga,
                ]);
            } else {
                // Create new
                $cart = Cart::create([
                    'user_id' => auth('pelanggan')->id(),
                    'produk_id' => $request->produk_id,
                    'qty' => $request->qty,
                    'harga' => $produk->harga_jual,
                    'subtotal' => $request->qty * $produk->harga_jual,
                ]);
            }

            DB::commit();
            \Log::info('storeAjax success', ['cart_id' => $cart->id, 'qty' => $cart->qty]);
            return response()->json([
                'success' => true,
                'qty' => $cart->qty,
                'cart_total_qty' => $this->getCartTotalQty(),
                'cart_id' => $cart->id,
                'message' => 'Produk berhasil ditambahkan ke keranjang!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storeAjax error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Gagal menambahkan ke keranjang: ' . $e->getMessage()], 500);
        }
    }

    public function getCartItems()
    {
        if (!auth('pelanggan')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $carts = Cart::where('user_id', auth('pelanggan')->id())
            ->get()
            ->mapWithKeys(function ($cart) {
                return [$cart->produk_id => ['qty' => $cart->qty, 'id' => $cart->id]];
            });

        return response()->json($carts);
    }

    public function updateAjax(Request $request, $perusahaan_slug, $id)
    {
        if (!auth('pelanggan')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::findOrFail($id);

        // Cek ownership
        if ($cart->user_id !== auth('pelanggan')->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $produk = Produk::withoutGlobalScopes()->findOrFail($cart->produk_id);

        // Cek stok
        if ($produk->stok < $request->qty) {
            return response()->json(['error' => 'Stok tidak mencukupi!'], 422);
        }

        $cart->update([
            'qty' => $request->qty,
            'subtotal' => $request->qty * $cart->harga,
        ]);

        return response()->json([
            'success' => true,
            'qty' => $cart->qty,
            'cart_total_qty' => $this->getCartTotalQty(),
            'subtotal' => $cart->subtotal,
            'message' => 'Keranjang berhasil diperbarui'
        ]);
    }

    public function destroyAjax(Request $request, $perusahaan_slug, $id)
    {
        if (!auth('pelanggan')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::findOrFail($id);

        // Cek ownership
        if ($cart->user_id !== auth('pelanggan')->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'cart_total_qty' => $this->getCartTotalQty(),
            'message' => 'Item dihapus dari keranjang'
        ]);
    }
}
