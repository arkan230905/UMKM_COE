<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\BTKL;
use App\Models\BOP;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    // ===============================
    // 🏠 Halaman Utama BOM
    // ===============================
    public function index()
    {
        // Ambil semua produk (untuk dropdown)
        $produks = Produk::all();

        return view('master-data.bom.index', compact('produks'));
    }

    // ===============================
    // ➕ Form Tambah BOM
    // ===============================
    public function create()
    {
        $produks = Produk::all();
        $bahan_bakus = BahanBaku::all();
        return view('master-data.bom.create', compact('produks', 'bahan_bakus'));
    }

    // ===============================
    // 💾 Simpan BOM ke Database
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required',
            'bahan_baku_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            $produk = Produk::findOrFail($request->produk_id);
            $total_bahan = 0;

            foreach ($request->bahan_baku_id as $i => $bahan_id) {
                $bahan = BahanBaku::findOrFail($bahan_id);
                $qty = $request->jumlah[$i];
                $subtotal = $bahan->harga_satuan * $qty;

                $total_bahan += $subtotal;

                Bom::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => $bahan_id,
                    'jumlah' => $qty,
                    'total_biaya' => $subtotal,
                ]);
            }

            $btkl_sum = BTKL::sum('nominal');
            $bop_sum = BOP::sum('nominal');
            $grand_total = $total_bahan + $btkl_sum + $bop_sum;
            $harga_jual = $grand_total * 1.6;

            $produk->update(['harga_produk' => $harga_jual]);
        });

        return redirect()->route('master-data.bom.index')->with('success', 'BOM berhasil ditambahkan!');
    }

    // ===============================
    // 📄 Tampilkan BOM per Produk (AJAX)
    // ===============================
    public function view($produk_id)
    {
        $produk = Produk::findOrFail($produk_id);

        // Ambil bahan baku terkait
        $items = Bom::where('produk_id', $produk_id)
            ->join('bahan_bakus', 'boms.bahan_baku_id', '=', 'bahan_bakus.id')
            ->select(
                'bahan_bakus.nama_bahan',
                'bahan_bakus.satuan',
                'bahan_bakus.harga_satuan',
                'boms.jumlah',
                'boms.total_biaya'
            )
            ->get();

        $total_bahan = $items->sum('total_biaya');
        $btkl_sum = BTKL::sum('nominal');
        $bop_sum = BOP::sum('nominal');
        $grand_total = $total_bahan + $btkl_sum + $bop_sum;
        $harga_jual = $grand_total * 1.6;

        return view('master-data.bom.partials.table', compact(
            'produk',
            'items',
            'total_bahan',
            'btkl_sum',
            'bop_sum',
            'grand_total',
            'harga_jual'
        ));
    }
}
