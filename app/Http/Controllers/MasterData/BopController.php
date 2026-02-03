<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BopProses;
use App\Models\ProsesProduksi;
use App\Models\Bop;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BopController extends Controller
{
    /**
     * Display the unified BOP page
     */
    public function index()
    {
        try {
            // Get only processes that have BTKL data (from bom_job_btkl table)
            $prosesIdsWithBTKL = DB::table('bom_job_btkl')
                ->distinct()
                ->pluck('proses_produksi_id')
                ->toArray();
            
            $prosesProduksis = ProsesProduksi::whereIn('id', $prosesIdsWithBTKL)
                ->with('bopProses')
                ->orderBy('kode_proses')
                ->get();

            // Get all expense accounts (kode 5) as BOP Lainnya candidates
            $akunBeban = Coa::where('kode_akun', 'LIKE', '5%')
                ->where('is_akun_header', false) // Only show non-header accounts
                ->orderBy('kode_akun')
                ->get();

            // Transform expense accounts to BOP Lainnya format for display
            $bopLainnya = $akunBeban->map(function($akun) {
                // Check if there's existing BOP data for this account in bops table
                $existingBop = \App\Models\Bop::where('kode_akun', $akun->kode_akun)->first();
                
                return (object) [
                    'id' => $existingBop->id ?? null,
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'budget' => $existingBop->jumlah ?? 0, // Use 'jumlah' field
                    'kuantitas_per_jam' => 1, // Default value
                    'aktual' => 0, // Default value
                    'periode' => $existingBop->periode ?? date('Y-m'),
                    'keterangan' => $existingBop->nama_biaya ?? $akun->nama_akun,
                    'is_active' => true,
                    'created_at' => $existingBop->created_at ?? now(),
                    'updated_at' => $existingBop->updated_at ?? now(),
                ];
            });

            // Calculate BOP Lainnya totals
            $totalBopLainnya = $bopLainnya->sum('budget') ?? 0;
            $jumlahBopLainnya = $bopLainnya->count() ?? 0;

            return view('master-data.bop.index', compact(
                'prosesProduksis',
                'bopLainnya',
                'totalBopLainnya',
                'jumlahBopLainnya',
                'akunBeban'
            ));
            
        } catch (\Exception $e) {
            // Set default values if there's an error
            $prosesProduksis = collect([]);
            $bopLainnya = collect([]);
            $totalBopLainnya = 0;
            $jumlahBopLainnya = 0;
            $akunBeban = collect([]);
            
            return view('master-data.bop.index', compact(
                'prosesProduksis',
                'bopLainnya',
                'totalBopLainnya',
                'jumlahBopLainnya',
                'akunBeban'
            ))->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store BOP Lainnya
     */
    public function storeLainnya(Request $request)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|string|exists:coas,kode_akun',
            'budget' => 'required|numeric|min:0',
            'kuantitas_per_jam' => 'required|integer|min:1',
            'periode' => 'required|string',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get COA data
            $coa = Coa::where('kode_akun', $validated['kode_akun'])->first();
            if (!$coa) {
                throw new \Exception('Akun tidak ditemukan');
            }

            // Check if account is expense account (kode 5)
            if (!str_starts_with($validated['kode_akun'], '5')) {
                throw new \Exception('Hanya akun beban (kode 5) yang dapat digunakan untuk BOP Lainnya');
            }

            $validated['nama_akun'] = $coa->nama_akun;
            $validated['metode_pembebanan'] = 'jam_mesin'; // Default method
            $validated['aktual'] = 0; // Default aktual
            $validated['is_active'] = true;

            \App\Models\BopLainnya::create($validated);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'BOP Lainnya berhasil disimpan']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete BOP Lainnya
     */
    public function destroyLainnya($id)
    {
        try {
            DB::beginTransaction();

            $bopLainnya = \App\Models\BopLainnya::findOrFail($id);
            $bopLainnya->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'BOP Lainnya berhasil dihapus']);

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
            return redirect()->route('master-data.bop.index')
                ->with('warning', 'Semua proses BTKL sudah memiliki BOP atau belum memiliki kapasitas per jam.');
        }

        // Get all expense accounts (kode 5) for dynamic BOP components
        $akunBeban = Coa::where('kode_akun', 'LIKE', '5%')
            ->where('is_akun_header', false)
            ->orderBy('kode_akun')
            ->get();

        return view('master-data.bop.create-proses', compact('availableProses', 'akunBeban'));
    }

    /**
     * Store BOP Proses
     */
    public function storeProses(Request $request)
    {
        $validated = $request->validate([
            'proses_produksi_id' => 'required|exists:proses_produksis,id|unique:bop_proses,proses_produksi_id',
            'akun_beban' => 'required|array|min:1',
            'akun_beban.*' => 'required|exists:coas,kode_akun',
            'nominal_per_jam' => 'required|array|min:1',
            'nominal_per_jam.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get BTKL process to validate capacity
            $prosesProduksi = ProsesProduksi::findOrFail($validated['proses_produksi_id']);
            
            if ($prosesProduksi->kapasitas_per_jam <= 0) {
                throw new \Exception('Proses BTKL harus memiliki kapasitas per jam yang valid.');
            }

            // Calculate total BOP per jam from all inputs
            $totalBopPerJam = array_sum($validated['nominal_per_jam']);

            // Create BOP Proses with dynamic data
            $bopProses = BopProses::create([
                'proses_produksi_id' => $validated['proses_produksi_id'],
                'total_bop_per_jam' => $totalBopPerJam,
                'budget' => $totalBopPerJam * 8, // Default 8 jam per shift
                'aktual' => 0,
                'periode' => date('Y-m'),
                'keterangan' => "BOP untuk proses {$prosesProduksi->nama_proses}",
            ]);

            // Create BOP detail records for each expense account
            foreach ($validated['akun_beban'] as $index => $akunKode) {
                $nominal = $validated['nominal_per_jam'][$index];
                
                if ($nominal > 0) {
                    // Create or find KomponenBOP for this akun
                    $komponenBop = \App\Models\KomponenBop::firstOrCreate([
                        'kode_komponen' => 'BOP-' . str_replace('.', '', $akunKode),
                        'nama_komponen' => \App\Models\Coa::find($akunKode)->nama_akun,
                        'satuan' => 'jam',
                        'tarif_per_satuan' => $nominal,
                        'is_active' => true
                    ]);
                    
                    // Create BOP detail record
                    \App\Models\BopProsesDetail::create([
                        'bop_proses_id' => $bopProses->id,
                        'komponen_bop_id' => $komponenBop->id,
                        'nominal_per_jam' => $nominal,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil ditambahkan dengan ' . count(array_filter($validated['nominal_per_jam'])) . ' komponen beban.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambah BOP Proses: ' . $e->getMessage());
        }
    }

    public function showProses($id)
    {
        try {
            $bopProses = BopProses::with(['prosesProduksi', 'bopProsesDetails.komponenBop'])->findOrFail($id);
            return view('master-data.bop.show-proses', compact('bopProses'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing BOP Proses
     */
    public function editProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            return view('master-data.bop.edit-proses', compact('bopProses'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop.index')
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
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui BOP Proses: ' . $e->getMessage());
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
                ->route('master-data.bop.index')
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
                ->route('master-data.bop.index')
                ->with('success', "Berhasil sync kapasitas untuk {$updated} BOP Proses");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal sync kapasitas: ' . $e->getMessage());
        }
    }

    /**
     * Set budget for BOP Proses
     */
    public function setBudgetProses(Request $request, $id)
    {
        $validated = $request->validate([
            'budget' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $bopProses = BopProses::findOrFail($id);
            $bopProses->update(['budget' => $validated['budget']]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Budget BOP Proses berhasil diset']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update aktual BOP from expense payments
     * This method will be called when expense payments are made
     */
    public function updateAktualFromExpense($kodeAkun, $jumlah)
    {
        try {
            DB::beginTransaction();

            // Update BOP Lainnya aktual
            $bopLainnya = \App\Models\BopLainnya::where('kode_akun', $kodeAkun)
                ->where('is_active', true)
                ->first();

            if ($bopLainnya) {
                $bopLainnya->increment('aktual', $jumlah);
            }

            // Update BOP Proses aktual if related to specific process expenses
            // This can be expanded based on business logic

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}