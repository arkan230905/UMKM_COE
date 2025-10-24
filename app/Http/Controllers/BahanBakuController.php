<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    // Menampilkan semua data bahan baku
    public function index()
    {
        $bahanBaku = BahanBaku::all();
        return view('master-data.bahan-baku.index', compact('bahanBaku'));
    }

    // Menampilkan form tambah data
    public function create()
    {
        return view('master-data.bahan-baku.create');
    }

    // Simpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        BahanBaku::create([
            'nama_bahan' => $request->nama_bahan,
            'satuan' => $request->satuan,
            'stok' => $request->stok,
            'harga' => $request->harga,
        ]);

        return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        return view('master-data.bahan-baku.edit', compact('bahanBaku'));
    }

    // Update data
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->update($request->all());

        return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku berhasil diperbarui!');
    }

    // Hapus data
    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->delete();

        return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku berhasil dihapus!');
    }
}
