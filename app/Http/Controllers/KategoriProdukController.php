<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use Illuminate\Http\Request;

class KategoriProdukController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        $validated = $request->validate([
            'kode_kategori' => 'nullable|string|max:50|unique:kategori_produks,kode_kategori',
            'nama' => 'required|string|max:100|unique:kategori_produks,nama',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        // Auto-generate kode_kategori if not provided
        if (empty($validated['kode_kategori'])) {
            $validated['kode_kategori'] = 'KAT-' . strtoupper(uniqid());
        }

        KategoriProduk::create($validated);

        return redirect()->route('master-data.kategori-produk.index')
            ->with('success', 'Kategori produk berhasil ditambahkan');
    }

    public function edit($id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        return view('master-data.kategori-produk.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        
        $validated = $request->validate([
            'kode_kategori' => 'nullable|string|max:50|unique:kategori_produks,kode_kategori,' . $id,
            'nama' => 'required|string|max:100|unique:kategori_produks,nama,' . $id,
            'deskripsi' => 'nullable|string|max:255',
        ]);

        // Auto-generate kode_kategori if not provided and current one is empty
        if (empty($validated['kode_kategori']) && empty($kategori->kode_kategori)) {
            $validated['kode_kategori'] = 'KAT-' . strtoupper(uniqid());
        } elseif (empty($validated['kode_kategori'])) {
            // Keep existing kode_kategori if new one is not provided
            $validated['kode_kategori'] = $kategori->kode_kategori;
        }

        $kategori->update($validated);

        return redirect()->route('master-data.kategori-produk.index')
            ->with('success', 'Kategori produk berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        
        if ($kategori->produks()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh produk');
        }

        $kategori->delete();
        return redirect()->route('master-data.kategori-produk.index')
            ->with('success', 'Kategori produk berhasil dihapus');
    }
}
