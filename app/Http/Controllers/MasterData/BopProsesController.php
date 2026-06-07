<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BopProses;
use App\Models\ProsesProduksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BopProsesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Get all BTKL processes with their BOP data
            // 🔒 SECURITY: Filter by user_id
            $prosesProduksis = ProsesProduksi::with('bopProses')
                ->where('user_id', auth()->id())
                ->orderBy('kode_proses')
                ->paginate(10);

            return view('master-data.bop-proses.index', compact('prosesProduksis'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get BTKL processes that don't have BOP yet
        $availableProses = ProsesProduksi::whereDoesntHave('bopProses')
            ->where('user_id', auth()->id()) // 🔒 SECURITY: Filter by user_id
            ->orderBy('nama_proses')
            ->get();

        if ($availableProses->isEmpty()) {
            return redirect()->route('master-data.bop-proses.index')
                ->with('warning', 'Semua proses BTKL sudah memiliki BOP atau belum memiliki kapasitas per jam.');
        }

        // 🔒 SECURITY: Get bahan pendukung filtered by user_id
        $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())
            ->orderBy('nama_bahan')
            ->get();

        return view('master-data.bop-proses.create', compact('availableProses', 'bahanPendukungs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'proses_produksi_id' => 'required|exists:proses_produksis,id|unique:bop_proses,proses_produksi_id',
            'komponen_bop' => 'required|array|min:1',
            'komponen_bop.*.bahan_pendukung_id' => 'nullable|exists:bahan_pendukungs,id',
            'komponen_bop.*.component' => 'nullable|string',
            'komponen_bop.*.rate_per_hour' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get BTKL process
            $prosesProduksi = ProsesProduksi::findOrFail($validated['proses_produksi_id']);
            
            // 🔒 SECURITY: Verify proses produksi belongs to user
            if ($prosesProduksi->user_id != auth()->id()) {
                throw new \Exception('Unauthorized access to proses produksi');
            }

            // Process komponen_bop array
            $processedKomponen = [];
            
            foreach ($validated['komponen_bop'] as $key => $komponen) {
                $item = [];
                
                // Check if it's bahan pendukung or lainnya
                if (!empty($komponen['bahan_pendukung_id'])) {
                    // It's bahan pendukung
                    $bahanPendukung = \App\Models\BahanPendukung::find($komponen['bahan_pendukung_id']);
                    
                    // 🔒 SECURITY: Verify bahan pendukung belongs to user
                    if ($bahanPendukung && $bahanPendukung->user_id == auth()->id()) {
                        $item['bahan_pendukung_id'] = $komponen['bahan_pendukung_id'];
                        $item['component'] = $bahanPendukung->nama_bahan;
                        $item['rate_per_hour'] = $komponen['rate_per_hour'];
                        $processedKomponen[] = $item;
                    }
                } elseif (!empty($komponen['component'])) {
                    // It's lainnya component
                    $item['component'] = $komponen['component'];
                    $item['rate_per_hour'] = $komponen['rate_per_hour'];
                    $processedKomponen[] = $item;
                }
            }

            // Create BOP Proses (calculations will be done automatically in model)
            BopProses::create([
                'proses_produksi_id' => $validated['proses_produksi_id'],
                'komponen_bop' => $processedKomponen,
                'user_id' => auth()->id(), // 🔒 SECURITY: Set user_id
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('success', 'BOP Proses berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            return view('master-data.bop-proses.show', compact('bopProses'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            
            // 🔒 SECURITY: Get bahan pendukung filtered by user_id
            $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())
                ->orderBy('nama_bahan')
                ->get();
            
            return view('master-data.bop-proses.edit', compact('bopProses', 'bahanPendukungs'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'komponen_bop' => 'required|array|min:1',
            'komponen_bop.*.bahan_pendukung_id' => 'nullable|exists:bahan_pendukungs,id',
            'komponen_bop.*.component' => 'nullable|string',
            'komponen_bop.*.rate_per_hour' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $bopProses = BopProses::findOrFail($id);
            
            // Process komponen_bop array
            $processedKomponen = [];
            
            foreach ($validated['komponen_bop'] as $key => $komponen) {
                $item = [];
                
                // Check if it's bahan pendukung or lainnya
                if (!empty($komponen['bahan_pendukung_id'])) {
                    // It's bahan pendukung
                    $bahanPendukung = \App\Models\BahanPendukung::find($komponen['bahan_pendukung_id']);
                    
                    // 🔒 SECURITY: Verify bahan pendukung belongs to user
                    if ($bahanPendukung && $bahanPendukung->user_id == auth()->id()) {
                        $item['bahan_pendukung_id'] = $komponen['bahan_pendukung_id'];
                        $item['component'] = $bahanPendukung->nama_bahan;
                        $item['rate_per_hour'] = $komponen['rate_per_hour'];
                        $processedKomponen[] = $item;
                    }
                } elseif (!empty($komponen['component'])) {
                    // It's lainnya component
                    $item['component'] = $komponen['component'];
                    $item['rate_per_hour'] = $komponen['rate_per_hour'];
                    $processedKomponen[] = $item;
                }
            }
            
            // Update komponen_bop
            $bopProses->komponen_bop = $processedKomponen;
            $bopProses->save();

            DB::commit();

            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('success', 'BOP Proses berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $bopProses = BopProses::findOrFail($id);
            $bopProses->delete();

            DB::commit();

            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('success', 'BOP Proses berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Gagal menghapus BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Sync data from BTKL for all BOP Proses (deprecated)
     */
    public function syncKapasitas()
    {
        try {
            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('info', 'Sync kapasitas tidak diperlukan lagi karena sistem sudah menggunakan pembebanan per produk');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}