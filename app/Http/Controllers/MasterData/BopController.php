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
            // Auto-fix database if nama_bop_proses column doesn't exist
            $this->ensureDatabaseStructure();

            // Get all BOP Proses with their production process data
            $bopProses = BopProses::with('prosesProduksi')
                ->where('is_active', true)
                ->orderBy('id')
                ->get();

            // Get all production processes (BTKL data) for dropdown
            $prosesProduksis = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
                ->orderBy('kode_proses')
                ->get();

            // Prepare BTKL data for auto-fill functionality
            $btklData = [];
            foreach ($prosesProduksis as $proses) {
                $jabatan = null;
                if ($proses->jabatan_id) {
                    $jabatan = \App\Models\Jabatan::find($proses->jabatan_id);
                }
                
                $tarifBtkl = $proses->tarif_btkl ?? 0;
                
                $btklData[$proses->id] = [
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'tarif_btkl_per_jam' => $tarifBtkl,
                    'nama_btkl' => $proses->nama_proses,
                    'jabatan' => $jabatan ? $jabatan->nama_jabatan : 'Tidak diketahui'
                ];
            }

            // Get all expense accounts (kode 5) as BOP Lainnya candidates
            $akunBeban = Coa::where('kode_akun', 'LIKE', '5%')
                ->orderBy('kode_akun')
                ->get();

            // Transform expense accounts to BOP Lainnya format for display
            $bopLainnya = $akunBeban->map(function($akun) {
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

            $totalBopLainnya = $bopLainnya->sum('budget') ?? 0;
            $jumlahBopLainnya = $bopLainnya->count() ?? 0;

            // Get Beban Operasional master data
            $bebanOperasional = collect([]);
            try {
                if (\Schema::hasTable('beban_operasional')) {
                    $bebanOperasional = \App\Models\BebanOperasional::query()
                        ->orderBy('kode', 'asc')
                        ->get();
                }
            } catch (\Exception $bebanError) {
                \Log::error('Error loading BebanOperasional: ' . $bebanError->getMessage());
            }

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
            \Log::error('Error in BopController@index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Ensure database structure is correct
     */
    private function ensureDatabaseStructure()
    {
        try {
            // Check if nama_bop_proses column exists
            $columns = DB::select("SHOW COLUMNS FROM bop_proses LIKE 'nama_bop_proses'");
            
            if (empty($columns)) {
                // Add the missing column
                DB::statement("ALTER TABLE `bop_proses` ADD COLUMN `nama_bop_proses` VARCHAR(255) NULL AFTER `id`");
                \Log::info('BOP Database - Auto-fixed: Added nama_bop_proses column');
            }
            
            // Check if periode column exists
            $periodeColumns = DB::select("SHOW COLUMNS FROM bop_proses LIKE 'periode'");
            
            if (empty($periodeColumns)) {
                // Add the missing periode column
                DB::statement("ALTER TABLE `bop_proses` ADD COLUMN `periode` VARCHAR(10) NULL");
                \Log::info('BOP Database - Auto-fixed: Added periode column');
            }
            
            // Make proses_produksi_id nullable
            DB::statement("ALTER TABLE `bop_proses` MODIFY COLUMN `proses_produksi_id` BIGINT UNSIGNED NULL");
            
        } catch (\Exception $e) {
            \Log::error('BOP Database - Auto-fix failed: ' . $e->getMessage());
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
     * Get BOP Proses data for editing (AJAX)
     */
    public function getProses($id)
    {
        try {
            $bopProses = BopProses::with('prosesProduksi')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'bop' => $bopProses
            ]);
            
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
        // Debug: Log all request data
        \Log::info('BOP Update - Raw Request Data:', [
            'all_data' => $request->all(),
            'komponen_bop' => $request->input('komponen_bop'),
            'has_komponen_bop' => $request->has('komponen_bop'),
            'is_array' => is_array($request->input('komponen_bop'))
        ]);

        try {
            $validated = $request->validate([
                'komponen_bop' => 'required|array|min:1',
                'komponen_bop.*.component' => 'required|string',
                'komponen_bop.*.rate_per_hour' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('BOP Update - Validation Failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        // Debug: Log validated data
        \Log::info('BOP Update - Validated Data:', $validated);

        try {
            DB::beginTransaction();

            $bopProses = BopProses::findOrFail($id);
            
            // Filter out empty components and validate at least one has rate > 0
            $validComponents = collect($validated['komponen_bop'])->filter(function($component) {
                $hasComponent = !empty($component['component']);
                $rateValue = floatval($component['rate_per_hour'] ?? 0);
                $hasRate = $rateValue > 0;
                
                // Debug log each component
                \Log::info('BOP Update - Checking component:', [
                    'component' => $component,
                    'hasComponent' => $hasComponent,
                    'hasRate' => $hasRate,
                    'rate_value' => $rateValue,
                    'rate_original' => $component['rate_per_hour'] ?? 'NULL'
                ]);
                
                return $hasComponent && $hasRate;
            });

            // Debug: Log valid components
            \Log::info('BOP Update - Valid Components:', [
                'count' => $validComponents->count(),
                'components' => $validComponents->toArray(),
                'all_components_count' => count($validated['komponen_bop'])
            ]);

            if ($validComponents->isEmpty()) {
                $errorMsg = 'Harap isi minimal satu komponen BOP dengan nominal lebih dari 0. ';
                $errorMsg .= 'Total komponen diterima: ' . count($validated['komponen_bop']) . '. ';
                $errorMsg .= 'Komponen valid (rate > 0): ' . $validComponents->count() . '.';
                
                \Log::error('BOP Update - No Valid Components:', [
                    'all_components' => $validated['komponen_bop'],
                    'error_message' => $errorMsg
                ]);
                
                throw new \Exception($errorMsg);
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
        // Log incoming request
        \Log::info('BOP Store - Request received', [
            'nama_bop_proses' => $request->input('nama_bop_proses'),
            'komponen_name' => $request->input('komponen_name'),
            'komponen_rate' => $request->input('komponen_rate'),
            'method' => $request->method()
        ]);

        try {
            // Simplified validation
            $request->validate([
                'nama_bop_proses' => 'required|string|max:255',
                'komponen_name' => 'required|array|min:1',
                'komponen_name.*' => 'required|string|max:255',
                'komponen_rate' => 'required|array|min:1',
                'komponen_rate.*' => 'required|numeric|min:0.01',
            ], [
                'nama_bop_proses.required' => 'Nama BOP Proses wajib diisi',
                'komponen_name.required' => 'Minimal harus ada 1 komponen',
                'komponen_name.*.required' => 'Nama komponen wajib diisi',
                'komponen_rate.required' => 'Rate komponen wajib diisi',
                'komponen_rate.*.required' => 'Rate komponen wajib diisi',
                'komponen_rate.*.min' => 'Rate komponen harus lebih dari 0',
            ]);

            DB::beginTransaction();

            // Build components array
            $components = [];
            $komponenNames = $request->input('komponen_name', []);
            $komponenRates = $request->input('komponen_rate', []);
            $komponenDescs = $request->input('komponen_desc', []);

            foreach ($komponenNames as $index => $name) {
                if (!empty(trim($name)) && isset($komponenRates[$index]) && floatval($komponenRates[$index]) > 0) {
                    $components[] = [
                        'component' => trim($name),
                        'rate_per_hour' => floatval($komponenRates[$index]),
                        'description' => $komponenDescs[$index] ?? ''
                    ];
                }
            }

            if (empty($components)) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Calculate values
            $totalBopPerProduk = array_sum(array_column($components, 'rate_per_hour'));
            $bopPerUnit = $totalBopPerProduk;

            // Prepare data for insert
            $insertData = [
                'nama_bop_proses' => $request->input('nama_bop_proses'),
                'proses_produksi_id' => null,
                'komponen_bop' => $components,
                'total_bop_per_jam' => $totalBopPerProduk,
                'kapasitas_per_jam' => 1,
                'bop_per_unit' => $bopPerUnit,
                'keterangan' => $request->input('keterangan', "BOP untuk {$request->input('nama_bop_proses')}"),
                'is_active' => true,
                'coa_debit_id' => $request->input('coa_debit_id', '1173'), // Default BDP-BOP
                'coa_kredit_id' => $request->input('coa_kredit_id', '210'), // Default Hutang Usaha
            ];
            
            // Add periode if column exists
            $periodeColumns = DB::select("SHOW COLUMNS FROM bop_proses LIKE 'periode'");
            if (!empty($periodeColumns)) {
                $insertData['periode'] = date('Y-m');
            }

            // Create BOP Proses
            $bopProses = BopProses::create($insertData);

            \Log::info('BOP Store - Success', [
                'id' => $bopProses->id,
                'nama_bop_proses' => $bopProses->nama_bop_proses,
                'bop_per_unit' => $bopProses->bop_per_unit,
                'components_count' => count($components)
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil ditambahkan dengan ' . count($components) . ' komponen.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('BOP Store - Validation Error', [
                'errors' => $e->errors()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa kembali data yang dimasukkan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('BOP Store - Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
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
        // Validate form data (same as create)
        $validated = $request->validate([
            'nama_bop_proses' => 'required|string|max:255',
            'komponen_name' => 'required|array|min:1',
            'komponen_name.*' => 'required|string|max:255',
            'komponen_rate' => 'required|array|min:1',
            'komponen_rate.*' => 'required|numeric|min:0.01',
            'komponen_desc' => 'nullable|array',
            'komponen_desc.*' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama_bop_proses.required' => 'Nama BOP Proses wajib diisi',
            'komponen_name.required' => 'Minimal harus ada 1 komponen',
            'komponen_name.*.required' => 'Nama komponen wajib diisi',
            'komponen_rate.required' => 'Minimal harus ada 1 nilai rate',
            'komponen_rate.*.required' => 'Nilai rate wajib diisi',
            'komponen_rate.*.min' => 'Nilai rate harus lebih dari 0',
        ]);

        try {
            DB::beginTransaction();

            $bopProses = BopProses::findOrFail($id);
            
            // Update nama_bop_proses
            $bopProses->nama_bop_proses = $validated['nama_bop_proses'];
            
            // Build components array from form data
            $components = [];
            foreach ($validated['komponen_name'] as $index => $name) {
                $rate = floatval($validated['komponen_rate'][$index] ?? 0);
                $desc = $validated['komponen_desc'][$index] ?? '';
                
                if (!empty(trim($name)) && $rate > 0) {
                    $components[] = [
                        'component' => trim($name),
                        'rate_per_hour' => $rate,
                        'description' => $desc,
                    ];
                }
            }

            if (empty($components)) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Calculate values - using per-product basis
            $totalBopPerProduk = array_sum(array_column($components, 'rate_per_hour'));
            
            // BOP per unit is same as total BOP per produk (no division by capacity)
            $bopPerUnit = $totalBopPerProduk;
            
            // For backward compatibility, store total_bop_per_jam as the per-product total
            $totalBopPerJam = $totalBopPerProduk;

            // Update BOP Proses
            $bopProses->update([
                'komponen_bop' => $components,
                'total_bop_per_jam' => $totalBopPerJam,
                'bop_per_unit' => $bopPerUnit,
                'keterangan' => $validated['keterangan'] ?? null,
                'coa_debit_id' => $request->input('coa_debit_id', $bopProses->coa_debit_id ?? '1173'),
                'coa_kredit_id' => $request->input('coa_kredit_id', $bopProses->coa_kredit_id ?? '210'),
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil diperbarui dengan ' . count($components) . ' komponen.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui BOP Proses: ' . $e->getMessage());
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
                'nama_beban' => 'required|string|max:255',
                'budget_bulanan' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:500',
                'status' => 'required|in:aktif,nonaktif'
            ]);

            // Add created_by
            $validated['created_by'] = auth()->id();
            
            // Don't manually set kode - let the model's booted() method handle it
            // $validated['kode'] = BebanOperasional::generateKode();
            
            // Create the record
            $bebanOperasional = \App\Models\BebanOperasional::create($validated);

            // Refresh to get the generated kode
            $bebanOperasional->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Master Beban Operasional berhasil ditambahkan',
                'data' => [
                    'id' => $bebanOperasional->id,
                    'kode' => $bebanOperasional->kode,
                    'nama_beban' => $bebanOperasional->nama_beban,
                    'budget_bulanan' => $bebanOperasional->budget_bulanan,
                    'budget_bulanan_formatted' => $bebanOperasional->budget_bulanan_formatted,
                    'keterangan' => $bebanOperasional->keterangan,
                    'status' => $bebanOperasional->status,
                    'status_badge' => $bebanOperasional->status_badge,
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
            
            // Check if data is used in transactions
            $usageCount = \App\Models\PembayaranBeban::where('beban_operasional_id', $id)->count();
            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak bisa dihapus karena sudah digunakan pada ' . $usageCount . ' transaksi pembayaran beban.'
                ], 422);
            }
            
            // Hard delete the record
            $bebanOperasional->delete();

            return response()->json([
                'success' => true,
                'message' => 'Master Beban Operasional berhasil dihapus'
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