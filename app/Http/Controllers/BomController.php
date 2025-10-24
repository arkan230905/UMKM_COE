<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\BomDetail;

class BomController extends Controller
{
    // Menampilkan daftar produk
    public function index()
    {
        $produks = Produk::all();
        return view('master-data.bom.index', compact('produks'));
    }

    // Form tambah BOM
    public function create(Request $request)
    {
        $produks = Produk::all();
        $bahanBaku = BahanBaku::all();
        $selectedProdukId = $request->query('produk_id');

        return view('master-data.bom.create', compact('produks', 'bahanBaku', 'selectedProdukId'));
    }

    // Form edit BOM
    public function edit($produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        $bahanBaku = BahanBaku::all();

        $bom = Bom::firstOrCreate(['produk_id' => $produk_id]);
        $bomDetails = BomDetail::where('bom_id', $bom->id)->get();

        return view('master-data.bom.edit', compact('produk', 'bahanBaku', 'bomDetails'));
    }

    // Simpan atau update BOM
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah.*' => 'required|numeric|min:0',
            'kategori.*' => 'required|in:BTKL,BOP',
        ]);

        $bom = Bom::firstOrCreate(['produk_id' => $request->produk_id]);

        foreach ($request->bahan_baku_id as $i => $bahan_id) {
            $bahan = BahanBaku::findOrFail($bahan_id);
            $jumlah = $request->jumlah[$i];
            $kategori = $request->kategori[$i];

            BomDetail::updateOrCreate(
                ['bom_id' => $bom->id, 'bahan_baku_id' => $bahan_id],
                [
                    'jumlah' => $jumlah,
                    'satuan' => $bahan->satuan,
                    'harga_per_satuan' => $bahan->harga_satuan,
                    'total_harga' => $jumlah * $bahan->harga_satuan,
                    'kategori' => $kategori
                ]
            );
        }

        return redirect()->route('master-data.bom.show', $request->produk_id)
                         ->with('success', 'BOM berhasil disimpan.');
    }

    // Update BOM (edit)
    public function update(Request $request, $produk_id)
    {
        return $this->store($request); // bisa gunakan store karena logikanya sama
    }

    // Tampilkan detail BOM
    public function show($produk_id)
    {
        $produk = Produk::findOrFail($produk_id);

        $bomDetails = BomDetail::whereHas('bom', function($q) use ($produk_id) {
            $q->where('produk_id', $produk_id);
        })->with('bahanBaku')->get();

        $totalBOM = $bomDetails->sum(fn($d) => $d->total_harga);

        return view('master-data.bom.show', compact('produk', 'bomDetails', 'totalBOM'));
    }
}
