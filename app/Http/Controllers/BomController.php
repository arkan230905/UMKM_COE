<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\BomDetail;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    // Index BOM
    public function index(Request $request)
    {
        $produks = Produk::orderBy('nama_produk')->get();
        $selectedProdukId = $request->produk_id;

        $bomItems = [];
        if ($selectedProdukId) {
            $bomItems = DB::table('boms')
                ->join('bahan_bakus', 'boms.bahan_baku_id', '=', 'bahan_bakus.id')
                ->where('boms.produk_id', $selectedProdukId)
                ->select(
                    'boms.*',
                    'bahan_bakus.nama_bahan',
                    'bahan_bakus.satuan',
                    'bahan_bakus.harga_satuan'
                )
                ->get()
                ->map(function($item) {
                    // Hitung harga per satuan kecil
                    $item->detail_harga = [];
                    switch ($item->satuan) {
                        case 'Kg':
                            $item->detail_harga['g'] = $item->harga_satuan / 1000;
                            $item->detail_harga['mg'] = $item->harga_satuan / 1000000;
                            break;
                        case 'Liter':
                            $item->detail_harga['ml'] = $item->harga_satuan / 1000;
                            $item->detail_harga['cl'] = $item->harga_satuan / 100;
                            break;
                    }
                    $item->total_harga = $item->jumlah * $item->harga_satuan;
                    return $item;
                });
        }

        return view('master-data.bom.index', compact('produks', 'bomItems', 'selectedProdukId'));
    }

    // Form Create BOM
    public function create()
    {
        $produks = Produk::orderBy('nama_produk')->get();
        $bahanBaku = BahanBaku::orderBy('nama_bahan')->get();

        return view('master-data.bom.create', compact('produks', 'bahanBaku'));
    }

    // Simpan BOM
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'bahan_baku.*' => 'required|exists:bahan_bakus,id',
            'jumlah.*' => 'required|numeric|min:0',
        ]);

        $bom = Bom::create(['produk_id' => $request->produk_id]);

        foreach ($request->bahan_baku as $index => $bahanId) {
            $bahan = BahanBaku::find($bahanId);
            $jumlah = $request->jumlah[$index];
            $harga_per_satuan = $bahan->harga_satuan;
            $total_harga = $jumlah * $harga_per_satuan;

            BomDetail::create([
                'bom_id' => $bom->id,
                'bahan_baku_id' => $bahanId,
                'jumlah' => $jumlah,
                'satuan' => $bahan->satuan,
                'harga_per_satuan' => $harga_per_satuan,
                'total_harga' => $total_harga,
            ]);
        }

        return redirect()->route('master-data.bom.index')->with('success', 'BOM berhasil dibuat');
    }
}
