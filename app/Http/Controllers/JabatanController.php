<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Services\BomSyncService;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $search = request('search');
        $kategori = request('kategori');
        
        $q = Jabatan::query();
        
        if ($search) {
            $q->where('nama', 'like', "%{$search}%");
        }
        
        if ($kategori) {
            $q->where('kategori', $kategori);
        }
        
        $jabatans = $q->orderBy('nama')->paginate(10)->withQueryString();
        
        return view('master-data.jabatan.index', compact('jabatans', 'search'));
    }

    public function create()
    {
        return view('master-data.jabatan.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'tunjangan_transport' => $this->normalizeMoney($request->input('tunjangan_transport')),
            'tunjangan_konsumsi' => $this->normalizeMoney($request->input('tunjangan_konsumsi')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);

        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:jabatans,nama',
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_konsumsi' => 'nullable|numeric|min:0|max:999999999',
            'asuransi' => 'nullable|numeric|min:0|max:999999999',
            'gaji' => 'nullable|numeric|min:0|max:999999999',
            'tarif' => 'nullable|numeric|min:0|max:999999999',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['tunjangan_transport'] = $data['tunjangan_transport'] ?? 0;
        $data['tunjangan_konsumsi'] = $data['tunjangan_konsumsi'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;

        $data['gaji_pokok'] = $data['gaji'] ?? 0;
        $data['tarif_per_jam'] = $data['tarif'];
        unset($data['gaji']);
        
        $prefix = strtoupper(substr($data['kategori'], 0, 2));
        $lastJabatan = Jabatan::where('kode_jabatan', 'like', $prefix . '%')
            ->orderBy('kode_jabatan', 'desc')
            ->first();
            
        $nextNumber = $lastJabatan ? ((int) substr($lastJabatan->kode_jabatan, 2) + 1) : 1;
        
        $data['kode_jabatan'] = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        Jabatan::create($data);

        return redirect()->route('master-data.kualifikasi-tenaga-kerja.index', ['notify' => 'Kualifikasi berhasil ditambahkan.']);
    }

    public function edit(Jabatan $kualifikasi_tenaga_kerja)
    {
        return view('master-data.jabatan.edit', ['jabatan' => $kualifikasi_tenaga_kerja]);
    }

    public function update(Request $request, Jabatan $kualifikasi_tenaga_kerja)
    {
        $jabatan = $kualifikasi_tenaga_kerja;

        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'tunjangan_transport' => $this->normalizeMoney($request->input('tunjangan_transport')),
            'tunjangan_konsumsi' => $this->normalizeMoney($request->input('tunjangan_konsumsi')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);

        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:jabatans,nama,' . $jabatan->id,
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_konsumsi' => 'nullable|numeric|min:0|max:999999999',
            'asuransi' => 'nullable|numeric|min:0|max:999999999',
            'gaji' => 'nullable|numeric|min:0|max:999999999',
            'tarif' => 'nullable|numeric|min:0|max:999999999',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['tunjangan_transport'] = $data['tunjangan_transport'] ?? 0;
        $data['tunjangan_konsumsi'] = $data['tunjangan_konsumsi'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;

        $data['gaji_pokok'] = $data['gaji'] ?? 0;
        $data['tarif_per_jam'] = $data['tarif'];
        unset($data['gaji']);

        if ($jabatan->kategori !== $data['kategori']) {
            $prefix = strtoupper(substr($data['kategori'], 0, 2));
            $lastJabatan = Jabatan::where('kode_jabatan', 'like', $prefix . '%')
                ->orderBy('kode_jabatan', 'desc')
                ->first();
                
            $nextNumber = $lastJabatan ? ((int) substr($lastJabatan->kode_jabatan, 2) + 1) : 1;
            
            $data['kode_jabatan'] = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        $jabatan->update($data);

        if ($jabatan->kategori === 'btkl') {
            BomSyncService::syncBomFromJabatanChange($jabatan->id);
        }

        return redirect()->route('master-data.kualifikasi-tenaga-kerja.index', ['notify' => 'Kualifikasi berhasil diperbarui.']);
    }

    public function destroy(Jabatan $kualifikasi_tenaga_kerja)
    {
        try {
            $pegawaiCount = \App\Models\Pegawai::where('jabatan', $kualifikasi_tenaga_kerja->nama)->count();

            if ($pegawaiCount > 0) {
                $pegawaiNames = \App\Models\Pegawai::where('jabatan', $kualifikasi_tenaga_kerja->nama)
                    ->pluck('nama')->implode(', ');

                return redirect()->route('master-data.kualifikasi-tenaga-kerja.index', [
                    'notify_error' => "Jabatan '{$kualifikasi_tenaga_kerja->nama}' tidak dapat dihapus karena digunakan oleh {$pegawaiCount} pegawai: {$pegawaiNames}"
                ]);
            }

            $kualifikasi_tenaga_kerja->delete();

            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index', ['notify' => 'Kualifikasi berhasil dihapus.']);

        } catch (\Exception $e) {
            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index', ['notify_error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getByKategori(Request $request)
    {
        $kategoriId = $request->get('kategori_id');

        if (!$kategoriId) {
            return response()->json(['success' => false], 400);
        }

        if (is_numeric($kategoriId)) {
            $kategoriPegawai = \App\Models\KategoriPegawai::find($kategoriId);
            if (!$kategoriPegawai) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $kategoriName = strtolower($kategoriPegawai->nama);

            $jabatans = Jabatan::where(function($q) use ($kategoriName, $kategoriId) {
                $q->where('kategori', $kategoriName)
                      ->orWhere('kategori_id', $kategoriId);
            });
        } else {
            $jabatans = Jabatan::where('kategori', strtolower($kategoriId));
        }

        $jabatans = $jabatans->select(
            'id','nama','kategori','kategori_id',
            'gaji_pokok','tarif_per_jam','tunjangan','asuransi'
        )->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'data' => $jabatans
        ]);
    }

    public function getDetail(Request $request)
    {
        $jabatan = Jabatan::find($request->jabatan_id);

        if (!$jabatan) {
            return response()->json(['success' => false], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jabatan
        ]);
    }

    private function normalizeMoney($value): ?string
    {
        if (!$value) return $value;

        $v = str_replace(['.', ''], ['', '.'], $value);
        return $v;
    }
}
