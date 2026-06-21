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

            // 🔒 MULTI-TENANT: Get BOP Proses for logged-in user only
            $bopProses = BopProses::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('id')
                ->get();


            // 🔒 MULTI-TENANT: Get production processes for logged-in user only
            $prosesProduksis = ProsesProduksi::where('user_id', auth()->id())
                ->orderBy('kode_proses')
                ->get();

            // Prepare BTKL data for auto-fill functionality
            $btklData = [];
            foreach ($prosesProduksis as $proses) {
                $jabatan = null;
                if ($proses->jabatan_id) {
                    $jabatan = \App\Models\Kualifikasi::find($proses->jabatan_id);
                }
                
                $tarifPerProduk = $proses->tarif_per_produk ?? 0;
                $jumlahPegawai = $proses->jumlah_pegawai ?? 1;
                $tarifBtkl = $tarifPerProduk * $jumlahPegawai;
                
                $btklData[$proses->id] = [
                    'tarif_btkl_per_jam' => $tarifBtkl,
                    'nama_btkl' => $proses->nama_proses,
                    'jabatan' => $jabatan ? ($jabatan->nama_kualifikasi ?? $jabatan->nama_kualifikasi) : 'Tidak diketahui'
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

            // 🔒 SECURITY: Get bahan pendukung filtered by user_id
            $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())
                ->orderBy('nama_bahan')
                ->get();

            return view('master-data.bop.index', compact(
                'bopProses',
                'prosesProduksis',
                'bopLainnya',
                'totalBopLainnya',
                'jumlahBopLainnya',
                'akunBeban',
                'btklData',
                'bebanOperasional',
                'bahanPendukungs'
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
        
        // Get all processes (allow both with and without BOP for editing)
        $availableProses = ProsesProduksi::when($prosesId, function($query, $prosesId) {
                return $query->where('id', $prosesId);
            })
            ->orderBy('nama_proses')
            ->get();

        if ($availableProses->isEmpty()) {
            return redirect()->route('master-data.bop.index')
                ->with('warning', 'Tidak ada proses produksi yang tersedia.');
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
            'komponen_bop.*.rate_per_produk' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get BTKL process
            $prosesProduksi = ProsesProduksi::findOrFail($validated['proses_produksi_id']);

            // Filter out empty components and validate at least one has rate > 0
            $validComponents = collect($validated['komponen_bop'])->filter(function($component) {
                return !empty($component['component']) && floatval($component['rate_per_produk']) > 0;
            });

            if ($validComponents->isEmpty()) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Check for duplicate components
            $componentNames = $validComponents->pluck('component')->toArray();
            if (count($componentNames) !== count(array_unique($componentNames))) {
                throw new \Exception('Komponen BOP tidak boleh duplikat.');
            }

            // Calculate values from komponen_bop array (per produk)
            // Use sum but ensure rounding for precision
            $totalBopPerProduk = 0;
            foreach ($validComponents as $component) {
                $totalBopPerProduk += round((float)$component['rate_per_produk'], 2);
            }
            $totalBopPerProduk = round($totalBopPerProduk, 2);
            
            // Calculate BTKL per produk - using new method from main
            $tarifPerProduk = $prosesProduksi->tarif_per_produk ?? 0;
            $jumlahPegawai = $prosesProduksi->jumlah_pegawai ?? 1;
            $btklPerProduk = round((float)$tarifPerProduk * $jumlahPegawai, 2);
            
            $totalBiayaPerProduk = round($btklPerProduk + $totalBopPerProduk, 2);

            // Prepare component data for storage (all components in JSON)
            $komponenData = [];
            foreach ($validComponents as $komponen) {
                $komponenData[] = [
                    'component' => $komponen['component'],
                    'rate_per_produk' => round((float)$komponen['rate_per_produk'], 2),
                    'description' => $komponen['description'] ?? ''
                ];
            }

            // Debug: Log the calculated values
            \Log::info('BOP Debug - Total BOP per Produk: ' . $totalBopPerProduk);
            \Log::info('BOP Debug - BTKL per Produk: ' . $btklPerProduk);
            \Log::info('BOP Debug - Total Biaya per Produk: ' . $totalBiayaPerProduk);
            \Log::info('BOP Debug - Komponen BOP: ' . json_encode($komponenData));

            // Create or Update BOP Proses with new structure
            $bopProses = BopProses::updateOrCreate(
                ['proses_produksi_id' => $validated['proses_produksi_id']],
                [
                    'komponen_bop' => $komponenData,
                    'total_bop_per_produk' => $totalBopPerProduk,
                    'total_biaya_per_produk' => $totalBiayaPerProduk,
                    'bop_per_unit' => $totalBopPerProduk,
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
            $bopProses = BopProses::findOrFail($id);
            
            return view('master-data.bop.show-proses-modal', compact('bopProses'));
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'BOP Proses tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**

* Show form for editing BOP Proses
     */
    public function editProses($id)
    {
        try {
            $bopProses = BopProses::findOrFail($id);
            return view('master-data.bop.edit-proses', compact('bopProses'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing BOP Proses V2
     */
    public function editProsesV2($id)
    {
        try {
            $bopProses = BopProses::findOrFail($id);
            $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())->get();
            
            return view('master-data.bop.edit-proses-v2', compact('bopProses', 'bahanPendukungs'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.bop.index')
                ->with('error', 'BOP Proses tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update BOP Proses V2
     */
    public function updateProsesV2(Request $request, $id)
    {
        \Log::info('BOP V2 Update - Request received', [
            'id' => $id,
            'nama_bop_proses' => $request->input('nama_bop_proses'),
            'jumlah_produksi_perbulan' => $request->input('jumlah_produksi_perbulan'),
            'bahan_pendukung' => $request->input('bahan_pendukung'),
            'bop_lainnya' => $request->input('bop_lainnya'),
        ]);

        try {
            $validated = $request->validate([
                'nama_bop_proses' => 'required|string|max:255',
                'jumlah_produksi_perbulan' => 'required|integer|min:1',
                'bahan_pendukung' => 'nullable|array',
                'bahan_pendukung.*.bahan_pendukung_id' => 'required_with:bahan_pendukung|exists:bahan_pendukungs,id',
                'bahan_pendukung.*.satuan' => 'required_with:bahan_pendukung|string',
                'bahan_pendukung.*.harga_satuan' => 'required_with:bahan_pendukung|numeric|min:0',
                'bahan_pendukung.*.qty_penggunaan_bulan' => 'required_with:bahan_pendukung|numeric|min:0',
                'bahan_pendukung.*.coa_debit' => 'nullable|string',
                'bahan_pendukung.*.coa_kredit' => 'nullable|string',
                'bop_lainnya' => 'nullable|array',
                'bop_lainnya.*.nama_komponen' => 'required_with:bop_lainnya|string|max:255',
                'bop_lainnya.*.nominal_per_bulan' => 'required_with:bop_lainnya|numeric|min:0',
                'bop_lainnya.*.coa_debit' => 'nullable|string',
                'bop_lainnya.*.coa_kredit' => 'nullable|string',
                'bop_lainnya.*.keterangan' => 'nullable|string',
            ], [
                'nama_bop_proses.required' => 'Nama BOP Proses wajib diisi',
                'jumlah_produksi_perbulan.required' => 'Jumlah Produksi Per Bulan wajib diisi',
                'jumlah_produksi_perbulan.min' => 'Jumlah Produksi Per Bulan minimal 1',
                'bahan_pendukung.*.bahan_pendukung_id.required_with' => 'Bahan pendukung harus dipilih',
                'bahan_pendukung.*.bahan_pendukung_id.exists' => 'Bahan pendukung tidak ditemukan',
                'bahan_pendukung.*.qty_penggunaan_bulan.required_with' => 'Qty Penggunaan Per Bulan wajib diisi',
                'bahan_pendukung.*.qty_penggunaan_bulan.min' => 'Qty Penggunaan Per Bulan harus >= 0',
                'bop_lainnya.*.nama_komponen.required_with' => 'Nama komponen BOP Lainnya wajib diisi',
                'bop_lainnya.*.nominal_per_bulan.required_with' => 'Nominal Per Bulan wajib diisi',
            ]);

            DB::beginTransaction();

            $bopProses = BopProses::findOrFail($id);

            // Prepare komponen bahan pendukung array with calculations
            $komponenBahanPendukung = [];
            $jumlahProduksi = (int) $validated['jumlah_produksi_perbulan'];
            
            if (!empty($validated['bahan_pendukung'])) {
                foreach ($validated['bahan_pendukung'] as $index => $item) {
                    $bahanPendukungId = (int) $item['bahan_pendukung_id'];
                    $hargaSatuan = (float) $item['harga_satuan'];
                    $qtyPenggunaan = (float) $item['qty_penggunaan_bulan'];
                    
                    // Skip if qty is 0
                    if ($qtyPenggunaan <= 0) {
                        continue;
                    }
                    
                    // Calculate: Total Nominal/Bulan = Harga Satuan × Qty Penggunaan
                    $totalNominalBulan = $hargaSatuan * $qtyPenggunaan;
                    
                    // Calculate: Rp/Produk = Total Nominal ÷ Jumlah Produksi (ROUNDED)
                    $rpPerProdukRaw = $jumlahProduksi > 0 ? $totalNominalBulan / $jumlahProduksi : 0;
                    $rpPerProduk = round($rpPerProdukRaw); // Round to nearest integer
                    
                    // Get bahan pendukung data for nama
                    $bahan = \App\Models\BahanPendukung::find($bahanPendukungId);
                    
                    $komponenBahanPendukung[] = [
                        'bahan_pendukung_id' => $bahanPendukungId,
                        'nama' => $bahan->nama_bahan ?? 'Unknown',
                        'satuan' => $item['satuan'],
                        'harga_satuan' => round($hargaSatuan, 2),
                        'qty_penggunaan_bulan' => round($qtyPenggunaan, 2),
                        'total_nominal_bulan' => round($totalNominalBulan, 2),
                        'total' => $rpPerProduk, // Changed from rp_per_produk to total for model compatibility
                        'coa_debit' => $item['coa_debit'] ?? null,
                        'coa_kredit' => $item['coa_kredit'] ?? null,
                    ];
                    
                    \Log::info('BOP V2 - Bahan Pendukung processed', [
                        'id' => $bahanPendukungId,
                        'nama' => $bahan->nama_bahan ?? 'Unknown',
                        'harga_satuan' => $hargaSatuan,
                        'qty_penggunaan' => $qtyPenggunaan,
                        'total_nominal' => $totalNominalBulan,
                        'rp_per_produk' => $rpPerProduk,
                    ]);
                }
            }

            // Prepare komponen lainnya array with calculations
            $komponenLainnya = [];
            
            if (!empty($validated['bop_lainnya'])) {
                foreach ($validated['bop_lainnya'] as $index => $item) {
                    $namaKomponen = trim($item['nama_komponen']);
                    $nominalPerBulan = (float) $item['nominal_per_bulan'];
                    $keterangan = $item['keterangan'] ?? '';
                    
                    // Skip if nominal is 0
                    if ($nominalPerBulan <= 0) {
                        continue;
                    }
                    
                    // Calculate: Rp/Produk = Nominal Per Bulan ÷ Jumlah Produksi (ROUNDED)
                    $rpPerProdukRaw = $jumlahProduksi > 0 ? $nominalPerBulan / $jumlahProduksi : 0;
                    $rpPerProduk = round($rpPerProdukRaw); // Round to nearest integer
                    
                    $komponenLainnya[] = [
                        'nama_komponen' => $namaKomponen,
                        'nominal_per_bulan' => round($nominalPerBulan, 2),
                        'nilai_per_produk' => $rpPerProduk, // Changed from rp_per_produk to nilai_per_produk for model compatibility
                        'coa_debit' => $item['coa_debit'] ?? null,
                        'coa_kredit' => $item['coa_kredit'] ?? null,
                        'keterangan' => $keterangan,
                    ];
                    
                    \Log::info('BOP V2 - Komponen Lainnya processed', [
                        'nama' => $namaKomponen,
                        'nominal_per_bulan' => $nominalPerBulan,
                        'rp_per_produk' => $rpPerProduk,
                    ]);
                }
            }

            // Validate at least one component (bahan pendukung OR lainnya)
            if (empty($komponenBahanPendukung) && empty($komponenLainnya)) {
                throw new \Exception('Harap isi minimal satu komponen (Bahan Pendukung atau BOP Lainnya) dengan nominal lebih dari 0.');
            }

            // Calculate total BOP per produk (sum of ROUNDED values)
            $totalBopBahanPendukung = array_sum(array_column($komponenBahanPendukung, 'total'));
            $totalBopLainnya = array_sum(array_column($komponenLainnya, 'nilai_per_produk'));
            $totalBopPerProduk = $totalBopBahanPendukung + $totalBopLainnya;

            // Update data
            $bopProses->nama_bop_proses = $validated['nama_bop_proses'];
            $bopProses->komponen_bahan_pendukung = !empty($komponenBahanPendukung) ? $komponenBahanPendukung : null;
            $bopProses->komponen_lainnya = !empty($komponenLainnya) ? $komponenLainnya : null;
            $bopProses->jumlah_produksi_perbulan = $jumlahProduksi;
            $bopProses->total_bop_per_produk = $totalBopPerProduk;
            $bopProses->keterangan = "BOP Proses V2 - Auto-Calculation (Updated)";
            $bopProses->is_active = true;
            $bopProses->save();

            \Log::info('BOP V2 Update - Success', [
                'id' => $bopProses->id,
                'nama_bop_proses' => $bopProses->nama_bop_proses,
                'jumlah_produksi_perbulan' => $jumlahProduksi,
                'total_bop_bahan_pendukung' => $totalBopBahanPendukung,
                'total_bop_lainnya' => $totalBopLainnya,
                'total_bop_per_produk' => $totalBopPerProduk,
                'bahan_pendukung_count' => count($komponenBahanPendukung),
                'lainnya_count' => count($komponenLainnya),
            ]);

            DB::commit();

            $message = 'BOP Proses V2 berhasil diupdate';
            if (!empty($komponenBahanPendukung) && !empty($komponenLainnya)) {
                $message .= ' dengan ' . count($komponenBahanPendukung) . ' bahan pendukung dan ' . count($komponenLainnya) . ' komponen lainnya.';
            } elseif (!empty($komponenBahanPendukung)) {
                $message .= ' dengan ' . count($komponenBahanPendukung) . ' bahan pendukung.';
            } elseif (!empty($komponenLainnya)) {
                $message .= ' dengan ' . count($komponenLainnya) . ' komponen lainnya.';
            }

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('BOP V2 Update - Validation Error', [
                'errors' => $e->errors()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa input Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('BOP V2 Update - Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate BOP Proses V2: ' . $e->getMessage());
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
            $totalBopPerProduk = $validComponents->sum('rate_per_produk');
            $bopPerUnit = $totalBopPerProduk;

            // Update BOP Proses with JSON data
            $bopProses->update([
                'komponen_bop' => $validComponents->values()->all(),
                'total_bop_per_produk' => $totalBopPerProduk,
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
     * Sync data from BTKL for all BOP Proses (deprecated - no longer needed)
     */
    public function syncKapasitas()
    {
        try {
            // This method is deprecated as we no longer use kapasitas_per_jam
            return redirect()
                ->route('master-data.bop.index')
                ->with('info', 'Sync kapasitas tidak diperlukan lagi karena sistem sudah menggunakan pembebanan per produk');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
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
                'komponen_name.*' => 'required',
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

            // ========================================
            // SEPARATE: BAHAN PENDUKUNG & LAINNYA
            // ========================================
            $komponenBahanPendukung = [];
            $komponenLainnya = [];
            
            $komponenNames = $request->input('komponen_name', []);
            $komponenRates = $request->input('komponen_rate', []);
            $komponenDescs = $request->input('komponen_desc', []);
            $komponenCoaDebits = $request->input('komponen_coa_debit', []);
            $komponenCoaKredits = $request->input('komponen_coa_kredit', []);

            // Get all bahan pendukung untuk matching
            $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())->get();
            
            foreach ($komponenNames as $index => $name) {
                $rate = round((float)($komponenRates[$index] ?? 0), 2);
                
                if (empty($name) || $rate <= 0) {
                    continue; // Skip empty or zero rate
                }
                
                // Check if this is a bahan pendukung ID (numeric)
                if (is_numeric($name)) {
                    // This is from dropdown (bahan pendukung)
                    $bahan = $bahanPendukungs->firstWhere('id', $name);
                    
                    if ($bahan) {
                        $komponenBahanPendukung[] = [
                            'bahan_pendukung_id' => (int)$bahan->id,
                            'nama' => $bahan->nama_bahan,
                            'qty_per_produk' => 1, // Default 1, bisa diubah nanti jika perlu
                            'harga_satuan' => $rate,
                            'total' => $rate,
                            'coa_debit' => $komponenCoaDebits[$index] ?? '1173',
                            'coa_kredit' => $komponenCoaKredits[$index] ?? '530',
                            'keterangan' => $komponenDescs[$index] ?? ''
                        ];
                        
                        \Log::info('BOP - Bahan Pendukung detected', [
                            'id' => $bahan->id,
                            'nama' => $bahan->nama_bahan,
                            'rate' => $rate
                        ]);
                    }
                } else {
                    // This is manual input (komponen lainnya)
                    $komponenLainnya[] = [
                        'nama_komponen' => trim($name),
                        'nilai_per_produk' => $rate,
                        'coa_debit' => $komponenCoaDebits[$index] ?? '1173',
                        'coa_kredit' => $komponenCoaKredits[$index] ?? '550',
                        'keterangan' => $komponenDescs[$index] ?? ''
                    ];
                    
                    \Log::info('BOP - Komponen Lainnya detected', [
                        'nama' => trim($name),
                        'rate' => $rate
                    ]);
                }
            }

            // Validate at least one component
            if (empty($komponenBahanPendukung) && empty($komponenLainnya)) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Calculate totals
            $totalBahanPendukung = array_sum(array_column($komponenBahanPendukung, 'total'));
            $totalLainnya = array_sum(array_column($komponenLainnya, 'nilai_per_produk'));
            $totalBopPerProduk = round($totalBahanPendukung + $totalLainnya, 2);

            // Prepare data for insert
            $insertData = [
                'user_id' => auth()->id(),
                'nama_bop_proses' => $request->input('nama_bop_proses') ?: 'BOP Proses',
                'komponen_bahan_pendukung' => !empty($komponenBahanPendukung) ? $komponenBahanPendukung : null,
                'komponen_lainnya' => !empty($komponenLainnya) ? $komponenLainnya : null,
                'total_bop_per_produk' => $totalBopPerProduk,
                'keterangan' => $request->input('keterangan') ?: "BOP Proses",
                'is_active' => true,
            ];

            // Create BOP Proses
            $bopProses = BopProses::create($insertData);

            \Log::info('BOP Store - Success', [
                'id' => $bopProses->id,
                'nama_bop_proses' => $bopProses->nama_bop_proses,
                'total_bop_per_produk' => $totalBopPerProduk,
                'bahan_pendukung_count' => count($komponenBahanPendukung),
                'lainnya_count' => count($komponenLainnya),
                'komponen_bahan_pendukung' => $komponenBahanPendukung,
                'komponen_lainnya' => $komponenLainnya
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil ditambahkan dengan ' . 
                    count($komponenBahanPendukung) . ' bahan pendukung dan ' . 
                    count($komponenLainnya) . ' komponen lainnya.');

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
            'komponen_name.*' => 'required',
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
            
            // ========================================
            // SEPARATE: BAHAN PENDUKUNG & LAINNYA
            // ========================================
            $komponenBahanPendukung = [];
            $komponenLainnya = [];
            
            $komponenNames = $validated['komponen_name'];
            $komponenRates = $validated['komponen_rate'];
            $komponenDescs = $validated['komponen_desc'] ?? [];
            $komponenCoaDebits = $request->input('komponen_coa_debit', []);
            $komponenCoaKredits = $request->input('komponen_coa_kredit', []);

            // Get all bahan pendukung untuk matching
            $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())->get();
            
            foreach ($komponenNames as $index => $name) {
                $rate = round((float)($komponenRates[$index] ?? 0), 2);
                
                if (empty($name) || $rate <= 0) {
                    continue; // Skip empty or zero rate
                }
                
                // Check if this is a bahan pendukung ID (numeric)
                if (is_numeric($name)) {
                    // This is from dropdown (bahan pendukung)
                    $bahan = $bahanPendukungs->firstWhere('id', $name);
                    
                    if ($bahan) {
                        $komponenBahanPendukung[] = [
                            'bahan_pendukung_id' => (int)$bahan->id,
                            'nama' => $bahan->nama_bahan,
                            'qty_per_produk' => 1, // Default 1
                            'harga_satuan' => $rate,
                            'total' => $rate,
                            'coa_debit' => $komponenCoaDebits[$index] ?? '1173',
                            'coa_kredit' => $komponenCoaKredits[$index] ?? '530',
                            'keterangan' => $komponenDescs[$index] ?? ''
                        ];
                    }
                } else {
                    // This is manual input (komponen lainnya)
                    $komponenLainnya[] = [
                        'nama_komponen' => trim($name),
                        'nilai_per_produk' => $rate,
                        'coa_debit' => $komponenCoaDebits[$index] ?? '1173',
                        'coa_kredit' => $komponenCoaKredits[$index] ?? '550',
                        'keterangan' => $komponenDescs[$index] ?? ''
                    ];
                }
            }

            // Validate at least one component
            if (empty($komponenBahanPendukung) && empty($komponenLainnya)) {
                throw new \Exception('Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.');
            }

            // Calculate totals
            $totalBahanPendukung = array_sum(array_column($komponenBahanPendukung, 'total'));
            $totalLainnya = array_sum(array_column($komponenLainnya, 'nilai_per_produk'));
            $totalBopPerProduk = round($totalBahanPendukung + $totalLainnya, 2);

            // Update BOP Proses
            $updateData = [
                'nama_bop_proses' => $validated['nama_bop_proses'],
                'komponen_bahan_pendukung' => !empty($komponenBahanPendukung) ? $komponenBahanPendukung : null,
                'komponen_lainnya' => !empty($komponenLainnya) ? $komponenLainnya : null,
                'total_bop_per_produk' => $totalBopPerProduk,
                'keterangan' => $validated['keterangan'] ?? null,
            ];
            
            $bopProses->update($updateData);

            \Log::info('BOP Update - Success', [
                'id' => $bopProses->id,
                'nama_bop_proses' => $bopProses->nama_bop_proses,
                'total_bop_per_produk' => $totalBopPerProduk,
                'bahan_pendukung_count' => count($komponenBahanPendukung),
                'lainnya_count' => count($komponenLainnya),
            ]);

            DB::commit();

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', 'BOP Proses berhasil diperbarui dengan ' . 
                    count($komponenBahanPendukung) . ' bahan pendukung dan ' . 
                    count($komponenLainnya) . ' komponen lainnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('BOP Update - Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
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
            $bopProses = BopProses::findOrFail($id);
            
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

    /**
     * Show form for creating BOP Proses V2 (Bahan Pendukung dengan auto-calculation)
     */
    public function createProsesV2()
    {
        try {
            // 🔒 SECURITY: Get bahan pendukung filtered by user_id
            $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', auth()->id())
                ->with('satuan')
                ->orderBy('nama_bahan')
                ->get();

            return view('master-data.bop.create-proses-v2', compact('bahanPendukungs'));
            
        } catch (\Exception $e) {
            \Log::error('Error in BopController@createProsesV2: ' . $e->getMessage());
            
            return redirect()
                ->route('master-data.bop.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store BOP Proses V2 (Bahan Pendukung dengan auto-calculation)
     */
    public function storeProsesV2(Request $request)
    {
        \Log::info('BOP V2 Store - Request received', [
            'nama_bop_proses' => $request->input('nama_bop_proses'),
            'jumlah_produksi_perbulan' => $request->input('jumlah_produksi_perbulan'),
            'bahan_pendukung' => $request->input('bahan_pendukung'),
            'bop_lainnya' => $request->input('bop_lainnya'),
        ]);

        try {
            $validated = $request->validate([
                'nama_bop_proses' => 'required|string|max:255',
                'jumlah_produksi_perbulan' => 'required|integer|min:1',
                'bahan_pendukung' => 'nullable|array',
                'bahan_pendukung.*.bahan_pendukung_id' => 'required_with:bahan_pendukung|exists:bahan_pendukungs,id',
                'bahan_pendukung.*.satuan' => 'required_with:bahan_pendukung|string',
                'bahan_pendukung.*.harga_satuan' => 'required_with:bahan_pendukung|numeric|min:0',
                'bahan_pendukung.*.qty_penggunaan_bulan' => 'required_with:bahan_pendukung|numeric|min:0',
                'bahan_pendukung.*.coa_debit' => 'nullable|string',
                'bahan_pendukung.*.coa_kredit' => 'nullable|string',
                'bop_lainnya' => 'nullable|array',
                'bop_lainnya.*.nama_komponen' => 'required_with:bop_lainnya|string|max:255',
                'bop_lainnya.*.nominal_per_bulan' => 'required_with:bop_lainnya|numeric|min:0',
                'bop_lainnya.*.coa_debit' => 'nullable|string',
                'bop_lainnya.*.coa_kredit' => 'nullable|string',
                'bop_lainnya.*.keterangan' => 'nullable|string',
            ], [
                'nama_bop_proses.required' => 'Nama BOP Proses wajib diisi',
                'jumlah_produksi_perbulan.required' => 'Jumlah Produksi Per Bulan wajib diisi',
                'jumlah_produksi_perbulan.min' => 'Jumlah Produksi Per Bulan minimal 1',
                'bahan_pendukung.*.bahan_pendukung_id.required_with' => 'Bahan pendukung harus dipilih',
                'bahan_pendukung.*.bahan_pendukung_id.exists' => 'Bahan pendukung tidak ditemukan',
                'bahan_pendukung.*.qty_penggunaan_bulan.required_with' => 'Qty Penggunaan Per Bulan wajib diisi',
                'bahan_pendukung.*.qty_penggunaan_bulan.min' => 'Qty Penggunaan Per Bulan harus >= 0',
                'bop_lainnya.*.nama_komponen.required_with' => 'Nama komponen BOP Lainnya wajib diisi',
                'bop_lainnya.*.nominal_per_bulan.required_with' => 'Nominal Per Bulan wajib diisi',
            ]);

            DB::beginTransaction();

            // Prepare komponen bahan pendukung array with calculations
            $komponenBahanPendukung = [];
            $jumlahProduksi = (int) $validated['jumlah_produksi_perbulan'];
            
            if (!empty($validated['bahan_pendukung'])) {
                foreach ($validated['bahan_pendukung'] as $index => $item) {
                    $bahanPendukungId = (int) $item['bahan_pendukung_id'];
                    $hargaSatuan = (float) $item['harga_satuan'];
                    $qtyPenggunaan = (float) $item['qty_penggunaan_bulan'];
                    
                    // Skip if qty is 0
                    if ($qtyPenggunaan <= 0) {
                        continue;
                    }
                    
                    // Calculate: Total Nominal/Bulan = Harga Satuan × Qty Penggunaan
                    $totalNominalBulan = $hargaSatuan * $qtyPenggunaan;
                    
                    // Calculate: Rp/Produk = Total Nominal ÷ Jumlah Produksi (ROUNDED)
                    $rpPerProdukRaw = $jumlahProduksi > 0 ? $totalNominalBulan / $jumlahProduksi : 0;
                    $rpPerProduk = round($rpPerProdukRaw); // Round to nearest integer
                    
                    // Get bahan pendukung data for nama
                    $bahan = \App\Models\BahanPendukung::find($bahanPendukungId);
                    
                    $komponenBahanPendukung[] = [
                        'bahan_pendukung_id' => $bahanPendukungId,
                        'nama' => $bahan->nama_bahan ?? 'Unknown',
                        'satuan' => $item['satuan'],
                        'harga_satuan' => round($hargaSatuan, 2),
                        'qty_penggunaan_bulan' => round($qtyPenggunaan, 2),
                        'total_nominal_bulan' => round($totalNominalBulan, 2),
                        'total' => $rpPerProduk, // Changed from rp_per_produk to total for model compatibility
                        'coa_debit' => $item['coa_debit'] ?? null,
                        'coa_kredit' => $item['coa_kredit'] ?? null,
                    ];
                    
                    \Log::info('BOP V2 - Bahan Pendukung processed', [
                        'id' => $bahanPendukungId,
                        'nama' => $bahan->nama_bahan ?? 'Unknown',
                        'harga_satuan' => $hargaSatuan,
                        'qty_penggunaan' => $qtyPenggunaan,
                        'total_nominal' => $totalNominalBulan,
                        'rp_per_produk' => $rpPerProduk,
                    ]);
                }
            }

            // Prepare komponen lainnya array with calculations
            $komponenLainnya = [];
            
            if (!empty($validated['bop_lainnya'])) {
                foreach ($validated['bop_lainnya'] as $index => $item) {
                    $namaKomponen = trim($item['nama_komponen']);
                    $nominalPerBulan = (float) $item['nominal_per_bulan'];
                    $keterangan = $item['keterangan'] ?? '';
                    
                    // Skip if nominal is 0
                    if ($nominalPerBulan <= 0) {
                        continue;
                    }
                    
                    // Calculate: Rp/Produk = Nominal Per Bulan ÷ Jumlah Produksi (ROUNDED)
                    $rpPerProdukRaw = $jumlahProduksi > 0 ? $nominalPerBulan / $jumlahProduksi : 0;
                    $rpPerProduk = round($rpPerProdukRaw); // Round to nearest integer
                    
                    $komponenLainnya[] = [
                        'nama_komponen' => $namaKomponen,
                        'nominal_per_bulan' => round($nominalPerBulan, 2),
                        'nilai_per_produk' => $rpPerProduk, // Changed from rp_per_produk to nilai_per_produk for model compatibility
                        'coa_debit' => $item['coa_debit'] ?? null,
                        'coa_kredit' => $item['coa_kredit'] ?? null,
                        'keterangan' => $keterangan,
                    ];
                    
                    \Log::info('BOP V2 - Komponen Lainnya processed', [
                        'nama' => $namaKomponen,
                        'nominal_per_bulan' => $nominalPerBulan,
                        'rp_per_produk' => $rpPerProduk,
                    ]);
                }
            }

            // Validate at least one component (bahan pendukung OR lainnya)
            if (empty($komponenBahanPendukung) && empty($komponenLainnya)) {
                throw new \Exception('Harap isi minimal satu komponen (Bahan Pendukung atau BOP Lainnya) dengan nominal lebih dari 0.');
            }

            // Calculate total BOP per produk (sum of ROUNDED values)
            $totalBopBahanPendukung = array_sum(array_column($komponenBahanPendukung, 'rp_per_produk'));
            $totalBopLainnya = array_sum(array_column($komponenLainnya, 'rp_per_produk'));
            $totalBopPerProduk = $totalBopBahanPendukung + $totalBopLainnya;

            // Prepare data for insert
            $insertData = [
                'user_id' => auth()->id(),
                'nama_bop_proses' => $validated['nama_bop_proses'],
                'komponen_bahan_pendukung' => !empty($komponenBahanPendukung) ? $komponenBahanPendukung : null,
                'komponen_lainnya' => !empty($komponenLainnya) ? $komponenLainnya : null,
                'jumlah_produksi_perbulan' => $jumlahProduksi,
                'total_bop_per_produk' => $totalBopPerProduk,
                'keterangan' => "BOP Proses V2 - Auto-Calculation",
                'is_active' => true,
            ];

            // Create BOP Proses
            $bopProses = BopProses::create($insertData);

            \Log::info('BOP V2 Store - Success', [
                'id' => $bopProses->id,
                'nama_bop_proses' => $bopProses->nama_bop_proses,
                'jumlah_produksi_perbulan' => $jumlahProduksi,
                'total_bop_bahan_pendukung' => $totalBopBahanPendukung,
                'total_bop_lainnya' => $totalBopLainnya,
                'total_bop_per_produk' => $totalBopPerProduk,
                'bahan_pendukung_count' => count($komponenBahanPendukung),
                'lainnya_count' => count($komponenLainnya),
            ]);

            DB::commit();

            $message = 'BOP Proses V2 berhasil ditambahkan';
            if (!empty($komponenBahanPendukung) && !empty($komponenLainnya)) {
                $message .= ' dengan ' . count($komponenBahanPendukung) . ' bahan pendukung dan ' . count($komponenLainnya) . ' komponen lainnya.';
            } elseif (!empty($komponenBahanPendukung)) {
                $message .= ' dengan ' . count($komponenBahanPendukung) . ' bahan pendukung.';
            } elseif (!empty($komponenLainnya)) {
                $message .= ' dengan ' . count($komponenLainnya) . ' komponen lainnya.';
            }

            return redirect()
                ->route('master-data.bop.index')
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('BOP V2 Store - Validation Error', [
                'errors' => $e->errors()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa input Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('BOP V2 Store - Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambah BOP Proses V2: ' . $e->getMessage());
        }
    }
}