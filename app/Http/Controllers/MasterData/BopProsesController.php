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
            $prosesProduksis = ProsesProduksi::with('bopProses')
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
            ->where('kapasitas_per_jam', '>', 0) // Only processes with capacity
            ->orderBy('nama_proses')
            ->get();

        if ($availableProses->isEmpty()) {
            return redirect()->route('master-data.bop-proses.index')
                ->with('warning', 'Semua proses BTKL sudah memiliki BOP atau belum memiliki kapasitas per jam.');
        }

        return view('master-data.bop-proses.create', compact('availableProses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'proses_produksi_id' => 'required|exists:proses_produksis,id|unique:bop_proses,proses_produksi_id',
            'listrik_per_jam' => 'required|numeric|min:0',
            'gas_bbm_per_jam' => 'required|numeric|min:0',
            'penyusutan_mesin_per_jam' => 'required|numeric|min:0',
            'maintenance_per_jam' => 'required|numeric|min:0',
            'gaji_mandor_per_jam' => 'required|numeric|min:0',
            'lain_lain_per_jam' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get BTKL process to validate capacity
            $prosesProduksi = ProsesProduksi::findOrFail($validated['proses_produksi_id']);
            
            if ($prosesProduksi->kapasitas_per_jam <= 0) {
                throw new \Exception('Proses BTKL harus memiliki kapasitas per jam yang valid.');
            }

            // Create BOP Proses (calculations will be done automatically in model)
            BopProses::create($validated);

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
            return view('master-data.bop-proses.edit', compact('bopProses'));
            
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
            'listrik_per_jam' => 'required|numeric|min:0',
            'gas_bbm_per_jam' => 'required|numeric|min:0',
            'penyusutan_mesin_per_jam' => 'required|numeric|min:0',
            'maintenance_per_jam' => 'required|numeric|min:0',
            'gaji_mandor_per_jam' => 'required|numeric|min:0',
            'lain_lain_per_jam' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $bopProses = BopProses::findOrFail($id);
            $bopProses->update($validated);

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
     * Sync kapasitas dari BTKL untuk semua BOP Proses
     */
    public function syncKapasitas()
    {
        try {
            DB::beginTransaction();

            $bopProses = BopProses::with('prosesProduksi')->get();
            $updated = 0;

            foreach ($bopProses as $bop) {
                if ($bop->prosesProduksi && $bop->kapasitas_per_jam != $bop->prosesProduksi->kapasitas_per_jam) {
                    $bop->update([
                        'kapasitas_per_jam' => $bop->prosesProduksi->kapasitas_per_jam
                    ]);
                    $updated++;
                }
            }

            DB::commit();

            return redirect()
                ->route('master-data.bop-proses.index')
                ->with('success', "Berhasil sync kapasitas untuk {$updated} BOP Proses");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal sync kapasitas: ' . $e->getMessage());
        }
    }
}