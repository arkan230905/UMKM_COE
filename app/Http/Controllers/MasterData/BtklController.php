<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Btkl;
use App\Models\Jabatan;
use App\Models\ProsesProduksi;
use App\Services\BomSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BtklController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $btkls = Btkl::with('jabatan.pegawais')
                ->orderBy('kode_proses')
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
        try {
            // Generate kode proses otomatis
            $lastBtkl = Btkl::orderBy('kode_proses', 'desc')->first();
            $nextNumber = $lastBtkl ? (int)substr($lastBtkl->kode_proses, 2) + 1 : 1;
            $nextKode = 'BT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // PERBAIKAN MULTI-TENANT: Ambil jabatan BTKL dengan filter user_id yang benar
            $jabatanBtkl = Jabatan::where('kategori_tenaga_kerja', 'BTKL')
                ->where('user_id', auth()->id()) // Filter berdasarkan user yang login
                ->with(['pegawais' => function($query) {
                    $query->where('user_id', auth()->id()); // Filter pegawai juga berdasarkan user_id
                }])
                ->orderBy('nama')
                ->get();

            // Debug logging untuk memastikan filter bekerja
            \Log::info('BTKL Create - Multi-tenant filter:', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Unknown',
                'total_jabatan' => $jabatanBtkl->count(),
                'jabatan_details' => $jabatanBtkl->map(function($j) {
                    return [
                        'id' => $j->id,
                        'nama' => $j->nama,
                        'user_id' => $j->user_id,
                        'pegawai_count' => $j->pegawais->count(),
                        'pegawai_user_ids' => $j->pegawais->pluck('user_id')->unique()->toArray(),
                        'tarif' => $j->tarif
                    ];
                })->toArray()
            ]);

            // Hitung jumlah pegawai per jabatan dengan filter user_id yang benar
            $employeeData = $jabatanBtkl->map(function($jabatan) {
                $pegawaiCount = $jabatan->pegawais->count(); // Sudah difilter di query
                
                return [
                    'id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'tarif' => $jabatan->tarif ?? 0,
                    'pegawai_count' => $pegawaiCount
                ];
            });

            $satuanOptions = ['pcs', 'kg', 'liter', 'meter', 'unit', 'jam', 'batch'];

            \Log::info('BTKL Create - Final employeeData with user filter:', [
                'user_id' => auth()->id(),
                'employee_data' => $employeeData->toArray()
            ]);

            return view('master-data.btkl.create', compact(
                'nextKode', 
                'jabatanBtkl', 
                'employeeData', 
                'satuanOptions'
            ));
            
        } catch (\Exception $e) {
            \Log::error('BTKL Create Error:', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_proses' => 'required|string|max:20|unique:btkls,kode_proses',
            'nama_btkl' => 'required|string|max:255',
            'jabatan_id' => 'required|exists:jabatans,id',
            'satuan' => 'required|in:Jam,Unit,Batch',
            'kapasitas_per_jam' => 'required|integer|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get jabatan data for automatic calculation
            $jabatan = Jabatan::find($validated['jabatan_id']);
            
            // Calculate automatic BTKL rate
            $jumlahPegawai = $jabatan->pegawais()->count();
            $tarifPerJam = $jabatan->tarif ?? 0;
            $tarifBtkl = $tarifPerJam * $jumlahPegawai;

            // If kode_proses is empty, generate it
            if (empty($validated['kode_proses'])) {
                $lastBtkl = Btkl::orderBy('kode_proses', 'desc')->first();
                if ($lastBtkl) {
                    $lastNumber = (int) substr($lastBtkl->kode_proses, -3);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }
                $validated['kode_proses'] = 'PROC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }

            // Add calculated tarif_per_jam to validated data
            $validated['tarif_per_jam'] = $tarifBtkl;

            $btkl = Btkl::create($validated);

            // Create corresponding ProsesProduksi for BOP display
            ProsesProduksi::create([
                'kode_proses' => $btkl->kode_proses,
                'nama_proses' => $btkl->nama_btkl,
                'deskripsi' => $btkl->deskripsi_proses,
                'tarif_btkl' => $tarifBtkl,
                'satuan_btkl' => $btkl->satuan,
                'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                'btkl_id' => $btkl->id,
            ]);

            // Sync BOM when BTKL data changes
            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil ditambahkan. Tarif BTKL: Rp ' . number_format($tarifBtkl) . ' (Tarif Jabatan: Rp ' . number_format($tarifPerJam) . ' × ' . $jumlahPegawai . ' pegawai)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data BTKL: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $btkl = Btkl::with('jabatan')->findOrFail($id);
            
            // PERBAIKAN MULTI-TENANT: Get jabatan BTKL with proper user_id filter
            $jabatanBtkl = Jabatan::where('kategori_tenaga_kerja', 'BTKL')
                ->where('user_id', auth()->id()) // Filter berdasarkan user yang login
                ->with(['pegawais' => function($query) {
                    $query->where('user_id', auth()->id()); // Filter pegawai juga berdasarkan user_id
                }])
                ->orderBy('nama')
                ->get();
                
            $satuanOptions = ['Jam', 'Unit', 'Batch'];
            
            // Map employee data dengan pegawai_count yang akurat dan filter user_id
            $employeeData = $jabatanBtkl->map(function($jabatan) {
                $pegawaiCount = $jabatan->pegawais->count(); // Sudah difilter di query
                
                return [
                    'id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'pegawai_count' => $pegawaiCount,
                    'tarif' => $jabatan->tarif ?? 0
                ];
            });

            \Log::info('BTKL Edit - Multi-tenant filter:', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Unknown',
                'total_jabatan' => $jabatanBtkl->count(),
                'employee_data' => $employeeData->toArray()
            ]);
                
            return view('master-data.btkl.edit', compact('btkl', 'jabatanBtkl', 'satuanOptions', 'employeeData'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.btkl.index')
                ->with('error', 'Data BTKL tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_proses' => 'required|string|max:20|unique:btkls,kode_proses,' . $id,
            'nama_btkl' => 'required|string|max:255',
            'jabatan_id' => 'required|exists:jabatans,id',
            'satuan' => 'required|in:Jam,Unit,Batch',
            'kapasitas_per_jam' => 'required|integer|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            // Get jabatan data for automatic calculation
            $jabatan = Jabatan::find($validated['jabatan_id']);
            
            // Calculate automatic BTKL rate
            $jumlahPegawai = $jabatan->pegawais()->count();
            $tarifPerJam = $jabatan->tarif ?? 0;
            $tarifBtkl = $tarifPerJam * $jumlahPegawai;

            // Add calculated tarif_per_jam to validated data
            $validated['tarif_per_jam'] = $tarifBtkl;

            $btkl = Btkl::findOrFail($id);
            $btkl->update($validated);

            // Update corresponding ProsesProduksi if exists
            $prosesProduksi = ProsesProduksi::where('btkl_id', $btkl->id)->first();
            if ($prosesProduksi) {
                $prosesProduksi->update([
                    'kode_proses' => $btkl->kode_proses,
                    'nama_proses' => $btkl->nama_btkl,
                    'deskripsi' => $btkl->deskripsi_proses,
                    'tarif_btkl' => $tarifBtkl,
                    'satuan_btkl' => $btkl->satuan,
                    'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                ]);
            }

            // Sync BOM when BTKL data changes
            BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil diperbarui. Tarif BTKL: Rp ' . number_format($tarifBtkl) . ' (Tarif Jabatan: Rp ' . number_format($tarifPerJam) . ' × ' . $jumlahPegawai . ' pegawai)');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data BTKL: ' . $e->getMessage());
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

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Gagal menghapus data BTKL: ' . $e->getMessage());
        }
    }
}
