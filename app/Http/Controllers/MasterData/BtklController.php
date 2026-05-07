<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Btkl;
use App\Models\ProsesProduksi;
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

        // 2. Hitung pegawai per jabatan — hanya pegawai milik user yang sama
        //    MULTI-TENANT: pegawais.jabatan_id = jabatans.id DAN pegawais.user_id = user ini
        //    IMPORTANT: Harus join dengan jabatans untuk memastikan jabatan_id milik user yang sama
        $pegawaiCount = DB::table('pegawais as p')
            ->join('jabatans as j', function($join) use ($userId) {
                $join->on('p.jabatan_id', '=', 'j.id')
                     ->where('j.user_id', '=', $userId); // CRITICAL: Ensure jabatan belongs to same user
            })
            ->where('p.user_id', $userId) // CRITICAL: Multi-tenant filter
            ->where('j.kategori', 'btkl') // Only BTKL jabatan
            ->whereNotNull('p.jabatan_id')
            ->select('p.jabatan_id', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('p.jabatan_id')
            ->pluck('jumlah', 'p.jabatan_id');

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

        return [$jabatanBtkl, $employeeData];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $userId = auth()->id();
            $btkls  = DB::table('btkls')
                ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                ->where('btkls.user_id', $userId)
                ->select('btkls.*', 'jabatans.nama as jabatan_nama', 'jabatans.tarif as jabatan_tarif')
                ->orderBy('btkls.kode_proses')
                ->get();

            return view('master-data.btkl.index', compact('btkls'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get ALL Jabatan with category 'btkl' and their employees
        // This ensures we get all BTKL positions regardless of whether they have employees
        $jabatanBtkl = Jabatan::where('kategori', 'btkl')
            ->with('pegawais')
            ->orderBy('nama')
            ->get();

        // Debug logging
        \Log::info('BTKL Create - Jabatan BTKL loaded:', [
            'total_jabatan' => $jabatanBtkl->count(),
            'jabatan_details' => $jabatanBtkl->map(function($j) {
                return [
                    'id' => $j->id,
                    'nama' => $j->nama,
                    'pegawai_count' => $j->pegawais->count(),
                    'pegawai_ids' => $j->pegawais->pluck('id')->toArray(),
                    'tarif' => $j->tarif
                ];
            })->toArray()
        ]);

        // Generate next process code
        $lastBtkl = Btkl::orderBy('kode_proses', 'desc')->first();
        if ($lastBtkl) {
            // Extract number from last code (e.g., PROC-001 -> 001)
            $lastNumber = (int) substr($lastBtkl->kode_proses, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $nextKode = 'PROC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $satuanOptions = ['Jam', 'Unit', 'Batch'];

        // Map employee data dengan pegawai_count yang akurat
        $employeeData = $jabatanBtkl->map(function($jabatan) {
            $pegawaiCount = $jabatan->pegawais->count();
            
            \Log::info('BTKL Create - Jabatan Data:', [
                'id' => $jabatan->id,
                'nama' => $jabatan->nama,
                'pegawai_count' => $pegawaiCount,
                'pegawai_details' => $jabatan->pegawais->map(function($p) {
                    return [
                        'id' => $p->id,
                        'nama' => $p->nama,
                        'jabatan_id' => $p->jabatan_id
                    ];
                })->toArray(),
                'tarif' => $jabatan->tarif ?? 0,
                'kategori' => $jabatan->kategori
            ]);
            
            return [
                'id' => $jabatan->id,
                'nama' => $jabatan->nama,
                'pegawai_count' => $pegawaiCount,
                'tarif' => $jabatan->tarif ?? 0
            ];
        });

        \Log::info('BTKL Create - Final employeeData:', $employeeData->toArray());
        
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
            'satuan'           => 'required|in:Jam,Unit,Batch',
            'kapasitas_per_jam'=> 'required|integer|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $userId  = auth()->id();
            $jabatan = DB::table('jabatans')->where('id', $validated['jabatan_id'])->first();

            // Hitung pegawai user ini yang terdaftar di jabatan tersebut
            $jumlahPegawai = DB::table('pegawais')
                ->where('user_id', $userId)
                ->where('jabatan_id', $jabatan->id)
                ->count();

            $tarifPerJam = (float) ($jabatan->tarif ?? 0);
            $tarifBtkl   = $tarifPerJam * $jumlahPegawai;

            $validated['tarif_per_jam'] = $tarifBtkl;
            $validated['user_id']       = $userId;

            $btkl = Btkl::create($validated);

            ProsesProduksi::create([
                'kode_proses'     => $btkl->kode_proses,
                'nama_proses'     => $btkl->nama_btkl,
                'deskripsi'       => $btkl->deskripsi_proses,
                'tarif_btkl'      => $tarifBtkl,
                'satuan_btkl'     => $btkl->satuan,
                'kapasitas_per_jam'=> $btkl->kapasitas_per_jam,
                'btkl_id'         => $btkl->id,
            ]);

            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil ditambahkan. Tarif BTKL: Rp ' . number_format($tarifBtkl) .
                    ' (Tarif Jabatan: Rp ' . number_format($tarifPerJam) . ' × ' . $jumlahPegawai . ' pegawai)');

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
            'satuan'           => 'required|in:Jam,Unit,Batch',
            'kapasitas_per_jam'=> 'required|integer|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $userId  = auth()->id();
            $jabatan = DB::table('jabatans')->where('id', $validated['jabatan_id'])->first();

            $jumlahPegawai = DB::table('pegawais')
                ->where('user_id', $userId)
                ->where('jabatan_id', $jabatan->id)
                ->count();

            $tarifPerJam = (float) ($jabatan->tarif ?? 0);
            $tarifBtkl   = $tarifPerJam * $jumlahPegawai;

            $validated['tarif_per_jam'] = $tarifBtkl;

            $btkl = Btkl::findOrFail($id);
            $btkl->update($validated);

            $prosesProduksi = ProsesProduksi::where('btkl_id', $btkl->id)->first();
            if ($prosesProduksi) {
                $prosesProduksi->update([
                    'kode_proses'      => $btkl->kode_proses,
                    'nama_proses'      => $btkl->nama_btkl,
                    'deskripsi'        => $btkl->deskripsi_proses,
                    'tarif_btkl'       => $tarifBtkl,
                    'satuan_btkl'      => $btkl->satuan,
                    'kapasitas_per_jam'=> $btkl->kapasitas_per_jam,
                ]);
            }

            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil diperbarui. Tarif BTKL: Rp ' . number_format($tarifBtkl) .
                    ' (Tarif Jabatan: Rp ' . number_format($tarifPerJam) . ' × ' . $jumlahPegawai . ' pegawai)');

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
