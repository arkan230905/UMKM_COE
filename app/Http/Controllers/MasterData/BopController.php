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
            // Get all production processes with capacity
            $prosesProduksis = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
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
                // Check if there's existing BOP data for this account in bop_lainnyas table
                $existingBop = \App\Models\BopLainnya::where('kode_akun', $akun->kode_akun)->first();
                
                return (object) [
                    'id' => $existingBop->id ?? null,
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'budget' => $existingBop->budget ?? 0,
                    'kuantitas_per_jam' => $existingBop->kuantitas_per_jam ?? 1,
                    'aktual' => $existingBop->aktual ?? 0,
                    'periode' => $existingBop->periode ?? date('Y-m'),
                    'keterangan' => $existingBop->keterangan ?? $akun->nama_akun,
                    'is_active' => $existingBop->is_active ?? true,
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
     * Get BOP Lainnya data for editing
     */
    public function getLainnya($id)
    {
        try {
            $bopLainnya = \App\Models\BopLainnya::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'bop' => $bopLainnya
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update BOP Lainnya
     */
    public function updateLainnya(Request $request, $id)
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

            $bopLainnya = \App\Models\BopLainnya::findOrFail($id);
            
            $validated['nama_akun'] = $coa->nama_akun;
            $validated['metode_pembebanan'] = $bopLainnya->metode_pembebanan ?? 'jam_mesin';
            $validated['is_active'] = true;

            $bopLainnya->update($validated);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'BOP Lainnya berhasil diperbarui']);

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
        
        // Get all processes with capacity (allow both with and without BOP for editing)
        $availableProses = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
            ->when($prosesId, function($query, $prosesId) {
                return $query->where('id', $prosesId);
            })
            ->orderBy('nama_proses')
            ->get();

        if ($availableProses->isEmpty()) {
            return redirect()->route('master-data.bop.index')
                ->with('warning', 'Tidak ada proses BTKL dengan kapasitas per jam yang valid.');
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
            'komponen_bop' => 'required|array|min:1',
            'komponen_bop.*.component' => 'required|string',
            'komponen_bop.*.rate_per_hour' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get BTKL process to validate capacity
            $prosesProduksi = ProsesProduksi::findOrFail($validated['proses_produksi_id']);
            
            if ($prosesProduksi->kapasitas_per_jam <= 0) {
                throw new \Exception('Proses BTKL harus memiliki kapasitas per jam yang valid.');
            }

            // Filter out empty components and validate at least one has rate > 0
            $validComponents = collect($validated['komponen_bop'])->filter(function($component) {
                return !empty($component['component']) && floatval($component['rate_per_hour']) > 0;
            });

            if ($validComponents->isEmpty()) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Check for duplicate components
            $componentNames = $validComponents->pluck('component')->toArray();
            if (count($componentNames) !== count(array_unique($componentNames))) {
                throw new \Exception('Komponen BOP tidak boleh duplikat.');
            }

            // Calculate values from komponen_bop array
            $totalBopPerJam = $validComponents->sum('rate_per_hour');
            $kapasitasPerJam = $prosesProduksi->kapasitas_per_jam;
            $bopPerUnit = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;
            $budget = $totalBopPerJam * 8; // 8 jam per shift

            // Debug: Log the calculated values
            \Log::info('BOP Debug - Total BOP per Jam: ' . $totalBopPerJam);
            \Log::info('BOP Debug - Komponen BOP: ' . json_encode($validComponents->values()->all()));

            // Create or Update BOP Proses with JSON data
            $bopProses = BopProses::updateOrCreate(
                ['proses_produksi_id' => $validated['proses_produksi_id']],
                [
                    'komponen_bop' => $validComponents->values()->all(),
                    'total_bop_per_jam' => $totalBopPerJam,
                    'kapasitas_per_jam' => $kapasitasPerJam,
                    'bop_per_unit' => $bopPerUnit,
                    'budget' => $budget,
                    'aktual' => 0,
                    'periode' => date('Y-m'),
                    'keterangan' => "BOP untuk proses {$prosesProduksi->nama_proses}",
                    'is_active' => true,
                ]
            );

            // Debug: Log the saved BOP data
            \Log::info('BOP Debug - Saved BOP ID: ' . $bopProses->id);
            \Log::info('BOP Debug - Saved total_bop_per_jam: ' . $bopProses->total_bop_per_jam);

            DB::commit();

            return redirect()->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil ditambahkan dengan ' . $validComponents->count() . ' komponen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambah BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing BOP Proses
     */
    public function showProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
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
            'komponen_bop' => 'required|array|min:1',
            'komponen_bop.*.component' => 'required|string',
            'komponen_bop.*.rate_per_hour' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $bopProses = BopProses::findOrFail($id);
            
            // Filter out empty components and validate at least one has rate > 0
            $validComponents = collect($validated['komponen_bop'])->filter(function($component) {
                return !empty($component['component']) && floatval($component['rate_per_hour']) > 0;
            });

            if ($validComponents->isEmpty()) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Check for duplicate components
            $componentNames = $validComponents->pluck('component')->toArray();
            if (count($componentNames) !== count(array_unique($componentNames))) {
                throw new \Exception('Komponen BOP tidak boleh duplikat.');
            }

            // Calculate values from komponen_bop array
            $totalBopPerJam = $validComponents->sum('rate_per_hour');
            $kapasitasPerJam = $bopProses->prosesProduksi->kapasitas_per_jam;
            $bopPerUnit = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;
            $budget = $totalBopPerJam * 8; // 8 jam per shift

            // Update BOP Proses with JSON data
            $bopProses->update([
                'komponen_bop' => $validComponents->values()->all(),
                'total_bop_per_jam' => $totalBopPerJam,
                'kapasitas_per_jam' => $kapasitasPerJam,
                'bop_per_unit' => $bopPerUnit,
                'budget' => $budget,
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil diperbarui dengan ' . $validComponents->count() . ' komponen.');

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