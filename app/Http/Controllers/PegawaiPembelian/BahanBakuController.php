<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $bahanBakus = BahanBaku::with('satuan')->latest()->paginate(15);
        return view('pegawai-pembelian.bahan-baku.index', compact('bahanBakus'));
    }

    public function create()
    {
        $satuans = Satuan::all();
        return view('pegawai-pembelian.bahan-baku.create', compact('satuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        BahanBaku::create($validated);

        return redirect()->route('pegawai-pembelian.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan!');
    }

    public function show($id)
    {
        $bahanBaku = BahanBaku::with('satuan')->findOrFail($id);
        return view('pegawai-pembelian.bahan-baku.show', compact('bahanBaku'));
    }

    public function edit($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $satuans = Satuan::all();
        return view('pegawai-pembelian.bahan-baku.edit', compact('bahanBaku', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->update($validated);

        return redirect()->route('pegawai-pembelian.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil diupdate!');
    }

    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->delete();

        return redirect()->route('pegawai-pembelian.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus!');
    }
}
