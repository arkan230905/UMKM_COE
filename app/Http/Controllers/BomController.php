<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\ProsesProduksi;
use App\Models\BomJobCosting;
use App\Models\BomJobBTKL;
use App\Models\BomJobBOP;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
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
        // Only show products that have COMPLETE HPP calculation
        // Complete means: has biaya bahan AND has BTKL AND has BOP
        $query = Produk::with(['bomJobCosting', 'satuan']);
        
        // Only show products that have BOM Job Costing with complete data
        $query->whereHas('bomJobCosting', function($q) {
            $q->where(function($subQ) {
                $subQ->where('total_bbb', '>', 0)
                     ->orWhere('total_bahan_pendukung', '>', 0);
            })
            ->where('total_btkl', '>', 0)
            ->where('total_bop', '>', 0);
        });
        
        // Filter by product name
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
        }
        
        $produks = $query->orderBy('nama_produk')->paginate(15);
        
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
                        'satuan' => $detail->satuan ?: ($bahanBaku->satuan->nama ?? ''), // Use BOM detail satuan first
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
                        'satuan' => $detail->satuan ?: ($bahanPendukung->satuan->nama ?? ''), // Use BOM detail satuan first
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
                        $jumlahPegawai = Pegawai::where('jabatan', $item->nama_jabatan)->count();
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
                // NOTE: total_biaya_bahan HANYA menghitung bahan baku, tanpa bahan pendukung
                $totalBiayaBahan = $bomJobCosting->total_bbb;  // HANYA BBB
                $totalBiayaBTKL = $bomJobCosting->total_btkl;
                $totalBiayaBOP = $bomJobCosting->total_bop;
            }

            // Total BOM lengkap tetap menghitung semua komponen
            $totalBiayaBOM = $bomJobCosting ? $bomJobCosting->total_hpp : ($totalBiayaBahan + $totalBiayaBTKL + $totalBiayaBOP);

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
            
            // Get BTKL data dari BomJobBTKL
            $btklDataForDisplay = [];
            if ($bomJobCosting) {
                $btklDataRaw = DB::table('bom_job_btkl')
                    ->leftJoin('proses_produksis', 'bom_job_btkl.nama_proses', '=', 'proses_produksis.nama_proses')
                    ->leftJoin('jabatans', 'proses_produksis.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*', 
                        'proses_produksis.kode_proses',
                        'proses_produksis.nama_proses as proses_nama',
                        'proses_produksis.kapasitas_per_jam as proses_kapasitas',
                        'jabatans.nama as nama_jabatan',
                        'jabatans.tarif as tarif_per_jam_jabatan'
                    )
                    ->get();
                    
                $btklDataForDisplay = $btklDataRaw->map(function($item) {
                    // Get jumlah pegawai from keterangan or calculate from jabatan
                    $jumlahPegawai = 0;
                    $tarifPerJamJabatan = $item->tarif_per_jam ?? 0;
                    
                    // Try to extract from keterangan (format: "X pegawai @ Rp Y/jam")
                    if ($item->keterangan && preg_match('/(\d+)\s*pegawai/', $item->keterangan, $matches)) {
                        $jumlahPegawai = intval($matches[1]);
                    } elseif ($item->nama_jabatan) {
                        $jumlahPegawai = Pegawai::where('jabatan', $item->nama_jabatan)->count();
                    }
                    
                    // Use tarif from jabatan if available, otherwise from bom_job_btkl
                    if ($item->tarif_per_jam_jabatan && $item->tarif_per_jam_jabatan > 0) {
                        $tarifPerJamJabatan = $item->tarif_per_jam_jabatan;
                    } elseif ($item->tarif_per_jam && $item->tarif_per_jam > 0) {
                        $tarifPerJamJabatan = $item->tarif_per_jam;
                    }
                    
                    // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                    $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                    
                    // Use kapasitas from proses or bom_job_btkl
                    $kapasitasPerJam = $item->kapasitas_per_jam ?? $item->proses_kapasitas ?? 0;
                    
                    // Calculate biaya per produk
                    $biayaPerProduk = $kapasitasPerJam > 0 ? $tarifBtkl / $kapasitasPerJam : 0;
                    
                    return [
                        'id' => $item->id,
                        'kode_proses' => $item->kode_proses ?? 'N/A',
                        'nama_proses' => $item->nama_proses ?? $item->proses_nama ?? 'N/A',
                        'nama_jabatan' => $item->nama_jabatan ?? 'N/A',
                        'tarif_per_jam' => $tarifPerJamJabatan,
                        'tarif_btkl' => $tarifBtkl,
                        'kapasitas_per_jam' => $kapasitasPerJam,
                        'durasi_jam' => $item->durasi_jam ?? 1,
                        'jumlah_pegawai' => $jumlahPegawai,
                        'satuan' => 'Jam',
                        'biaya_per_produk' => $biayaPerProduk,
                        'subtotal' => $item->subtotal ?? $biayaPerProduk,
                        'keterangan' => $item->keterangan ?? ''
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
                $bomJobBopData = BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
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
                
                // BOP should be 0 if no data exists
                $totalBiayaBOP = 0;
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
     * Update BOM prices from stock report and return fresh data
     */
    public function updateBomFromStockReport($produkId)
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

            // Get or create BomJobCosting
            $bomJobCosting = BomJobCosting::where('produk_id', $validated['produk_id'])->first();
            
            if (!$bomJobCosting) {
                return back()->with('error', 'Produk belum memiliki data biaya bahan. Silakan isi biaya bahan terlebih dahulu.');
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
            $produk = Produk::find($validated['produk_id']);
            $produk->harga_pokok = $validated['total_hpp'];
            $produk->save();

            DB::commit();

            return redirect()->route('master-data.harga-pokok-produksi.index')
                ->with('success', 'Harga Pokok Produksi berhasil dihitung dan disimpan untuk produk: ' . $produk->nama_produk);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan HPP: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            // Find product
            $produk = Produk::with('bomJobCosting')->findOrFail($id);
            
            if (!$produk->bomJobCosting) {
                return redirect()->route('master-data.harga-pokok-produksi.index')
                    ->with('error', 'Produk ini belum memiliki data HPP.');
            }
            
            $bomJobCosting = $produk->bomJobCosting;
            
            // Get selected BTKL processes
            $selectedBtklIds = BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)
                ->pluck('nama_proses')
                ->toArray();
            
            // Match nama_proses with proses_produksis.nama_proses to get IDs
            $selectedProsesIds = ProsesProduksi::whereIn('nama_proses', $selectedBtklIds)
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
