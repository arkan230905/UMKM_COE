<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BiayaBahanBaku;
use App\Models\HargaPokokProduksiBiayaBahanBaku;
use App\Models\HargaPokokProduksiBtkl;
use App\Models\HargaPokokProduksiBop;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get all HPP records for current user
        $hppRecords = $this->getHppRecords();
        
        // Filter by product name
        if ($request->filled('nama_produk')) {
            $hppRecords = $hppRecords->filter(function($record) {
                return stripos($record['nama_produk'], $request->nama_produk) !== false;
            });
        }
        
        // Paginate results
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $total = $hppRecords->count();
        $sliced = $hppRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $hppRecords = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        return view('master-data.bom.index', compact('hppRecords'));
    }

    public function create()
    {
        try {
            // Run the improved BOM fix command that uses actual stock report prices
            Artisan::call('bom:fix-from-actual-stock');
            
            // Return fresh calculated data
            return $this->calculateBomCost($produkId);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating BOM from stock report: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get BOM details for AJAX requests (production create page)
     */
    public function getBomDetails($produkId)
    {
        try {
            $produk = Produk::findOrFail($produkId);
            
            // Get BOM data similar to show method
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produkId)
                ->with([
                    'detailBBB' => function($query) {
                        $query->with('bahanBaku.satuan');
                    },
                    'detailBTKL' => function($query) {
                        $query->with('btkl.jabatan');
                    },
                    'detailBahanPendukung' => function($query) {
                        $query->with('bahanPendukung.satuan');
                    },
                    'detailBOP' => function($query) {
                        $query->with('bop');
                    }
                ])
                ->first();

            if (!$bomJobCosting) {
                return response()->json(['success' => false, 'message' => 'BOM not found for this product'])
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }

            // Use the same breakdown logic as production controller
            $breakdown = $this->getProductionCostBreakdownForProduk($produkId, $bomJobCosting);
            
            return response()->json(['success' => true, 'breakdown' => $breakdown])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            
        } catch (\Exception $e) {
            \Log::error('getBomDetails error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
    }

    /**
     * Get production cost breakdown for a product (similar to ProduksiController)
     */
    private function getProductionCostBreakdownForProduk($produkId, $bomJobCosting)
    {
        $breakdown = [
            'biaya_bahan' => [
                'bahan_baku' => [],
                'bahan_pendukung' => [],
                'total' => 0
            ],
            'btkl' => [],
            'bop' => []
        ];

        // Get Bahan Baku from BOM
        if ($bomJobCosting && $bomJobCosting->detailBBB) {
            foreach ($bomJobCosting->detailBBB as $detail) {
                $bahanBaku = $detail->bahanBaku;
                // Subtotal is already the cost per product, use it directly
                $biayaPerProduk = $detail->subtotal;
                
                $breakdown['biaya_bahan']['bahan_baku'][] = [
                    'nama' => $bahanBaku->nama_bahan,
                    'qty' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? $bahanBaku->satuan->nama ?? 'Unit',
                    'harga_per_unit' => $biayaPerProduk
                ];
                $breakdown['biaya_bahan']['total'] += $biayaPerProduk;
            }
        }

        // Get Bahan Pendukung from BOM (displayed separately, not added to biaya_bahan total)
        if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
            foreach ($bomJobCosting->detailBahanPendukung as $detail) {
                $bahanPendukung = $detail->bahanPendukung;
                // Subtotal is already the cost per product, use it directly
                $biayaPerProduk = $detail->subtotal;
                
                $breakdown['biaya_bahan']['bahan_pendukung'][] = [
                    'nama' => $bahanPendukung->nama_bahan,
                    'qty' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? $bahanPendukung->satuan->nama ?? 'Unit',
                    'harga_per_unit' => $biayaPerProduk
                ];
                // NOTE: Bahan pendukung TIDAK ditambahkan ke total biaya_bahan
                // Sesuai permintaan user, biaya bahan baku HANYA menghitung bahan baku
            }
        }

        // Get BTKL from BOM
        if ($bomJobCosting) {
            foreach ($bomJobCosting->detailBTKL as $detail) {
                // Use tarif_per_jam and kapasitas_per_jam to calculate per unit cost
                if ($detail->tarif_per_jam > 0 && $detail->kapasitas_per_jam > 0) {
                    $hargaPerUnit = $detail->tarif_per_jam / $detail->kapasitas_per_jam;
                } elseif ($detail->btkl && $detail->btkl->tarif_per_jam > 0 && $detail->kapasitas_per_jam > 0) {
                    $hargaPerUnit = $detail->btkl->tarif_per_jam / $detail->kapasitas_per_jam;
                } else {
                    $hargaPerUnit = 0;
                }
                
                $namaProses = $detail->nama_proses ?? ($detail->btkl ? $detail->btkl->nama_btkl : 'Proses Tidak Diketahui');
                
                $breakdown['btkl'][] = [
                    'nama' => $namaProses,
                    'harga_per_unit' => $hargaPerUnit
                ];
            }
        }

        // Get BOP from BOM - Group by process like in detail page
        if ($bomJobCosting) {
            $bopByProcess = [];
            
            // Get BOP data from bom_job_bop table
            $bopDataRaw = \Illuminate\Support\Facades\DB::table('bom_job_bop')
                ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id)
                ->select('bom_job_bop.*')
                ->get();
            
            // Group BOP components by process
            foreach ($bopDataRaw as $item) {
                $namaProses = 'Umum';
                $namaBiaya = strtolower($item->nama_bop ?? '');
                
                if (stripos($namaBiaya, 'penggorengan') !== false) {
                    $namaProses = 'Penggorengan';
                } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                    $namaProses = 'Perbumbuan';
                } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                    $namaProses = 'Pengemasan';
                }
                
                if (!isset($bopByProcess[$namaProses])) {
                    $bopByProcess[$namaProses] = 0;
                }
                
                $bopByProcess[$namaProses] += $item->tarif ?? 0;
            }
            
            // Convert to array format expected by frontend
            foreach ($bopByProcess as $processName => $totalCost) {
                $breakdown['bop'][] = [
                    'nama' => $processName,
                    'harga_per_unit' => $totalCost
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Get breakdown from regular BOM (fallback method)
     */
    private function getBreakdownFromRegularBom($bom)
    {
        $breakdown = [
            'biaya_bahan' => [
                'bahan_baku' => [],
                'bahan_pendukung' => [],
                'total' => 0
            ],
            'btkl' => [],
            'bop' => []
        ];

        // Get materials from regular BOM details
        if ($bom && $bom->details) {
            foreach ($bom->details as $detail) {
                if ($detail->bahan_baku_id) {
                    $bahanBaku = $detail->bahanBaku;
                    $hargaPerUnit = $detail->jumlah > 0 ? $detail->subtotal / $detail->jumlah : 0;
                    
                    $breakdown['biaya_bahan']['bahan_baku'][] = [
                        'nama' => $bahanBaku->nama_bahan,
                        'qty' => $detail->jumlah,
                        'satuan' => $detail->satuan ?? $bahanBaku->satuan->nama ?? 'Unit',
                        'harga_per_unit' => $hargaPerUnit
                    ];
                    $breakdown['biaya_bahan']['total'] += $hargaPerUnit;
                }
            }
        }

        return $breakdown;
    }

    public function create(Request $request)
    {
        try {
            // Get products that have biaya bahan (can recalculate HPP)
            // Allow recalculation for any product that has material costs
            $produks = Produk::whereHas('bomJobCosting', function($query) {
                $query->where(function($q) {
                    $q->where('total_bbb', '>', 0)
                      ->orWhere('total_bahan_pendukung', '>', 0);
                });
            })->with('bomJobCosting')->get();
            
            if ($produks->isEmpty()) {
                return redirect()->route('master-data.harga-pokok-produksi.index')
                    ->with('info', 'Tidak ada produk dengan biaya bahan yang dapat dihitung HPP-nya. Pastikan produk sudah memiliki data BOM dengan biaya bahan.');
            }
            
            // Get all BTKL processes
            $prosesBtkl = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
                ->with(['jabatan'])
                ->get()
                ->map(function($proses) {
                    // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                    $jumlahPegawai = 0;
                    $tarifPerJamJabatan = 0;
                    
                    if ($proses->jabatan) {
                        $jumlahPegawai = Pegawai::where('jabatan', $proses->jabatan->nama)->count();
                        $tarifPerJamJabatan = $proses->jabatan->tarif ?? 0;
                    }
                    
                    $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                    $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
                    
                    return [
                        'id' => $proses->id,
                        'kode_proses' => $proses->kode_proses,
                        'nama_proses' => $proses->nama_proses,
                        'nama_jabatan' => $proses->jabatan->nama ?? '-',
                        'jumlah_pegawai' => $jumlahPegawai,
                        'tarif_per_jam_jabatan' => $tarifPerJamJabatan,
                        'tarif_btkl' => $tarifBtkl,
                        'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                        'btkl_per_produk' => $btklPerProduk
                    ];
                });
            
            // Get all BOP processes separately
            $prosesBop = \App\Models\BopProses::where('is_active', true)
                ->with('prosesProduksi')
                ->get()
                ->map(function($bop) {
                    // Get komponen BOP for display
                    $komponenBop = [];
                    if ($bop->komponen_bop) {
                        $komponenBop = is_array($bop->komponen_bop) 
                            ? $bop->komponen_bop 
                            : json_decode($bop->komponen_bop, true);
                    }
                    
                    // Generate kode from nama_bop_proses if no prosesProduksi
                    $kodeProses = $bop->prosesProduksi ? $bop->prosesProduksi->kode_proses : 'BOP-' . $bop->id;
                    
                    return [
                        'id' => $bop->id,
                        'nama_bop_proses' => $bop->nama_bop_proses,
                        'proses_produksi_id' => $bop->proses_produksi_id,
                        'nama_proses' => $bop->prosesProduksi->nama_proses ?? 'Umum',
                        'kode_proses' => $kodeProses,
                        'total_bop_per_jam' => $bop->total_bop_per_jam,
                        'bop_per_unit' => $bop->bop_per_unit,
                        'kapasitas_per_jam' => $bop->kapasitas_per_jam,
                        'komponen_bop' => $komponenBop
                    ];
                });
            
            return view('master-data.bom.create', compact('produks', 'prosesBtkl', 'prosesBop'));
            
        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
                'proses_ids' => 'required|array|min:1',
                'proses_ids.*' => 'exists:proses_produksis,id',
                'bop_ids' => 'nullable|array',
                'bop_ids.*' => 'exists:bop_proses,id',
                'biaya_bahan' => 'required|numeric|min:0',
                'total_btkl' => 'required|numeric|min:0',
                'total_bop' => 'required|numeric|min:0',
                'total_hpp' => 'required|numeric|min:0',
            ]);

        $user_id = auth()->id();
        $produk_id = $request->produk_id;

        // Clear existing selections for this product
        HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->delete();
        HargaPokokProduksiBtkl::where('user_id', $user_id)->delete();
        HargaPokokProduksiBop::where('user_id', $user_id)->delete();

            // Save BTKL for each selected process
            foreach ($validated['proses_ids'] as $prosesId) {
                $proses = ProsesProduksi::with(['jabatan'])->find($prosesId);
                
                if (!$proses) continue;
                
                // Calculate BTKL
                $jumlahPegawai = 0;
                $tarifPerJamJabatan = 0;
                
                if ($proses->jabatan) {
                    $jumlahPegawai = Pegawai::where('jabatan', $proses->jabatan->nama)->count();
                    $tarifPerJamJabatan = $proses->jabatan->tarif ?? 0;
                }
                
                $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
                
                // Save BTKL
                BomJobBTKL::create([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'btkl_id' => null,
                    'nama_proses' => $proses->nama_proses,
                    'tarif_per_jam' => $tarifPerJamJabatan,
                    'durasi_jam' => 1,
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'subtotal' => $btklPerProduk,
                    'keterangan' => $jumlahPegawai . ' pegawai @ Rp ' . number_format($tarifPerJamJabatan, 0, ',', '.') . '/jam',
                ]);
            }

            // Save BOP for each selected BOP process
            if (isset($validated['bop_ids']) && is_array($validated['bop_ids'])) {
                foreach ($validated['bop_ids'] as $bopId) {
                    $bopProses = \App\Models\BopProses::with('prosesProduksi')->find($bopId);
                    
                    if (!$bopProses) continue;
                    
                    // Use BOP per produk directly
                    $bopPerProduk = $bopProses->bop_per_unit ?? 0;
                    
                    // Get komponen BOP
                    $komponenBop = [];
                    if ($bopProses->komponen_bop) {
                        $komponenBop = is_array($bopProses->komponen_bop) 
                            ? $bopProses->komponen_bop 
                            : json_decode($bopProses->komponen_bop, true);
                    }
                    
                    // Save each BOP component
                    if (is_array($komponenBop)) {
                        // Calculate total rate per hour for proportion calculation (outside loop)
                        $totalRatePerHour = 0;
                        foreach ($komponenBop as $k) {
                            $totalRatePerHour += floatval($k['rate_per_hour'] ?? 0);
                        }
                        
                        foreach ($komponenBop as $komponen) {
                            $ratePerHour = floatval($komponen['rate_per_hour'] ?? 0);
                            // Calculate rate per produk based on proportion of total BOP per produk
                            $ratePerPcs = $totalRatePerHour > 0 ? ($ratePerHour / $totalRatePerHour) * $bopPerProduk : 0;
                            
                            BomJobBOP::create([
                                'bom_job_costing_id' => $bomJobCosting->id,
                                'bop_id' => null,
                                'nama_bop' => $bopProses->nama_bop_proses . ' - ' . ($komponen['component'] ?? 'BOP'),
                                'tarif' => $ratePerPcs,
                                'jumlah' => 1,
                                'subtotal' => $ratePerPcs,
                                'keterangan' => $komponen['description'] ?? $komponen['keterangan'] ?? null,
                            ]);
                        }
                    }
                }
            }
        }

        // Save BTKL selections
        if ($request->filled('selected_btkl')) {
            foreach ($request->selected_btkl as $proses_id) {
                HargaPokokProduksiBtkl::create([
                    'user_id' => $user_id,
                    'proses_produksis_id' => $proses_id,
                ]);
            }
        }

        // Save BOP selections
        if ($request->filled('selected_bop')) {
            foreach ($request->selected_bop as $bop_id) {
                HargaPokokProduksiBop::create([
                    'user_id' => $user_id,
                    'bop_proses_id' => $bop_id,
                ]);
            }
        }

        return redirect()->route('master-data.harga-pokok-produksi.index')
            ->with('success', 'Harga Pokok Produksi berhasil disimpan!');
    }

    public function show($produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        
        // Get selected components for this product
        $selectedBbb = HargaPokokProduksiBiayaBahanBaku::where('user_id', auth()->id())
            ->with('biayaBahanBaku.bahanBaku')
            ->get();
            
        $selectedBtkl = HargaPokokProduksiBtkl::where('user_id', auth()->id())
            ->with('prosesProduksi')
            ->get();
            
        $selectedBop = HargaPokokProduksiBop::where('user_id', auth()->id())
            ->with('bopProses')
            ->get();

        // Calculate totals
        $totalBbb = $this->calculateTotalBbb($selectedBbb);
        $totalBtkl = $this->calculateTotalBtkl($selectedBtkl);
        $totalBop = $this->calculateTotalBop($selectedBop);
        $totalHpp = $totalBbb + $totalBtkl + $totalBop;

        return view('master-data.bom.show', compact(
            'produk',
            'selectedBbb',
            'selectedBtkl', 
            'selectedBop',
            'totalBbb',
            'totalBtkl',
            'totalBop',
            'totalHpp'
        ));
    }

    public function destroy($produk_id)
    {
        $user_id = auth()->id();
        
        // Delete all selections for this product
        HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->delete();
        HargaPokokProduksiBtkl::where('user_id', $user_id)->delete();
        HargaPokokProduksiBop::where('user_id', $user_id)->delete();

        return redirect()->route('master-data.harga-pokok-produksi.index')
            ->with('success', 'Harga Pokok Produksi berhasil dihapus!');
    }

    private function getHppRecords()
    {
        $user_id = auth()->id();
        
        // Get all unique products that have BBB selections (since BBB is product-specific)
        $bbbProducts = HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
            ->with('biayaBahanBaku')
            ->get()
            ->pluck('biayaBahanBaku.produk_id')
            ->filter()
            ->unique()
            ->values();

        // Since BTKL and BOP are not product-specific, we'll only use BBB products
        // or create a general HPP record if user has BTKL/BOP selections
        
        $hasAnySelections = HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)->exists() ||
                           HargaPokokProduksiBtkl::where('user_id', $user_id)->exists() ||
                           HargaPokokProduksiBop::where('user_id', $user_id)->exists();

        // If we have BBB products, use those
        if ($bbbProducts->isNotEmpty()) {
            $allProductIds = $bbbProducts;
        } 
        // If we have BTKL/BOP selections but no BBB, we need to show something
        // Let's get all products for this user as potential HPP candidates
        elseif ($hasAnySelections) {
            $allProductIds = \App\Models\Produk::where('user_id', $user_id)
                ->pluck('id')
                ->toArray();
            
            // Get all BTKL processes
            $prosesBtkl = ProsesProduksi::where('kapasitas_per_jam', '>', 0)
                ->with(['jabatan'])
                ->get()
                ->map(function($proses) use ($selectedProsesIds) {
                    // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                    $jumlahPegawai = 0;
                    $tarifPerJamJabatan = 0;
                    
                    if ($proses->jabatan) {
                        $jumlahPegawai = Pegawai::where('jabatan', $proses->jabatan->nama)->count();
                        $tarifPerJamJabatan = $proses->jabatan->tarif ?? 0;
                    }
                    
                    $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                    $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
                    
                    return [
                        'id' => $proses->id,
                        'kode_proses' => $proses->kode_proses,
                        'nama_proses' => $proses->nama_proses,
                        'nama_jabatan' => $proses->jabatan->nama ?? '-',
                        'jumlah_pegawai' => $jumlahPegawai,
                        'tarif_per_jam_jabatan' => $tarifPerJamJabatan,
                        'tarif_btkl' => $tarifBtkl,
                        'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                        'btkl_per_produk' => $btklPerProduk,
                        'is_selected' => in_array($proses->id, $selectedProsesIds)
                    ];
                });
            
            // Get selected BOP processes
            $selectedBopIds = BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
                ->pluck('nama_bop')
                ->toArray();
            
            // Extract BOP process IDs from nama_bop (format: "nama_bop_proses - component")
            $selectedBopProsesIds = [];
            foreach ($selectedBopIds as $namaBop) {
                // Extract the nama_bop_proses part (before the " - ")
                $parts = explode(' - ', $namaBop);
                if (count($parts) > 0) {
                    $namaBopProses = $parts[0];
                    $bopProses = \App\Models\BopProses::where('nama_bop_proses', $namaBopProses)->first();
                    if ($bopProses) {
                        $selectedBopProsesIds[] = $bopProses->id;
                    }
                }
            }
            
            // Get all BOP processes separately
            $prosesBop = \App\Models\BopProses::where('is_active', true)
                ->with('prosesProduksi')
                ->get()
                ->map(function($bop) use ($selectedBopProsesIds) {
                    // Get komponen BOP for display
                    $komponenBop = [];
                    if ($bop->komponen_bop) {
                        $komponenBop = is_array($bop->komponen_bop) 
                            ? $bop->komponen_bop 
                            : json_decode($bop->komponen_bop, true);
                    }
                    
                    // Generate kode from nama_bop_proses if no prosesProduksi
                    $kodeProses = $bop->prosesProduksi ? $bop->prosesProduksi->kode_proses : 'BOP-' . $bop->id;
                    
                    return [
                        'id' => $bop->id,
                        'nama_bop_proses' => $bop->nama_bop_proses,
                        'proses_produksi_id' => $bop->proses_produksi_id,
                        'nama_proses' => $bop->prosesProduksi->nama_proses ?? 'Umum',
                        'kode_proses' => $kodeProses,
                        'total_bop_per_jam' => $bop->total_bop_per_jam,
                        'bop_per_unit' => $bop->bop_per_unit,
                        'kapasitas_per_jam' => $bop->kapasitas_per_jam,
                        'komponen_bop' => $komponenBop,
                        'is_selected' => in_array($bop->id, $selectedBopProsesIds)
                    ];
                });
            
            return view('master-data.bom.edit', compact('produk', 'bomJobCosting', 'prosesBtkl', 'prosesBop', 'selectedProsesIds', 'selectedBopProsesIds'));
            
        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'proses_ids' => 'required|array|min:1',
                'proses_ids.*' => 'exists:proses_produksis,id',
                'bop_ids' => 'nullable|array',
                'bop_ids.*' => 'exists:bop_proses,id',
                'biaya_bahan' => 'required|numeric|min:0',
                'total_btkl' => 'required|numeric|min:0',
                'total_bop' => 'required|numeric|min:0',
                'total_hpp' => 'required|numeric|min:0',
            ]);

            // Find product and BomJobCosting
            $produk = Produk::with('bomJobCosting')->findOrFail($id);
            $bomJobCosting = $produk->bomJobCosting;
            
            if (!$bomJobCosting) {
                return back()->with('error', 'Produk belum memiliki data biaya bahan.');
            }

            // Delete existing BTKL and BOP data
            BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->delete();

            // Save BTKL for each selected process
            foreach ($validated['proses_ids'] as $prosesId) {
                $proses = ProsesProduksi::with(['jabatan'])->find($prosesId);
                
                if (!$proses) continue;
                
                // Calculate BTKL
                $jumlahPegawai = 0;
                $tarifPerJamJabatan = 0;
                
                if ($proses->jabatan) {
                    $jumlahPegawai = Pegawai::where('jabatan', $proses->jabatan->nama)->count();
                    $tarifPerJamJabatan = $proses->jabatan->tarif ?? 0;
                }
                
                $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $tarifBtkl / $proses->kapasitas_per_jam : 0;
                
                // Save BTKL
                BomJobBTKL::create([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'btkl_id' => null,
                    'nama_proses' => $proses->nama_proses,
                    'tarif_per_jam' => $tarifPerJamJabatan,
                    'durasi_jam' => 1,
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'subtotal' => $btklPerProduk,
                    'keterangan' => $jumlahPegawai . ' pegawai @ Rp ' . number_format($tarifPerJamJabatan, 0, ',', '.') . '/jam',
                ]);
            }

            // Save BOP for each selected BOP process
            if (isset($validated['bop_ids']) && is_array($validated['bop_ids'])) {
                foreach ($validated['bop_ids'] as $bopId) {
                    $bopProses = \App\Models\BopProses::with('prosesProduksi')->find($bopId);
                    
                    if (!$bopProses) continue;
                    
                    // Use BOP per produk directly
                    $bopPerProduk = $bopProses->bop_per_unit ?? 0;
                    
                    // Get komponen BOP
                    $komponenBop = [];
                    if ($bopProses->komponen_bop) {
                        $komponenBop = is_array($bopProses->komponen_bop) 
                            ? $bopProses->komponen_bop 
                            : json_decode($bopProses->komponen_bop, true);
                    }
                    
                    // Save each BOP component
                    if (is_array($komponenBop)) {
                        // Calculate total rate per hour for proportion calculation (outside loop)
                        $totalRatePerHour = 0;
                        foreach ($komponenBop as $k) {
                            $totalRatePerHour += floatval($k['rate_per_hour'] ?? 0);
                        }
                        
                        foreach ($komponenBop as $komponen) {
                            $ratePerHour = floatval($komponen['rate_per_hour'] ?? 0);
                            // Calculate rate per produk based on proportion of total BOP per produk
                            $ratePerPcs = $totalRatePerHour > 0 ? ($ratePerHour / $totalRatePerHour) * $bopPerProduk : 0;
                            
                            BomJobBOP::create([
                                'bom_job_costing_id' => $bomJobCosting->id,
                                'bop_id' => null,
                                'nama_bop' => $bopProses->nama_bop_proses . ' - ' . ($komponen['component'] ?? 'BOP'),
                                'tarif' => $ratePerPcs,
                                'jumlah' => 1,
                                'subtotal' => $ratePerPcs,
                                'keterangan' => $komponen['description'] ?? $komponen['keterangan'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Update BomJobCosting totals
            $bomJobCosting->total_btkl = $validated['total_btkl'];
            $bomJobCosting->total_bop = $validated['total_bop'];
            $bomJobCosting->total_hpp = $validated['total_hpp'];
            $bomJobCosting->hpp_per_unit = $validated['total_hpp'];
            $bomJobCosting->save();

            // Update product harga_pokok
            $produk->harga_pokok = $validated['total_hpp'];
            $produk->save();

            DB::commit();

            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('success', 'Harga Pokok Produksi berhasil diperbarui untuk produk: ' . $produk->nama_produk);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui HPP: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bom = Bom::findOrFail($id);
            $bom->delete();

            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('success', 'BOM berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus BOM: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        try {
            $bom = Bom::with(['produk', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuan'])->findOrFail($id);
            
            return view('master-data.bom.print', compact('bom'));
            
        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Terjadi kesalahan saat mencetak BOM: ' . $e->getMessage());
        }
    }

    /**
     * Calculate BOP total from master BOP data (consistent with detail view)
     */
    private function calculateBOPFromMasterData($bomJobCostingId)
    {
        // Get BOP data from master table
        $allBopData = \Illuminate\Support\Facades\DB::table('bops')
            ->where('periode', '2026-02')
            ->get();
        
        if ($allBopData->count() == 0) {
            return 0;
        }
        
        // Categorize BOP data by process
        $bopData = $allBopData->map(function($bop) {
            $namaProses = 'Umum';
            $namaBiaya = strtolower($bop->nama_biaya ?? '');
            
            if (stripos($namaBiaya, 'penggorengan') !== false) {
                $namaProses = 'Penggorengan';
            } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                $namaProses = 'Perbumbuan';
            } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                $namaProses = 'Pengemasan';
            }
            
            return [
                'id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'kode' => $produk->kode_produk,  // Fixed: use kode_produk instead of kode
                'satuan' => $produk->satuan->nama ?? '-',
                'stok' => $produk->stok,
                'harga_jual' => $produk->harga_jual,
            ];
        })->filter()->values();
    }

    private function calculateTotalBbb($selectedBbb)
    {
        $total = 0;
        foreach ($selectedBbb as $bbb) {
            if ($bbb->biayaBahanBaku) {
                $total += $bbb->biayaBahanBaku->subtotal;
            }
        }
        return $total;
    }

    private function calculateTotalBtkl($selectedBtkl)
    {
        $total = 0;
        foreach ($selectedBtkl as $btkl) {
            if ($btkl->prosesProduksi) {
                // Calculate based on process data - use correct field names
                $tarif = $btkl->prosesProduksi->tarif_btkl ?? 0;
                $kapasitas = $btkl->prosesProduksi->kapasitas_per_jam ?? 1;
                
                $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;
                $total += $biayaPerProduk;
            }
        }
        return $total;
    }

    private function calculateTotalBop($selectedBop)
    {
        $total = 0;
        foreach ($selectedBop as $bop) {
            if ($bop->bopProses) {
                $total += $bop->bopProses->total_bop_per_produk ?? 0;
            }
        }
        return $total;
    }

    // API endpoints for data
    public function getAvailableBbb($produk_id)
    {
        // Get biaya bahan baku data from the new table structure
        $biayaBahanBaku = \App\Models\BiayaBahanBaku::where('produk_id', $produk_id)
            ->where('user_id', auth()->id())
            ->with(['bahanBaku.satuan'])
            ->get();
            
        // Transform data to match expected format
        $transformedData = $biayaBahanBaku->map(function($item) {
            return [
                'id' => $item->id,
                'nama_bahan' => $item->bahanBaku->nama_bahan ?? 'Unknown',
                'jumlah' => $item->jumlah,
                'satuan' => $item->satuan,
                'harga_satuan' => $item->harga_satuan,
                'subtotal' => $item->subtotal,
                'stok' => $item->bahanBaku->stok ?? 0,
                'keterangan' => $item->keterangan
            ];
        });
            
        return response()->json($transformedData);
    }

    public function getAvailableBtkl($produk_id)
    {
        // Get all BTKL (proses produksi) data for the user
        // Since proses_produksis doesn't have produk_id, we get all for the user
        $prosesProduksi = \App\Models\ProsesProduksi::where('user_id', auth()->id())
            ->get();
            
        // Transform data to include calculated costs
        $transformedData = $prosesProduksi->map(function($item) {
            $tarif = $item->tarif_btkl ?? 0;
            $kapasitas = $item->kapasitas_per_jam ?? 1;
            $jumlahProduksi = $item->jumlah_produksi_per_jam ?? 1;
            
            // Calculate cost per product
            $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;
            
            return [
                'id' => $item->id,
                'nama_proses' => $item->nama_proses ?? 'Proses Produksi',
                'tarif_per_jam' => $tarif,
                'kapasitas_per_jam' => $kapasitas,
                'jumlah_produksi_per_jam' => $jumlahProduksi,
                'satuan' => $item->satuan_btkl ?? 'Unit',
                'biaya_per_produk' => $biayaPerProduk,
                'deskripsi' => $item->deskripsi,
                'kode_proses' => $item->kode_proses
            ];
        });
            
        return response()->json($transformedData);
    }

    public function getAvailableBop()
    {
        // Get BOP data for the user
        $bopProses = \App\Models\BopProses::where('user_id', auth()->id())
            ->with('prosesProduksi')
            ->get();
        
        // Transform data to match expected format
        $transformedData = $bopProses->map(function($item) {
            return [
                'id' => $item->id,
                'nama_bop' => $item->prosesProduksi->nama_proses ?? 'BOP Item',
                'tarif' => $item->total_bop_per_produk ?? $item->bop_per_unit ?? 0,
                'satuan' => 'Unit',
                'deskripsi' => 'BOP untuk ' . ($item->prosesProduksi->nama_proses ?? 'proses'),
                'kategori' => 'Overhead',
                'listrik' => $item->listrik_per_jam ?? 0,
                'gas_bbm' => $item->gas_bbm_per_jam ?? 0,
                'penyusutan' => $item->penyusutan_mesin_per_jam ?? 0,
                'maintenance' => $item->maintenance_per_jam ?? 0,
                'gaji_mandor' => $item->gaji_mandor_per_jam ?? 0,
                'lain_lain' => $item->lain_lain_per_jam ?? 0
            ];
        });
        
        return response()->json($transformedData);
    }
}
