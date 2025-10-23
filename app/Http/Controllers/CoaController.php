<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coa;

class CoaController extends Controller
{
    // Menampilkan semua COA
    public function index()
    {
        $coas = Coa::all(); // Ambil semua data dari tabel coa
        return view('master-data.coa.index', compact('coas'));
    }

    // Menampilkan form tambah COA
    public function create()
    {
        return view('master-data.coa.create');
    }

    // Simpan COA baru
    public function store(Request $request)
    {
        $request->validate([
            'kode_akun' => 'required|string|max:50',
            'nama_akun' => 'required|string|max:255',
            'jenis' => 'required|string|max:50',
        ]);

        Coa::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'jenis' => $request->jenis,
        ]);

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil ditambahkan');
    }

    // Menampilkan form edit COA
    public function edit($id)
    {
        $coa = Coa::findOrFail($id);
        return view('master-data.coa.edit', compact('coa'));
    }

    // Update COA
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_akun' => 'required|string|max:50',
            'nama_akun' => 'required|string|max:255',
            'jenis' => 'required|string|max:50',
        ]);

        $coa = Coa::findOrFail($id);
        $coa->update([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'jenis' => $request->jenis,
        ]);

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil diperbarui');
    }

    // Hapus COA
    public function destroy($id)
    {
        $coa = Coa::findOrFail($id);
        $coa->delete();

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil dihapus');
    }
}
