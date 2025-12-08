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
        $prosesProduksis = ProsesProduksi::with('prosesBops.komponenBop')
            ->orderBy('kode_proses')
            ->paginate(10);
            
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
        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tarif_btkl' => 'required|numeric|min:0',
            'satuan_btkl' => 'required|string|max:20',
            'komponen_bop_id' => 'nullable|array',
            'komponen_bop_id.*' => 'exists:komponen_bops,id',
            'kuantitas_default' => 'nullable|array',
            'kuantitas_default.*' => 'numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $proses = ProsesProduksi::create([
                'nama_proses' => $validated['nama_proses'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tarif_btkl' => $validated['tarif_btkl'],
                'satuan_btkl' => $validated['satuan_btkl'],
            ]);

            // Simpan default BOP
            if (!empty($validated['komponen_bop_id'])) {
                foreach ($validated['komponen_bop_id'] as $key => $komponenId) {
                    if ($komponenId && isset($validated['kuantitas_default'][$key])) {
                        ProsesBop::create([
                            'proses_produksi_id' => $proses->id,
                            'komponen_bop_id' => $komponenId,
                            'kuantitas_default' => $validated['kuantitas_default'][$key],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('master-data.proses-produksi.index')
                ->with('success', 'Proses produksi berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProsesProduksi $prosesProduksi)
    {
        $prosesProduksi->load('prosesBops.komponenBop');
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
        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tarif_btkl' => 'required|numeric|min:0',
            'satuan_btkl' => 'required|string|max:20',
            'is_active' => 'boolean',
            'komponen_bop_id' => 'nullable|array',
            'komponen_bop_id.*' => 'exists:komponen_bops,id',
            'kuantitas_default' => 'nullable|array',
            'kuantitas_default.*' => 'numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $prosesProduksi->update([
                'nama_proses' => $validated['nama_proses'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tarif_btkl' => $validated['tarif_btkl'],
                'satuan_btkl' => $validated['satuan_btkl'],
                'is_active' => $request->has('is_active'),
            ]);

            // Update default BOP - hapus yang lama, buat yang baru
            $prosesProduksi->prosesBops()->delete();
            
            if (!empty($validated['komponen_bop_id'])) {
                foreach ($validated['komponen_bop_id'] as $key => $komponenId) {
                    if ($komponenId && isset($validated['kuantitas_default'][$key]) && $validated['kuantitas_default'][$key] > 0) {
                        ProsesBop::create([
                            'proses_produksi_id' => $prosesProduksi->id,
                            'komponen_bop_id' => $komponenId,
                            'kuantitas_default' => $validated['kuantitas_default'][$key],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('master-data.proses-produksi.index')
                ->with('success', 'Proses produksi berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
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
            return redirect()->route('master-data.proses-produksi.index')
                ->with('success', 'Proses produksi berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
