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
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $kategoris = KategoriBahanPendukung::where('user_id', auth()->id())
            ->withCount('bahanPendukungs')
            ->orderBy('nama')
            ->get();
        return view('master-data.kategori-bahan-pendukung.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 🔒 SECURITY: Add user_id to unique validation for multi-tenant isolation
            'nama' => 'required|string|max:100|unique:kategori_bahan_pendukung,nama,NULL,id,user_id,' . auth()->id(),
            'keterangan' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = true;
        $validated['user_id'] = auth()->id(); // 🔒 CRITICAL: multi-tenant isolation
        KategoriBahanPendukung::create($validated);

        return redirect()->route('master-data.kategori-bahan-pendukung.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $kategori = KategoriBahanPendukung::where('user_id', auth()->id())->findOrFail($id);
        
        $validated = $request->validate([
            // 🔒 SECURITY: Add user_id to unique validation for multi-tenant isolation
            'nama' => 'required|string|max:100|unique:kategori_bahan_pendukung,nama,' . $id . ',id,user_id,' . auth()->id(),
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
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $kategori = KategoriBahanPendukung::where('user_id', auth()->id())->findOrFail($id);
        
        if ($kategori->bahanPendukungs()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan');
        }

        $kategori->delete();
        return redirect()->route('master-data.kategori-bahan-pendukung.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
