<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BopProses;
use App\Models\ProsesProduksi;
use App\Models\Bop;
use App\Models\BebanOperasional;
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
            // Get all BOP Proses with their production process data
            $bopProses = BopProses::with('prosesProduksi')
                ->where('is_active', true)
                ->orderBy('id')
                ->get();

            // Get all production processes with capacity (for modal options)
            $prosesProduksis = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
                ->with('bopProses', 'btkl', 'jabatan')
                ->orderBy('kode_proses')
                ->get();

            // Prepare BTKL data for auto-fill functionality
            $btklData = [];
            foreach ($prosesProduksis as $proses) {
                $btklData[$proses->id] = [
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'tarif_btkl_per_jam' => $proses->tarif_btkl ?? 0,
                    'nama_btkl' => $proses->nama_proses,
                    'jabatan' => $proses->jabatan->nama_jabatan ?? 'Tidak diketahui'
                ];
            }

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

            // Get Beban Operasional master data
            $bebanOperasional = BebanOperasional::query()
                ->orderBy('kode', 'asc')
                ->get();

            return view('master-data.bop.index', compact(
                'bopProses',
                'prosesProduksis',
                'bopLainnya',
                'totalBopLainnya',
                'jumlahBopLainnya',
                'akunBeban',
                'btklData',
                'bebanOperasional'
            ));
            
        } catch (\Exception $e) {
            // Set default values if there's an error
            $bopProses = collect([]);
            $prosesProduksis = collect([]);
            $bopLainnya = collect([]);
            $totalBopLainnya = 0;
            $jumlahBopLainnya = 0;
            $akunBeban = collect([]);
            $btklData = [];
            $bebanOperasional = collect([]);
            
            return view('master-data.bop.index', compact(
                'bopProses',
                'prosesProduksis',
                'bopLainnya',
                'totalBopLainnya',
                'jumlahBopLainnya',
                'akunBeban',
                'btklData',
                'bebanOperasional'
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
     * Show BOP detail for modal (AJAX)
     */
    public function showProsesModal($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            
            // Get matching BTKL data based on process name
            $btkl = \App\Models\Btkl::where('nama_btkl', $bopProses->prosesProduksi->nama_proses)->first();
            
            return view('master-data.bop.show-proses-modal', compact('bopProses', 'btkl'));
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'BOP Proses tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show BOP detail page
     */
    public function showProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            
            // Get matching BTKL data based on process name
            $btkl = \App\Models\Btkl::where('nama_btkl', $bopProses->prosesProduksi->nama_proses)->first();
            
            return view('master-data.bop.show-proses', compact('bopProses', 'btkl'));
            
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

            // Update BOP Proses with JSON data
            $bopProses->update([
                'komponen_bop' => $validComponents->values()->all(),
                'total_bop_per_jam' => $totalBopPerJam,
                'kapasitas_per_jam' => $kapasitasPerJam,
                'bop_per_unit' => $bopPerUnit,
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
        ]);

        // Build komponen array
        $komponenBop = [];
        foreach ($validated['komponen_name'] as $index => $name) {
            if (!empty($name)) {
                $komponenBop[] = [
                    'name' => $name,
                    'rate_per_hour' => $validated['komponen_rate'][$index] ?? 0,
                    'description' => $validated['komponen_desc'][$index] ?? ''
                ];
                'total_bop_per_jam' => $validated['total_bop_per_jam'],
                'aktual' => $validated['aktual'] ?? 0,
                'keterangan' => $validated['keterangan'] ?? ''
            ]);

            // Build komponen array
            $komponenBop = [];
            foreach ($validated['komponen_name'] as $index => $name) {
                if (!empty($name)) {
                    $komponenBop[] = [
                        'name' => $name,
                        'rate_per_hour' => $validated['komponen_rate'][$index] ?? 0,
                        'description' => $validated['komponen_desc'][$index] ?? ''
                    ];
                }
            }

            // Save komponen as JSON
            $bopProses->update(['komponen_bop' => json_encode($komponenBop)]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'BOP Proses berhasil ditambahkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah BOP Proses: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store BOP Proses (simplified version)
     */
    public function storeProsesSimple(Request $request)
    {
        $validated = $request->validate([
            'proses_produksi_id' => 'required|exists:proses_produksis,id|unique:bop_proses,proses_produksi_id',
            'komponen_name' => 'required|array|min:1',
            'komponen_name.*' => 'required|string',
            'komponen_rate' => 'required|array|min:1',
            'komponen_rate.*' => 'required|numeric|min:0',
            'komponen_desc' => 'nullable|array',
            'komponen_desc.*' => 'nullable|string',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get BTKL process to validate capacity
            $prosesProduksi = ProsesProduksi::findOrFail($validated['proses_produksi_id']);
            
            if ($prosesProduksi->kapasitas_per_jam <= 0) {
                throw new \Exception('Proses BTKL harus memiliki kapasitas per jam yang valid.');
            }

            // Build components array from form data
            $components = [];
            foreach ($validated['komponen_name'] as $index => $name) {
                if (!empty(trim($name)) && floatval($validated['komponen_rate'][$index]) > 0) {
                    $components[] = [
                        'component' => trim($name),
                        'rate_per_hour' => floatval($validated['komponen_rate'][$index]),
                        'description' => $validated['komponen_desc'][$index] ?? ''
                    ];
                }
            }

            if (empty($components)) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Check for duplicate components
            $componentNames = array_column($components, 'component');
            if (count($componentNames) !== count(array_unique($componentNames))) {
                throw new \Exception('Komponen BOP tidak boleh duplikat.');
            }

            // Calculate values
            $totalBopPerJam = array_sum(array_column($components, 'rate_per_hour'));
            $kapasitasPerJam = $prosesProduksi->kapasitas_per_jam;
            $bopPerUnit = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;

            // Create BOP Proses
            $bopProses = BopProses::create([
                'proses_produksi_id' => $validated['proses_produksi_id'],
                'komponen_bop' => $components,
                'total_bop_per_jam' => $totalBopPerJam,
                'kapasitas_per_jam' => $kapasitasPerJam,
                'bop_per_unit' => $bopPerUnit,
                'periode' => date('Y-m'),
                'keterangan' => $validated['keterangan'] ?? "BOP untuk proses {$prosesProduksi->nama_proses}",
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil ditambahkan dengan ' . count($components) . ' komponen.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambah BOP Proses: ' . $e->getMessage());
        }
    }

    /**
     * Update BOP Proses (simplified version)
     */
    public function updateProsesSimple(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'edit_komponen_name' => 'required|array|min:1',
                'edit_komponen_name.*' => 'required|string',
                'edit_komponen_rate' => 'required|array|min:1',
                'edit_komponen_rate.*' => 'required|numeric|min:0',
                'edit_komponen_desc' => 'nullable|array',
                'edit_komponen_desc.*' => 'nullable|string',
                'keterangan' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $bopProses = BopProses::findOrFail($id);
            
            // Build components array from form data
            $components = [];
            foreach ($validated['edit_komponen_name'] as $index => $name) {
                if (!empty(trim($name)) && floatval($validated['edit_komponen_rate'][$index]) > 0) {
                    $components[] = [
                        'component' => trim($name),
                        'rate_per_hour' => floatval($validated['edit_komponen_rate'][$index]),
                        'description' => $validated['edit_komponen_desc'][$index] ?? ''
                    ];
                }
            }

            if (empty($components)) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Check for duplicate components
            $componentNames = array_column($components, 'component');
            if (count($componentNames) !== count(array_unique($componentNames))) {
                throw new \Exception('Komponen BOP tidak boleh duplikat.');
            }

            // Calculate values
            $totalBopPerJam = array_sum(array_column($components, 'rate_per_hour'));
            $kapasitasPerJam = $bopProses->kapasitas_per_jam;
            $bopPerUnit = $kapasitasPerJam > 0 ? $totalBopPerJam / $kapasitasPerJam : 0;

            // Update BOP Proses
            $bopProses->update([
                'komponen_bop' => $components,
                'total_bop_per_jam' => $totalBopPerJam,
                'bop_per_unit' => $bopPerUnit,
                'keterangan' => $validated['keterangan'] ?? $bopProses->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'BOP Proses berhasil diperbarui dengan ' . count($components) . ' komponen.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors()->all())
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error for debugging
            \Log::error('BOP Update Error: ' . $e->getMessage(), [
                'id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui BOP Proses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get BOP Proses data for editing
     */
    public function getBopProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'bop' => $bopProses
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data BOP Proses tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('BOP Get Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data BOP Proses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store Beban Operasional
     */
    public function storeBebanOperasional(Request $request)
    {
        try {
            $validated = $request->validate([
                'kategori' => 'required|in:Administrasi,Marketing,Utilitas,Distribusi,Lain-lain',
                'nama_beban' => 'required|string|max:255',
                'budget_bulanan' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:500',
                'status' => 'required|in:aktif,nonaktif'
            ]);

            $validated['created_by'] = auth()->id();
            $validated['kode'] = BebanOperasional::generateKode();
            
            $bebanOperasional = BebanOperasional::create($validated);

            // Add formatted fields for response
            $bebanOperasional->budget_bulanan_formatted = $bebanOperasional->budget_bulanan_formatted;
            $bebanOperasional->status_badge = $bebanOperasional->status_badge;

            return response()->json([
                'success' => true,
                'message' => 'Master Beban Operasional berhasil ditambahkan',
                'data' => $bebanOperasional,
                'debug' => [
                    'saved_data' => $validated,
                    'created_id' => $bebanOperasional->id,
                    'kode' => $bebanOperasional->kode
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('BebanOperasional Store Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan Beban Operasional: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Beban Operasional for editing
     */
    public function getBebanOperasional($id)
    {
        try {
            $bebanOperasional = BebanOperasional::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $bebanOperasional
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Beban Operasional tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data Beban Operasional: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Beban Operasional
     */
    public function updateBebanOperasional(Request $request, $id)
    {
        try {
            $bebanOperasional = BebanOperasional::findOrFail($id);

            $validated = $request->validate([
                'kategori' => 'required|in:Administrasi,Marketing,Utilitas,Distribusi,Lain-lain',
                'nama_beban' => 'required|string|max:255',
                'budget_bulanan' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:500',
                'status' => 'required|in:aktif,nonaktif'
            ]);

            $bebanOperasional->update($validated);

            // Add formatted fields for response
            $bebanOperasional->budget_bulanan_formatted = $bebanOperasional->budget_bulanan_formatted;
            $bebanOperasional->status_badge = $bebanOperasional->status_badge;

            return response()->json([
                'success' => true,
                'message' => 'Master Beban Operasional berhasil diperbarui',
                'data' => $bebanOperasional
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Beban Operasional tidak ditemukan'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Beban Operasional: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Beban Operasional
     */
    public function deleteBebanOperasional($id)
    {
        try {
            $bebanOperasional = BebanOperasional::findOrFail($id);
            
            // TODO: Add validation for usage in transactions
            // For now, allow delete but in production should check if used in transactions
            // if ($bebanOperasional->transaksiPembayaran()->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Data tidak bisa dihapus karena sudah digunakan pada transaksi pembayaran beban.'
            //     ], 422);
            // }
            
            // Soft delete by setting status to nonaktif instead of hard delete
            $bebanOperasional->update(['status' => 'nonaktif']);

            return response()->json([
                'success' => true,
                'message' => 'Master Beban Operasional berhasil dinonaktifkan'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Master Beban Operasional tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan Master Beban Operasional: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Beban Operasional data with filters
     */
    public function getBebanOperasionalData(Request $request)
    {
        try {
            $query = BebanOperasional::query();

            // Filter by kategori
            if ($request->filled('kategori')) {
                $query->kategori($request->kategori);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by nama beban
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            $bebanOperasional = $query->orderBy('kode', 'asc')->get();
            
            // Add formatted fields for each item
            $bebanOperasional->each(function ($item) {
                $item->budget_bulanan_formatted = $item->budget_bulanan_formatted;
                $item->status_badge = $item->status_badge;
            });

            return response()->json([
                'success' => true,
                'data' => $bebanOperasional,
                'current_filter' => [
                    'kategori' => $request->kategori,
                    'status' => $request->status,
                    'search' => $request->search
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data Beban Operasional: ' . $e->getMessage()
            ], 500);
        }
    }
}