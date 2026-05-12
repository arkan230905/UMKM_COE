<?php

namespace App\Http\Controllers;

use App\Models\ProsesProduksi;
use App\Models\KomponenBop;
use App\Models\ProsesBop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesProduksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // 🔒 MULTI-TENANT: Filter by user_id
        // Load with essential relationships only from logged-in user
        // Global scope on Jabatan and Pegawai models will automatically filter by user_id
        $prosesProduksis = ProsesProduksi::with(['jabatan.pegawais'])
            ->where('user_id', auth()->id())
            ->orderBy('kode_proses')
            ->paginate(10);
        
        // Calculate statistics
        $totalProses = $prosesProduksis->count();
        $totalBiayaPerProduk = $prosesProduksis->sum('biaya_per_produk');
        $rataRataTarif = $totalProses > 0 ? $prosesProduksis->sum('tarif_btkl') / $totalProses : 0;
        $rataRataKapasitas = $totalProses > 0 ? $prosesProduksis->sum('kapasitas_per_jam') / $totalProses : 0;
        $rataRataBiayaPerUnit = $totalProses > 0 ? $totalBiayaPerProduk / $totalProses : 0;

        $statistics = [
            'total_proses' => $totalProses,
            'total_biaya_per_produk' => $totalBiayaPerProduk,
            'rata_rata_tarif' => $rataRataTarif,
            'rata_rata_kapasitas' => $rataRataKapasitas,
            'rata_rata_biaya_per_unit' => $rataRataBiayaPerUnit,
        ];
        
        return view('master-data.proses-produksi.index', compact('prosesProduksis', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Komponen BOP sudah tidak digunakan lagi setelah cleanup migration
        // Sekarang BOP langsung disimpan di bop_proses dengan kolom budget dan aktual
        
        // 🔒 MULTI-TENANT: Only get jabatans from logged-in user
        $jabatans = \App\Models\Jabatan::where('user_id', auth()->id())->orderBy('nama')->get();
        
        return view('master-data.proses-produksi.create', compact('jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('BTKL Store Request', [
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatans,id',
            'deskripsi' => 'nullable|string',
            'tarif_btkl' => 'required|numeric|min:0',
            'satuan_btkl' => 'required|string|max:20',
            'kapasitas_per_jam' => 'required|integer|min:1',
        ]);

        try {

            // 🔒 MULTI-TENANT SECURITY: Verify jabatan belongs to logged-in user
            $jabatan = \App\Models\Jabatan::with(['pegawais' => function($query) {
                // 🔒 SECURITY: Only get pegawais from logged-in user
                $query->where('user_id', auth()->id());
            }])
            ->where('user_id', auth()->id())
            ->findOrFail($validated['jabatan_id']);
// Count pegawai manually using jabatan name instead of jabatan_id
            $jumlahPegawai = \App\Models\Pegawai::where('jabatan', $jabatan->nama)->count();
            $tarifPerJam = $jabatan->tarif;
            $expectedTarifBTKL = $tarifPerJam * $jumlahPegawai;
            
            // Validasi konsistensi
            if ($jumlahPegawai === 0) {
                return back()->withInput()->with('error', 
                    'Jabatan "' . $jabatan->nama . '" belum memiliki pegawai. ' .
                    'Silakan tambahkan pegawai terlebih dahulu.');
            }
            
            if ($tarifPerJam <= 0) {
                return back()->withInput()->with('error', 
                    'Jabatan "' . $jabatan->nama . '" belum memiliki tarif per jam yang valid. ' .
                    'Silakan set tarif per jam di master jabatan.');
            }
            
            // Use calculated value instead of user input for security
            $validated['tarif_btkl'] = $expectedTarifBTKL;
            
            $createData = [
                'nama_proses' => $validated['nama_proses'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tarif_btkl' => $validated['tarif_btkl'],
                'satuan_btkl' => $validated['satuan_btkl'],
                'kapasitas_per_jam' => $validated['kapasitas_per_jam'],
                'jabatan_id' => $validated['jabatan_id'], // Store jabatan reference
                'biaya_btkl_per_produk' => $validated['kapasitas_per_jam'] > 0 
                    ? $validated['tarif_btkl'] / $validated['kapasitas_per_jam'] 
                    : 0, // CRITICAL: Calculate and store biaya per produk
            ];

            \Log::info('BTKL Create Data', $createData);

            $btkl = ProsesProduksi::create($createData);

            \Log::info('BTKL Created Successfully', [
                'id' => $btkl->id, 
                'kode' => $btkl->kode_proses,
                'jabatan' => $jabatan->nama,
                'jumlah_pegawai' => $jumlahPegawai,
                'tarif_per_jam' => $tarifPerJam,
                'tarif_btkl' => $expectedTarifBTKL,
                'biaya_per_produk' => $expectedTarifBTKL / $validated['kapasitas_per_jam']
            ]);

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil ditambahkan! ' . 
                       'Jabatan: ' . $jabatan->nama . ' (' . $jumlahPegawai . ' pegawai) | ' .
                       'Tarif BTKL: Rp ' . number_format($expectedTarifBTKL, 0, ',', '.') . '/jam | ' .
                       'Biaya per Produk: ' . format_rupiah_clean($expectedTarifBTKL / $validated['kapasitas_per_jam']) . '/unit');
        } catch (\Exception $e) {
            \Log::error('Error creating BTKL: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProsesProduksi $prosesProduksi)
    {
        // MULTI-TENANT: Check ownership
        if ($prosesProduksi->user_id != auth()->id()) {
            abort(404);
        }
        
        $prosesProduksi->load('bomProses.bom.produk');
        return view('master-data.proses-produksi.show', compact('prosesProduksi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProsesProduksi $prosesProduksi)
    {
        // 🔒 MULTI-TENANT: Check ownership
        if ($prosesProduksi->user_id != auth()->id()) {
            abort(404);
        }
        
        // Komponen BOP sudah tidak digunakan lagi setelah cleanup migration
        // Load bopProses instead
        $prosesProduksi->load('bopProses');
        
        // 🔒 MULTI-TENANT: Only get jabatans from logged-in user
        $jabatans = \App\Models\Jabatan::where('user_id', auth()->id())->orderBy('nama')->get();
        
        return view('master-data.proses-produksi.edit', compact('prosesProduksi', 'jabatans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProsesProduksi $prosesProduksi)
    {
        // 🔒 MULTI-TENANT: Check ownership
        if ($prosesProduksi->user_id != auth()->id()) {
            abort(404);
        }
        
        \Log::info('BTKL Update Request', [
            'id' => $prosesProduksi->id,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatans,id',
            'deskripsi' => 'nullable|string',
            'tarif_btkl' => 'required|numeric|min:0',
            'satuan_btkl' => 'required|string|max:20',
            'kapasitas_per_jam' => 'required|integer|min:1',
        ]);

        try {

            // 🔒 MULTI-TENANT SECURITY: Verify jabatan belongs to logged-in user
            $jabatan = \App\Models\Jabatan::with(['pegawais' => function($query) {
                // 🔒 SECURITY: Only get pegawais from logged-in user
                $query->where('user_id', auth()->id());
            }])
            ->where('user_id', auth()->id())
            ->findOrFail($validated['jabatan_id']);
// Count pegawai manually using jabatan name instead of jabatan_id
            $jumlahPegawai = \App\Models\Pegawai::where('jabatan', $jabatan->nama)->count();
            $tarifPerJam = $jabatan->tarif;
            $expectedTarifBTKL = $tarifPerJam * $jumlahPegawai;
            
            // Validasi konsistensi
            if ($jumlahPegawai === 0) {
                return back()->withInput()->with('error', 
                    'Jabatan "' . $jabatan->nama . '" belum memiliki pegawai. ' .
                    'Silakan tambahkan pegawai terlebih dahulu.');
            }
            
            if ($tarifPerJam <= 0) {
                return back()->withInput()->with('error', 
                    'Jabatan "' . $jabatan->nama . '" belum memiliki tarif per jam yang valid. ' .
                    'Silakan set tarif per jam di master jabatan.');
            }
            
            // Use calculated value instead of user input for security
            $validated['tarif_btkl'] = $expectedTarifBTKL;
            
            $updateData = [
                'nama_proses' => $validated['nama_proses'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tarif_btkl' => $validated['tarif_btkl'],
                'satuan_btkl' => $validated['satuan_btkl'],
                'kapasitas_per_jam' => $validated['kapasitas_per_jam'],
                'jabatan_id' => $validated['jabatan_id'], // Store jabatan reference
                'biaya_btkl_per_produk' => $validated['kapasitas_per_jam'] > 0 
                    ? $validated['tarif_btkl'] / $validated['kapasitas_per_jam'] 
                    : 0, // CRITICAL: Calculate and store biaya per produk
            ];

            \Log::info('BTKL Update Data', $updateData);

            $prosesProduksi->update($updateData);

            \Log::info('BTKL Updated Successfully', [
                'id' => $prosesProduksi->id,
                'jabatan' => $jabatan->nama,
                'jumlah_pegawai' => $jumlahPegawai,
                'tarif_per_jam' => $tarifPerJam,
                'tarif_btkl' => $expectedTarifBTKL,
                'biaya_per_produk' => $expectedTarifBTKL / $validated['kapasitas_per_jam']
            ]);

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil diperbarui! ' . 
                       'Jabatan: ' . $jabatan->nama . ' (' . $jumlahPegawai . ' pegawai) | ' .
                       'Tarif BTKL: Rp ' . number_format($expectedTarifBTKL, 0, ',', '.') . '/jam | ' .
                       'Biaya per Produk: ' . format_rupiah_clean($expectedTarifBTKL / $validated['kapasitas_per_jam']) . '/unit');
        } catch (\Exception $e) {
            \Log::error('Error updating BTKL: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProsesProduksi $prosesProduksi)
    {
        // MULTI-TENANT: Check ownership
        if ($prosesProduksi->user_id != auth()->id()) {
            abort(404);
        }
        
        try {
            $prosesProduksi->delete();
            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
