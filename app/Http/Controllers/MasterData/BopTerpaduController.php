<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BopProses;
use App\Models\ProsesProduksi;
use App\Models\Bop;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BopTerpaduController extends Controller
{
    /**
     * Display the unified BOP page
     */
    public function index()
    {
        try {
            // Get all BTKL processes with their BOP data
            $prosesProduksis = ProsesProduksi::with('bopProses')
                ->orderBy('kode_proses')
                ->get();

            // Get BOP Budget data
            $bopBudgets = Bop::with('coa')
                ->where('budget', '>', 0)
                ->orderBy('kode_akun')
                ->get();

            // Calculate totals
            $totalBudget = $bopBudgets->sum('budget');
            $totalAktual = $bopBudgets->sum('aktual');

            // Get COA for BOP accounts (overhead accounts)
            $akunBop = Coa::where('kode_akun', 'LIKE', '5%') // Assuming 5xxx is overhead accounts
                ->orWhere('nama_akun', 'LIKE', '%overhead%')
                ->orWhere('nama_akun', 'LIKE', '%bop%')
                ->orderBy('kode_akun')
                ->get();

            return view('master-data.bop-terpadu.index', compact(
                'prosesProduksis',
                'bopBudgets', 
                'totalBudget',
                'totalAktual',
                'akunBop'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store BOP Budget
     */
    public function storeBudget(Request $request)
    {
        $validated = $request->validate([
            'periode' => 'required|string',
            'kode_akun' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get COA data
            $coa = Coa::where('kode_akun', $validated['kode_akun'])->first();
            if (!$coa) {
                throw new \Exception('Akun tidak ditemukan');
            }

            // Create or update BOP budget
            Bop::updateOrCreate(
                [
                    'kode_akun' => $validated['kode_akun'],
                    'periode' => $validated['periode']
                ],
                [
                    'nama_akun' => $coa->nama_akun,
                    'budget' => $validated['budget'],
                    'keterangan' => $validated['keterangan'],
                    'is_active' => true
                ]
            );

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Budget BOP berhasil disimpan']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Store BOP Aktual
     */
    public function storeAktual(Request $request)
    {
        $validated = $request->validate([
            'bop_id' => 'required|exists:bops,id',
            'aktual' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $bop = Bop::findOrFail($validated['bop_id']);
            $bop->update(['aktual' => $validated['aktual']]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Aktual BOP berhasil disimpan']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show form for creating BOP Proses
     */
    public function createProses(Request $request)
    {
        $prosesId = $request->get('proses_id');
        
        // Get available processes that don't have BOP yet
        $availableProses = ProsesProduksi::whereDoesntHave('bopProses')
            ->where('kapasitas_per_jam', '>', 0)
            ->when($prosesId, function($query, $prosesId) {
                return $query->where('id', $prosesId);
            })
            ->orderBy('nama_proses')
            ->get();

        if ($availableProses->isEmpty()) {
            return redirect()->route('master-data.bop-terpadu.index')
                ->with('warning', 'Semua proses BTKL sudah memiliki BOP atau belum memiliki kapasitas per jam.');
        }

        return view('master-data.bop-terpadu.create-proses', compact('availableProses'));
    }

    /**
     * Store BOP Proses
     */
    public function storeProses(Request $request)
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
                ->route('master-data.bop-terpadu.index')
                ->with('success', 'BOP Proses berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing BOP Proses
     */
    public function editProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            return view('master-data.bop-terpadu.edit-proses', compact('bopProses'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop-terpadu.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update BOP Proses
     */
    public function updateProses(Request $request, $id)
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
                ->route('master-data.bop-terpadu.index')
                ->with('success', 'BOP Proses berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Show BOP Proses detail
     */
    public function showProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            return view('master-data.bop-terpadu.show-proses', compact('bopProses'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop-terpadu.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Delete BOP Proses
     */
    public function destroyProses($id)
    {
        DB::beginTransaction();
        
        try {
            $bopProses = BopProses::findOrFail($id);
            $bopProses->delete();

            DB::commit();

            return redirect()
                ->route('master-data.bop-terpadu.index')
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
                ->route('master-data.bop-terpadu.index')
                ->with('success', "Berhasil sync kapasitas untuk {$updated} BOP Proses");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal sync kapasitas: ' . $e->getMessage());
        }
    }

    /**
     * Get BOP analysis data for dashboard
     */
    public function getAnalysisData()
    {
        try {
            // BOP per Proses Summary
            $bopProsesData = BopProses::with('prosesProduksi')
                ->get()
                ->map(function($bop) {
                    return [
                        'nama_proses' => $bop->prosesProduksi->nama_proses,
                        'bop_per_jam' => $bop->total_bop_per_jam,
                        'bop_per_unit' => $bop->bop_per_unit,
                        'kapasitas' => $bop->kapasitas_per_jam
                    ];
                });

            // Budget vs Aktual Summary
            $budgetAktualData = Bop::where('budget', '>', 0)
                ->get()
                ->map(function($bop) {
                    return [
                        'nama_akun' => $bop->nama_akun,
                        'budget' => $bop->budget,
                        'aktual' => $bop->aktual ?? 0,
                        'variance' => $bop->budget - ($bop->aktual ?? 0),
                        'variance_percent' => $bop->budget > 0 ? ((($bop->budget - ($bop->aktual ?? 0)) / $bop->budget) * 100) : 0
                    ];
                });

            return response()->json([
                'success' => true,
                'bop_proses' => $bopProsesData,
                'budget_aktual' => $budgetAktualData
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}