<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\KlasifikasiTunjangan;
use App\Models\JabatanTunjanganTambahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KlasifikasiTenagaKerjaController extends Controller
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
        // Normalisasi angka berformat (1.234,56 atau 1,234.56) -> 1234.56
        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0',
            'asuransi' => 'nullable|numeric|min:0',
            'gaji' => 'nullable|numeric|min:0',
            'tarif' => 'nullable|numeric|min:0',
        ]);

        // Normalisasi nilai default
        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['gaji'] = $data['gaji'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;
        
        // Generate kode_jabatan hanya jika kolom ada
        if (Schema::hasColumn('jabatans', 'kode_jabatan')) {
            $prefix = strtoupper(substr($data['kategori'], 0, 2));
            $lastJabatan = Jabatan::where('kode_jabatan', 'like', $prefix . '%')
                ->orderBy('kode_jabatan', 'desc')
                ->first();
                
            $nextNumber = 1;
            if ($lastJabatan) {
                $lastNumber = (int) substr($lastJabatan->kode_jabatan, 2);
                $nextNumber = $lastNumber + 1;
            }
            
            $data['kode_jabatan'] = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        $jabatan = Jabatan::create($data);

        // Simpan tunjangan tambahan
        $this->saveTunjangans($jabatan, $request);
        $this->saveTunjanganTambahans($jabatan, $request);

        return redirect()->route('master-data.jabatan.index')->with('success','Jabatan berhasil ditambahkan');
    }

    public function edit(Jabatan $jabatan)
    {
        return view('master-data.jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        // Normalisasi angka berformat (1.234,56 atau 1,234.56) -> 1234.56
        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0',
            'asuransi' => 'nullable|numeric|min:0',
            'gaji' => 'nullable|numeric|min:0',
            'tarif' => 'nullable|numeric|min:0',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['gaji'] = $data['gaji'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;

        $jabatan->update($data);

        // Simpan tunjangan tambahan
        $this->saveTunjangans($jabatan, $request);
        $this->saveTunjanganTambahans($jabatan, $request);

        return redirect()->route('master-data.jabatan.index')->with('success','Jabatan berhasil diperbarui');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();
        return redirect()->route('master-data.jabatan.index')->with('success','Jabatan berhasil dihapus');
    }

    /**
     * Simpan tunjangan tambahan dari form
     */
    private function saveTunjangans($jabatan, Request $request)
    {
        // Skip jika tabel tidak ada
        if (!Schema::hasTable('klasifikasi_tunjangans')) {
            return;
        }

        $names = $request->input('tunjangan_names', []);
        $values = $request->input('tunjangan_values', []);

        // Hapus tunjangan lama
        $jabatan->tunjangans()->delete();

        // Simpan tunjangan baru
        foreach ($names as $index => $name) {
            if (!empty($name) && !empty($values[$index])) {
                $value = $this->normalizeMoney($values[$index]);
                $jabatan->tunjangans()->create([
                    'nama_tunjangan' => $name,
                    'nilai_tunjangan' => (float)$value,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Simpan tunjangan tambahan dari form
     */
    private function saveTunjanganTambahans($jabatan, Request $request)
    {
        // Skip jika tabel tidak ada
        if (!Schema::hasTable('jabatan_tunjangan_tambahans')) {
            return;
        }

        $names = $request->input('tunjangan_tambahan_names', []);
        $values = $request->input('tunjangan_tambahan_values', []);

        // Hapus tunjangan tambahan lama
        $jabatan->tunjanganTambahans()->delete();

        // Simpan tunjangan tambahan baru
        foreach ($names as $index => $name) {
            if (!empty($name) && !empty($values[$index])) {
                $value = $this->normalizeMoney($values[$index]);
                $jabatan->tunjanganTambahans()->create([
                    'nama' => $name,
                    'nominal' => (float)$value,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function normalizeMoney($value): ?string
    {
        if ($value === null || $value === '') return $value;
        // Hilangkan spasi
        $v = trim((string)$value);
        // Jika format id-ID (mengandung . sebagai ribuan dan , sebagai desimal)
        if (preg_match('/\d\.\d{3}(?:\.\d{3})*(,\d+)?$/', $v)) {
            $v = str_replace('.', '', $v); // hapus pemisah ribuan
            $v = str_replace(',', '.', $v); // ganti desimal ke .
            return $v;
        }
        // Jika format en-US (mengandung , sebagai ribuan dan . sebagai desimal)
        if (preg_match('/\d,\d{3}(?:,\d{3})*(\.\d+)?$/', $v)) {
            $v = str_replace(',', '', $v);
            return $v;
        }
        // Hanya angka + titik/koma sederhana
        $v = str_replace([',', ' '], ['', ''], $v);
        return $v;
    }
}
