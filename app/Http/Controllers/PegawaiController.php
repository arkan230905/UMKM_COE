<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Tampilkan daftar semua pegawai.
     */
    public function index()
    {
        $pegawais = Pegawai::orderBy('id', 'asc')->get();

        return view('master-data.pegawai.index', compact('pegawais'));
    }

    /**
     * Tampilkan form tambah pegawai.
     */
    public function create()
    {
        return view('master-data.pegawai.create');
    }

    /**
     * Simpan data pegawai baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string|max:100',
            'gaji' => 'required|numeric|min:0',
        ]);

        Pegawai::create($request->all());

        return redirect()->route('master-data.pegawai.index')
                         ->with('success', 'Pegawai berhasil ditambahkan.');
    }

    /**
     * Form edit data pegawai.
     */
    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return view('master-data.pegawai.edit', compact('pegawai'));
    }

    /**
     * Update data pegawai.
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email,' . $pegawai->id,
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string|max:100',
            'gaji' => 'required|numeric|min:0',
        ]);

        $pegawai->update($request->all());

        return redirect()->route('master-data.pegawai.index')
                         ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Hapus data pegawai.
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        return redirect()->route('master-data.pegawai.index')
                         ->with('success', 'Pegawai berhasil dihapus.');
    }
}
