<?php

namespace App\Http\Controllers;

use App\Models\Produk;
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
            'harga_jual' => 'required|numeric',
        ]);

        Produk::create($request->all());
        return redirect()->route('master-data.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        return view('master-data.produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga_jual' => 'required|numeric',
        ]);

        $produk->update($request->all());
        return redirect()->route('master-data.produk.index')->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();
        return redirect()->route('master-data.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
