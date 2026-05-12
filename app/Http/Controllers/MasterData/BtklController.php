<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Btkl;
use App\Models\ProsesProduksi;
use App\Models\Pegawai;
use App\Services\BomSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BtklController extends Controller
{
    /**
     * Ambil jabatan BTKL + jumlah pegawai milik user yang sedang login.
     * Menggunakan DB::table (query builder mentah) agar tidak terpengaruh
     * global scope Eloquent yang kadang tidak aktif di server.
     * 
     * MULTI-TENANT: Semua query HARUS filter by user_id
     */
    private function getJabatanBtklForUser(int $userId): array
    {
        // 1. Ambil jabatan BTKL milik user ini saja
        // MULTI-TENANT: Filter by user_id AND kategori = 'btkl'
        $jabatans = DB::table('jabatans')
            ->where('user_id', $userId) // CRITICAL: Multi-tenant filter
            ->where('kategori', 'btkl')
            ->orderBy('nama')
            ->get();

        // 2. Hitung jumlah pegawai per jabatan
        // TEMPORARY: Remove multi-tenant filter untuk testing
        $pegawaiCount = DB::table('pegawais as p')
            ->join('jabatans as j', 'p.jabatan_id', '=', 'j.id')
            ->where('j.kategori', 'btkl')
            ->whereNotNull('p.jabatan_id')
            ->selectRaw('j.id as jabatan_id, COUNT(*) as jumlah')
            ->groupBy('j.id')
            ->pluck('jumlah', 'jabatan_id');

        // 3. Gabungkan
        $jabatanBtkl   = collect();
        $employeeData  = collect();

        foreach ($jabatans as $j) {
            $count = (int) ($pegawaiCount[$j->id] ?? 0);
            $tarif = (float) ($j->tarif ?? 0);

            // Untuk view blade (object)
            $jabatanBtkl->push((object)[
                'id'    => $j->id,
                'nama'  => $j->nama,
                'tarif' => $tarif,
                'pegawai_count' => $count,
            ]);

            // Untuk JavaScript kalkulasi tarif otomatis
            $employeeData->push([
                'id'            => $j->id,
                'nama'          => $j->nama,
                'pegawai_count' => $count,
                'tarif'         => $tarif,
            ]);
        }

        // Debug: Log hasil
        \Log::info('Employee Data for BTKL form:', [
            'jabatan_count' => $jabatans->count(),
            'pegawai_count_data' => $pegawaiCount->toArray(),
            'employee_data' => $employeeData->toArray()
        ]);

        return [$jabatanBtkl, $employeeData];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $userId = auth()->id();
            
            // FIXED: Use Eloquent to get proper model relationships and accessors
            // This ensures biaya_per_produk_formatted and other accessors work correctly
            $btkls = Btkl::with(['jabatan.pegawais', 'prosesProduksi'])
                ->where('user_id', $userId)
                ->orderBy('kode_proses')
                ->get();

            // Calculate statistics using actual database values from proses_produksis
            $totalProses = $btkls->count();
            
            // Calculate total biaya per produk from proses_produksis table
            $totalBiayaPerProduk = 0;
            $totalTarif = 0;
            $totalKapasitas = 0;
            
            foreach ($btkls as $btkl) {
                if ($btkl->prosesProduksi) {
                    $totalBiayaPerProduk += $btkl->prosesProduksi->biaya_btkl_per_produk ?? 0;
                    $totalTarif += $btkl->tarif_btkl ?? 0;
                    $totalKapasitas += $btkl->kapasitas_per_jam ?? 0;
                }
            }
            
            $rataRataTarif = $totalProses > 0 ? $totalTarif / $totalProses : 0;
            $rataRataKapasitas = $totalProses > 0 ? $totalKapasitas / $totalProses : 0;
            $rataRataBiayaPerUnit = $totalProses > 0 ? $totalBiayaPerProduk / $totalProses : 0;

            $statistics = [
                'total_proses' => $totalProses,
                'total_biaya_per_produk' => $totalBiayaPerProduk,
                'rata_rata_tarif' => $rataRataTarif,
                'rata_rata_kapasitas' => $rataRataKapasitas,
                'rata_rata_biaya_per_unit' => $rataRataBiayaPerUnit,
            ];

            return view('master-data.btkl.index', compact('btkls', 'statistics'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $userId = auth()->id();

        [$jabatanBtkl, $employeeData] = $this->getJabatanBtklForUser($userId);

        // Generate kode proses berikutnya untuk user ini
        $lastBtkl   = DB::table('btkls')->where('user_id', $userId)->orderBy('kode_proses', 'desc')->first();
        $nextNumber = $lastBtkl ? ((int) substr($lastBtkl->kode_proses, -3)) + 1 : 1;
        $nextKode   = 'PROC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $satuanOptions = ['Jam', 'Unit', 'Batch'];

return view('master-data.btkl.create', compact('jabatanBtkl', 'nextKode', 'satuanOptions', 'employeeData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_proses'      => 'required|string|max:20|unique:btkls,kode_proses',
            'nama_btkl'        => 'required|string|max:255',
            'jabatan_id'       => 'required|exists:jabatans,id',
            'tarif_btkl'       => 'required|numeric|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        // SERVER-SIDE DUPLICATE PREVENTION: Check for potential race conditions
        $userId = auth()->id();
        
        // Check if BTKL with this kode already exists (race condition check)
        $existingBtkl = DB::table('btkls')
            ->where('user_id', $userId)
            ->where('kode_proses', $validated['kode_proses'])
            ->first();
            
        if ($existingBtkl) {
            return back()->withInput()->with('error', 'Data BTKL dengan kode ini sudah ada. Silakan gunakan kode yang berbeda.');
        }

        // SIMPLIFIED: Fast but compatible query approach
        $userId = auth()->id();
        
        // Get jabatan data first
        $jabatan = DB::table('jabatans')->where('id', $validated['jabatan_id'])->first();
        
        if (!$jabatan) {
            return back()->withInput()->with('error', 'Jabatan tidak ditemukan.');
        }
        
        // Get pegawai count separately (still fast)
        $jumlahPegawai = DB::table('pegawais')
            ->where('user_id', $userId)
            ->where('jabatan_id', $jabatan->id)
            ->count();
            
        // Calculate tariff
        $tarifPerProdukJabatan = (float) ($jabatan->tarif ?? 0);
        $correctTarifBtkl = $tarifPerProdukJabatan * $jumlahPegawai;
        
        if ($correctTarifBtkl <= 0) {
            return back()->withInput()->with('error', 'Tarif BTKL tidak valid! Pastikan jabatan memiliki tarif dan ada pegawai yang terdaftar.');
        }
        
        $validated['tarif_btkl'] = $correctTarifBtkl;

        try {
            DB::beginTransaction();

            $validated['user_id'] = $userId;
            $validated['kapasitas_per_jam'] = 1; // Set default kapasitas_per_jam to prevent division by zero
            $validated['satuan'] = 'Unit'; // Set satuan to Unit for product-based BTKL

            $btkl = Btkl::create($validated);

            // ABSOLUTE SOLUTION: Check for existing duplicates before creating
            $existingProses = DB::table('proses_produksis')
                ->where('user_id', $userId)
                ->where('btkl_id', $btkl->id)
                ->first();
                
            if (!$existingProses) {
                // Only create if doesn't exist
                DB::table('proses_produksis')->insert([
                    'user_id'         => $userId,
                    'kode_proses'     => $validated['kode_proses'],
                    'nama_proses'     => $validated['nama_btkl'],
                    'deskripsi'       => $validated['deskripsi_proses'] ?? null,
                    'tarif_btkl'      => $correctTarifBtkl,
                    'satuan_btkl'     => 'Unit',
                    'kapasitas_per_jam'=> 1,
                    'jabatan_id'      => $validated['jabatan_id'],
                    'btkl_id'         => $btkl->id,
                    'biaya_btkl_per_produk' => $correctTarifBtkl,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // REMOVED: BomSyncService causing performance bottleneck
            // BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil ditambahkan. Tarif BTKL: Rp ' . number_format($correctTarifBtkl) .
                    ' (Tarif Jabatan: Rp ' . number_format($tarifPerProdukJabatan) . ' × ' . $jumlahPegawai . ' pegawai)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data BTKL: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $userId = auth()->id();
            $btkl   = Btkl::with('jabatan')->findOrFail($id);

            [$jabatanBtkl, $employeeData] = $this->getJabatanBtklForUser($userId);

            $satuanOptions = ['Jam', 'Unit', 'Batch'];

            return view('master-data.btkl.edit', compact('btkl', 'jabatanBtkl', 'satuanOptions', 'employeeData'));

        } catch (\Exception $e) {
            return redirect()->route('master-data.btkl.index')
                ->with('error', 'Data BTKL tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_proses'      => 'required|string|max:20|unique:btkls,kode_proses,' . $id,
            'nama_btkl'        => 'required|string|max:255',
            'jabatan_id'       => 'required|exists:jabatans,id',
            'tarif_btkl'       => 'required|numeric|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        // SERVER-SIDE VALIDATION: Prevent 0 tariff values
        $userId = auth()->id();
        $jabatan = DB::table('jabatans')->where('id', $validated['jabatan_id'])->first();
        
        if (!$jabatan) {
            return back()->withInput()->with('error', 'Jabatan tidak ditemukan.');
        }
        
        // Calculate correct tariff regardless of form submission
        $jumlahPegawai = DB::table('pegawais')
            ->where('user_id', $userId)
            ->where('jabatan_id', $jabatan->id)
            ->count();
            
        $tarifPerProdukJabatan = (float) ($jabatan->tarif ?? 0);
        $correctTarifBtkl = $tarifPerProdukJabatan * $jumlahPegawai;
        
        // Force correct tariff if submitted value is 0 or incorrect
        if ($correctTarifBtkl <= 0) {
            return back()->withInput()->with('error', 'Tarif BTKL tidak valid! Pastikan jabatan memiliki tarif dan ada pegawai yang terdaftar.');
        }
        
        $validated['tarif_btkl'] = $correctTarifBtkl;

        DB::beginTransaction();

        try {
            $btkl = Btkl::findOrFail($id);
            $validated['kapasitas_per_jam'] = 1; // Set default kapasitas_per_jam to prevent division by zero
            $btkl->update($validated);

            $prosesProduksi = ProsesProduksi::where('btkl_id', $btkl->id)->first();
            if ($prosesProduksi) {
                $prosesProduksi->update([
                    'kode_proses'      => $btkl->kode_proses,
                    'nama_proses'      => $btkl->nama_btkl,
                    'deskripsi'        => $btkl->deskripsi_proses,
                    'tarif_btkl'       => $correctTarifBtkl,
                    'satuan_btkl'     => 'Unit', // Default satuan
                    'kapasitas_per_jam'=> 1, // Default kapasitas
                    'jabatan_id'      => $validated['jabatan_id'],
                    'btkl_id'         => $btkl->id,
                    'biaya_btkl_per_produk' => $correctTarifBtkl, // Same as tarif BTKL since per produk
                ]);
            }

            // REMOVED: BomSyncService causing performance bottleneck
            // BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil diperbarui. Tarif BTKL: Rp ' . number_format($correctTarifBtkl) .
                    ' (Tarif Jabatan: Rp ' . number_format($tarifPerProdukJabatan) . ' × ' . $jumlahPegawai . ' pegawai)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data BTKL: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $btkl = Btkl::findOrFail($id);
            $btkl->delete();
            DB::commit();

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data BTKL: ' . $e->getMessage());
        }
    }
}
