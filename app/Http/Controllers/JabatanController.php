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
        ], [
            'nama.unique' => 'Nama kualifikasi sudah ada. Silakan gunakan nama yang berbeda.',
            'tunjangan.max' => 'Tunjangan maksimal adalah Rp 999.999.999',
            'tunjangan_transport.max' => 'Tunjangan transport maksimal adalah Rp 999.999.999',
            'tunjangan_konsumsi.max' => 'Tunjangan konsumsi maksimal adalah Rp 999.999.999',
            'asuransi.max' => 'Asuransi maksimal adalah Rp 999.999.999',
            'gaji.max' => 'Gaji maksimal adalah Rp 999.999.999',
            'tarif.max' => 'Tarif maksimal adalah Rp 999.999.999',
        ]);

        // Normalisasi nilai default
        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['tunjangan_transport'] = $data['tunjangan_transport'] ?? 0;
        $data['tunjangan_konsumsi'] = $data['tunjangan_konsumsi'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;

        // Map form fields to correct DB columns
        $data['gaji_pokok'] = $data['gaji'] ?? 0;
        $data['tarif_per_jam'] = $data['tarif'];
        unset($data['gaji']);
        
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
        $jabatan = $kualifikasi_tenaga_kerja;
        
        // Normalisasi angka berformat (1.234,56 atau 1,234.56) -> 1234.56
        $request->merge([
            'tunjangan' => $this->normalizeMoney($request->input('tunjangan')),
            'tunjangan_transport' => $this->normalizeMoney($request->input('tunjangan_transport')),
            'tunjangan_konsumsi' => $this->normalizeMoney($request->input('tunjangan_konsumsi')),
            'asuransi' => $this->normalizeMoney($request->input('asuransi')),
            'gaji' => $this->normalizeMoney($request->input('gaji')),
            'tarif' => $this->normalizeMoney($request->input('tarif')),
        ]);
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:jabatans,nama,' . $kualifikasi_tenaga_kerja->id,
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_konsumsi' => 'nullable|numeric|min:0|max:999999999',
            'asuransi' => 'nullable|numeric|min:0|max:999999999',
            'gaji' => 'nullable|numeric|min:0|max:999999999',
            'tarif' => 'nullable|numeric|min:0|max:999999999',
        ], [
            'nama.unique' => 'Nama kualifikasi sudah ada. Silakan gunakan nama yang berbeda.',
            'tunjangan.max' => 'Tunjangan maksimal adalah Rp 999.999.999',
            'tunjangan_transport.max' => 'Tunjangan transport maksimal adalah Rp 999.999.999',
            'tunjangan_konsumsi.max' => 'Tunjangan konsumsi maksimal adalah Rp 999.999.999',
            'asuransi.max' => 'Asuransi maksimal adalah Rp 999.999.999',
            'gaji.max' => 'Gaji maksimal adalah Rp 999.999.999',
            'tarif.max' => 'Tarif maksimal adalah Rp 999.999.999',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['tunjangan_transport'] = $data['tunjangan_transport'] ?? 0;
        $data['tunjangan_konsumsi'] = $data['tunjangan_konsumsi'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;

        // Map form fields to correct DB columns
        $data['gaji_pokok'] = $data['gaji'] ?? 0;
        $data['tarif_per_jam'] = $data['tarif'];
        unset($data['gaji']);

        // Update kode_jabatan jika kategori berubah
        if ($jabatan->kategori !== $data['kategori']) {
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

        $jabatan->update($data);
        
        // Sync BOM when jabatan data changes (affects BTKL calculations)
        if ($jabatan->kategori === 'btkl') {
            BomSyncService::syncBomFromJabatanChange($jabatan->id);
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

    /**
     * Get jabatan by kategori
     */
    public function getByKategori(Request $request)
    {
        $kategoriId = $request->get('kategori_id');
        
        if (!$kategoriId) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori ID is required'
            ], 400);
        }

<<<<<<< HEAD
        // Handle both string kategori ('btkl') and integer kategori_id
        if (is_numeric($kategoriId)) {
            $kategoriPegawai = \App\Models\KategoriPegawai::find($kategoriId);
            $kategoriName = $kategoriPegawai ? strtolower($kategoriPegawai->nama) : null;
            $jabatans = Jabatan::where(function($q) use ($kategoriName, $kategoriId) {
                if ($kategoriName) $q->where('kategori', $kategoriName);
                $q->orWhere('kategori_id', $kategoriId);
            });
        } else {
            $jabatans = Jabatan::where('kategori', strtolower($kategoriId));
        }

        $jabatans = $jabatans->select('id', 'nama', 'kategori', 'gaji_pokok', 'tarif', 'tunjangan', 'asuransi')
=======
        // Lookup KategoriPegawai name, then match jabatans by kategori string
        $kategoriPegawai = \App\Models\KategoriPegawai::find($kategoriId);
        if (!$kategoriPegawai) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $kategoriName = strtolower($kategoriPegawai->nama); // 'btkl' or 'btktl'

        $jabatans = Jabatan::where('kategori', $kategoriName)
            ->orWhere('kategori_id', $kategoriId)
            ->select('id', 'nama', 'kategori', 'kategori_id', 'gaji_pokok', 'tarif_per_jam', 'tunjangan', 'asuransi')
>>>>>>> 09c795ee293c426b3d80634193e2fe2f90e330de
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jabatans
        ]);
    }

    /**
     * Get jabatan detail by ID
     */
    public function getDetail(Request $request)
    {
        $jabatanId = $request->get('jabatan_id');
        
        if (!$jabatanId) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan ID is required'
            ], 400);
        }

        $jabatan = Jabatan::select('id', 'nama', 'kategori', 'kategori_id', 'gaji_pokok', 'tarif_per_jam', 'tunjangan', 'asuransi')
            ->find($jabatanId);

        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jabatan
        ]);
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
