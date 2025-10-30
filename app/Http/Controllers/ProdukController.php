<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\PenjualanDetail;
use App\Models\Produksi;
use App\Models\Bom;
use App\Models\StockLayer;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Support\UnitConverter;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::with(['boms.bahanBaku'])->get();
        // Hitung harga BOM per produk (LIVE): total bahan = sum(harga_satuan bahan saat ini × qty konversi) + btkl_default + bop_default
        $hargaBom = [];
        $converter = new UnitConverter();
        foreach ($produks as $p) {
            $sumBahan = 0.0;
            foreach (($p->boms ?? []) as $it) {
                $bahan = $it->bahanBaku;
                if (!$bahan) { continue; }
                $qtyResep = (float) ($it->jumlah ?? 0);
                $satuanResep = $it->satuan_resep ?: $bahan->satuan;
                $qtyBase = $converter->convert($qtyResep, (string)$satuanResep, (string)$bahan->satuan);
                $hargaSatuan = (float) ($bahan->harga_satuan ?? 0);
                $sumBahan += $hargaSatuan * $qtyBase;
            }
            $btkl = (float) ($p->btkl_default ?? 0);
            $bop  = (float) ($p->bop_default ?? 0);
            $hargaBom[$p->id] = $sumBahan + $btkl + $bop;
        }
        return view('master-data.produk.index', compact('produks','hargaBom'));
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
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        // Harga jual kosong dulu; akan dihitung dari BOM
        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga_jual' => null,
            'margin_percent' => $request->input('margin_percent'),
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
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
            'margin_percent' => 'nullable|numeric|min:0',
            'bopb_method' => 'nullable|in:per_unit,per_hour',
            'bopb_rate' => 'nullable|numeric|min:0',
            'labor_hours_per_unit' => 'nullable|numeric|min:0',
            'btkl_per_unit' => 'nullable|numeric|min:0',
        ]);

        $produk->update([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'margin_percent' => $request->input('margin_percent'),
            'bopb_method' => $request->input('bopb_method'),
            'bopb_rate' => $request->input('bopb_rate'),
            'labor_hours_per_unit' => $request->input('labor_hours_per_unit'),
            'btkl_per_unit' => $request->input('btkl_per_unit'),
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
