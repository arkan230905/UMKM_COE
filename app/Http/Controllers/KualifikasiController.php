<?php

namespace App\Http\Controllers;

use App\Models\Kualifikasi;
use App\Services\BomSyncService;
use Illuminate\Http\Request;

class KualifikasiController extends Controller
{
    public function index()
    {
        $search = request('search');
        $kategori = request('kategori');
        
        // CRITICAL: Filter by user_id for multi-tenant
        $q = Kualifikasi::where('user_id', auth()->id());
        
        if ($search) {
            $q->where('nama_kualifikasi', 'like', "%{$search}%");
        }
        
        if ($kategori) {
            $q->where('kategori', $kategori);
        }
        
        $jabatans = $q->orderBy('nama_kualifikasi')->paginate(10)->withQueryString();
        
        return view('master-data.kualifikasi.index', compact('jabatans', 'search'));
    }

    public function create()
    {
        return view('master-data.kualifikasi.create');
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

            // CRITICAL: Add user_id to unique validation for multi-tenant isolation
            'nama_kualifikasi' => 'required|string|max:255|unique:kualifikasis,nama_kualifikasi,NULL,id,user_id,' . auth()->id(),
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_konsumsi' => 'nullable|numeric|min:0|max:999999999',
            'asuransi' => 'nullable|numeric|min:0|max:999999999',
            'gaji' => 'nullable|numeric|min:0|max:999999999',
            'tarif' => 'nullable|numeric|min:0|max:999999999',
            'target_produksi' => 'nullable|integer|min:0',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['tunjangan_transport'] = $data['tunjangan_transport'] ?? 0;
        $data['tunjangan_konsumsi'] = $data['tunjangan_konsumsi'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;
        $data['target_produksi'] = $data['target_produksi'] ?? 0;

        // Reset target_produksi and tarif if kategori is btktl
        if ($data['kategori'] === 'btktl') {
            $data['target_produksi'] = 0;
            $data['tarif'] = 0;
        }

        $data['gaji_pokok'] = $data['gaji'] ?? 0;
        $data['tarif_produk'] = $data['tarif'];
        unset($data['gaji']);
        
        $prefix = strtoupper(substr($data['kategori'], 0, 2));
        
        // 🔒 MULTI-TENANT: Generate kode_kualifikasi per user
        $lastKualifikasi = Kualifikasi::where('user_id', auth()->id())
            ->where('kode_kualifikasi', 'like', $prefix . '%')
            ->orderBy('kode_kualifikasi', 'desc')
            ->first();
        
        // Extract number and find next available
        if ($lastKualifikasi) {
            $lastNumber = (int) substr($lastKualifikasi->kode_kualifikasi, 2);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Ensure unique code by checking if it already exists
        $maxAttempts = 100;
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            $candidateCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $exists = Kualifikasi::where('user_id', auth()->id())
                ->where('kode_kualifikasi', $candidateCode)
                ->exists();
            
            if (!$exists) {
                $data['kode_kualifikasi'] = $candidateCode;
                break;
            }
            
            $nextNumber++;
            $attempts++;
        }
        
        // CRITICAL: Always set user_id for multi-tenant isolation
        $data['user_id'] = auth()->id();

        Kualifikasi::create($data);

        return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
            ->with('success', 'Kualifikasi berhasil ditambahkan.');
    }

    public function edit(Kualifikasi $kualifikasi_tenaga_kerja)
    {
        // 🔒 SECURITY: Check if user owns this kualifikasi (multi-tenant)
        if ($kualifikasi_tenaga_kerja->user_id !== auth()->id()) {
            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                ->with('error', 'Kualifikasi tenaga kerja tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
        return view('master-data.kualifikasi.edit', ['jabatan' => $kualifikasi_tenaga_kerja]);
    }

    public function update(Request $request, Kualifikasi $kualifikasi_tenaga_kerja)
    {
        // 🔒 SECURITY: Check if user owns this kualifikasi (multi-tenant)
        if ($kualifikasi_tenaga_kerja->user_id !== auth()->id()) {
            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                ->with('error', 'Kualifikasi tenaga kerja tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
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

            // CRITICAL: Add user_id to unique validation for multi-tenant isolation
            'nama_kualifikasi' => 'required|string|max:255|unique:kualifikasis,nama_kualifikasi,' . $jabatan->id . ',id,user_id,' . auth()->id(),
            'kategori' => 'required|in:btkl,btktl',
            'tunjangan' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999',
            'tunjangan_konsumsi' => 'nullable|numeric|min:0|max:999999999',
            'asuransi' => 'nullable|numeric|min:0|max:999999999',
            'gaji' => 'nullable|numeric|min:0|max:999999999',
            'tarif' => 'nullable|numeric|min:0|max:999999999',
            'target_produksi' => 'nullable|integer|min:0',
        ]);

        $data['tunjangan'] = $data['tunjangan'] ?? 0;
        $data['tunjangan_transport'] = $data['tunjangan_transport'] ?? 0;
        $data['tunjangan_konsumsi'] = $data['tunjangan_konsumsi'] ?? 0;
        $data['asuransi'] = $data['asuransi'] ?? 0;
        $data['tarif'] = $data['tarif'] ?? 0;
        $data['target_produksi'] = $data['target_produksi'] ?? 0;

        // Reset target_produksi and tarif if kategori is btktl
        if ($data['kategori'] === 'btktl') {
            $data['target_produksi'] = 0;
            $data['tarif'] = 0;
        }

        $data['gaji_pokok'] = $data['gaji'] ?? 0;
        $data['tarif_produk'] = $data['tarif'];
        unset($data['gaji']);


        if ($jabatan->kategori !== $data['kategori']) {
            $prefix = strtoupper(substr($data['kategori'], 0, 2));
            
            // 🔒 MULTI-TENANT: Generate kode_kualifikasi per user
            $lastKualifikasi = Kualifikasi::where('user_id', auth()->id())
                ->where('kode_kualifikasi', 'like', $prefix . '%')
                ->orderBy('kode_kualifikasi', 'desc')
                ->first();
            
            // Extract number and find next available
            if ($lastKualifikasi) {
                $lastNumber = (int) substr($lastKualifikasi->kode_kualifikasi, 2);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            // Ensure unique code by checking if it already exists
            $maxAttempts = 100;
            $attempts = 0;
            while ($attempts < $maxAttempts) {
                $candidateCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                $exists = Kualifikasi::where('user_id', auth()->id())
                    ->where('kode_kualifikasi', $candidateCode)
                    ->exists();
                
                if (!$exists) {
                    $data['kode_kualifikasi'] = $candidateCode;
                    break;
                }
                
                $nextNumber++;
                $attempts++;
            }
        }

        $jabatan->update($data);

        if ($jabatan->kategori === 'btkl') {
            BomSyncService::syncBomFromKualifikasiChange($jabatan->id);
        }

        return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
            ->with('success', 'Kualifikasi berhasil diperbarui.');
    }

    public function destroy(Kualifikasi $kualifikasi_tenaga_kerja)
    {
        // 🔒 SECURITY: Check if user owns this kualifikasi (multi-tenant)
        if ($kualifikasi_tenaga_kerja->user_id !== auth()->id()) {
            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                ->with('error', 'Kualifikasi tenaga kerja tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
        try {
            // 🔒 SECURITY: Check pegawai count with safety check for user_id column
            $pegawaiQuery = \App\Models\Pegawai::where('kualifikasi', $kualifikasi_tenaga_kerja->nama_kualifikasi);
            if (\Illuminate\Support\Facades\Schema::hasColumn('pegawais', 'user_id')) {
                $pegawaiQuery->where('user_id', auth()->id());
            }
            $pegawaiCount = $pegawaiQuery->count();

            if ($pegawaiCount > 0) {
                $pegawaiNameQuery = \App\Models\Pegawai::where('kualifikasi', $kualifikasi_tenaga_kerja->nama_kualifikasi);
                if (\Illuminate\Support\Facades\Schema::hasColumn('pegawais', 'user_id')) {
                    $pegawaiNameQuery->where('user_id', auth()->id());
                }
                $pegawaiNames = $pegawaiNameQuery->pluck('nama')->implode(', ');

                return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                    ->with('error', "Kualifikasi '{$kualifikasi_tenaga_kerja->nama_kualifikasi}' tidak dapat dihapus karena digunakan oleh {$pegawaiCount} pegawai: {$pegawaiNames}");
            }

            $kualifikasi_tenaga_kerja->delete();

            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                ->with('success', 'Kualifikasi berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('master-data.kualifikasi-tenaga-kerja.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getByKategori(Request $request)
    {
        // Support both 'kategori' and 'kategori_id' parameters
        $kategori = $request->get('kategori') ?? $request->get('kategori_id');

        if (!$kategori) {
            return response()->json(['success' => false, 'message' => 'Parameter kategori required'], 400);
        }

        // CRITICAL: Filter by user_id for multi-tenant
        $userId = auth()->id();
        $query = Kualifikasi::where('user_id', $userId);

        // If kategori is numeric, it's a kategori_id
        if (is_numeric($kategori)) {
            // 🔒 MULTI-TENANT: Only get kategori from logged-in user
            $kategoriPegawai = \App\Models\KategoriPegawai::where('user_id', auth()->id())->find($kategori);
            if (!$kategoriPegawai) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $kategoriName = strtolower($kategoriPegawai->nama);

            $query->where(function($q) use ($kategoriName, $kategori) {
                $q->where('kategori', $kategoriName)
                  ->orWhere('kategori_id', $kategori);
            });
        } else {
            // If kategori is string (btkl/btktl), filter by kategori
            $query->where('kategori', strtolower($kategori));
        }

        $jabatans = $query->select(
            'id','nama_kualifikasi','kategori','kategori_id',
            'gaji_pokok','tarif_produk as tarif',
            'tunjangan','tunjangan_transport','tunjangan_konsumsi','asuransi'
        )->orderBy('nama_kualifikasi')->get();

        return response()->json([
            'success' => true,
            'data' => $jabatans
        ]);
    }

    public function getDetail(Request $request)
    {
        $jabatan = Kualifikasi::find($request->jabatan_id);

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
