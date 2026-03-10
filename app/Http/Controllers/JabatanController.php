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
        // Normalisasi angka berformat (1.234,56 atau 1,234.56) -> 1234.56
        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:jabatans,nama',
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0',
            'asuransi' => 'nullable|numeric|min:0',
            'gaji' => 'nullable|numeric|min:0',
            'tarif' => 'nullable|numeric|min:0',
        ], [
            'nama.unique' => 'Nama kualifikasi sudah ada. Silakan gunakan nama yang berbeda.',
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
        return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')->with('success','Jabatan berhasil ditambahkan');
    }

    public function edit(Jabatan $kualifikasi_tenaga_kerja)
    {
        return view('master-data.jabatan.edit', ['jabatan' => $kualifikasi_tenaga_kerja]);
    }

    public function update(Request $request, Jabatan $kualifikasi_tenaga_kerja)
    {
        // Normalisasi angka berformat (1.234,56 atau 1,234.56) -> 1234.56
        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:jabatans,nama,' . $kualifikasi_tenaga_kerja->id,
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0',
            'asuransi' => 'nullable|numeric|min:0',
            'gaji' => 'nullable|numeric|min:0',
            'tarif' => 'nullable|numeric|min:0',
        ], [
            'nama.unique' => 'Nama kualifikasi sudah ada. Silakan gunakan nama yang berbeda.',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['gaji'] = $data['gaji'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;

        $kualifikasi_tenaga_kerja->update($data);
        
        // Sync BOM when jabatan data changes (affects BTKL calculations)
        if ($kualifikasi_tenaga_kerja->kategori === 'btkl') {
            BomSyncService::syncBomFromJabatanChange($kualifikasi_tenaga_kerja->id);
        }
        
        return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')->with('success','Jabatan berhasil diperbarui');
    }

    public function destroy(Jabatan $kualifikasi_tenaga_kerja)
    {
        // Log untuk debugging
        \Log::info('Attempting to delete jabatan', [
            'jabatan_id' => $kualifikasi_tenaga_kerja->id,
            'jabatan_nama' => $kualifikasi_tenaga_kerja->nama,
            'jabatan_kategori' => $kualifikasi_tenaga_kerja->kategori,
            'request_ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            // Cek apakah jabatan digunakan di tabel pegawai (berdasarkan ID untuk akurasi)
            $pegawaiCount = \App\Models\Pegawai::where('jabatan', $kualifikasi_tenaga_kerja->nama)->count();
            if ($pegawaiCount > 0) {
                \Log::warning('Jabatan cannot be deleted - used in pegawai table', [
                    'jabatan_id' => $kualifikasi_tenaga_kerja->id,
                    'pegawai_count' => $pegawaiCount
                ]);
                
                // Get pegawai names for better error message
                $pegawaiNames = \App\Models\Pegawai::where('jabatan', $kualifikasi_tenaga_kerja->nama)->pluck('nama')->implode(', ');
                
                return back()->with('error', 
                    "❌ Jabatan '{$kualifikasi_tenaga_kerja->nama}' tidak dapat dihapus karena digunakan oleh {$pegawaiCount} pegawai:<br><br>" .
                    "<strong>Pegawai:</strong> {$pegawaiNames}<br><br>" .
                    "💡 <strong>Solusi:</strong> Ubah jabatan pegawai tersebut terlebih dahulu, atau hapus pegawai jika tidak diperlukan."
                );
            }

            $jabatanName = $kualifikasi_tenaga_kerja->nama;
            $jabatanId = $kualifikasi_tenaga_kerja->id;
            
            // Hapus jabatan
            $deleted = $kualifikasi_tenaga_kerja->delete();
            
            \Log::info('Jabatan deletion result', [
                'deleted_jabatan_id' => $jabatanId,
                'deleted_jabatan_nama' => $jabatanName,
                'deletion_success' => $deleted
            ]);
            
            if ($deleted) {
                return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                    ->with('success', "✅ Jabatan '{$jabatanName}' berhasil dihapus");
            } else {
                return back()->with('error', "❌ Gagal menghapus jabatan '{$jabatanName}'. Silakan coba lagi.");
            }
                
        } catch (\Exception $e) {
            \Log::error('Error deleting jabatan', [
                'jabatan_id' => $kualifikasi_tenaga_kerja->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', '❌ Terjadi kesalahan saat menghapus jabatan: ' . $e->getMessage());
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
