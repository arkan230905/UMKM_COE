<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\BomCalculationService;

class BomController extends Controller
{
    protected $bomCalculationService;

    public function __construct(BomCalculationService $bomCalculationService)
    {
        $this->middleware('auth');
        $this->bomCalculationService = $bomCalculationService;
    }

    public function index(Request $request)
    {
        // Get all products with their BOM data
        $query = Produk::with(['boms', 'bomJobCosting', 'satuan']);
        
        // Filter by product name
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
        }
        
        // Filter by BOM status
        if ($request->filled('status')) {
            if ($request->status == 'ada') {
                $query->whereHas('boms')->orWhereHas('bomJobCosting');
            } elseif ($request->status == 'belum') {
                $query->whereDoesntHave('boms')->whereDoesntHave('bomJobCosting');
            } elseif ($request->status == 'lengkap') {
                $query->whereHas('bomJobCosting', function($q) {
                    $q->where('total_bbb', '>', 0)
                      ->orWhere('total_bahan_pendukung', '>', 0)
                      ->orWhere('total_btkl', '>', 0)
                      ->orWhere('total_bop', '>', 0);
                });
            } elseif ($request->status == 'tidak_lengkap') {
                $query->whereHas('bomJobCosting', function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('total_bbb', 0)
                           ->orWhere('total_bahan_pendukung', 0)
                           ->orWhere('total_btkl', 0)
                           ->orWhere('total_bop', 0);
                    });
                });
            }
        }
        
        $produks = $query->orderBy('nama_produk')->paginate(15);
        
        // Add calculated data to each product
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $totalBOP = 0;
            $btklCount = 0;
            
            // Get BomJobCosting for this product (primary data source)
            $bomJobCosting = $produk->bomJobCosting;
            
            if ($bomJobCosting) {
                // Use data from BomJobCosting for all components
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                $totalBTKL = $bomJobCosting->total_btkl ?? 0;
                $totalBOP = $bomJobCosting->total_bop ?? 0;
                
                // Count BTKL processes for display
                $btklCount = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->count();
            } else {
                // Fallback: Calculate from BOM details if no Job Costing
                $bom = $produk->boms->first();
                if ($bom) {
                    $bomDetails = \App\Models\BomDetail::where('bom_id', $bom->id)->get();
                    $totalBiayaBahan = $bomDetails->sum('total_harga');
                }
            }
            
            // Add calculated data to product
            $produk->total_biaya_bahan = $totalBiayaBahan;
            $produk->total_btkl = $totalBTKL;
            $produk->total_bop = $totalBOP;
            $produk->btkl_count = $btklCount;
            $produk->has_btkl = $btklCount > 0;
            
            // Calculate total HPP: Biaya Bahan + BTKL + BOP
            $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
            
            $produk->total_bom_cost = $totalBiayaHPP;
            
            // Update harga_pokok in produks table as patokan harga jual - TIDAK MENGHAPUS
            // Hanya update BOM, harga pokok tetap dipertahankan
            $currentHargaPokok = \App\Models\Produk::where('id', $produk->id)->value('harga_pokok') ?? 0;
            
            \App\Models\Produk::where('id', $produk->id)->update([
                'harga_pokok' => $currentHargaPokok // Tetap harga pokok yang ada
            ]);
        }
        
        return view('master-data.bom.index', compact('produks'));
    }

    public function updateBOMCosts(Request $request, $produkId)
    {
        try {
            $validated = $request->validate([
                'total_bop' => 'required|numeric|min:0'
            ]);

            $produk = Produk::findOrFail($produkId);
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produkId)->first();
            
            if ($bomJobCosting) {
                $bomJobCosting->total_bop = $validated['total_bop'];
                $bomJobCosting->save();
            }

            // Recalculate BOM
            $bom = Bom::where('produk_id', $produkId)->first();
            if ($bom) {
                \App\Services\BomSyncService::recalculateBomCosts($bom);
            }

            // Update harga_pokok in produks table
            $totalBiayaHPP = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
            \App\Models\Produk::where('id', $produkId)->update([
                'harga_pokok' => $totalBiayaHPP
            ]);

            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('success', 'BOP berhasil diperbarui dan Harga Pokok Produksi dihitung ulang untuk ' . $produk->nama_produk);

        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Gagal memperbarui BOP: ' . $e->getMessage());
        }
    }

    public function updateBOP(Request $request)
    {
        try {
            $validated = $request->validate([
                'bom_job_costing_id' => 'required|exists:bom_job_costings,id',
                'produk_id' => 'required|exists:produks,id',
                'total_bop' => 'required|numeric|min:0'
            ]);

            $bomJobCosting = \App\Models\BomJobCosting::find($validated['bom_job_costing_id']);
            $bomJobCosting->total_bop = $validated['total_bop'];
            $bomJobCosting->save();

            // Recalculate BOM total
            $bom = Bom::where('produk_id', $validated['produk_id'])->first();
            if ($bom) {
                \App\Services\BomSyncService::recalculateBomCosts($bom);
                $newTotal = $bom->fresh()->total_biaya;
            } else {
                $newTotal = 0;
            }

            // Update harga_pokok in produks table - TIDAK MENGHAPUS HARGA POKOK
            // Hanya update BOP, harga pokok tetap dipertahankan
            $currentHargaPokok = \App\Models\Produk::where('id', $validated['produk_id'])->value('harga_pokok') ?? 0;
            
            \App\Models\Produk::where('id', $validated['produk_id'])->update([
                'harga_pokok' => $currentHargaPokok // Tetap harga pokok yang ada
            ]);

            return response()->json([
                'success' => true,
                'message' => 'BOP berhasil diperbarui',
                'new_total' => $newTotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui BOP: ' . $e->getMessage()
            ], 500);
        }
    }

    // New method to handle BOP update from detail page
    public function updateBOPFromDetail(Request $request)
    {
        try {
            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
                'total_bop' => 'required|numeric|min:0'
            ]);

            $productId = $validated['produk_id'];
            $totalBOP = $validated['total_bop'];

            // Find or create BomJobCosting for this product
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $productId)->first();
            
            if (!$bomJobCosting) {
                // Create new BomJobCosting if doesn't exist
                $bomJobCosting = new \App\Models\BomJobCosting();
                $bomJobCosting->produk_id = $productId;
                $bomJobCosting->jumlah_produk = 1;
                $bomJobCosting->total_bbb = 0;
                $bomJobCosting->total_btkl = 0;
                $bomJobCosting->total_bahan_pendukung = 0;
                $bomJobCosting->total_bop = $totalBOP;
                $bomJobCosting->total_hpp = $totalBOP;
                $bomJobCosting->hpp_per_unit = $totalBOP;
                $bomJobCosting->save();
            } else {
                // Update existing BomJobCosting
                $bomJobCosting->total_bop = $totalBOP;
                $bomJobCosting->save();
                
                // Recalculate totals to update total_hpp and hpp_per_unit
                $bomJobCosting->recalculate();
            }

            // Update product harga_bom (HPP)
            $produk = \App\Models\Produk::find($productId);
            if ($produk) {
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                $totalHPP = $totalBiayaBahan + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
                
                $produk->harga_bom = $totalHPP; // Use total HPP from calculation
                $produk->harga_pokok = $totalHPP; // Update harga_pokok as patokan harga jual
                $produk->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'BOP berhasil diperbarui dari halaman detail',
                'total_bop' => $totalBOP,
                'total_hpp' => $bomJobCosting->fresh()->total_hpp
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui BOP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function populateAllBomData(Request $request)
    {
        try {
            // Check if master data exists
            $btklCount = \App\Models\Btkl::where('is_active', true)->count();
            $bopCount = \App\Models\BopProses::where('is_active', true)->count();
            
            if ($btklCount == 0 || $bopCount == 0) {
                return redirect()->route('master-data.harga-pokok-produksi.index')
                    ->with('error', 'Tidak ada data BTKL atau BOP yang aktif. Silakan buat data master BTKL dan BOP terlebih dahulu.');
            }
            
            // Run auto-population
            \App\Services\BomSyncService::autoPopulateAllProducts();
            
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('success', "Berhasil mengisi data BTKL dan BOP untuk semua produk. BTKL: {$btklCount} proses, BOP: {$bopCount} proses.");
                
        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Gagal mengisi data Harga Pokok Produksi: ' . $e->getMessage());
        }
    }

    public function syncBomData(Request $request, $produkId)
    {
        try {
            $produk = Produk::findOrFail($produkId);
            
            // Ensure BomJobCosting exists
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produkId)->first();
            
            if (!$bomJobCosting) {
                $bomJobCosting = \App\Models\BomJobCosting::create([
                    'produk_id' => $produkId,
                    'jumlah_produk' => 1,
                    'total_bbb' => 0,
                    'total_btkl' => 0,
                    'total_bahan_pendukung' => 0,
                    'total_bop' => 0,
                    'total_hpp' => 0,
                    'hpp_per_unit' => 0
                ]);
            }
            
            // Sync BTKL and BOP data
            \App\Services\BomSyncService::syncBTKLForBom($bomJobCosting);
            \App\Services\BomSyncService::syncBOPForBom($bomJobCosting);
            
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('success', "Berhasil menyinkronkan data BTKL dan BOP untuk produk: {$produk->nama_produk}");
                
        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Gagal menyinkronkan data Harga Pokok Produksi: ' . $e->getMessage());
        }
    }

    /**
     * Calculate BOM cost for realtime updates
     */
    public function calculateBomCost($produkId)
    {
        try {
            $produk = Produk::findOrFail($produkId);
            
            // Get BomJobCosting data
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produkId)
                ->with([
                    'detailBBB.bahanBaku.satuan',
                    'detailBTKL.btkl.jabatan',
                    'detailBahanPendukung.bahanPendukung.satuan',
                    'detailBOP'
                ])
                ->first();

            // Get Bahan Baku data
            $detailBahanBaku = [];
            if ($bomJobCosting && $bomJobCosting->detailBBB) {
                $detailBahanBaku = $bomJobCosting->detailBBB->map(function($detail) {
                    $bahanBaku = $detail->bahanBaku;
                    return [
                        'id' => $detail->id,
                        'nama_bahan' => $bahanBaku->nama_bahan,
                        'qty' => $detail->jumlah ?? 0,
                        'satuan' => $bahanBaku->satuan->nama ?? '',
                        'harga_satuan' => $detail->harga_satuan ?? 0,
                        'subtotal' => $detail->subtotal ?? 0,
                    ];
                })->toArray() ?? [];
            }
            
            // Get Bahan Pendukung data
            $detailBahanPendukung = [];
            if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
                $detailBahanPendukung = $bomJobCosting->detailBahanPendukung->map(function($detail) {
                    $bahanPendukung = $detail->bahanPendukung;
                    return [
                        'id' => $detail->id,
                        'nama_bahan' => $bahanPendukung->nama_bahan,
                        'qty' => $detail->jumlah ?? 0,
                        'satuan' => $bahanPendukung->satuan->nama ?? '',
                        'harga_satuan' => $detail->harga_satuan ?? 0,
                        'subtotal' => $detail->subtotal ?? 0,
                    ];
                })->toArray() ?? [];
            }
            
            $totalBBB = array_sum(array_column($detailBahanBaku, 'subtotal'));
            $totalBahanPendukung = array_sum(array_column($detailBahanPendukung, 'subtotal'));
            $totalBiayaBahan = $totalBBB + $totalBahanPendukung;
            
            // Get BTKL data
            $btklDataForDisplay = [];
            if ($bomJobCosting) {
                $btklDataRaw = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                    ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*', 
                        'btkls.kode_proses',
                        'btkls.nama_btkl',
                        'btkls.tarif_per_jam', 
                        'btkls.kapasitas_per_jam',
                        'btkls.satuan',
                        'btkls.deskripsi_proses',
                        'jabatans.nama as nama_jabatan',
                        'jabatans.kategori'
                    )
                    ->get();
                    
                $btklDataForDisplay = $btklDataRaw->map(function($item) {
                    $jumlahPegawai = 0;
                    if ($item->nama_jabatan) {
                        $jumlahPegawai = \App\Models\Pegawai::where('jabatan', $item->nama_jabatan)->count();
                    }
                    
                    return [
                        'id' => $item->id,
                        'kode_proses' => $item->kode_proses,
                        'nama_proses' => $item->nama_btkl,
                        'nama_jabatan' => $item->nama_jabatan,
                        'tarif_per_jam' => $item->tarif_per_jam ?? 0,
                        'kapasitas_per_jam' => $item->kapasitas_per_jam ?? 0,
                        'jumlah_pegawai' => $jumlahPegawai,
                        'subtotal' => $item->subtotal ?? 0
                    ];
                })->toArray();
            }

            // Get BOP data
            $bopData = [];
            $allBopData = \Illuminate\Support\Facades\DB::table('bops')
                ->where('periode', '2026-02')
                ->get();
            
            if ($allBopData->count() > 0) {
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
                        'id' => $bop->id,
                        'nama_proses' => $namaProses,
                        'nama_komponen' => $bop->nama_biaya ?? 'Komponen BOP',
                        'tarif' => $bop->jumlah ?? 0,
                        'subtotal' => $bop->jumlah ?? 0,
                        'keterangan' => $this->extractKeterangan($bop->nama_biaya ?? '')
                    ];
                })->toArray();
            }

            // Calculate totals
            $totalBiayaBTKL = 0;
            $totalBiayaBOP = 0;

            if ($bomJobCosting) {
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                $totalBiayaBTKL = $bomJobCosting->total_btkl;
                $totalBiayaBOP = $bomJobCosting->total_bop;
            }

            $totalBiayaBOM = $totalBiayaBahan + $totalBiayaBTKL + $totalBiayaBOP;

            return response()->json([
                'success' => true,
                'data' => [
                    'biaya_bahan' => [
                        'bahan_baku' => $detailBahanBaku,
                        'bahan_pendukung' => $detailBahanPendukung,
                        'total_bbb' => $totalBBB,
                        'total_bahan_pendukung' => $totalBahanPendukung,
                        'total_biaya_bahan' => $totalBiayaBahan
                    ],
                    'btkl' => $btklDataForDisplay,
                    'bop' => $bopData,
                    'total' => [
                        'total_biaya_bahan' => $totalBiayaBahan,
                        'total_biaya_btkl' => $totalBiayaBTKL,
                        'total_biaya_bop' => $totalBiayaBOP,
                        'total_bom' => $totalBiayaBOM
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating BOM cost: ' . $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            // Cari produk berdasarkan ID
            $produk = Produk::findOrFail($id);
            
            // Get BomJobCosting untuk data yang akurat
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $id)
                ->with([
                    'detailBBB.bahanBaku.satuan',
                    'detailBTKL.btkl.jabatan',
                    'detailBahanPendukung.bahanPendukung.satuan',
                    'detailBOP'
                ])
                ->first();

            // Get Bahan Baku data
            $detailBahanBaku = [];
            if ($bomJobCosting && $bomJobCosting->detailBBB) {
                $detailBahanBaku = $bomJobCosting->detailBBB->map(function($detail) {
                    $bahanBaku = $detail->bahanBaku;
                    return [
                        'id' => $detail->id,
                        'nama_bahan' => $bahanBaku->nama_bahan,
                        'stok' => $bahanBaku->stok ?? 0,
                        'satuan' => $detail->satuan ?? $bahanBaku->satuan->nama ?? '', // Use BOM detail satuan first
                        'qty' => $detail->jumlah ?? 0,
                        'jumlah' => $detail->jumlah ?? 0,
                        'harga_satuan' => $detail->harga_satuan ?? 0,
                        'subtotal' => $detail->subtotal ?? 0,
                    ];
                })->toArray() ?? [];
            }
            
            // Get Bahan Pendukung data
            $detailBahanPendukung = [];
            if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
                $detailBahanPendukung = $bomJobCosting->detailBahanPendukung->map(function($detail) {
                    $bahanPendukung = $detail->bahanPendukung;
                    return [
                        'id' => $detail->id,
                        'nama_bahan' => $bahanPendukung->nama_bahan,
                        'stok' => $bahanPendukung->stok ?? 0,
                        'satuan' => $detail->satuan ?? $bahanPendukung->satuan->nama ?? '', // Use BOM detail satuan first
                        'qty' => $detail->jumlah ?? 0,
                        'jumlah' => $detail->jumlah ?? 0,
                        'harga_satuan' => $detail->harga_satuan ?? 0,
                        'subtotal' => $detail->subtotal ?? 0,
                    ];
                })->toArray() ?? [];
            }
            
            $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
            $totalBBB = array_sum(array_column($detailBahanBaku, 'subtotal'));
            $totalBahanPendukung = array_sum(array_column($detailBahanPendukung, 'subtotal'));
            $totalBiayaBahan = $totalBBB + $totalBahanPendukung;
            
            // Get BTKL data dari BomJobBTKL (yang sudah terisi dengan data benar)
            $btklDataForDisplay = [];
            if ($bomJobCosting) {
                $btklDataRaw = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                    ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*', 
                        'btkls.kode_proses',
                        'btkls.nama_btkl',
                        'btkls.tarif_per_jam as tarif_per_jam_btkl', 
                        'btkls.kapasitas_per_jam',
                        'btkls.satuan',
                        'btkls.deskripsi_proses',
                        'jabatans.nama as nama_jabatan',
                        'jabatans.kategori',
                        'jabatans.tarif as tarif_per_jam_jabatan'
                    )
                    ->get();
                    
                $btklDataForDisplay = $btklDataRaw->map(function($item) {
                    // Get jumlah pegawai from jabatan
                    $jumlahPegawai = 0;
                    if ($item->nama_jabatan) {
                        $jumlahPegawai = \App\Models\Pegawai::where('jabatan', $item->nama_jabatan)->count();
                    }
                    
                    // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                    $tarifPerJamJabatan = $item->tarif_per_jam_jabatan ?? 0;
                    $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                    
                    return [
                        'id' => $item->id,
                        'kode_proses' => $item->kode_proses,
                        'nama_proses' => $item->nama_btkl,
                        'nama_jabatan' => $item->nama_jabatan,
                        'tarif_per_jam' => $tarifPerJamJabatan, // Tarif per jam jabatan (untuk display "X pegawai @ Rp Y/jam")
                        'tarif_btkl' => $tarifBtkl, // Tarif BTKL yang sudah dikalikan jumlah pegawai
                        'kapasitas_per_jam' => $item->kapasitas_per_jam ?? 0,
                        'waktu_pengerjaan' => $item->waktu_pengerjaan ?? 0,
                        'jumlah_pegawai' => $jumlahPegawai,
                        'satuan' => $item->satuan ?? 'Jam',
                        'subtotal_btkl' => $tarifBtkl * ($item->waktu_pengerjaan ?? 0),
                        'subtotal' => $item->subtotal ?? 0
                    ];
                })->toArray();
            }

            // Get BOP data dari BomJobBOP (tan join ke bops)
            $bopData = [];
            if ($bomJobCosting) {
                $bopDataRaw = \Illuminate\Support\Facades\DB::table('bom_job_bop')
                    ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_bop.*')
                    ->get();
                    
                $bopData = $bopDataRaw->map(function($item) {
                    $namaProses = 'Umum';
                    $namaBiaya = strtolower($item->nama_bop ?? '');
                    
                    if (stripos($namaBiaya, 'penggorengan') !== false) {
                        $namaProses = 'Menggoreng';
                    } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                        $namaProses = 'Perbumbuan';
                    } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                        $namaProses = 'Packing';
                    }
                    
                    return [
                        'id' => $item->id,
                        'nama_proses' => $namaProses,
                        'nama_komponen' => $item->nama_bop ?? 'Komponen BOP',
                        'tarif' => $item->tarif ?? 0,
                        'keterangan' => $item->keterangan ?? ''
                    ];
                })->toArray();
            }
            
            // Jika tidak ada data BOP master, fallback ke BomJobBOP
            if (empty($bopData) && $bomJobCosting) {
                $bomJobBopData = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
                    ->with(['bop'])
                    ->get();
                    
                if ($bomJobBopData->count() > 0) {
                    $bopData = $bomJobBopData->map(function($item) {
                        $namaProses = 'Umum';
                        if ($item->bop && $item->bop->nama_bop) {
                            $namaBop = $item->bop->nama_bop;
                            if (stripos($namaBop, 'penggorengan') !== false) {
                                $namaProses = 'Penggorengan';
                            } elseif (stripos($namaBop, 'perbumbuan') !== false) {
                                $namaProses = 'Perbumbuan';
                            } elseif (stripos($namaBop, 'pengemasan') !== false) {
                                $namaProses = 'Pengemasan';
                            }
                        }
                        
                        return [
                            'id' => $item->id,
                            'nama_proses' => $namaProses,
                            'nama_komponen' => $item->bop->nama_bop ?? 'Komponen BOP',
                            'tarif' => $item->tarif ?? 0,
                            'jumlah' => $item->jumlah ?? 0,
                            'subtotal' => $item->subtotal ?? 0,
                            'keterangan' => $item->keterangan ?? $item->bop->keterangan ?? '-'
                        ];
                    })->toArray();
                }
            }

            // Calculate totals for BTKL and BOP
            $totalBiayaBTKL = 0;
            $totalBiayaBOP = 0;

            if ($bomJobCosting) {
                $totalBiayaBTKL = $bomJobCosting->total_btkl ?? 0;
                $totalBiayaBOP = $bomJobCosting->total_bop ?? 0;
            } else {
                // Fallback: Calculate BTKL from btklDataForDisplay if no BomJobCosting
                if (!empty($btklDataForDisplay)) {
                    foreach ($btklDataForDisplay as $btkl) {
                        $jumlahPegawai = $btkl['jumlah_pegawai'] ?? 0;
                        $tarifPerJamJabatan = $btkl['tarif_per_jam'] ?? 0;
                        $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                        $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 1;
                        $biayaPerProduk = $kapasitasPerJam > 0 ? $tarifBtkl / $kapasitasPerJam : 0;
                        $totalBiayaBTKL += $biayaPerProduk;
                    }
                }
                
                // Use standard BOP values if no BomJobCosting
                $totalBiayaBOP = 1740 + 290 + 1160; // Total = 3.190
            }

            $totalBiayaBOM = $totalBiayaBahan + $totalBiayaBTKL + $totalBiayaBOP;

            return view('master-data.bom.show', compact(
                'produk',
                'bomJobCosting',
                'btklDataForDisplay',
                'bopData',
                'detailBahanBaku',
                'detailBahanPendukung',
                'totalBBB',
                'totalBahanPendukung',
                'totalBiayaBahan',
                'totalBiayaBTKL',
                'totalBiayaBOP',
                'totalBiayaBOM'
            ));

        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan detail BOM: ' . $e->getMessage());
        }
    }
    
    /**
     * Extract keterangan from nama biaya
     */
    private function extractKeterangan($namaBiaya)
    {
        $keterangan = '-';
        
        if (stripos($namaBiaya, 'listrik') !== false) {
            if (stripos($namaBiaya, 'mesin') !== false) {
                if (stripos($namaBiaya, 'perbumbuan') !== false) {
                    $keterangan = 'Mesin Ringan';
                } else {
                    $keterangan = 'Pemanas Minyak';
                }
            } elseif (stripos($namaBiaya, 'mixer') !== false) {
                $keterangan = 'Mesin Ringan';
            } else {
                $keterangan = 'Listrik umum';
            }
        } elseif (stripos($namaBiaya, 'gas') !== false || stripos($namaBiaya, 'bbm') !== false) {
            $keterangan = 'Bahan bakar';
        } elseif (stripos($namaBiaya, 'penyusutan') !== false) {
            if (stripos($namaBiaya, 'mesin') !== false) {
                if (stripos($namaBiaya, 'perbumbuan') !== false) {
                    $keterangan = 'Drum / Mixer';
                } else {
                    $keterangan = 'Rutin';
                }
            } elseif (stripos($namaBiaya, 'alat') !== false) {
                if (stripos($namaBiaya, 'packing') !== false) {
                    $keterangan = 'Alat Packing';
                } else {
                    $keterangan = 'Drum / Mixer';
                }
            }
        } elseif (stripos($namaBiaya, 'maintenance') !== false) {
            if (stripos($namaBiaya, 'penggorengan') !== false) {
                $keterangan = 'Mesin Goreng';
            } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                $keterangan = 'Rutin';
            } else {
                $keterangan = 'Rutin';
            }
        } elseif (stripos($namaBiaya, 'kebersihan') !== false) {
            if (stripos($namaBiaya, 'air') !== false) {
                $keterangan = 'Cuci alat';
            } elseif (stripos($namaBiaya, 'area') !== false) {
                $keterangan = 'Area';
            } else {
                $keterangan = 'Rutin';
            }
        } elseif (stripos($namaBiaya, 'plastik') !== false || stripos($namaBiaya, 'kemasan') !== false) {
            $keterangan = 'Penunjang';
        } elseif (stripos($namaBiaya, 'lain-lain') !== false) {
            $keterangan = 'Lain-lain';
        }
        
        return $keterangan;
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
                return response()->json(['success' => false, 'message' => 'BOM not found for this product']);
            }

            // Use the same breakdown logic as production controller
            $breakdown = $this->getProductionCostBreakdownForProduk($produkId, $bomJobCosting);
            
            return response()->json(['success' => true, 'breakdown' => $breakdown]);
            
        } catch (\Exception $e) {
            \Log::error('getBomDetails error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
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

        // Get Bahan Pendukung from BOM
        if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
            foreach ($bomJobCosting->detailBahanPendukung as $detail) {
                $bahanPendukung = $detail->bahanPendukung;
                $hargaPerUnit = $detail->jumlah > 0 ? $detail->subtotal / $detail->jumlah : 0;
                
                $breakdown['biaya_bahan']['bahan_pendukung'][] = [
                    'nama' => $bahanPendukung->nama_bahan,
                    'qty' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? $bahanPendukung->satuan->nama ?? 'Unit',
                    'harga_per_unit' => $hargaPerUnit
                ];
                $breakdown['biaya_bahan']['total'] += $hargaPerUnit;
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

        // Get BOP from BOM
        if ($bomJobCosting) {
            foreach ($bomJobCosting->detailBOP as $detail) {
                // Use tarif if available, otherwise calculate from jumlah and kapasitas
                if ($detail->tarif > 0) {
                    $hargaPerUnit = $detail->tarif;
                } elseif ($detail->bop && $detail->bop->jumlah > 0) {
                    // Calculate per unit from BOP master data
                    $hargaPerUnit = $detail->bop->jumlah / 200; // Assuming 200 units per hour
                } else {
                    $hargaPerUnit = 0;
                }
                
                $namaProses = $detail->nama_bop ?? ($detail->bop ? $detail->bop->nama_biaya : 'Proses Tidak Diketahui');
                
                $breakdown['bop'][] = [
                    'nama' => $namaProses,
                    'harga_per_unit' => $hargaPerUnit
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
            // Get products that don't have BOM yet
            $produkIdsWithBom = Bom::pluck('produk_id')->toArray();
            $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
            
            if ($produks->isEmpty()) {
                return redirect()->route('master-data.harga-pokok-produksi.index')
                    ->with('info', 'Semua produk sudah memiliki BOM. Tidak ada produk yang bisa ditambahkan BOM-nya.');
            }
            
            return view('master-data.bom.create', compact('produks'));
            
        } catch (\Exception $e) {
            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Basic implementation - can be expanded as needed
        try {
            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id|unique:boms,produk_id',
                'total_biaya' => 'required|numeric|min:0',
            ]);

            $bom = Bom::create([
                'produk_id' => $validated['produk_id'],
                'kode_bom' => 'BOM-' . str_pad($validated['produk_id'], 3, '0', STR_PAD_LEFT),
                'total_biaya' => $validated['total_biaya'],
                'catatan' => 'Bill of Materials untuk ' . Produk::find($validated['produk_id'])->nama_produk,
            ]);

            return redirect()->route('master-data.bom.show', $bom->id)
                ->with('success', 'BOM berhasil dibuat.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat BOM: ' . $e->getMessage());
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
                'nama_proses' => $namaProses,
                'tarif' => $bop->jumlah ?? 0,
            ];
        })->toArray();
        
        // Get BTKL data for capacity information
        $btklDataRaw = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
            ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
            ->where('bom_job_btkl.bom_job_costing_id', $bomJobCostingId)
            ->select('btkls.nama_btkl', 'btkls.kapasitas_per_jam')
            ->get();
            
        $btklDataForDisplay = $btklDataRaw->map(function($item) {
            return [
                'nama_proses' => $item->nama_btkl,
                'kapasitas_per_jam' => $item->kapasitas_per_jam ?? 0,
            ];
        })->toArray();
        
        // Calculate BOP total
        $totalBiayaBOP = 0;
        $bopByProcess = [];
        
        // Group BOP by process
        foreach ($bopData as $bop) {
            $prosesName = $bop['nama_proses'];
            if (!isset($bopByProcess[$prosesName])) {
                $bopByProcess[$prosesName] = 0;
            }
            $bopByProcess[$prosesName] += $bop['tarif'];
        }
        
        // Calculate BOP per unit for each process based on BTKL capacity
        foreach ($bopByProcess as $prosesName => $bopPerJam) {
            $kapasitasPerJam = 0;
            
            // Find matching BTKL capacity with typo handling
            foreach($btklDataForDisplay as $btkl) {
                $namaProsesBtkl = $btkl['nama_proses'] ?? '';
                
                if (stripos($namaProsesBtkl, $prosesName) !== false) {
                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                    break;
                }
                
                // Handle typo: "Permbumbuan" should match "Perbumbuan"
                if ($prosesName === 'Perbumbuan' && stripos($namaProsesBtkl, 'Permbumbuan') !== false) {
                    $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 0;
                    break;
                }
            }
            
            // Calculate BOP per unit for this process
            if ($kapasitasPerJam > 0) {
                $bopPerUnit = $bopPerJam / $kapasitasPerJam;
                $totalBiayaBOP += $bopPerUnit;
            }
        }
        
        return $totalBiayaBOP;
    }

    // Add other methods as needed
}
