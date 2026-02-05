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
        try {
            $prosesProduksis = ProsesProduksi::with(['prosesBops.komponenBop', 'jabatan.pegawais'])
                ->orderBy('kode_proses')
                ->paginate(10);
        } catch (\Exception $e) {
            // Jika tabel proses_bops tidak ada, load tanpa relasi
            $prosesProduksis = ProsesProduksi::with(['jabatan.pegawais'])
                ->orderBy('kode_proses')
                ->paginate(10);
        }
        
        return view('master-data.proses-produksi.index', compact('prosesProduksis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $komponenBops = KomponenBop::active()->orderBy('nama_komponen')->get();
        return view('master-data.proses-produksi.create', compact('komponenBops'));
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
            // Get jabatan data for verification
            $jabatan = \App\Models\Jabatan::with('pegawais')->findOrFail($validated['jabatan_id']);
            
            // Verify calculation (for security)
            $jumlahPegawai = $jabatan->pegawais->count();
            $tarifPerJam = $jabatan->tarif;
            $expectedTarifBTKL = $tarifPerJam * $jumlahPegawai;
            
            // Use calculated value instead of user input for security
            $validated['tarif_btkl'] = $expectedTarifBTKL;
            
            $createData = [
                'nama_proses' => $validated['nama_proses'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tarif_btkl' => $validated['tarif_btkl'],
                'satuan_btkl' => $validated['satuan_btkl'],
                'kapasitas_per_jam' => $validated['kapasitas_per_jam'],
                'jabatan_id' => $validated['jabatan_id'], // Store jabatan reference
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
        $prosesProduksi->load('bomProses.bom.produk');
        return view('master-data.proses-produksi.show', compact('prosesProduksi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProsesProduksi $prosesProduksi)
    {
        $prosesProduksi->load('prosesBops.komponenBop');
        $komponenBops = KomponenBop::active()->orderBy('nama_komponen')->get();
        return view('master-data.proses-produksi.edit', compact('prosesProduksi', 'komponenBops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProsesProduksi $prosesProduksi)
    {
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
            // Get jabatan data for verification
            $jabatan = \App\Models\Jabatan::with('pegawais')->findOrFail($validated['jabatan_id']);
            
            // Verify calculation (for security)
            $jumlahPegawai = $jabatan->pegawais->count();
            $tarifPerJam = $jabatan->tarif;
            $expectedTarifBTKL = $tarifPerJam * $jumlahPegawai;
            
            // Use calculated value instead of user input for security
            $validated['tarif_btkl'] = $expectedTarifBTKL;
            
            $updateData = [
                'nama_proses' => $validated['nama_proses'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tarif_btkl' => $validated['tarif_btkl'],
                'satuan_btkl' => $validated['satuan_btkl'],
                'kapasitas_per_jam' => $validated['kapasitas_per_jam'],
                'jabatan_id' => $validated['jabatan_id'], // Store jabatan reference
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
        try {
            $prosesProduksi->delete();
            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
