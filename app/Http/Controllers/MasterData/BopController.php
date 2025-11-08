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
            // Ambil semua akun beban (COA dengan tipe Expense)
            $akunBeban = Coa::where('tipe_akun', 'Expense')
                ->orderBy('kode_akun')
                ->get();

            // Ambil data BOP yang sudah ada
            $bops = Bop::with('coa')
                ->orderBy('kode_akun')
                ->get()
                ->keyBy('kode_akun');

            // Pastikan semua akun beban ada di $bops
            foreach ($akunBeban as $akun) {
                if (!isset($bops[$akun->kode_akun])) {
                    $bops[$akun->kode_akun] = new Bop([
                        'kode_akun' => $akun->kode_akun,
                        'nama_akun' => $akun->nama_akun,
                        'budget' => 0,
                        'aktual' => 0
                    ]);
                }
            }

            // Urutkan kembali berdasarkan kode akun
            $bops = $bops->sortBy('kode_akun');

            return view('master-data.bop.index', [
                'akunBeban' => $akunBeban,
                'bops' => $bops
            ]);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $bop = Bop::findOrFail($id);
            $akunBeban = Coa::where('tipe_akun', 'Expense')
                ->orderBy('kode_akun')
                ->get();
                
            return view('master-data.bop.edit', [
                'bop' => $bop,
                'akunBeban' => $akunBeban
            ]);
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop.index')
                ->with('error', 'Data BOP tidak ditemukan: ' . $e->getMessage());
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
    public function update(Request $request, $id)
    {
        // Debug log request data
        \Log::info('Update BOP Request:', $request->all());

        // Validasi input
        $validated = $request->validate([
            'kode_akun' => 'required|string|exists:coas,kode_akun',
            'budget' => 'required|string',
            'budget_value' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Temukan data BOP yang akan diupdate
            $bop = Bop::findOrFail($id);
            
            // Debug sebelum konversi
            \Log::info('Before conversion:', [
                'budget_value' => $validated['budget_value'],
                'budget' => $validated['budget']
            ]);
            
            // Format budget (pastikan tidak ada koma atau titik)
            $budget = (float) str_replace(['.', ','], '', $validated['budget']);
            
            // Pastikan budget tidak negatif
            $budget = max(0, $budget);
            
            // Dapatkan data COA untuk nama akun
            $coa = Coa::where('kode_akun', $validated['kode_akun'])->first();
            
            // Update data
            $updateData = [
                'kode_akun' => $validated['kode_akun'],
                'nama_akun' => $coa->nama_akun ?? 'BOP',
                'budget' => $budget,
                'keterangan' => $validated['keterangan'] ?? null,
                'is_active' => true
            ];
            
            $bop->update($updateData);
            
            // Log untuk debugging
            \Log::info('BOP Updated', [
                'id' => $bop->id,
                'kode_akun' => $bop->kode_akun,
                'budget' => $budget,
                'update_data' => $updateData,
                'request_data' => $request->all(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'Budget BOP berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating BOP: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui budget BOP: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            // Temukan data BOP yang akan dihapus
            $bop = Bop::findOrFail($id);
            
            // Hapus budget (set ke 0) alih-alih menghapus record
            $bop->update([
                'budget' => 0,
                'is_active' => false,
                'keterangan' => 'Dihapus pada ' . now()->format('d/m/Y H:i:s')
            ]);
            
            // Log untuk debugging
            \Log::info('BOP Deleted', [
                'id' => $id,
                'kode_akun' => $bop->kode_akun,
                'deleted_at' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'Budget BOP berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting BOP: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->with('error', 'Gagal menghapus budget BOP: ' . $e->getMessage());
        }
    }
}
