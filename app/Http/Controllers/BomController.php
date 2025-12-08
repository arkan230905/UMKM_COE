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

    public function index()
    {
        $produks = Produk::all();
        $selectedProductId = request('produk_id');
        
        $query = Bom::with(['produk', 'details.bahanBaku.satuan']);
        
        if ($selectedProductId) {
            $query->where('produk_id', $selectedProductId);
        }
        
        // Paginate dulu baru mapping
        $boms = $query->latest()->paginate(10);
        
        // Mapping data setelah pagination
        $boms->getCollection()->transform(function($bom) {
            // Gunakan data yang sudah tersimpan di database
            // Jangan hitung ulang karena sudah benar di database
            $totalBiayaBahanBaku = $bom->details->sum('total_harga');
            
            // total_biaya di database sudah berisi HPP (Bahan + BTKL + BOP)
            // Jadi kita hanya perlu menambahkan attribute untuk tampilan
            $bom->total_biaya_produksi = $bom->total_biaya; // HPP dari database
            $bom->total_biaya_bahan_baku = $totalBiayaBahanBaku; // Untuk info tambahan
            
            return $bom;
        });
            
        return view('master-data.bom.index', [
            'boms' => $boms,
            'produks' => $produks,
            'selectedProductId' => $selectedProductId
        ]);
    }

    public function create()
    {
        // Ambil ID produk yang sudah memiliki BOM
        $produkIdsWithBom = Bom::pluck('produk_id')->toArray();
        
        // Ambil produk yang belum memiliki BOM
        $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
        
        // Jika tidak ada produk yang bisa dibuat BOM-nya
        if ($produks->isEmpty()) {
            return redirect()->route('master-data.bom.index')
                ->with('info', 'Semua produk sudah memiliki BOM. Tidak ada produk yang bisa ditambahkan BOM-nya.');
        }
        
        // Eager load satuan relationship and filter out any null relationships
        $bahanBakus = BahanBaku::with(['satuan' => function($query) {
            $query->select('id', 'nama', 'kode');
        }])->get();
        
        // Debug data bahan baku
        \Log::info('Bahan Baku Data:', $bahanBakus->toArray());
        
        // Ambil semua satuan yang tersedia
        $satuans = \App\Models\Satuan::all(['id', 'nama', 'kode']);
        
        return view('master-data.bom.create', [
            'produks' => $produks,
            'bahanBakus' => $bahanBakus,
            'satuans' => $satuans
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log request data
        \Log::info('BOM Store Request Data:', $request->all());
        
        // Validasi input
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id|unique:boms,produk_id',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|numeric|min:0.0001',
            'satuan' => 'required|array|min:1',
            // Process Costing fields (optional)
            'proses_id' => 'nullable|array',
            'proses_id.*' => 'nullable|exists:proses_produksis,id',
            'proses_durasi' => 'nullable|array',
            'proses_durasi.*' => 'nullable|numeric|min:0',
            'proses_urutan' => 'nullable|array',
        ], [
            'produk_id.unique' => 'Produk ini sudah memiliki BOM',
            'bahan_baku_id.required' => 'Minimal pilih satu bahan baku',
            'jumlah.*.min' => 'Jumlah tidak boleh nol',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total biaya bahan baku (BBB)
            $totalBiayaBahan = 0;
            $details = [];
            $bahanBakuErrors = [];

            foreach ($request->bahan_baku_id as $key => $bahanBakuId) {
                $bahanBaku = BahanBaku::with('satuan')->findOrFail($bahanBakuId);
                $jumlah = (float)$request->jumlah[$key];
                $satuanDipilih = strtoupper(trim($request->satuan[$key] ?? ($bahanBaku->satuan->kode ?? 'KG')));
                
                $hargaSatuan = (float)$bahanBaku->harga_satuan;
                if ($hargaSatuan <= 0) {
                    $bahanBakuErrors[] = "Bahan baku {$bahanBaku->nama_bahan} belum memiliki harga.";
                    continue;
                }
                
                $jumlahDalamKg = $bahanBaku->convertToKg($jumlah, $satuanDipilih);
                $subtotal = $hargaSatuan * $jumlahDalamKg;
                $hargaPerSatuanDipilih = $subtotal / $jumlah;
                
                $details[] = [
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'satuan' => $satuanDipilih,
                    'harga_per_satuan' => $hargaPerSatuanDipilih,
                    'total_harga' => $subtotal,
                    'kategori' => 'BBB',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $totalBiayaBahan += $subtotal;
            }
            
            if (!empty($bahanBakuErrors)) {
                DB::rollBack();
                return back()->withInput()->withErrors(['bahan_baku' => $bahanBakuErrors]);
            }

            // Hitung BTKL dan BOP dari proses produksi (Process Costing)
            $totalBtkl = 0;
            $totalBop = 0;
            $prosesData = [];
            
            $hasProses = !empty($request->proses_id) && is_array($request->proses_id);
            
            if ($hasProses) {
                foreach ($request->proses_id as $key => $prosesId) {
                    if (empty($prosesId)) continue;
                    
                    $proses = \App\Models\ProsesProduksi::with('prosesBops.komponenBop')->find($prosesId);
                    if (!$proses) continue;
                    
                    $durasi = (float)($request->proses_durasi[$key] ?? 0);
                    $urutan = (int)($request->proses_urutan[$key] ?? ($key + 1));
                    
                    // Hitung BTKL = durasi × tarif_btkl
                    $biayaBtkl = $durasi * $proses->tarif_btkl;
                    
                    // Hitung BOP dari komponen default
                    $biayaBop = 0;
                    $bopDetails = [];
                    foreach ($proses->prosesBops as $pb) {
                        $kuantitas = $pb->kuantitas_default * $durasi;
                        $tarif = $pb->komponenBop->tarif_per_satuan ?? 0;
                        $totalBiayaKomponen = $kuantitas * $tarif;
                        $biayaBop += $totalBiayaKomponen;
                        
                        $bopDetails[] = [
                            'komponen_bop_id' => $pb->komponen_bop_id,
                            'kuantitas' => $kuantitas,
                            'tarif' => $tarif,
                            'total_biaya' => $totalBiayaKomponen
                        ];
                    }
                    
                    $prosesData[] = [
                        'proses_produksi_id' => $prosesId,
                        'urutan' => $urutan,
                        'durasi' => $durasi,
                        'satuan_durasi' => $proses->satuan_btkl,
                        'biaya_btkl' => $biayaBtkl,
                        'biaya_bop' => $biayaBop,
                        'bop_details' => $bopDetails
                    ];
                    
                    $totalBtkl += $biayaBtkl;
                    $totalBop += $biayaBop;
                }
            }
            
            // Fallback ke persentase jika tidak ada proses
            if (!$hasProses || empty($prosesData)) {
                $totalBtkl = $totalBiayaBahan * 0.6;
                $totalBop = $totalBiayaBahan * 0.4;
            }
            
            // Hitung total HPP
            $totalHpp = $totalBiayaBahan + $totalBtkl + $totalBop;

            // Simpan data BOM
            $bom = new Bom();
            $bom->produk_id = $request->produk_id;
            $bom->bahan_baku_id = $request->bahan_baku_id[0];
            $bom->jumlah = $request->jumlah[0];
            $bom->satuan_resep = $request->satuan[0] ?? 'pcs';
            $bom->total_bbb = $totalBiayaBahan;
            $bom->total_biaya = $totalHpp;
            $bom->btkl_per_unit = $totalBtkl;
            $bom->bop_rate = $totalBiayaBahan > 0 ? ($totalBop / $totalBiayaBahan) : 0;
            $bom->bop_per_unit = $totalBop;
            $bom->total_btkl = $totalBtkl;
            $bom->total_bop = $totalBop;
            $bom->total_hpp = $totalHpp;
            $bom->periode = now()->format('Y-m');
            $bom->save();

            // Simpan detail BOM (Bahan Baku)
            foreach ($details as $detail) {
                $bom->details()->create($detail);
            }
            
            // Simpan proses produksi (Process Costing)
            if (!empty($prosesData)) {
                foreach ($prosesData as $pd) {
                    $bopDetails = $pd['bop_details'];
                    unset($pd['bop_details']);
                    
                    $bomProses = $bom->proses()->create($pd);
                    
                    // Simpan detail BOP per proses
                    foreach ($bopDetails as $bopDetail) {
                        $bomProses->bomProsesBops()->create($bopDetail);
                    }
                }
            }

            // Update harga produk
            $bom->updateProductPrice();

            DB::commit();

            // Kembalikan response JSON untuk AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'BOM berhasil disimpan',
                    'bom_id' => $bom->id,
                    'redirect_url' => route('master-data.bom.index') . '?highlight=' . $bom->id
                ]);
            }

            return redirect()
                ->route('master-data.bom.index')
                ->with([
                    'success' => 'BOM berhasil disimpan',
                    'bom_id' => $bom->id
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat menyimpan BOM: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan BOM: ' . $e->getMessage(),
                    'errors' => ['system' => $e->getMessage()]
                ], 422);
            }
            
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
            // Ambil data BOM dengan relasi yang diperlukan termasuk proses produksi
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

            return view('master-data.bom.show', compact('bom'));
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
        $bom = Bom::with(['details.bahanBaku.satuan', 'produk', 'proses.prosesProduksi'])
            ->findOrFail($id);
            
        $bahanBakus = BahanBaku::with('satuan')->get();
        $bomDetails = $bom->details;
        $produk = $bom->produk;
        
        return view('master-data.bom.edit', compact('bom', 'bahanBakus', 'bomDetails', 'produk'));
    }

    public function update(Request $request, $id)
    {
        // Temukan BOM yang akan diupdate
        $bom = Bom::findOrFail($id);
        
        // Validasi input
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id|unique:boms,produk_id,' . $id,
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|numeric|min:0.0001',
            'satuan' => 'required|array|min:1',
            // Process Costing fields (optional)
            'proses_id' => 'nullable|array',
            'proses_id.*' => 'nullable|exists:proses_produksis,id',
            'proses_durasi' => 'nullable|array',
            'proses_durasi.*' => 'nullable|numeric|min:0',
            'proses_urutan' => 'nullable|array',
        ], [
            'produk_id.unique' => 'Produk ini sudah memiliki BOM',
            'bahan_baku_id.required' => 'Minimal pilih satu bahan baku',
            'jumlah.*.min' => 'Jumlah tidak boleh nol',
        ]);

        DB::beginTransaction();

        try {
            // Inisialisasi variabel
            $totalBiayaBahan = 0;
            $details = [];
            $bahanBakuErrors = [];
            
            // Proses setiap bahan baku yang dimasukkan
            foreach ($request->bahan_baku_id as $key => $bahanBakuId) {
                $bahanBaku = BahanBaku::with('satuan')->findOrFail($bahanBakuId);
                $jumlah = (float)$request->jumlah[$key];
                $satuanDipilih = strtoupper(trim($request->satuan[$key] ?? ($bahanBaku->satuan->kode ?? 'KG')));
                
                // Validasi harga satuan
                $hargaSatuan = (float)$bahanBaku->harga_satuan;
                if ($hargaSatuan <= 0) {
                    $bahanBakuErrors[] = "Bahan baku {$bahanBaku->nama_bahan} belum memiliki harga. Silakan lakukan pembelian terlebih dahulu.";
                    continue;
                }
                
                // Konversi ke KG untuk perhitungan
                $jumlahDalamKg = $bahanBaku->convertToKg($jumlah, $satuanDipilih);
                $subtotal = $hargaSatuan * $jumlahDalamKg;
                
                // Hitung harga per satuan yang dipilih (untuk display)
                $hargaPerSatuanDipilih = $subtotal / $jumlah;
                
                // Simpan detail bahan baku
                $details[] = [
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'satuan' => $satuanDipilih,
                    'harga_per_satuan' => $hargaPerSatuanDipilih,
                    'total_harga' => $subtotal,
                    'kategori' => 'BBB',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $totalBiayaBahan += $subtotal;
            }
            
            // Jika ada error validasi bahan baku
            if (!empty($bahanBakuErrors)) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors(['bahan_baku' => $bahanBakuErrors]);
            }

            // Hitung BTKL dan BOP dari proses produksi (Process Costing)
            $totalBtkl = 0;
            $totalBop = 0;
            $prosesData = [];
            
            $hasProses = !empty($request->proses_id) && is_array($request->proses_id);
            
            if ($hasProses) {
                foreach ($request->proses_id as $key => $prosesId) {
                    if (empty($prosesId)) continue;
                    
                    $proses = \App\Models\ProsesProduksi::with('prosesBops.komponenBop')->find($prosesId);
                    if (!$proses) continue;
                    
                    $durasi = (float)($request->proses_durasi[$key] ?? 0);
                    $urutan = (int)($request->proses_urutan[$key] ?? ($key + 1));
                    
                    // Hitung BTKL = durasi × tarif_btkl
                    $biayaBtkl = $durasi * $proses->tarif_btkl;
                    
                    // Hitung BOP dari komponen default
                    $biayaBop = 0;
                    $bopDetails = [];
                    foreach ($proses->prosesBops as $pb) {
                        $kuantitas = $pb->kuantitas_default * $durasi;
                        $tarif = $pb->komponenBop->tarif_per_satuan ?? 0;
                        $totalBiayaKomponen = $kuantitas * $tarif;
                        $biayaBop += $totalBiayaKomponen;
                        
                        $bopDetails[] = [
                            'komponen_bop_id' => $pb->komponen_bop_id,
                            'kuantitas' => $kuantitas,
                            'tarif' => $tarif,
                            'total_biaya' => $totalBiayaKomponen
                        ];
                    }
                    
                    $prosesData[] = [
                        'proses_produksi_id' => $prosesId,
                        'urutan' => $urutan,
                        'durasi' => $durasi,
                        'satuan_durasi' => $proses->satuan_btkl,
                        'biaya_btkl' => $biayaBtkl,
                        'biaya_bop' => $biayaBop,
                        'bop_details' => $bopDetails
                    ];
                    
                    $totalBtkl += $biayaBtkl;
                    $totalBop += $biayaBop;
                }
            }
            
            // Fallback ke persentase jika tidak ada proses
            if (!$hasProses || empty($prosesData)) {
                $totalBtkl = $totalBiayaBahan * 0.6;
                $totalBop = $totalBiayaBahan * 0.4;
            }
            
            // Hitung total HPP
            $totalHpp = $totalBiayaBahan + $totalBtkl + $totalBop;

            // Update data BOM
            $bom->bahan_baku_id = $request->bahan_baku_id[0];
            $bom->jumlah = $request->jumlah[0];
            $bom->satuan_resep = $request->satuan[0] ?? 'pcs';
            $bom->total_bbb = $totalBiayaBahan;
            $bom->total_biaya = $totalHpp;
            $bom->btkl_per_unit = $totalBtkl;
            $bom->bop_rate = $totalBiayaBahan > 0 ? ($totalBop / $totalBiayaBahan) : 0;
            $bom->bop_per_unit = $totalBop;
            $bom->total_btkl = $totalBtkl;
            $bom->total_bop = $totalBop;
            $bom->total_hpp = $totalHpp;
            $bom->periode = now()->format('Y-m');
            $bom->save();
            
            // Hapus detail lama
            $bom->details()->delete();
            
            // Simpan detail baru
            foreach ($details as $detail) {
                $bom->details()->create($detail);
            }
            
            // Hapus proses lama
            foreach ($bom->proses as $oldProses) {
                $oldProses->bomProsesBops()->delete();
            }
            $bom->proses()->delete();
            
            // Simpan proses produksi baru (Process Costing)
            if (!empty($prosesData)) {
                foreach ($prosesData as $pd) {
                    $bopDetails = $pd['bop_details'];
                    unset($pd['bop_details']);
                    
                    $bomProses = $bom->proses()->create($pd);
                    
                    // Simpan detail BOP per proses
                    foreach ($bopDetails as $bopDetail) {
                        $bomProses->bomProsesBops()->create($bopDetail);
                    }
                }
            }

            // Update harga produk
            $bom->updateProductPrice();
            
            DB::commit();
            
            return redirect()->route('master-data.bom.index')
                ->with('success', 'BOM berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat update BOM: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
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
