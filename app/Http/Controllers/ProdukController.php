<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\PenjualanDetail;
use App\Models\Produksi;
use App\Models\Bom;
use App\Models\StockLayer;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::all();
        return view('master-data.produk.index', compact('produks'));
    }

    public function create()
    {
        return view('master-data.produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        // Harga jual kosong dulu
        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga_jual' => null,
        ]);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan. Silakan tambahkan BOM.');
    }

    public function edit(Produk $produk)
    {
        return view('master-data.produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $produk->update([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Produk $produk)
    {
        // Cegah hapus jika masih dipakai transaksi atau master terkait
        $blockers = [];
        $pd = PenjualanDetail::where('produk_id', $produk->id)->count();
        if ($pd > 0) $blockers[] = "Dipakai di Penjualan (".$pd.")";
        $pr = Produksi::where('produk_id', $produk->id)->count();
        if ($pr > 0) $blockers[] = "Dipakai di Produksi (".$pr.")";
        $bm = Bom::where('produk_id', $produk->id)->count();
        if ($bm > 0) $blockers[] = "Memiliki BOM (".$bm.")";
        $sl = StockLayer::where('item_type','product')->where('item_id',$produk->id)->count();
        if ($sl > 0) $blockers[] = "Memiliki Stock Layer (".$sl.")";
        $sm = StockMovement::where('item_type','product')->where('item_id',$produk->id)->count();
        if ($sm > 0) $blockers[] = "Memiliki Mutasi Stok (".$sm.")";

        if (!empty($blockers)) {
            return redirect()->route('master-data.produk.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih terkait: '.implode(', ', $blockers).'. Hapus/arsipkan transaksi terkait terlebih dahulu.');
        }

        $produk->delete();
        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Produk berhasil dihapus.');
    }
}
