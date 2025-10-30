<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    // Menampilkan daftar pegawai dengan paginasi dan pencarian
    public function index()
    {
        $search = request('search');
        $jenis = request('jenis');
        
        $query = Pegawai::query();
        
        // Filter berdasarkan jenis pegawai
        if (in_array(strtolower((string)$jenis), ['btkl','btktl'])) {
            $query->where('kategori_tenaga_kerja', strtoupper($jenis));
        }
        
        // Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('no_telp', 'like', '%' . $search . '%')
                  ->orWhere('jabatan', 'like', '%' . $search . '%');
            });
        }
        
        // Paginasi dengan 10 item per halaman
        $pegawais = $query->orderBy('nama')->paginate(10);
        
        return view('master-data.pegawai.index', compact('pegawais', 'jenis', 'search'));
    }

    // Tampilkan form create
    public function create()
    {
        return view('master-data.pegawai.create');
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string',
            'kategori_tenaga_kerja' => 'required|in:BTKL,BTKTL',
            'tanggal_masuk' => 'required|date',
            'status_aktif' => 'required|boolean'
        ];

        // Tambahkan validasi berdasarkan kategori tenaga kerja
        if ($request->kategori_tenaga_kerja === 'BTKL') {
            $rules['tarif_per_jam'] = 'required|numeric|min:0';
        } else {
            $rules['gaji_pokok'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Generate nomor induk pegawai
        $lastPegawai = Pegawai::orderBy('nomor_induk_pegawai', 'desc')->first();
        $lastNumber = $lastPegawai ? intval(substr($lastPegawai->nomor_induk_pegawai, 3)) : 0;
        $nomorInduk = 'EMP' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        // Siapkan data untuk disimpan
        $data = [
            'nomor_induk_pegawai' => $nomorInduk,
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_telp' => $validated['no_telp'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'jabatan' => $validated['jabatan'],
            'kategori_tenaga_kerja' => $validated['kategori_tenaga_kerja'],
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'status_aktif' => $validated['status_aktif'],
            'tunjangan' => $request->tunjangan ?? 0,
            'created_by' => auth()->id()
        ];

        // Set gaji berdasarkan kategori
        if ($validated['kategori_tenaga_kerja'] === 'BTKL') {
            $data['tarif_per_jam'] = $validated['tarif_per_jam'];
            $data['gaji'] = 0; // Akan dihitung berdasarkan presensi
        } else {
            $data['gaji_pokok'] = $validated['gaji_pokok'];
            $data['gaji'] = $validated['gaji_pokok'] + ($request->tunjangan ?? 0);
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

        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email,'.$pegawai->nomor_induk_pegawai.',nomor_induk_pegawai',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'required|string',
            'kategori_tenaga_kerja' => 'required|in:BTKL,BTKTL',
            'tanggal_masuk' => 'required|date',
            'status_aktif' => 'required|boolean',
            'tunjangan' => 'nullable|numeric|min:0'
        ];

        // Tambahkan validasi berdasarkan kategori tenaga kerja
        if ($request->kategori_tenaga_kerja === 'BTKL') {
            $rules['tarif_per_jam'] = 'required|numeric|min:0';
        } else {
            $rules['gaji_pokok'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Siapkan data untuk diupdate
        $data = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_telp' => $validated['no_telp'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'jabatan' => $validated['jabatan'],
            'kategori_tenaga_kerja' => $validated['kategori_tenaga_kerja'],
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'status_aktif' => $validated['status_aktif'],
            'tunjangan' => $validated['tunjangan'] ?? 0,
            'updated_by' => auth()->id()
        ];

        // Update data gaji berdasarkan kategori
        if ($validated['kategori_tenaga_kerja'] === 'BTKL') {
            $data['tarif_per_jam'] = $validated['tarif_per_jam'];
            $data['gaji_pokok'] = 0; // Reset gaji pokok untuk BTKL
            // Gaji akan dihitung berdasarkan presensi
            $data['gaji'] = $data['tunjangan'] ?? 0;
        } else {
            $data['gaji_pokok'] = $validated['gaji_pokok'];
            $data['tarif_per_jam'] = 0; // Reset tarif per jam untuk BTKTL
            $data['gaji'] = $validated['gaji_pokok'] + ($validated['tunjangan'] ?? 0);
        }

        // Jika status tidak aktif, isi tanggal keluar
        if (!$validated['status_aktif'] && $pegawai->status_aktif) {
            $data['tanggal_keluar'] = now();
        } elseif ($validated['status_aktif'] && !$pegawai->status_aktif) {
            $data['tanggal_keluar'] = null;
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
