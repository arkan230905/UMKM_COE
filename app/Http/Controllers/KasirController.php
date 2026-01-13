<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function __construct()
    {
        // Check if user is logged in as kasir
        $this->middleware(function ($request, $next) {
            if (!session('kasir_id')) {
                return redirect()->route('kasir.login')->withErrors(['error' => 'Silakan login terlebih dahulu']);
            }
            return $next($request);
        });
    }

    /**
     * POS (Point of Sale) interface untuk kasir
     */
    public function index()
    {
        $produks = Produk::where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->get();

        $kasbank = Coa::whereIn('kode_akun', ['101', '102'])->orderBy('nama_akun')->get();

        return view('kasir.pos', [
            'title' => 'Point of Sale',
            'kasir' => [
                'id' => session('kasir_id'),
                'nama' => session('kasir_nama'),
                'kode' => session('kasir_kode'),
                'email' => session('kasir_email'),
                'jabatan' => session('kasir_jabatan'),
            ],
            'perusahaan' => [
                'nama' => session('perusahaan_nama'),
                'kode' => session('perusahaan_kode'),
            ],
            'produks' => $produks,
            'kasbank' => $kasbank
        ]);
    }

    /**
     * Cari produk untuk POS
     */
    public function cariProduk(Request $request)
    {
        $search = $request->get('q');
        
        $produks = Produk::where('stok', '>', 0)
            ->where(function($query) use ($search) {
                $query->where('nama_produk', 'like', "%{$search}%")
                      ->orWhere('kode_produk', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($produks);
    }

    /**
     * Simpan transaksi penjualan dari POS
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga_jual' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'sumber_dana' => 'required_if:payment_method,cash,transfer',
            'bayar' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate nomor penjualan
            $lastPenjualan = Penjualan::whereDate('created_at', today())->count();
            $nomorPenjualan = 'PJ' . date('Ymd') . str_pad($lastPenjualan + 1, 4, '0', STR_PAD_LEFT);

            // Hitung total
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga_jual'];
            }

            // Validasi pembayaran
            if ($request->bayar < $total) {
                return back()->withErrors(['bayar' => 'Jumlah bayar tidak mencukupi']);
            }

            // Simpan penjualan
            $penjualan = Penjualan::create([
                'nomor_penjualan' => $nomorPenjualan,
                'tanggal' => now(),
                'total' => $total,
                'bayar' => $request->bayar,
                'kembalian' => $request->bayar - $total,
                'payment_method' => $request->payment_method,
                'sumber_dana' => $request->sumber_dana,
                'kasir_id' => session('kasir_id'),
                'kasir_nama' => session('kasir_nama'),
                'status' => 'completed'
            ]);

            // Simpan detail dan update stok
            foreach ($request->items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                
                // Cek stok
                if ($produk->stok < $item['jumlah']) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak mencukupi");
                }

                // Simpan detail
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $produk->id,
                    'jumlah' => $item['jumlah'],
                    'harga_jual' => $item['harga_jual'],
                    'subtotal' => $item['jumlah'] * $item['harga_jual']
                ]);

                // Update stok
                $produk->decrement('stok', $item['jumlah']);
            }

            DB::commit();

            return redirect()->route('kasir.struk', $penjualan->id)
                ->with('success', 'Transaksi berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Cetak struk penjualan
     */
    public function cetak($id)
    {
        $penjualan = Penjualan::with(['details.produk'])->findOrFail($id);

        return view('kasir.struk', [
            'title' => 'Struk Penjualan',
            'penjualan' => $penjualan
        ]);
    }
}