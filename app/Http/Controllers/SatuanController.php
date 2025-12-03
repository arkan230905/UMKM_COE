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
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:satuans,kode',
            'nama' => 'required|string|max:50|unique:satuans,nama',
        ], [
            'kode.required' => 'Kode satuan harus diisi',
            'kode.max' => 'Kode maksimal 10 karakter',
            'kode.unique' => 'Kode satuan sudah digunakan',
            'nama.required' => 'Nama satuan harus diisi',
            'nama.max' => 'Nama maksimal 50 karakter',
            'nama.unique' => 'Nama satuan sudah digunakan',
        ]);

        try {
            // Simpan data
            Satuan::create([
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
            ]);

            return redirect()->route('master-data.satuan.index')
                ->with('success', 'Data satuan berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }


    public function edit(Satuan $satuan)
    {
        return view('master-data.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:satuans,kode,' . $satuan->id,
            'nama' => 'required|string|max:50|unique:satuans,nama,' . $satuan->id,
        ], [
            'kode.required' => 'Kode satuan harus diisi',
            'kode.max' => 'Kode maksimal 10 karakter',
            'kode.unique' => 'Kode satuan sudah digunakan',
            'nama.required' => 'Nama satuan harus diisi',
            'nama.max' => 'Nama maksimal 50 karakter',
            'nama.unique' => 'Nama satuan sudah digunakan',
        ]);

        try {
            // Update data
            $satuan->update([
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
            ]);

            return redirect()->route('master-data.satuan.index')
                ->with('success', 'Data satuan berhasil diperbarui!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Satuan $satuan)
    {
        $satuan->delete();

        return redirect()->route('master-data.satuan.index')->with('success', 'Satuan berhasil dihapus!');
    }
}
