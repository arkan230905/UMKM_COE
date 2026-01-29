<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\BomCalculationService; // Add this line

class BomController extends Controller
{
    protected $bomCalculationService;

    public function __construct(BomCalculationService $bomCalculationService)
    {
        $this->middleware('auth');
        $this->bomCalculationService = $bomCalculationService;
    }
    
    /**
     * Get biaya bahan data from biaya bahan page (final calculated data)
     * Data ini harus sesuai dengan yang ditampilkan di halaman biaya bahan
     */
    private function getBiayaBahanData($produkId = null)
    {
        try {
            if (!$produkId) {
                // Jika tidak ada produk yang dipilih, return empty
                return collect([]);
            }
            
            $produk = Produk::find($produkId);
            if (!$produk) {
                return collect([]);
            }
            
            // Gunakan logika yang sama dengan BiayaBahanController
            $converter = new \App\Support\UnitConverter();
            $allDetails = [];
            
            // 1. Ambil data Bahan Baku dari BOM yang sudah ada
            $bomDetails = \App\Models\BomDetail::with('bahanBaku.satuan')
                ->where('bom_id', function($query) use ($produk) {
                    $query->select('id')->from('boms')->where('produk_id', $produk->id);
                })
                ->get();
            
            foreach ($bomDetails as $detail) {
                if (!$detail->bahanBaku) continue;
                
                // Get satuan base from bahan baku
                $satuanBaseObj = $detail->bahanBaku->satuan;
                $satuanBase = is_object($satuanBaseObj) ? $satuanBaseObj->nama : ($satuanBaseObj ?: 'unit');
                
                $qty = (float) $detail->jumlah;
                $satuan = $detail->satuan ?: $satuanBase;
                
                // AMBIL HARGA TERBARU dari master bahan baku
                $hargaSatuan = (float) $detail->bahanBaku->harga_satuan;
                
                if (!$hargaSatuan || !$satuan || !$satuanBase) {
                    continue;
                }
                
                try {
                    $qtyBase = $converter->convert($qty, $satuan, $satuanBase);
                    $subtotal = $hargaSatuan * $qtyBase;
                    
                    $allDetails[] = [
                        'id' => $detail->bahanBaku->id,
                        'nama' => $detail->bahanBaku->nama_bahan,
                        'kode' => $detail->bahanBaku->kode_bahan,
                        'harga' => $hargaSatuan,
                        'jumlah' => $qty,
                        'jumlah_base' => $qtyBase,
                        'satuan' => $satuan,
                        'satuan_base' => $satuanBase,
                        'subtotal' => $subtotal,
                        'kategori' => 'Bahan Baku',
                        'tipe' => 'bahan_baku'
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // 2. Ambil data Bahan Pendukung dari BomJobCosting
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            
            if ($bomJobCosting) {
                $bomJobBahanPendukung = \App\Models\BomJobBahanPendukung::with('bahanPendukung.satuan')
                    ->where('bom_job_costing_id', $bomJobCosting->id)
                    ->get();
                
                foreach ($bomJobBahanPendukung as $jobPendukung) {
                    if (!$jobPendukung->bahanPendukung) continue;
                    
                    // Get satuan base from bahan pendukung
                    $satuanBaseObj = $jobPendukung->bahanPendukung->satuan;
                    $satuanBase = is_object($satuanBaseObj) ? $satuanBaseObj->nama : ($satuanBaseObj ?: 'unit');
                    
                    $qty = (float) $jobPendukung->jumlah;
                    $satuan = $jobPendukung->satuan ?: $satuanBase;
                    $hargaSatuan = (float) $jobPendukung->bahanPendukung->harga_satuan;
                    
                    if (!$hargaSatuan || !$satuan || !$satuanBase) {
                        continue;
                    }
                    
                    try {
                        $qtyBase = $converter->convert($qty, $satuan, $satuanBase);
                        $subtotal = $hargaSatuan * $qtyBase;
                        
                        $allDetails[] = [
                            'id' => $jobPendukung->bahanPendukung->id,
                            'nama' => $jobPendukung->bahanPendukung->nama_bahan,
                            'kode' => $jobPendukung->bahanPendukung->kode_bahan ?? 'BP-' . $jobPendukung->bahanPendukung->id,
                            'harga' => $hargaSatuan,
                            'jumlah' => $qty,
                            'jumlah_base' => $qtyBase,
                            'satuan' => $satuan,
                            'satuan_base' => $satuanBase,
                            'subtotal' => $subtotal,
                            'kategori' => 'Bahan Pendukung',
                            'tipe' => 'bahan_pendukung'
                        ];
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // 3. Jika tidak ada data dari BOM/BomJobCosting, tampilkan pesan yang jelas
            if (empty($allDetails)) {
                \Log::info('No biaya bahan data found for product', [
                    'produk_id' => $produkId,
                    'produk_nama' => $produk->nama_produk,
                    'has_bom' => \App\Models\Bom::where('produk_id', $produkId)->exists(),
                    'has_bomjobcosting' => \App\Models\BomJobCosting::where('produk_id', $produkId)->exists()
                ]);
            }
            
            return collect($allDetails);
            
        } catch (\Exception $e) {
            \Log::error('Error getting biaya bahan data: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Hitung BTKL dengan kapasitas per jam
     * Beban per produk = (durasi × tarif) ÷ kapasitas_per_jam
     */
    private function hitungBTKL($durasi, $tarif, $kapasitasPerJam = 0, $kapasitasMaster = 0)
    {
        if ($kapasitasPerJam && $kapasitasPerJam > 0) {
            $totalBiayaPerJam = $durasi * $tarif;
            return $totalBiayaPerJam / $kapasitasPerJam;
        } elseif ($kapasitasMaster && $kapasitasMaster > 0) {
            $totalBiayaPerJam = $durasi * $tarif;
            return $totalBiayaPerJam / $kapasitasMaster;
        } else {
            // Fallback ke perhitungan lama
            return $durasi * $tarif;
        }
    }
    
    /**
     * Calculate BOM cost with unit conversion
     */
    public function calculateBomCost(Request $request, $produkId)
    {
        $quantity = $request->input('quantity', 1);
        $bomCost = $this->bomCalculationService->calculateBomCost($produkId, $quantity);
        
        return response()->json([
            'success' => true,
            'data' => $bomCost
        ]);
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
                $query->whereHas('boms');
            } elseif ($request->status == 'belum') {
                $query->whereDoesntHave('boms');
            }
        }
        
        $produks = $query->orderBy('nama_produk')->paginate(15);
        
        // Add BTKL data to each product
        foreach ($produks as $produk) {
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $btklCount = 0;
            
            // Calculate actual biaya bahan from BOM details
            $bom = $produk->boms->first();
            if ($bom) {
                $bomDetails = \App\Models\BomDetail::where('bom_id', $bom->id)->get();
                $totalBiayaBahan = $bomDetails->sum('total_harga');
            }
            
            // Get BomJobCosting for this product
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            if ($bomJobCosting) {
                // Get BTKL data with biaya per produk (biaya_per_produk = tarif_per_jam ÷ kapasitas_per_jam)
                $bomJobBtkl = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                    ->join('proses_produksis', 'bom_job_btkl.proses_produksi_id', '=', 'proses_produksis.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_btkl.*', 'proses_produksis.tarif_btkl as tarif_per_jam', 'proses_produksis.kapasitas_per_jam')
                    ->get();
                
                $btklCount = $bomJobBtkl->count();
                
                // Calculate biaya per produk: tarif_per_jam ÷ kapasitas_per_jam
                $totalBTKL = $bomJobBtkl->sum(function($item) {
                    $kapasitas = $item->kapasitas_per_jam ?? 1;
                    return ($item->tarif_per_jam / $kapasitas) * $item->durasi_jam;
                });
                
                // Also add bahan pendukung to biaya bahan
                $bahanPendukung = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->sum('subtotal');
                $totalBiayaBahan += $bahanPendukung;
            }
            
            // Add data to product
            $produk->total_biaya_bahan = $totalBiayaBahan;
            $produk->total_btkl = $totalBTKL;
            $produk->btkl_count = $btklCount;
            $produk->has_btkl = $btklCount > 0;
        }
        
        return view('master-data.bom.index', compact('produks'));
    }

    /**
     * Update BOM costs when material prices change
     */
    public function updateBomCosts()
    {
        try {
            DB::beginTransaction();
            
            // Get all BOMs with their details
            $boms = Bom::with(['details.bahanBaku', 'details.bahanPendukung'])->get();
            
            foreach ($boms as $bom) {
                $totalBiaya = 0;
                
                foreach ($bom->details as $detail) {
                    // Ambil harga terbaru
                    if ($detail->bahan_baku_id) {
                        $hargaTerbaru = $detail->bahanBaku->harga_rata_rata ?? 0;
                    } elseif ($detail->bahan_pendukung_id) {
                        $hargaTerbaru = $detail->bahanPendukung->harga_satuan ?? 0;
                    } else {
                        $hargaTerbaru = 0;
                    }
                    
                    // Update harga di detail
                    $detail->update(['harga_satuan' => $hargaTerbaru]);
                    
                    // Hitung subtotal
                    $subtotal = $detail->jumlah * $hargaTerbaru;
                    $totalBiaya += $subtotal;
                }
                
                // Update total biaya BOM
                $bom->update(['total_biaya' => $totalBiaya]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Biaya BOM berhasil diperbarui!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui biaya BOM: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        // Cek apakah ada produk_id yang dipilih dari index
        $selectedProdukId = $request->get('produk_id');
        $selectedProduk = null;
        
        if ($selectedProdukId) {
            // Jika ada produk_id, ambil produk tersebut
            $selectedProduk = Produk::find($selectedProdukId);
            
            // Cek apakah produk sudah punya BOM
            if ($selectedProduk && $selectedProduk->boms->count() > 0) {
                return redirect()->route('master-data.bom.index')
                    ->with('error', 'Produk "' . $selectedProduk->nama_produk . '" sudah memiliki BOM.');
            }
            
            // Jika produk valid dan belum punya BOM, gunakan produk ini
            if ($selectedProduk) {
                $produks = collect([$selectedProduk]);
            } else {
                return redirect()->route('master-data.bom.index')
                    ->with('error', 'Produk tidak ditemukan.');
            }
        } else {
            // Jika tidak ada produk_id, ambil semua produk yang belum punya BOM
            $produkIdsWithBom = Bom::pluck('produk_id')->toArray();
            $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
            
            // Jika tidak ada produk yang bisa dibuat BOM-nya
            if ($produks->isEmpty()) {
                return redirect()->route('master-data.bom.index')
                    ->with('info', 'Semua produk sudah memiliki BOM. Tidak ada produk yang bisa ditambahkan BOM-nya.');
            }
        }
        
        // Data untuk biaya bahan (dari halaman biaya bahan)
        $biayaBahan = collect([]);
        if ($selectedProduk) {
            $biayaBahan = $this->getBiayaBahanData($selectedProduk->id);
        }
        
        // Data BTKL (proses produksi)
        $prosesProduksis = \App\Models\ProsesProduksi::with('bopProses')
            ->orderBy('kode_proses')
            ->get();
        
        return view('master-data.bom.create', [
            'produks' => $produks,
            'selectedProdukId' => $selectedProdukId,
            'selectedProduk' => $selectedProduk,
            'biayaBahan' => $biayaBahan,
            'prosesProduksis' => $prosesProduksis
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log request data
        \Log::info('BOM Store Request Data:', $request->all());
        
        // Validasi input
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id|unique:boms,produk_id',
            'bahan_id' => 'required|array|min:1',
            'bahan_id.*' => 'required|integer',
            'bahan_jumlah' => 'required|array|min:1',
            'bahan_jumlah.*' => 'required|numeric|min:0.0001',
            'proses_id' => 'nullable|array',
            'proses_id.*' => 'nullable|exists:proses_produksis,id',
            'jam_dibutuhkan' => 'nullable|array',
            'jam_dibutuhkan.*' => 'nullable|numeric|min:0',
            'bop_nama' => 'nullable|array',
            'bop_nama.*' => 'nullable|string',
            'bop_biaya_per_unit' => 'nullable|array',
            'bop_biaya_per_unit.*' => 'nullable|numeric|min:0',
            'bop_jumlah_unit' => 'nullable|array',
            'bop_jumlah_unit.*' => 'nullable|numeric|min:0',
            'total_biaya_bahan' => 'required|numeric|min:0',
            'total_btkl' => 'required|numeric|min:0',
            'total_bop' => 'required|numeric|min:0',
        ], [
            'produk_id.unique' => 'Produk ini sudah memiliki BOM',
            'bahan_id.required' => 'Minimal pilih satu bahan',
            'bahan_jumlah.*.min' => 'Jumlah tidak boleh nol',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total HPP
            $totalBiayaBahan = (float)$request->total_biaya_bahan;
            $totalBtkl = (float)$request->total_btkl;
            $totalBop = (float)$request->total_bop;
            $totalHpp = $totalBiayaBahan + $totalBtkl + $totalBop;

            // Simpan data BOM
            $bom = new Bom();
            $bom->produk_id = $request->produk_id;
            $bom->bahan_baku_id = $request->bahan_id[0]; // Untuk kompatibilitas
            $bom->jumlah = $request->bahan_jumlah[0]; // Untuk kompatibilitas
            $bom->satuan_resep = 'pcs'; // Default
            $bom->total_bbb = $totalBiayaBahan;
            $bom->total_biaya = $totalHpp;
            $bom->btkl_per_unit = $totalBtkl;
            $bom->bop_per_unit = $totalBop;
            $bom->total_btkl = $totalBtkl;
            $bom->total_bop = $totalBop;
            $bom->total_hpp = $totalHpp;
            $bom->periode = now()->format('Y-m');
            $bom->save();

            // Simpan detail bahan (dari biaya bahan)
            foreach ($request->bahan_id as $key => $bahanId) {
                $jumlah = (float)$request->bahan_jumlah[$key];
                
                // Cari data bahan dari biaya bahan
                $biayaBahan = $this->getBiayaBahanData($request->produk_id);
                $bahanData = $biayaBahan->firstWhere('id', $bahanId);
                
                if ($bahanData) {
                    $subtotal = $bahanData['harga'] * $jumlah;
                    
                    $bom->details()->create([
                        'bahan_baku_id' => $bahanId,
                        'jumlah' => $jumlah,
                        'satuan' => $bahanData['satuan'],
                        'harga_per_satuan' => $bahanData['harga'],
                        'total_harga' => $subtotal,
                        'kategori' => 'BBB',
                    ]);
                }
            }
            
            // Simpan proses BTKL
            if (!empty($request->proses_id)) {
                foreach ($request->proses_id as $key => $prosesId) {
                    if (empty($prosesId)) continue;
                    
                    $proses = \App\Models\ProsesProduksi::find($prosesId);
                    if (!$proses) continue;
                    
                    $jamDibutuhkan = (float)($request->jam_dibutuhkan[$key] ?? 0);
                    $biayaBtkl = $proses->kapasitas_per_jam > 0 ? 
                        ($jamDibutuhkan * $proses->tarif_per_jam) / $proses->kapasitas_per_jam : 0;
                    
                    $bom->proses()->create([
                        'proses_produksi_id' => $prosesId,
                        'urutan' => $key + 1,
                        'durasi' => $jamDibutuhkan,
                        'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                        'satuan_durasi' => 'jam',
                        'biaya_btkl' => $biayaBtkl,
                        'biaya_bop' => 0, // BOP manual terpisah
                    ]);
                }
            }

            // Update harga produk
            $bom->updateProductPrice();

            DB::commit();

            return redirect()
                ->route('master-data.bom.index')
                ->with('success', 'BOM berhasil disimpan dengan struktur baru');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat menyimpan BOM: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan BOM: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified BOM.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Ambil data BOM dengan relasi yang diperlukan
            $bom = Bom::with([
                'produk',
                'details' => function($query) {
                    $query->with(['bahanBaku' => function($q) {
                        $q->with('satuan')->withDefault();
                    }]);
                },
                'proses' => function($query) {
                    $query->with(['prosesProduksi', 'bomProsesBops.komponenBop'])
                        ->orderBy('urutan');
                }
            ])->findOrFail($id);

            // Get BomJobCosting untuk data BTKL yang benar
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $bom->produk_id)
                ->first();

            // Get BTKL data dari bom_job_btkl dengan biaya per produk yang benar
            $btklData = [];
            if ($bomJobCosting) {
                $btklData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                    ->join('proses_produksis', 'bom_job_btkl.proses_produksi_id', '=', 'proses_produksis.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_btkl.*', 'proses_produksis.tarif_btkl as tarif_per_jam', 'proses_produksis.kapasitas_per_jam')
                    ->get()
                    ->map(function($item) {
                        return [
                            'nama_proses' => $item->nama_proses,
                            'durasi_jam' => $item->durasi_jam,
                            'tarif_per_jam' => $item->tarif_per_jam,
                            'kapasitas_per_jam' => $item->kapasitas_per_jam,
                            'biaya_per_produk' => ($item->tarif_per_jam / ($item->kapasitas_per_jam ?? 1)) * $item->durasi_jam,
                            'subtotal' => $item->subtotal
                        ];
                    });
            }

            return view('master-data.bom.show', compact('bom', 'btklData'));
        } catch (\Exception $e) {
            \Log::error('Error in BomController@show: ' . $e->getMessage());
            return redirect()->route('master-data.bom.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan detail BOM: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman cetak BOM
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print($id)
    {
        $bom = Bom::with([
            'produk', 
            'details.bahanBaku.satuan',
            'proses' => function($query) {
                $query->with(['prosesProduksi', 'bomProsesBops.komponenBop'])
                    ->orderBy('urutan');
            }
        ])->findOrFail($id);
        
        return view('master-data.bom.print', compact('bom'));
    }

    public function edit($id)
    {
        $bom = Bom::with(['details.bahanBaku.satuan', 'produk'])
            ->findOrFail($id);
            
        // Data untuk biaya bahan (dari halaman biaya bahan)
        $biayaBahan = $this->getBiayaBahanData($bom->produk_id);
        
        // Get BomJobCosting untuk data BTKL yang benar
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $bom->produk_id)->first();
        
        // Get BTKL data dari bom_job_btkl (data yang benar)
        $btklData = [];
        if ($bomJobCosting) {
            $btklData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                ->join('proses_produksis', 'bom_job_btkl.proses_produksi_id', '=', 'proses_produksis.id')
                ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                ->select('bom_job_btkl.*', 'proses_produksis.tarif_btkl as tarif_per_jam', 'proses_produksis.kapasitas_per_jam')
                ->get()
                ->map(function($item) {
                    return [
                        'nama_proses' => $item->nama_proses,
                        'durasi_jam' => $item->durasi_jam,
                        'tarif_per_jam' => $item->tarif_per_jam,
                        'kapasitas_per_jam' => $item->kapasitas_per_jam,
                        'biaya_per_produk' => ($item->tarif_per_jam / ($item->kapasitas_per_jam ?? 1)) * $item->durasi_jam,
                        'subtotal' => $item->subtotal
                    ];
                });
        }
        
        // Data BOP (proses produksi) - yang bisa diedit
        $prosesProduksis = \App\Models\ProsesProduksi::with('bopProses')
            ->orderBy('kode_proses')
            ->get();
        
        return view('master-data.bom.edit', compact('bom', 'biayaBahan', 'btklData', 'prosesProduksis'));
    }

    public function update(Request $request, $id)
    {
        // Temukan BOM yang akan diupdate
        $bom = Bom::findOrFail($id);
        
        // Validasi input
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id|unique:boms,produk_id,' . $id,
            'bahan_id' => 'required|array|min:1',
            'bahan_id.*' => 'required|integer',
            'bahan_jumlah' => 'required|array|min:1',
            'bahan_jumlah.*' => 'required|numeric|min:0.0001',
            'proses_id' => 'nullable|array',
            'proses_id.*' => 'nullable|exists:proses_produksis,id',
            'jam_dibutuhkan' => 'nullable|array',
            'jam_dibutuhkan.*' => 'nullable|numeric|min:0',
            'bop_nama' => 'nullable|array',
            'bop_nama.*' => 'nullable|string',
            'bop_biaya_per_unit' => 'nullable|array',
            'bop_biaya_per_unit.*' => 'nullable|numeric|min:0',
            'bop_jumlah_unit' => 'nullable|array',
            'bop_jumlah_unit.*' => 'nullable|numeric|min:0',
            'total_biaya_bahan' => 'required|numeric|min:0',
            'total_btkl' => 'required|numeric|min:0',
            'total_bop' => 'required|numeric|min:0',
        ], [
            'produk_id.unique' => 'Produk ini sudah memiliki BOM',
            'bahan_id.required' => 'Minimal pilih satu bahan',
            'bahan_jumlah.*.min' => 'Jumlah tidak boleh nol',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total HPP
            $totalBiayaBahan = (float)$request->total_biaya_bahan;
            $totalBtkl = (float)$request->total_btkl;
            $totalBop = (float)$request->total_bop;
            $totalHpp = $totalBiayaBahan + $totalBtkl + $totalBop;

            // Update data BOM
            $bom->bahan_baku_id = $request->bahan_id[0]; // Untuk kompatibilitas
            $bom->jumlah = $request->bahan_jumlah[0]; // Untuk kompatibilitas
            $bom->satuan_resep = 'pcs'; // Default
            $bom->total_bbb = $totalBiayaBahan;
            $bom->total_biaya = $totalHpp;
            $bom->btkl_per_unit = $totalBtkl;
            $bom->bop_per_unit = $totalBop;
            $bom->total_btkl = $totalBtkl;
            $bom->total_bop = $totalBop;
            $bom->total_hpp = $totalHpp;
            $bom->periode = now()->format('Y-m');
            $bom->save();
            
            // Hapus detail lama
            $bom->details()->delete();
            
            // Simpan detail bahan baru (dari biaya bahan)
            foreach ($request->bahan_id as $key => $bahanId) {
                $jumlah = (float)$request->bahan_jumlah[$key];
                
                // Cari data bahan dari biaya bahan
                $biayaBahan = $this->getBiayaBahanData($bom->produk_id);
                $bahanData = $biayaBahan->firstWhere('id', $bahanId);
                
                if ($bahanData) {
                    $subtotal = $bahanData['harga'] * $jumlah;
                    
                    $bom->details()->create([
                        'bahan_baku_id' => $bahanId,
                        'jumlah' => $jumlah,
                        'satuan' => $bahanData['satuan'],
                        'harga_per_satuan' => $bahanData['harga'],
                        'total_harga' => $subtotal,
                        'kategori' => 'BBB',
                    ]);
                }
            }
            
            // Hapus proses lama
            foreach ($bom->proses as $oldProses) {
                $oldProses->bomProsesBops()->delete();
            }
            $bom->proses()->delete();
            
            // Simpan proses BTKL baru
            if (!empty($request->proses_id)) {
                foreach ($request->proses_id as $key => $prosesId) {
                    if (empty($prosesId)) continue;
                    
                    $proses = \App\Models\ProsesProduksi::find($prosesId);
                    if (!$proses) continue;
                    
                    $jamDibutuhkan = (float)($request->jam_dibutuhkan[$key] ?? 0);
                    $biayaBtkl = $proses->kapasitas_per_jam > 0 ? 
                        ($jamDibutuhkan * $proses->tarif_per_jam) / $proses->kapasitas_per_jam : 0;
                    
                    $bom->proses()->create([
                        'proses_produksi_id' => $prosesId,
                        'urutan' => $key + 1,
                        'durasi' => $jamDibutuhkan,
                        'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                        'satuan_durasi' => 'jam',
                        'biaya_btkl' => $biayaBtkl,
                        'biaya_bop' => 0, // BOP manual terpisah
                    ]);
                }
            }

            // Update harga produk
            $bom->updateProductPrice();
            
            DB::commit();
            
            return redirect()->route('master-data.bom.index')
                ->with('success', 'BOM berhasil diperbarui dengan struktur baru');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat update BOM: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui BOM: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $bom = Bom::findOrFail($id);
        $produk_id = $bom->produk_id;
        $produk = $bom->produk;
        
        DB::beginTransaction();
        
        try {
            // Hapus detail BOM
            $bom->details()->delete();
            
            // Hapus BOM
            $bom->delete();
            
            // Reset harga_bom di produk menjadi 0
            if ($produk) {
                $produk->update([
                    'harga_bom' => 0,
                    'harga_jual' => 0, // Optional: reset harga jual juga
                ]);
            }
            
            DB::commit();
            
            return redirect()
                ->route('master-data.produk.show', $produk_id)
                ->with('success', 'BOM berhasil dihapus dan harga produk direset.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat menghapus BOM: ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menghapus BOM: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate kode BOM
     */
    public function generateKodeBom()
    {
        // Generate kode BOM dengan format BOM-YYYYMMDD-XXX
        $date = now()->format('Ymd');
        $number = Bom::withTrashed()->where('kode', 'like', 'BOM-' . $date . '-%')->count() + 1;
        return 'BOM-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Konversi jumlah ke KG berdasarkan satuan
     */
    /**
     * Helper method to convert to KG (kept for backward compatibility)
     * @deprecated Use BahanBaku::convertToKg() instead
     */
    private function konversiKeKg($jumlah, $satuan)
    {
        $bahanBaku = new BahanBaku();
        return $bahanBaku->convertToKg($jumlah, $satuan);
    }

    public function updateByProduk(Request $request, $produk_id)
    {
        $produk = Produk::findOrFail($produk_id);
        $rows = $request->input('rows', []); // rows[bom_id][jumlah], rows[bom_id][satuan_resep]
        $converter = new UnitConverter();
        $total_bahan = 0.0;

        foreach ($rows as $bomId => $row) {
            $bom = Bom::with('bahanBaku')->where('produk_id', $produk->id)->findOrFail($bomId);
            $qtyResep = (float) ($row['jumlah'] ?? 0);
            $satuanResep = $row['satuan_resep'] ?? $bom->bahanBaku->satuan;

            // Hitung ulang subtotal berdasarkan konversi ke satuan bahan
            $qtyBase = $converter->convert($qtyResep, (string)$satuanResep, (string)$bom->bahanBaku->satuan);
            $subtotal = (float) ($bom->bahanBaku->harga_satuan ?? 0) * (float) $qtyBase;
            $total_bahan += $subtotal;

            $bom->update([
                'jumlah' => $qtyResep,
                'satuan_resep' => $satuanResep,
                'total_biaya' => $subtotal,
            ]);
        }

        // BTKL/BOPB: gunakan field produk jika ada, jika tidak pakai input/fallback persen dari total bahan
        $btklRate = (float) (config('app.btkl_percent') ?? 0.2);
        $bopRate  = (float) (config('app.bop_percent') ?? 0.1);

        if (!is_null($produk->btkl_per_unit)) {
            $btkl_sum = (float) $produk->btkl_per_unit;
        } else {
            $btkl_sum = $request->filled('btkl') ? (float)$request->btkl : ($total_bahan * $btklRate);
        }

        if ($produk->bopb_method && $produk->bopb_rate) {
            $method = strtolower($produk->bopb_method);
            if ($method === 'per_unit') {
                $bop_sum = (float) $produk->bopb_rate;
            } elseif ($method === 'per_hour') {
                $hours = (float) ($produk->labor_hours_per_unit ?? 0);
                $bop_sum = (float) $produk->bopb_rate * $hours;
            } else {
                $bop_sum = $total_bahan * $bopRate;
            }
        } else {
            $bop_sum  = $request->filled('bop')  ? (float)$request->bop  : ($total_bahan * $bopRate);
        }

        $grand_total = $total_bahan + $btkl_sum + $bop_sum;
        $margin = (float) ($produk->margin_percent ?? 0);
        $harga_jual = $grand_total * (1 + $margin/100);

        $produk->update([
            'harga_jual' => $harga_jual,
            'btkl_default' => $btkl_sum,
            'bop_default' => $bop_sum,
        ]);

        return redirect()->route('master-data.bom.index', ['produk_id' => $produk->id])
            ->with('success', 'BOM berhasil diperbarui.');
    }
}
