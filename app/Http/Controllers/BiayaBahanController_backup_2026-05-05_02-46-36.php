<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Bom;
use App\Models\BomDetail;
use App\Models\BomJobCosting;
use App\Models\BomJobBBB;
use App\Models\BomJobBahanPendukung;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\BomJobBOP;
use App\Models\BomJobBTKL;
use App\Support\UnitConverter;
use App\Services\BomSyncService;
use App\Services\BiayaBahanConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiayaBahanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Produk::query()->where('user_id', $user->id); // 🔒 SECURITY: Add user_id filter
        
        // Filter by nama produk
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
        }
        
        // ✅ REMOVED: Filter by harga BOM range (kolom sudah dihapus)
// Filter sekarang menggunakan total_bbb dari bom_job_costings
        
        $produks = $query->orderBy('nama_produk')->paginate(10)->withQueryString();
        
        // LOGIKA SEDERHANA - AMBIL DATA LANGSUNG DARI DATABASE
        $produkBiaya = [];
        
        foreach ($produks as $produk) {
            // 🔒 SEDERHANA: Get BomJobCosting untuk produk ini dengan multi-tenant filtering
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)
                ->where('user_id', auth()->id())
                ->first();
            
            // Get BOM untuk backup data (jika ada)
            $bom = Bom::with('details.bahanBaku')
                ->where('produk_id', $produk->id)
                ->first();
            
            if ($bomJobCosting || $bom) {
                // Prioritaskan BomJobCosting, fallback ke BOM
                $totalBiayaBahanBaku = 0;
                $detailBahanBaku = [];
                
                // Get BBB data directly from bom_job_bbb table
                $bbbData = DB::table('bom_job_bbb as bbb')
                    ->leftJoin('bahan_bakus as bb', 'bbb.bahan_baku_id', '=', 'bb.id')
                    ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
                    ->where('bbb.user_id', auth()->id())
                    ->where('bbb.produk_id', $produk->id)
                    ->select(
                        'bbb.id',
                        'bb.nama_bahan',
                        'bbb.jumlah as qty',
                        'bbb.satuan',
                        'bbb.harga_satuan',
                        'bbb.subtotal',
                        's.nama as satuan_nama'
                    )
                    ->get();
                
                if ($bbbData->count() > 0) {
                    $totalBiayaBahanBaku = $bbbData->sum('subtotal') ?? 0;
                    $detailBahanBaku = $bbbData->map(function($detail) {
                        return [
                            'nama_bahan' => $detail->nama_bahan ?? 'Unknown',
                            'qty' => $detail->qty ?? 0,
                            'satuan' => $detail->satuan_nama ?? $detail->satuan ?? 'unit',
                            'harga_satuan' => $detail->harga_satuan ?? 0,
                            'subtotal' => $detail->subtotal ?? 0,
                            'tipe' => 'Bahan Baku',
                            'status' => 'aktif'
                        ];
                    })->toArray() ?? [];
                }
                
                // 2. Tidak ada Bahan Pendukung (dihapus total)
                $totalBiayaBahanPendukung = 0;
                $detailBahanPendukung = [];
                
                // Total biaya bahan - GUNAKAN DATA DARI QUERY LANGSUNG
                $totalBiayaBahan = $totalBiayaBahanBaku; // Hanya dari BBB query langsung
                
                // Calculate complete HPP (Biaya Bahan + BTKL + BOP)
                $totalHPP = $totalBiayaBahan;
                if ($bomJobCosting) {
                    $totalHPP = $totalBiayaBahan + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
                }
                
                // ✅ REMOVED: Update harga_bom produk (kolom sudah dihapus)
                // Data biaya bahan sekarang disimpan di bom_job_costings, bukan di produks
                
                $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
                
                $produkBiaya[] = [
                    'produk' => $produk,
                    'total_biaya' => $totalBiayaBahan,
                    'total_biaya_bahan_baku' => $totalBiayaBahanBaku,
                    'total_biaya_bahan_pendukung' => $totalBiayaBahanPendukung,
                    'detail_bahan' => $allDetails,
                    'detail_bahan_baku' => $detailBahanBaku,
                    'detail_bahan_pendukung' => $detailBahanPendukung,
                    'total_biaya_bahan' => $totalBiayaBahan,
                    'bom_job_costing' => $bomJobCosting
                ];
            } else {
                // Produk tanpa BOM
                $produkBiaya[] = [
                    'produk' => $produk,
                    'total_biaya' => 0,
                    'total_biaya_bahan_baku' => 0,
                    'total_biaya_bahan_pendukung' => 0,
                    'detail_bahan' => [],
                    'detail_bahan_baku' => [],
                    'detail_bahan_pendukung' => [],
                    'total_biaya_bahan' => 0,
                    'bom_job_costing' => null
                ];
            }
        }
        
        
    // DEBUG: Add logging
    \Log::info("=== BIAYA BAHAN CONTROLLER DEBUG ===");
    \Log::info("User ID: " . auth()->id());
    \Log::info("User exists: " . (auth()->check() ? "YES" : "NO"));
    
    $query = Produk::query()->where('user_id', auth()->id());
    $produks = $query->orderBy('nama_produk')->get();
    \Log::info("Products found: " . $produks->count());
    
    foreach ($produks as $produk) {
        \Log::info("Product: " . $produk->nama_produk . " (ID: " . $produk->id . ")");
        
        $bbbData = DB::table('bom_job_bbb as bbb')
            ->leftJoin('bahan_bakus as bb', 'bbb.bahan_baku_id', '=', 'bb.id')
            ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
            ->where('bbb.user_id', auth()->id())
            ->where('bbb.produk_id', $produk->id)
            ->select(
                'bbb.id',
                'bb.nama_bahan',
                'bbb.jumlah as qty',
                'bbb.satuan',
                'bbb.harga_satuan',
                'bbb.subtotal',
                's.nama as satuan_nama'
            )
            ->get();
        
        \Log::info("BBB records for product " . $produk->id . ": " . $bbbData->count());
        
        if ($bbbData->count() > 0) {
            foreach ($bbbData as $bbb) {
                \Log::info("  - " . $bbb->nama_bahan . ": " . $bbb->subtotal);
            }
        }
    }
    
    \Log::info("Final produkBiaya count: " . count($produkBiaya));
    \Log::info("=== END DEBUG ===");
    
        return view('master-data.biaya-bahan.index', compact('produks', 'produkBiaya'));
    }

    /**
     * Show the form for creating biaya bahan for a product
     */
    public function create($id)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::with(['satuan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Get existing BOM details (for reference if any)
        $bomDetails = BomDetail::with('bahanBaku.satuan')
            ->where('bom_id', function($query) use ($produk) {
                $query->select('id')->from('boms')->where('produk_id', $produk->id);
            })
            ->get();
        
        // Get existing Bahan Pendukung (for reference if any)
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        $bomJobBahanPendukung = $bomJobCosting ? 
            BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->get() : [];
        
        // Get available bahan baku and bahan pendukung for selection with sub satuan
        // CRITICAL MULTI-TENANT: Only show bahan baku milik user yang login
        $bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->where('user_id', auth()->id())->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->where('user_id', auth()->id())->orderBy('nama_bahan')->get();
        
        // Get all satuan for dropdown
        $satuans = \App\Models\Satuan::orderBy('nama')->get();
        
        return view('master-data.biaya-bahan.create-simple', compact(
            'produk',
            'bahanBakus',
            'satuans'
        ));
    }

    /**
     * Show biaya bahan details for a specific product
     */
    public function show($id)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::with(['satuan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Get BOM job costing (this is where biaya bahan data is stored)
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', auth()->id())
            ->with(['detailBBB.bahanBaku.satuan', 'detailBahanPendukung.bahanPendukung.satuan'])
            ->first();
        
        if (!$bomJobCosting) {
            return redirect()->route('master-data.biaya-bahan.create', $produk->id)
                ->with('error', 'Belum ada data biaya bahan untuk produk ini. Silakan tambahkan biaya bahan terlebih dahulu.');
        }
        
        // Prepare detail data - AMBIL LANGSUNG DARI DATABASE DENGAN PERHITUNGAN YANG BENAR
        $detailBahanBaku = [];
        
        // Prioritaskan data dari BomJobBBB (primary storage)
        if ($bomJobCosting && $bomJobCosting->detailBBB && $bomJobCosting->detailBBB->count() > 0) {
            $detailBahanBaku = $bomJobCosting->detailBBB->map(function($detail) {
                $bahanBaku = $detail->bahanBaku;
                
                // Cek apakah bahan ini dihapus atau tidak ada di database
                $isDeleted = !$bahanBaku || ($detail->harga_satuan == 0 && !empty($detail->catatan_hapus));
                
                // Hitung stok real-time dari stock movements (konsisten dengan laporan stok)
                $stokRealTime = 0;
                if ($bahanBaku) {
                    $stockIn = \App\Models\StockMovement::where('item_type', 'material')
                        ->where('item_id', $bahanBaku->id)
                        ->where('direction', 'in')
                        ->sum('qty');
                    
                    $stockOut = \App\Models\StockMovement::where('item_type', 'material')
                        ->where('item_id', $bahanBaku->id)
                        ->where('direction', 'out')
                        ->sum('qty');
                    
                    $stokRealTime = $stockIn - $stockOut;
                }
                
                return [
                    'id' => $detail->id,
                    'nama_bahan' => $bahanBaku ? $bahanBaku->nama_bahan : ($detail->nama_bahan_terhapus ?? 'Unknown'),
                    'qty' => $detail->jumlah ?? 0,
                    'satuan' => $detail->satuan ?? 'unit',
                    'harga_satuan' => $detail->harga_satuan ?? 0, // SUDAH HARGA KONVERSI YANG BENAR
                    'subtotal' => $detail->subtotal ?? 0, // SUDAH PERHITUNGAN YANG BENAR
                    'tipe' => 'Bahan Baku',
                    'status' => $isDeleted ? 'dihapus' : 'aktif',
                    'catatan_hapus' => $detail->catatan_hapus ?? null,
                    'nama_bahan_terhapus' => $detail->nama_bahan_terhapus ?? null,
                    'harga_terakhir' => $detail->harga_terakhir ?? null,
                    'stok_tersedia' => $stokRealTime, // Stok real-time sesuai laporan stok
                    'stok_master' => $bahanBaku ? $bahanBaku->stok : 0 // Stok dari master data untuk perbandingan
                ];
            })->toArray() ?? [];
        } else {
            // Fallback ke BomDetail jika tidak ada di BomJobBBB
            if ($bom) {
                $bomDetails = BomDetail::with('bahanBaku.satuan')->where('bom_id', $bom->id)->get();
                if ($bomDetails && $bomDetails->count() > 0) {
                    $detailBahanBaku = $bomDetails->map(function($detail) {
                        $bahanBaku = $detail->bahanBaku;
                        return [
                            'id' => $detail->id,
                            'nama_bahan' => $bahanBaku->nama_bahan ?? 'Unknown',
                            'qty' => $detail->jumlah ?? 0,
                            'satuan' => $detail->satuan ?? 'unit',
                            'harga_satuan' => $detail->harga_per_satuan ?? 0,
                            'subtotal' => $detail->total_harga ?? 0,
                            'tipe' => 'Bahan Baku',
                            'status' => 'aktif' // Default status untuk fallback data
                        ];
                    })->toArray() ?? [];
                }
            }
        }
        
        $detailBahanPendukung = [];
        if ($bomJobCosting && $bomJobCosting->detailBahanPendukung && $bomJobCosting->detailBahanPendukung->count() > 0) {
            $detailBahanPendukung = $bomJobCosting->detailBahanPendukung->map(function($detail) {
                $bahanPendukung = $detail->bahanPendukung;
                
                // Cek apakah bahan ini dihapus atau tidak ada di database
                $isDeleted = !$bahanPendukung || ($detail->harga_satuan == 0 && !empty($detail->catatan_hapus));
                
                return [
                    'id' => $detail->id,
                    'nama_bahan' => $bahanPendukung ? $bahanPendukung->nama_bahan : ($detail->nama_bahan_terhapus ?? 'Unknown'),
                    'qty' => $detail->jumlah ?? 0,
                    'satuan' => $detail->satuan ?? 'unit',
                    'harga_satuan' => $detail->harga_satuan ?? 0, // SUDAH HARGA KONVERSI YANG BENAR
                    'subtotal' => $detail->subtotal ?? 0, // SUDAH PERHITUNGAN YANG BENAR
                    'tipe' => 'Bahan Pendukung',
                    'status' => $isDeleted ? 'dihapus' : 'aktif',
                    'catatan_hapus' => $detail->catatan_hapus ?? null,
                    'nama_bahan_terhapus' => $detail->nama_bahan_terhapus ?? null,
                    'harga_terakhir' => $detail->harga_terakhir ?? null
                ];
            })->toArray() ?? [];
        }
        
        $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
        
        // Calculate totals - GUNAKAN DATA YANG SUDAH DIPERBAIKI
        $totalBiayaBahanBaku = array_sum(array_column($detailBahanBaku, 'subtotal'));
        $totalBiayaBahanPendukung = array_sum(array_column($detailBahanPendukung, 'subtotal'));
        $totalBiaya = $totalBiayaBahanBaku + $totalBiayaBahanPendukung;
        
        // Alias untuk view compatibility
        $totalBiayaBahan = $totalBiaya;
        
        return view('master-data.biaya-bahan.show', compact(
            'produk',
            'bomJobCosting',
            'detailBahanBaku',
            'detailBahanPendukung',
            'allDetails',
            'totalBiayaBahanBaku',
            'totalBiayaBahanPendukung',
            'totalBiaya',
            'totalBiayaBahan'
        ));
    }

    /**
     * Store biaya bahan for a product - VERSI SEDERHANA YANG BENAR
     */
    public function store(Request $request, $id)
    {
        try {
            // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
            $produk = Produk::where('user_id', auth()->id())
                ->findOrFail($id);

            // 🔒 SIMPLIFIED: Only handle bahan baku input
            $bahanBakuInput = $request->input('bahan_baku', []);

            $validBahanBaku = [];
            foreach ((array) $bahanBakuInput as $item) {
                if (empty($item['id']) || empty($item['jumlah']) || (float) $item['jumlah'] <= 0 || empty($item['satuan'])) {
                    continue;
                }
                $validBahanBaku[] = $item;
            }

            if (count($validBahanBaku) === 0) {
                return back()->withInput()->withErrors([
                    'error' => 'Tidak ada data bahan baku yang valid! Pastikan pilih bahan baku, isi jumlah, dan pilih satuan.'
                ]);
            }

            DB::beginTransaction();

            $totalBiaya = 0;
            $savedCount = 0;

            // 🔒 SEDERHANA: GET OR CREATE BOM JOB COSTING
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)
                ->where('user_id', auth()->id())
                ->first();
            if (!$bomJobCosting) {
                $bomJobCosting = new BomJobCosting();
                $bomJobCosting->produk_id = $produk->id;
                $bomJobCosting->user_id = auth()->id();
                $bomJobCosting->total_bbb = 0;
                $bomJobCosting->save();
            }
            
            // 🔒 SEDERHANA: HAPUS DATA LAMA HANYA DARI bom_job_bbb
            BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
                ->where('user_id', auth()->id())
                ->delete();
            
            // 🔒 SEDERHANA: SIMPAN LANGSUNG KE bom_job_bbb DENGAN KONVERSI YANG BENAR
            foreach ($validBahanBaku as $item) {
                // Verify bahan baku belongs to current user
                $bahanBaku = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->where('user_id', auth()->id())->find($item['id']);
                if (!$bahanBaku) continue;
                
                $jumlah = (float)$item['jumlah'];
                $satuanInput = (string)$item['satuan'];
                
                // 🔒 PERHITUNGAN YANG SAMA DENGAN JAVASCRIPT
                $hargaUtama = $bahanBaku->harga_satuan ?? 0;
                $satuanUtama = $bahanBaku->satuan ? $bahanBaku->satuan->nama : 'unit';
                
                // Hitung harga konversi seperti di JavaScript
                $hargaKonversi = $hargaUtama;
                
                if ($satuanInput !== $satuanUtama) {
                    // Cari sub satuan yang cocok
                    $subSatuanData = [];
                    
                    if ($bahanBaku->subSatuan1) {
                        $subSatuanData[] = [
                            'nama' => $bahanBaku->subSatuan1->nama,
                            'konversi' => $bahanBaku->sub_satuan_1_konversi,
                            'nilai' => $bahanBaku->sub_satuan_1_nilai
                        ];
                    }
                    if ($bahanBaku->subSatuan2) {
                        $subSatuanData[] = [
                            'nama' => $bahanBaku->subSatuan2->nama,
                            'konversi' => $bahanBaku->sub_satuan_2_konversi,
                            'nilai' => $bahanBaku->sub_satuan_2_nilai
                        ];
                    }
                    if ($bahanBaku->subSatuan3) {
                        $subSatuanData[] = [
                            'nama' => $bahanBaku->subSatuan3->nama,
                            'konversi' => $bahanBaku->sub_satuan_3_konversi,
                            'nilai' => $bahanBaku->sub_satuan_3_nilai
                        ];
                    }
                    
                    // Cari match untuk konversi
                    foreach ($subSatuanData as $sub) {
                        if (strtolower(trim($sub['nama'])) === strtolower(trim($satuanInput))) {
                            $konversi = (float)$sub['konversi'] ?? 1;
                            $nilai = (float)$sub['nilai'] ?? 1;
                            // Formula yang sama dengan JavaScript: (hargaUtama * konversi) / nilai
                            $hargaKonversi = ($hargaUtama * $konversi) / $nilai;
                            break;
                        }
                    }
                }
                
                $subtotal = $jumlah * $hargaKonversi;
                $totalBiaya += $subtotal;
                
                // Simpan langsung ke BomJobBBB dengan harga konversi
                $bbbDetail = new BomJobBBB();
                $bbbDetail->bom_job_costing_id = $bomJobCosting->id;
                $bbbDetail->bahan_baku_id = $bahanBaku->id;
                $bbbDetail->jumlah = $jumlah;
                $bbbDetail->satuan = $satuanInput;
                $bbbDetail->harga_satuan = $hargaKonversi; // Simpan harga konversi
                $bbbDetail->subtotal = $subtotal;
                $bbbDetail->user_id = auth()->id(); // 🔒 MULTI-TENANT SECURITY
                $bbbDetail->save();
                
                $savedCount++;
            }
            
            // Update total di bom_job_costing
            $bomJobCosting->total_bbb = $totalBiaya;
            $bomJobCosting->total_hpp = $totalBiaya;
            $bomJobCosting->save();
            
            DB::commit();

            $message = "Berhasil menyimpan biaya bahan untuk \"{$produk->nama_produk}\"!";
            if ($savedCount > 0) {
                $message .= " ({$savedCount} item tersimpan)";
            }

            return redirect()->route('master-data.biaya-bahan.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors([
                'error' => 'ERROR: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing biaya bahan for a product
     */
    public function edit($id)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $produk = Produk::with(['satuan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Get BomJobCosting untuk data utama
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)
            ->where('user_id', auth()->id())
            ->first();
        
        // Get existing Bahan Baku dari BomJobBBB (primary storage)
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $bomJobBBB = $bomJobCosting ? 
            BomJobBBB::with('bahanBaku.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->where('user_id', auth()->id())
                ->get() : [];
        
        // Get existing Bahan Pendukung
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $bomJobBahanPendukung = $bomJobCosting ? 
            BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->where('user_id', auth()->id())
                ->get() : [];
        
        // Get available bahan baku and bahan pendukung for selection with sub satuan
        // CRITICAL MULTI-TENANT: Only show bahan baku milik user yang login
        $bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->where('user_id', auth()->id())->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->where('user_id', auth()->id())->orderBy('nama_bahan')->get();
        
        // Get all satuan for dropdown
        $satuans = \App\Models\Satuan::orderBy('nama')->get();
        
        return view('master-data.biaya-bahan.edit', compact(
            'produk',
            'bomJobBBB',
            'bomJobBahanPendukung',
            'bahanBakus',
            'bahanPendukungs',
            'satuans'
        ));
    }

    /**
     * Update biaya bahan for a product - VERSI SEDERHANA YANG BENAR
     */
    public function update(Request $request, $id)
    {
        try {
            // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
            $produk = Produk::where('user_id', auth()->id())
                ->findOrFail($id);

            $bahanBakuInput = $request->input('bahan_baku', []);
            $bahanPendukungInput = $request->input('bahan_pendukung', []);

            $validBahanBaku = [];
            foreach ((array) $bahanBakuInput as $item) {
                if (empty($item['id']) || empty($item['jumlah']) || (float) $item['jumlah'] <= 0 || empty($item['satuan'])) {
                    continue;
                }
                $validBahanBaku[] = $item;
            }

            if (count($validBahanBaku) === 0) {
                return back()->withInput()->withErrors([
                    'error' => 'Tidak ada data bahan baku yang valid! Pastikan pilih bahan baku, isi jumlah, dan pilih satuan.'
                ]);
            }

            DB::beginTransaction();

            $totalBiaya = 0;
            $savedCount = 0;

            // 🔒 SEDERHANA: GET OR CREATE BOM JOB COSTING
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)
                ->where('user_id', auth()->id())
                ->first();
            if (!$bomJobCosting) {
                $bomJobCosting = new BomJobCosting();
                $bomJobCosting->produk_id = $produk->id;
                $bomJobCosting->user_id = auth()->id();
                $bomJobCosting->total_bbb = 0;
                $bomJobCosting->save();
            }
            
            // 🔒 SEDERHANA: HAPUS DATA LAMA HANYA DARI bom_job_bbb
            BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
                ->where('user_id', auth()->id())
                ->delete();
            
            // 🔒 SEDERHANA: SIMPAN LANGSUNG KE bom_job_bbb DENGAN KONVERSI YANG BENAR
            foreach ($validBahanBaku as $item) {
                // Verify bahan baku belongs to current user
                $bahanBaku = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->where('user_id', auth()->id())->find($item['id']);
                if (!$bahanBaku) continue;
                
                $jumlah = (float)$item['jumlah'];
                $satuanInput = (string)$item['satuan'];
                
                // 🔒 PERHITUNGAN YANG SAMA DENGAN JAVASCRIPT
                $hargaUtama = $bahanBaku->harga_satuan ?? 0;
                $satuanUtama = $bahanBaku->satuan ? $bahanBaku->satuan->nama : 'unit';
                
                // Hitung harga konversi seperti di JavaScript
                $hargaKonversi = $hargaUtama;
                
                if ($satuanInput !== $satuanUtama) {
                    // Cari sub satuan yang cocok
                    $subSatuanData = [];
                    
                    if ($bahanBaku->subSatuan1) {
                        $subSatuanData[] = [
                            'nama' => $bahanBaku->subSatuan1->nama,
                            'konversi' => $bahanBaku->sub_satuan_1_konversi,
                            'nilai' => $bahanBaku->sub_satuan_1_nilai
                        ];
                    }
                    if ($bahanBaku->subSatuan2) {
                        $subSatuanData[] = [
                            'nama' => $bahanBaku->subSatuan2->nama,
                            'konversi' => $bahanBaku->sub_satuan_2_konversi,
                            'nilai' => $bahanBaku->sub_satuan_2_nilai
                        ];
                    }
                    if ($bahanBaku->subSatuan3) {
                        $subSatuanData[] = [
                            'nama' => $bahanBaku->subSatuan3->nama,
                            'konversi' => $bahanBaku->sub_satuan_3_konversi,
                            'nilai' => $bahanBaku->sub_satuan_3_nilai
                        ];
                    }
                    
                    // Cari match untuk konversi
                    foreach ($subSatuanData as $sub) {
                        if (strtolower(trim($sub['nama'])) === strtolower(trim($satuanInput))) {
                            $konversi = (float)$sub['konversi'] ?? 1;
                            $nilai = (float)$sub['nilai'] ?? 1;
                            // Formula yang sama dengan JavaScript: (hargaUtama * konversi) / nilai
                            $hargaKonversi = ($hargaUtama * $konversi) / $nilai;
                            break;
                        }
                    }
                }
                
                $subtotal = $jumlah * $hargaKonversi;
                $totalBiaya += $subtotal;
                
                // Simpan langsung ke BomJobBBB dengan harga konversi
                $bbbDetail = new BomJobBBB();
                $bbbDetail->bom_job_costing_id = $bomJobCosting->id;
                $bbbDetail->bahan_baku_id = $bahanBaku->id;
                $bbbDetail->jumlah = $jumlah;
                $bbbDetail->satuan = $satuanInput;
                $bbbDetail->harga_satuan = $hargaKonversi; // Simpan harga konversi
                $bbbDetail->subtotal = $subtotal;
                $bbbDetail->user_id = auth()->id(); // 🔒 MULTI-TENANT SECURITY
                $bbbDetail->save();
                
                $savedCount++;
            }
            
            // Update total di bom_job_costing
            $bomJobCosting->total_bbb = $totalBiaya;
            $bomJobCosting->total_hpp = $totalBiaya;
            $bomJobCosting->save();
            
            DB::commit();

            $message = "Berhasil menyimpan biaya bahan untuk \"{$produk->nama_produk}\"!";
            if ($savedCount > 0) {
                $message .= " ({$savedCount} item tersimpan, Total: Rp " . number_format($totalBiaya, 0, ',', '.') . ")";
            }
            
            return redirect()->route('master-data.biaya-bahan.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors([
                'error' => 'ERROR: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
            $produk = Produk::where('user_id', auth()->id())
                ->findOrFail($id);
            
            DB::beginTransaction();
            
            // Hapus BOM details jika ada
            $bom = Bom::where('produk_id', $produk->id)->first();
            if ($bom) {
                BomDetail::where('bom_id', $bom->id)->delete();
                $bom->delete();
            }
            
            // Hapus BomJobCosting dan details jika ada
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
            if ($bomJobCosting) {
                BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->delete();
                BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->delete();
                $bomJobCosting->delete();
            }
            
            // ✅ REMOVED: Reset harga_bom produk (kolom sudah dihapus)
            // Data biaya bahan sudah dihapus dari bom_job_costings
            
            DB::commit();
            
            return redirect()->route('master-data.biaya-bahan.index')
                ->with('success', "Biaya bahan untuk produk \"{$produk->nama_produk}\" telah dihapus dengan sempurna!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'ERROR: ' . $e->getMessage());
        }
    }
}
