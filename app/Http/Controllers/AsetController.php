<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    public function index()
    {
        $asets = Aset::all();
        return view('master-data.aset.index', compact('asets'));
    }

    public function create()
    {
        return view('master-data.aset.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'tanggal_beli' => 'required|date',
        ]);

        Aset::create($validated);

        return redirect()->route('master-data.aset.index')->with('success', 'Aset berhasil ditambahkan');
    }

    public function edit(Aset $aset)
    {
        return view('master-data.aset.edit', compact('aset'));
    }

    public function update(Request $request, Aset $aset)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'tanggal_beli' => 'required|date',
        ]);

        $aset->update($validated);

        return redirect()->route('master-data.aset.index')->with('success', 'Aset berhasil diperbarui');
    }

    public function destroy(Aset $aset)
    {
        $aset->delete();
        return redirect()->route('master-data.aset.index')->with('success', 'Aset berhasil dihapus');
    }
}
