<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Satuan;
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
        $satuans = Satuan::all();
        return view('master-data.bahan-baku.create', compact('satuans'));
    }

    // Simpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        // Get the satuan name from the satuan_id
        $satuan = Satuan::findOrFail($request->satuan_id);
        
        BahanBaku::create([
            'nama_bahan' => $request->nama_bahan,
            'satuan_id' => $request->satuan_id,
            'satuan' => $satuan->nama, // Add the satuan name
            'stok' => $request->stok,
            'harga_satuan' => $request->harga_satuan,
        ]);

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $bahanBaku = BahanBaku::with('satuan')->findOrFail($id);
        $satuans = Satuan::all();
        return view('master-data.bahan-baku.edit', compact('bahanBaku', 'satuans'));
    }

    // Update data
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id);
        
        // Get the satuan name from the satuan_id
        $satuan = Satuan::findOrFail($request->satuan_id);
        
        // Update properties one by one and save
        $bahanBaku->nama_bahan = $request->nama_bahan;
        $bahanBaku->satuan_id = $request->satuan_id;
        $bahanBaku->satuan = $satuan->nama; // Update the satuan name
        $bahanBaku->stok = $request->stok;
        $bahanBaku->harga_satuan = $request->harga_satuan;
        
        // Save changes
        $bahanBaku->save();

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil diperbarui!');
    }

    // Hapus data
    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->delete();

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil dihapus!');
    }
}
