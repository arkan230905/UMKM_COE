<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PegawaiController extends Controller
{
    // Menampilkan daftar pegawai dengan paginasi dan pencarian
    public function index()
    {
        $search = request('search');
        $jenis = request('jenis');
        
        $query = Pegawai::query();
        
        // Filter berdasarkan jenis pegawai (opsional)
        if ($jenis && in_array(strtolower((string)$jenis), ['btkl','btktl'])) {
            $query->where('jenis_pegawai', strtoupper($jenis));
        }
        
        // Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
                
                if (Schema::hasColumn('pegawais', 'no_telp')) {
                    $q->orWhere('no_telp', 'like', '%' . $search . '%');
                }
                
                if (Schema::hasColumn('pegawais', 'jabatan')) {
                    $q->orWhere('jabatan', 'like', '%' . $search . '%');
                }
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
            'jabatan_id' => 'required|exists:jabatans,id',
            'jenis_kelamin' => 'required|in:L,P',
            'bank' => 'required|string|max:100',
            'nomor_rekening' => 'required|string|max:50',
            'nama_rekening' => 'required|string|max:100',
        ]);

        $jab = \App\Models\Jabatan::find($validated['jabatan_id']);
        if (!$jab) {
            return redirect()->back()->withErrors(['jabatan_id' => 'Jabatan tidak ditemukan']);
        }
        
        $jenisPegawai = strtolower($jab->kategori ?? 'btkl');
        
        // Prepare data for creation with only mandatory columns
        $pegawaiData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ];

        // Tambahkan kolom opsional jika ada di tabel
        if (Schema::hasColumn('pegawais', 'no_telp')) {
            $pegawaiData['no_telp'] = $validated['no_telepon'];
        }

        if (Schema::hasColumn('pegawais', 'jabatan')) {
            $pegawaiData['jabatan'] = $jab->nama;
        }

        if (Schema::hasColumn('pegawais', 'jenis_pegawai')) {
            $pegawaiData['jenis_pegawai'] = $jenisPegawai;
        }

        if (Schema::hasColumn('pegawais', 'kategori_tenaga_kerja')) {
            $pegawaiData['kategori_tenaga_kerja'] = strtoupper($jab->kategori ?? 'BTKL');
        }

        if (Schema::hasColumn('pegawais', 'gaji')) {
            $pegawaiData['gaji'] = (float)($jab->gaji ?? 0);
        }

        if (Schema::hasColumn('pegawais', 'gaji_pokok')) {
            $pegawaiData['gaji_pokok'] = (float)($jab->gaji ?? 0);
        }

        if (Schema::hasColumn('pegawais', 'tunjangan')) {
            $pegawaiData['tunjangan'] = (float)($jab->tunjangan ?? 0);
        }

        if (Schema::hasColumn('pegawais', 'asuransi')) {
            $pegawaiData['asuransi'] = (float)($jab->asuransi ?? 0);
        }

        if (Schema::hasColumn('pegawais', 'tarif_per_jam')) {
            $pegawaiData['tarif_per_jam'] = (float)($jab->tarif ?? 0);
        }

        if (Schema::hasColumn('pegawais', 'bank')) {
            $pegawaiData['bank'] = $validated['bank'] ?? null;
        }

        if (Schema::hasColumn('pegawais', 'nomor_rekening')) {
            $pegawaiData['nomor_rekening'] = $validated['nomor_rekening'] ?? null;
        }

        if (Schema::hasColumn('pegawais', 'nama_rekening')) {
            $pegawaiData['nama_rekening'] = $validated['nama_rekening'] ?? null;
        }
        
        // Log the data being saved for debugging
        \Log::info('Creating new Pegawai:', $pegawaiData);
        
        // Create the pegawai record
        Pegawai::create($pegawaiData);

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
            'jabatan_id' => 'required|exists:jabatans,id',
            'jenis_kelamin' => 'required|in:L,P',
            'bank' => 'nullable|string|max:100',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_rekening' => 'nullable|string|max:100',
        ]);

        $jab = \App\Models\Jabatan::find($validated['jabatan_id']);
        $jenisPegawai = strtolower($jab->kategori ?? 'btkl');
        
        // Prepare data for update
        $updateData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_telp' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'jabatan' => $jab->nama,
            'jenis_pegawai' => $jenisPegawai,
            'gaji' => $jab->gaji,
            'gaji_pokok' => $jab->gaji_pokok ?? $jab->gaji ?? 0,
            'tarif_per_jam' => $jab->tarif ?? 0,
            'tunjangan' => $jab->tunjangan ?? 0,
            'asuransi' => $jab->asuransi ?? 0,
            'bank' => $validated['bank'] ?? null,
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'nama_rekening' => $validated['nama_rekening'] ?? null,
        ];
        
        // Log the update data for debugging
        \Log::info('Updating Pegawai:', $updateData);
        
        // Update the pegawai record
        $pegawai->update($updateData);

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    // Hapus pegawai
    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
