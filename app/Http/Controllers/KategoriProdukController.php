<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use Illuminate\Http\Request;

class KategoriProdukController extends Controller
{
    public function index()
    {
        $kategoris = KategoriProduk::withCount('produks')->orderBy('nama')->get();
        return view('master-data.kategori-produk.index', compact('kategoris'));
    }

    public function create()
    {
        return view('master-data.kategori-produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategori_produks,kode_kategori',
            'nama'          => 'required|string|max:100',
            'deskripsi'     => 'nullable|string',
        ]);

        $kategori = KategoriProduk::create([
            'kode_kategori' => strtoupper($request->kode_kategori),
            'nama'          => $request->nama,
            'deskripsi'     => $request->deskripsi,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan.',
                'id'      => $kategori->id,
                'data'    => $kategori,
            ]);
        }

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(KategoriProduk $kategoriProduk)
    {
        return redirect()->route('master-data.kategori-produk.index');
    }

    public function edit(KategoriProduk $kategoriProduk)
    {
        return view('master-data.kategori-produk.edit', ['kategori' => $kategoriProduk]);
    }

    public function update(Request $request, KategoriProduk $kategoriProduk)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategori_produks,kode_kategori,' . $kategoriProduk->id,
            'nama'          => 'required|string|max:100',
            'deskripsi'     => 'nullable|string',
        ]);

        $kategoriProduk->update([
            'kode_kategori' => strtoupper($request->kode_kategori),
            'nama'          => $request->nama,
            'deskripsi'     => $request->deskripsi,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui.']);
        }

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriProduk $kategoriProduk)
    {
        $kategoriProduk->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
        }

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }
}
