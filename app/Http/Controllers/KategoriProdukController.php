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
        $kategoris = KategoriProduk::where('user_id', auth()->id())
            ->withCount('produks')
            ->orderBy('nama')
            ->get();
        return view('master-data.kategori-produk.index', compact('kategoris'));
    }

    public function create()
    {
        return view('master-data.kategori-produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'nullable|string|max:50|unique:kategori_produks,kode_kategori,NULL,id,user_id,' . auth()->id(),
            'nama'          => 'required|string|max:100',
            'deskripsi'     => 'nullable|string',
        ]);

        $kategori = KategoriProduk::create([
            'user_id'       => auth()->id(),
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
        // Ensure user can only edit their own categories
        if ($kategoriProduk->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('master-data.kategori-produk.edit', ['kategori' => $kategoriProduk]);
    }

    public function update(Request $request, KategoriProduk $kategoriProduk)
    {
        // Ensure user can only update their own categories
        if ($kategoriProduk->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategori_produks,kode_kategori,' . $kategoriProduk->id . ',id,user_id,' . auth()->id(),
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
        // Ensure user can only delete their own categories
        if ($kategoriProduk->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if category has products
        if ($kategoriProduk->produks()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh produk');
        }

        $kategoriProduk->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
        }

        return redirect()->route('master-data.produk.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }
}
