<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pegawai;

class PegawaiController extends Controller
{
    /**
     * Menampilkan daftar pegawai, bisa difilter berdasarkan kategori.
     */
    public function index(Request $request)
    {
        $kategori = $request->query('kategori'); // Ambil parameter filter kategori

        $query = Pegawai::query();

        if ($kategori && in_array($kategori, ['BTKL', 'BTKTL'])) {
            $query->where('kategori_tenaga_kerja', $kategori);
        }

        $pegawais = $query->get();

        return view('master-data.pegawai.index', compact('pegawais', 'kategori'));
    }

    /**
     * Tampilkan form untuk menambahkan pegawai baru.
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
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
            'no_telp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string',
            'kategori_tenaga_kerja' => 'required|in:BTKL,BTKTL',
            'gaji' => 'required|numeric|min:0',
        ]);

        Pegawai::create($validated);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    /**
     * Tampilkan form untuk mengedit pegawai.
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

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email,' . $id,
            'no_telp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string',
            'kategori_tenaga_kerja' => 'required|in:BTKL,BTKTL',
            'gaji' => 'required|numeric|min:0',
        ]);

        $pegawai->update($validated);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Hapus pegawai.
     */
    public function destroy($id)
    {
        Pegawai::findOrFail($id)->delete();
        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
