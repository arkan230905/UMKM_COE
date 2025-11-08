<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
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
            $totalBiayaBahanBaku = $bom->details->sum('total_harga');
            $btkl = $totalBiayaBahanBaku * 0.6; // 60% dari total biaya bahan baku
            $bop = $totalBiayaBahanBaku * 0.4; // 40% dari total biaya bahan baku
            $totalBiayaProduksi = $totalBiayaBahanBaku + $btkl + $bop;
            
            $bom->total_biaya = $totalBiayaBahanBaku;
            $bom->total_btkl = $btkl;
            $bom->total_bop = $bop;
            $bom->total_biaya_produksi = $totalBiayaProduksi;
            
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
            $query->select('id', 'nama');
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
        ], [
            'produk_id.unique' => 'Produk ini sudah memiliki BOM',
            'bahan_baku_id.required' => 'Minimal pilih satu bahan baku',
            'jumlah.*.min' => 'Jumlah tidak boleh nol',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total biaya bahan baku
            $totalBiayaBahan = 0;
            $details = [];
            $bahanBakuDigunakan = [];
            $bahanBakuErrors = [];

            // Validasi stok dan hitung total biaya
            foreach ($request->bahan_baku_id as $key => $bahanBakuId) {
                $bahanBaku = BahanBaku::with('satuan')->findOrFail($bahanBakuId);
                $jumlah = (float)$request->jumlah[$key];
                $satuanDipilih = strtoupper(trim($request->satuan[$key] ?? ($bahanBaku->satuan->kode ?? 'KG')));
                
                // Validasi harga - sementara nonaktifkan validasi harga
                // if (empty($bahanBaku->harga_satuan) || $bahanBaku->harga_satuan <= 0) {
                //     $bahanBaku->harga_satuan = 0; // Set harga default sementara
                // }
                
                // Validasi stok
                if (!$bahanBaku->hasEnoughStock($jumlah, $satuanDipilih)) {
                    $available = $bahanBaku->getAvailableStock($satuanDipilih);
                    $bahanBakuErrors[] = "Stok {$bahanBaku->nama_bahan} tidak mencukupi. " .
                                      "Tersedia: " . number_format($available, 3, ',', '.') . " {$satuanDipilih}, " .
                                      "Dibutuhkan: " . number_format($jumlah, 3, ',', '.') . " {$satuanDipilih}";
                    continue;
                }
                
                // Konversi ke KG untuk perhitungan
                $jumlahDalamKg = $bahanBaku->convertToKg($jumlah, $satuanDipilih);
                $hargaSatuan = (float)$bahanBaku->harga_satuan;
                $subtotal = $hargaSatuan * $jumlahDalamKg;
                
                // Simpan detail
                $details[] = [
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'satuan' => $satuanDipilih,
                    'harga_per_satuan' => $hargaSatuan,
                    'total_harga' => $subtotal,
                    'kategori' => $request->kategori[$key] ?? 'BOP',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $totalBiayaBahan += $subtotal;
                
                // Simpan total penggunaan bahan baku untuk validasi stok
                if (!isset($bahanBakuDigunakan[$bahanBakuId])) {
                    $bahanBakuDigunakan[$bahanBakuId] = [
                        'bahan' => $bahanBaku,
                        'total_digunakan' => 0
                    ];
                }
                $bahanBakuDigunakan[$bahanBakuId]['total_digunakan'] += $jumlahDalamKg;
                
                $subtotal = $hargaSatuan * $jumlahDalamKg;

                $details[] = [
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'satuan' => $request->satuan[$key] ?? ($bahanBaku->satuan->nama ?? 'pcs'),
                    'harga_per_satuan' => $hargaSatuan,
                    'total_harga' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $totalBiayaBahan += $subtotal;
            }

            // Validasi stok
            foreach ($bahanBakuDigunakan as $item) {
                if ($item['bahan']->stok < $item['total_digunakan']) {
                    $bahanBakuErrors[] = "Stok {$item['bahan']->nama} tidak mencukupi. Stok tersedia: " . 
                                       number_format($item['bahan']->stok, 3, ',', '.') . 
                                       " KG, dibutuhkan: " . 
                                       number_format($item['total_digunakan'], 3, ',', '.') . " KG";
                }
            }
            
            // Jika ada error validasi bahan baku
            if (!empty($bahanBakuErrors)) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors(['bahan_baku' => $bahanBakuErrors]);
            }

            // Hitung BTKL (30% dari total biaya bahan baku)
            $btkl = $totalBiayaBahan * 0.3;
            
            // Hitung BOP (20% dari total biaya bahan baku)
            $bopRate = 20; // 20%
            $bop = $totalBiayaBahan * ($bopRate / 100);
            
            // Hitung total biaya produksi
            $totalBiayaProduksi = $totalBiayaBahan + $btkl + $bop;

            // Simpan data BOM
            $bom = new Bom();
            $bom->produk_id = $request->produk_id;
            $bom->bahan_baku_id = $request->bahan_baku_id[0]; // Simpan bahan baku pertama
            $bom->jumlah = $request->jumlah[0]; // Simpan jumlah bahan baku pertama
            $bom->satuan_resep = $request->satuan[0] ?? 'pcs'; // Simpan satuan bahan baku pertama
            $bom->total_biaya = $totalBiayaProduksi;
            $bom->btkl_per_unit = $btkl;
            $bom->bop_rate = $bopRate;
            $bom->bop_per_unit = $bop;
            $bom->total_btkl = $btkl;
            $bom->total_bop = $bop;
            $bom->periode = now()->format('Y-m');
            // Hitung dan simpan total biaya
            $bom->hitungTotalBiaya();
            $bom->save();

            // Simpan detail BOM
            foreach ($details as $detail) {
                $bom->details()->create($detail);
            }

            // Update harga produk
            $bom->updateHargaProduk();

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
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id|unique:boms,produk_id',
            'details' => 'required|array|min:1',
            'details.*.bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'details.*.jumlah' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        
        try {
            // Validasi harga_satuan bahan; jika belum ada pembelian -> error spesifik
            $totalBiaya = 0;
            $details = [];
            $missing = [];

            foreach ($request->details as $detail) {
                $bahanBaku = BahanBaku::find($detail['bahan_baku_id']);
                $harga = (float)($bahanBaku->harga_satuan ?? 0);
                if ($harga <= 0) {
                    $missing[] = (string)($bahanBaku->nama_bahan ?? $bahanBaku->nama ?? ('ID #'.$bahanBaku->id));
                    continue;
                }
                $qty = (float)($detail['jumlah'] ?? $detail['kuantitas'] ?? 0);
                $subtotal = $harga * $qty;

                $details[] = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'jumlah' => $qty,
                    'satuan' => $detail['satuan'] ?? null,
                    'harga_per_satuan' => $harga,
                    'total_harga' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $totalBiaya += $subtotal;
            }

            if (!empty($missing)) {
                return back()->withErrors([
                    'harga_satuan' => 'Bahan baku '.implode(', ', $missing).' belum pernah dibeli (harga belum ada).'
                ])->withInput();
            }

            // Simpan BOM
            $bom = new Bom();
            $bom->produk_id = $validated['produk_id'];
            // generate kode BOM otomatis
            $prefix = 'BOM-'.date('Ym').'-';
            $latest = Bom::where('kode_bom','like',$prefix.'%')->orderBy('kode_bom','desc')->first();
            $number = $latest ? ((int) substr($latest->kode_bom, strlen($prefix))) + 1 : 1;
            $bom->kode_bom = $prefix . str_pad((string)$number, 4, '0', STR_PAD_LEFT);
            $bom->total_biaya = $totalBiaya;
            $bom->save();

            // Simpan detail BOM
            $bom->details()->createMany($details);

            // Tidak mengubah harga_jual produk di tahap BOM

            DB::commit();

            return redirect()->route('master-data.bom.index', ['produk_id' => $bom->produk_id])
                ->with('success', 'BOM berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $bom = Bom::with(['produk', 'details.bahanBaku.satuan'])->findOrFail($id);
        
        // Hitung total biaya
        $totalBiayaBahan = $bom->details->sum('total_harga');
        $totalBiayaProduksi = $bom->total_biaya;
        
        // Hitung persentase
        $persentaseBahan = $totalBiayaBahan > 0 ? ($totalBiayaBahan / $totalBiayaProduksi) * 100 : 0;
        $persentaseBTKL = $bom->total_btkl > 0 ? ($bom->total_btkl / $totalBiayaProduksi) * 100 : 0;
        $persentaseBOP = $bom->total_bop > 0 ? ($bom->total_bop / $totalBiayaProduksi) * 100 : 0;
        
        return view('master-data.bom.show', [
            'bom' => $bom,
            'totalBiayaBahan' => $totalBiayaBahan,
            'totalBiayaProduksi' => $totalBiayaProduksi,
            'persentaseBahan' => $persentaseBahan,
            'persentaseBTKL' => $persentaseBTKL,
            'persentaseBOP' => $persentaseBOP,
        ]);
    }

    /**
     * Menampilkan halaman cetak BOM
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print($id)
    {
        $bom = Bom::with(['produk', 'details.bahanBaku.satuan'])->findOrFail($id);
        
        // Hitung total biaya
        $totalBiayaBahan = $bom->details->sum('total_harga');
        $totalBiayaProduksi = $bom->total_biaya;
        
        // Hitung persentase
        $persentaseBahan = $totalBiayaBahan > 0 ? ($totalBiayaBahan / $totalBiayaProduksi) * 100 : 0;
        $persentaseBTKL = $bom->total_btkl > 0 ? ($bom->total_btkl / $totalBiayaProduksi) * 100 : 0;
        $persentaseBOP = $bom->total_bop > 0 ? ($bom->total_bop / $totalBiayaProduksi) * 100 : 0;
        
        return view('master-data.bom.print', [
            'bom' => $bom,
            'totalBiayaBahan' => $totalBiayaBahan,
            'totalBiayaProduksi' => $totalBiayaProduksi,
            'persentaseBahan' => $persentaseBahan,
            'persentaseBTKL' => $persentaseBTKL,
            'persentaseBOP' => $persentaseBOP,
        ]);
    }

    public function edit($id)
    {
        $bom = Bom::with(['details.bahanBaku.satuan', 'produk'])
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
            'kategori' => 'sometimes|array',
            'kategori.*' => 'sometimes|string|in:BTKL,BOP',
        ], [
            'produk_id.unique' => 'Produk ini sudah memiliki BOM',
            'bahan_baku_id.required' => 'Minimal pilih satu bahan baku',
            'jumlah.*.min' => 'Jumlah tidak boleh nol',
        ]);

        DB::beginTransaction();

        try {
            // Inisialisasi variabel
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $totalBOP = 0;
            $details = [];
            $bahanBakuDigunakan = [];
            $bahanBakuErrors = [];
            
            // Dapatkan detail BOM yang sudah ada
            $existingDetails = $bom->details()->get();
            $oldQuantities = [];
            
            // Simpan kuantitas lama untuk perhitungan stok
            foreach ($existingDetails as $detail) {
                $bahanBaku = $detail->bahanBaku;
                $oldQtyInKg = $bahanBaku->convertToKg($detail->jumlah, $detail->satuan);
                $oldQuantities[$detail->bahan_baku_id] = $oldQtyInKg;
            }
            
            // Proses setiap bahan baku yang dimasukkan
            foreach ($request->bahan_baku_id as $key => $bahanBakuId) {
                $bahanBaku = BahanBaku::with('satuan')->findOrFail($bahanBakuId);
                $jumlah = (float)$request->jumlah[$key];
                $satuanDipilih = strtoupper(trim($request->satuan[$key] ?? ($bahanBaku->satuan->kode ?? 'KG')));
                $kategori = $request->kategori[$key] ?? 'BOP';
                
                // Konversi ke KG untuk perhitungan
                $jumlahDalamKg = $bahanBaku->convertToKg($jumlah, $satuanDipilih);
                
                // Hitung perbedaan stok (tambahkan kembali kuantitas lama, kurangi dengan yang baru)
                $oldQtyInKg = $oldQuantities[$bahanBakuId] ?? 0;
                $stockDifference = $oldQtyInKg - $jumlahDalamKg;
                
                // Periksa ketersediaan stok (memperhitungkan perbedaan)
                $availableStock = $bahanBaku->stok + $stockDifference;
                
                if ($availableStock < 0) {
                    $availableInUnit = $bahanBaku->getAvailableStock($satuanDipilih);
                    $bahanBakuErrors[] = "Stok {$bahanBaku->nama_bahan} tidak mencukupi. " .
                                      "Tersedia: " . number_format($availableInUnit, 3, ',', '.') . " {$satuanDipilih}, " .
                                      "Dibutuhkan: " . number_format($jumlah, 3, ',', '.') . " {$satuanDipilih}";
                    continue;
                }
                
                // Hitung harga per KG dan subtotal
                $hargaPerKg = (float)$bahanBaku->harga_satuan;
                $subtotal = $hargaPerKg * $jumlahDalamKg;
                
                // Hitung BTKL dan BOP
                if ($kategori === 'BTKL') {
                    $totalBTKL += $subtotal;
                } else {
                    $totalBOP += $subtotal;
                }
                
                // Simpan detail bahan baku
                $details[] = [
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'satuan' => $satuanDipilih,
                    'harga_per_satuan' => $hargaPerKg,
                    'total_harga' => $subtotal,
                    'kategori' => $kategori,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $totalBiayaBahan += $subtotal;
                
                // Simpan total penggunaan bahan baku
                $bahanBakuDigunakan[$bahanBakuId] = [
                    'bahan' => $bahanBaku,
                    'old_quantity' => $oldQtyInKg,
                    'new_quantity' => $jumlahDalamKg
                ];
            }
            
            // Jika ada error validasi bahan baku
            if (!empty($bahanBakuErrors)) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors(['bahan_baku' => $bahanBakuErrors]);
            }
            
            // Update data BOM
            $bom->update([
                'produk_id' => $request->produk_id,
                'total_biaya' => $totalBiayaBahan,
                'total_btkl' => $totalBTKL,
                'total_bop' => $totalBOP,
                'updated_at' => now()
            ]);
            
            // Update stok bahan baku (kembalikan stok lama, kurangi dengan yang baru)
            foreach ($bahanBakuDigunakan as $item) {
                $bahan = $item['bahan'];
                $stokDifference = $item['old_quantity'] - $item['new_quantity'];
                $bahan->increment('stok', $stokDifference);
            }
            
            // Hapus detail lama
            $bom->details()->delete();
            
            // Simpan detail baru
            $bom->details()->createMany($details);
            
            DB::commit();
            
            return redirect()->route('master-data.bom.show', $bom->id)
                ->with('success', 'BOM berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui BOM: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    public function destroy($id)
    {
        $bom = Bom::findOrFail($id);
        $produk_id = $bom->produk_id;
        
        DB::beginTransaction();
        
        try {
            $bom->details()->delete();
            $bom->delete();
            
            DB::commit();
            
            return redirect()
                ->route('master-data.produk.show', $produk_id)
                ->with('success', 'BOM berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollBack();
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
