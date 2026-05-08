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
            
            // FIXED: Use Eloquent to get proper model relationships and accessors
            // This ensures biaya_per_produk_formatted and other accessors work correctly
            $btkls = Btkl::with(['jabatan.pegawais'])
                ->where('user_id', $userId)
                ->orderBy('kode_proses')
                ->get();

            // Calculate statistics
            $totalProses = $btkls->count();
            $totalBiayaPerProduk = $btkls->sum('biaya_per_produk');
            $rataRataTarif = $totalProses > 0 ? $btkls->sum('tarif_btkl') / $totalProses : 0;
            $rataRataKapasitas = $totalProses > 0 ? $btkls->sum('kapasitas_per_jam') / $totalProses : 0;
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

            // Calculate biaya BTKL per produk
            // FIXED: Use $validated instead of $btkl to ensure we get the correct value
            $biayaBtklPerProduk = 0;
            if ($validated['kapasitas_per_jam'] > 0) {
                $biayaBtklPerProduk = $tarifBtkl / $validated['kapasitas_per_jam'];
            }

            $prosesProduksi = ProsesProduksi::create([
                'user_id'         => $userId,
                'kode_proses'     => $validated['kode_proses'],
                'nama_proses'     => $validated['nama_btkl'],
                'deskripsi'       => $validated['deskripsi_proses'] ?? null,
                'tarif_btkl'      => $tarifBtkl,
                'satuan_btkl'     => $validated['satuan'],
                'kapasitas_per_jam'=> $validated['kapasitas_per_jam'],
                'jabatan_id'      => $validated['jabatan_id'],
                'btkl_id'         => $btkl->id, // CRITICAL: Link to Btkl record
                'biaya_btkl_per_produk' => $biayaBtklPerProduk,
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
