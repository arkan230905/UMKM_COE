<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Ambil produk yang tersedia (stok > 0)
        $produks = Produk::where('stok', '>', 0)
            ->with('satuan')
            ->orderBy('nama_produk')
            ->get();
        
        // Hitung stok tersedia dari stock_movements
        $produks = $produks->map(function($p) {
            $stokMasuk = DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('direction', 'in')
                ->sum('qty');
            
            $stokKeluar = DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('direction', 'out')
                ->sum('qty');
            
            $p->stok_tersedia = max(0, $stokMasuk - $stokKeluar);
            return $p;
        })->filter(function($p) {
            return $p->stok_tersedia > 0;
        });
        
        // Ambil keranjang dari session
        $cart = session('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));
        
        return view('pelanggan.dashboard', compact('produks', 'cart', 'cartCount', 'user'));
    }
    
    public function addToCart(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'required|integer|min:1',
        ]);
        
        $produk = Produk::findOrFail($request->produk_id);
        
        // Cek stok
        $stokMasuk = DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $produk->id)
            ->where('direction', 'in')
            ->sum('qty');
        
        $stokKeluar = DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $produk->id)
            ->where('direction', 'out')
            ->sum('qty');
        
        $stokTersedia = $stokMasuk - $stokKeluar;
        
        if ($request->qty > $stokTersedia) {
            return back()->with('error', 'Stok tidak mencukupi. Tersedia: ' . $stokTersedia);
        }
        
        $cart = session('cart', []);
        
        if (isset($cart[$produk->id])) {
            $cart[$produk->id]['qty'] += $request->qty;
        } else {
            $cart[$produk->id] = [
                'produk_id' => $produk->id,
                'nama' => $produk->nama_produk,
                'harga' => $produk->harga_jual,
                'qty' => $request->qty,
                'foto' => $produk->foto_produk,
            ];
        }
        
        session(['cart' => $cart]);
        
        return back()->with('success', 'Produk ditambahkan ke keranjang!');
    }
    
    public function cart()
    {
        $cart = session('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['qty'];
        }
        
        return view('pelanggan.cart', compact('cart', 'total'));
    }
    
    public function updateCart(Request $request)
    {
        $cart = session('cart', []);
        
        if (isset($cart[$request->produk_id])) {
            if ($request->qty > 0) {
                $cart[$request->produk_id]['qty'] = $request->qty;
            } else {
                unset($cart[$request->produk_id]);
            }
        }
        
        session(['cart' => $cart]);
        
        return back()->with('success', 'Keranjang diupdate!');
    }
    
    public function removeFromCart($produk_id)
    {
        $cart = session('cart', []);
        unset($cart[$produk_id]);
        session(['cart' => $cart]);
        
        return back()->with('success', 'Produk dihapus dari keranjang!');
    }
    
    public function checkout()
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('pelanggan.dashboard')->with('error', 'Keranjang kosong!');
        }
        
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['qty'];
        }
        
        return view('pelanggan.checkout', compact('cart', 'total'));
    }
    
    public function processCheckout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,transfer',
            'alamat_pengiriman' => 'required|string',
            'catatan' => 'nullable|string',
        ]);
        
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('pelanggan.dashboard')->with('error', 'Keranjang kosong!');
        }
        
        DB::beginTransaction();
        
        try {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['harga'] * $item['qty'];
            }
            
            // Buat penjualan
            $penjualan = Penjualan::create([
                'tanggal' => now(),
                'payment_method' => $request->payment_method,
                'jumlah' => array_sum(array_column($cart, 'qty')),
                'total' => $total,
                'user_id' => Auth::id(),
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'catatan' => $request->catatan,
                'status' => 'pending', // pending, processing, completed, cancelled
            ]);
            
            // Simpan detail
            foreach ($cart as $item) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $item['harga'],
                    'subtotal' => $item['harga'] * $item['qty'],
                ]);
            }
            
            DB::commit();
            
            // Kosongkan keranjang
            session()->forget('cart');
            
            return redirect()->route('pelanggan.orders')->with('success', 'Pesanan berhasil dibuat! Nomor pesanan: ' . $penjualan->nomor_penjualan);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }
    
    public function orders()
    {
        $orders = Penjualan::where('user_id', Auth::id())
            ->with('details.produk')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pelanggan.orders', compact('orders'));
    }
}
