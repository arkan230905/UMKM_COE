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
            $query->where('kategori', strtoupper($jenis));
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
        $jabatans = \App\Models\Jabatan::select('id','nama','kategori','tunjangan','asuransi','gaji','tarif')->orderBy('nama')->get();
        return view('master-data.pegawai.create', compact('jabatans'));
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'jabatan_id' => 'required|exists:jabatans,id',
        ]);

        // Generate kode_pegawai otomatis: PGW0001, PGW0002, ...
        $last = Pegawai::orderByDesc('id')->value('kode_pegawai');
        $seq = 0;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $seq = (int)$m[1];
        }
        $kodeBaru = 'PGW' . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);

        $jab = \App\Models\Jabatan::find($validated['jabatan_id']);
        Pegawai::create([
            'kode_pegawai' => $kodeBaru,
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'nama_bank' => $request->nama_bank,
            'no_rekening' => $request->no_rekening,
            'jabatan' => $jab?->nama ?? '',
            'kategori' => strtoupper($jab?->kategori ?? ''),
            'asuransi' => $jab?->asuransi ?? 0,
            'tarif' => $jab?->tarif ?? 0,
            'tunjangan' => $jab?->tunjangan ?? 0,
            'gaji' => $jab?->gaji ?? 0,
        ]);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    // Form edit pegawai
    public function edit(Pegawai $pegawai)
    {
        $jabatans = \App\Models\Jabatan::select('id','nama','kategori','tunjangan','asuransi','gaji','tarif')->orderBy('nama')->get();
        return view('master-data.pegawai.edit', compact('pegawai','jabatans'));
    }

    // Update data pegawai
    public function update(Request $request, Pegawai $pegawai)
    {

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email,'.$pegawai->id,
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'jabatan_id' => 'required|exists:jabatans,id',
        ]);

        $jab = \App\Models\Jabatan::find($validated['jabatan_id']);
        $pegawai->update([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'nama_bank' => $request->nama_bank,
            'no_rekening' => $request->no_rekening,
            'jabatan' => $jab?->nama ?? '',
            'kategori' => strtoupper($jab?->kategori ?? ''),
            'asuransi' => $jab?->asuransi ?? 0,
            'tarif' => $jab?->tarif ?? 0,
            'tunjangan' => $jab?->tunjangan ?? 0,
            'gaji' => $jab?->gaji ?? 0,
        ]);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    // Hapus pegawai
    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
