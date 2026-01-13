<?php

namespace App\Http\Controllers;

use App\Models\KategoriBahanPendukung;
use Illuminate\Http\Request;

class KategoriBahanPendukungController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $kategoris = KategoriBahanPendukung::withCount('bahanPendukungs')->orderBy('nama')->get();
        return view('master-data.kategori-bahan-pendukung.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_bahan_pendukung,nama',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = true;
        KategoriBahanPendukung::create($validated);

        return redirect()->route('master-data.kategori-bahan-pendukung.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriBahanPendukung::findOrFail($id);
        
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_bahan_pendukung,nama,' . $id,
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $kategori->update($validated);

        return redirect()->route('master-data.kategori-bahan-pendukung.index')
            ->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kategori = KategoriBahanPendukung::findOrFail($id);
        
        if ($kategori->bahanPendukungs()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan');
        }

        $kategori->delete();
        return redirect()->route('master-data.kategori-bahan-pendukung.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
