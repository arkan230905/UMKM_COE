<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    // Menampilkan daftar pegawai
    public function index()
    {
        $jenis = request('jenis');
        if (in_array(strtolower((string)$jenis), ['btkl','btktl'])) {
            $pegawais = Pegawai::where('jenis_pegawai', strtolower($jenis))->get();
        } else {
            $pegawais = Pegawai::all();
            $jenis = null;
        }

        return view('master-data.pegawai.index', compact('pegawais', 'jenis'));
    }

    // Tampilkan form create
    public function create()
    {
        return view('master-data.pegawai.create');
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string',
            'gaji' => 'required|numeric',
            'kategori_tenaga_kerja' => 'nullable|in:BTKL,BTKTL',
        ]);

        $data = $request->all();
        if (!empty($data['kategori_tenaga_kerja'])) {
            $data['jenis_pegawai'] = strtolower($data['kategori_tenaga_kerja']);
        }
        if (!isset($data['gaji_pokok'])) {
            $data['gaji_pokok'] = $data['gaji'] ?? 0;
        }
        if (!isset($data['tunjangan'])) {
            $data['tunjangan'] = $data['tunjangan'] ?? 0;
        }

        Pegawai::create($data);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    // Form edit pegawai
    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return view('master-data.pegawai.edit', compact('pegawai'));
    }

    // Update data pegawai
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email,'.$pegawai->id,
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string',
            'gaji' => 'required|numeric',
            'kategori_tenaga_kerja' => 'nullable|in:BTKL,BTKTL',
        ]);

        $data = $request->all();
        if (!empty($data['kategori_tenaga_kerja'])) {
            $data['jenis_pegawai'] = strtolower($data['kategori_tenaga_kerja']);
        }
        if (!isset($data['gaji_pokok'])) {
            $data['gaji_pokok'] = $data['gaji'] ?? $pegawai->gaji_pokok;
        }
        if (!isset($data['tunjangan'])) {
            $data['tunjangan'] = $data['tunjangan'] ?? ($pegawai->tunjangan ?? 0);
        }

        $pegawai->update($data);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    // Hapus pegawai
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
