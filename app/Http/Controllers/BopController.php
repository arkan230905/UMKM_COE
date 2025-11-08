<?php

namespace App\Http\Controllers;

use App\Models\Bop;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BopController extends Controller
{
    /**
     * Menampilkan daftar BOP dengan budget
     */
    public function index()
    {
        // Ambil semua akun beban (COA dengan tipe beban atau kode 5)
        $akunBeban = Coa::where(function($query) {
                            $query->whereIn('tipe_akun', ['Expense', 'Beban', 'Biaya'])
                                  ->orWhere('kode_akun', 'like', '5%');
                         })
                         ->orderBy('kode_akun')
                         ->get();

        // Ambil data BOP yang sudah ada
        $bops = Bop::with('coa')
                   ->orderBy('kode_akun')
                   ->get()
                   ->keyBy('kode_akun');

        return view('master-data.bop.index', compact('akunBeban', 'bops'));
    }

    /**
     * Menyimpan budget BOP baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|exists:coas,kode_akun',
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Cek apakah sudah ada data BOP untuk akun ini
            $bop = Bop::firstOrNew(['kode_akun' => $validated['kode_akun']]);
            
            // Ambil nama akun dari COA
            $coa = Coa::where('kode_akun', $validated['kode_akun'])->first();
            
            $bop->fill([
                'nama_akun' => $coa->nama_akun ?? 'Beban Lainnya',
                'budget' => $validated['budget'],
                'keterangan' => $validated['keterangan'] ?? null,
                'is_active' => true,
            ]);

            $bop->save();

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
     * Menampilkan form edit budget BOP
     */
    public function edit($id)
    {
        $bop = Bop::findOrFail($id);
        return view('master-data.bop.edit', compact('bop'));
    }

    /**
     * Memperbarui budget BOP
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $bop = Bop::findOrFail($id);
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
     * Menghapus budget BOP (hanya mengosongkan budget, tidak menghapus record)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $bop = Bop::findOrFail($id);
            
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
