<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Bop;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Ambil semua akun beban (COA dengan tipe beban)
            $akunBeban = Coa::where('tipe_akun', 'beban')
                ->orderBy('kode_akun')
                ->get();

            // Ambil data BOP yang sudah ada
            $bops = Bop::with('coa')
                ->orderBy('kode_akun')
                ->get()
                ->keyBy('kode_akun');

            return view('master-data.bop.index', compact('akunBeban', 'bops'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|exists:coas,kode_akun',
            'budget' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Get COA data
            $coa = Coa::where('kode_akun', $validated['kode_akun'])->firstOrFail();
            
            // Format budget to remove any formatting (like dots for thousands)
            $budget = str_replace(['.', ','], ['', '.'], $validated['budget']);
            $budget = (float)$budget;
            
            // Check if BOP already exists for this account
            $bop = Bop::where('kode_akun', $validated['kode_akun'])->first();
            
            if ($bop) {
                // Update existing BOP
                $bop->update([
                    'nama_akun' => $coa->nama_akun,
                    'budget' => $budget,
                    // Keep existing aktual value or set to 0 if null
                    'aktual' => $bop->aktual ?? 0,
                    'is_active' => true
                ]);
            } else {
                // Create new BOP
                Bop::create([
                    'kode_akun' => $validated['kode_akun'],
                    'nama_akun' => $coa->nama_akun,
                    'budget' => $budget,
                    'aktual' => 0, // Set default value for aktual
                    'is_active' => true
                ]);
            }


            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'Budget BOP berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan budget BOP: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bop $bop)
    {
        $validated = $request->validate([
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $bop->update([
                'budget' => $validated['budget'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'Budget BOP berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui budget BOP: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bop $bop)
    {
        try {
            DB::beginTransaction();

            // Hapus budget (set ke 0) alih-alih menghapus record
            $bop->update([
                'budget' => 0,
                'is_active' => false
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'Budget BOP berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal menghapus budget BOP: ' . $e->getMessage());
        }
    }
}
