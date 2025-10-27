<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index()
    {
        $satuans = Satuan::orderBy('id', 'asc')->get();
        return view('master-data.satuan.index', compact('satuans'));
    }

    public function create()
    {
        return view('master-data.satuan.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:50',
    ]);

    // Ambil kode terakhir
    $lastKode = Satuan::orderBy('id', 'desc')->first();

    // Tentukan kode baru
    if (!$lastKode) {
        $newKode = 'ST001';
    } else {
        // Ambil angka dari kode terakhir, misal ST005 → 5
        $lastNumber = (int) substr($lastKode->kode, 2);
        $newKode = 'ST' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    // Simpan data
    Satuan::create([
        'kode' => $newKode,
        'nama' => $request->nama,
    ]);

    return redirect()->route('master-data.satuan.index')
        ->with('success', 'Satuan berhasil ditambahkan!');
}


    public function edit(Satuan $satuan)
    {
        return view('master-data.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, Satuan $satuan)
{
    $request->validate([
        'nama' => 'required|string|max:50',
    ]);

    $satuan->update([
        'nama' => $request->nama,
    ]);

    return redirect()->route('master-data.satuan.index')
        ->with('success', 'Satuan berhasil diperbarui!');
}

    public function destroy(Satuan $satuan)
    {
        $satuan->delete();

        return redirect()->route('master-data.satuan.index')->with('success', 'Satuan berhasil dihapus!');
    }
}
