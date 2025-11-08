<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
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
        
        // Generate kode_jabatan
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

        Jabatan::create($data);
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
        return redirect()->route('master-data.jabatan.index')->with('success','Jabatan berhasil diperbarui');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();
        return redirect()->route('master-data.jabatan.index')->with('success','Jabatan berhasil dihapus');
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
