<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Bom;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\BomDetail;
use App\Models\BomJobCosting;
use App\Models\BomJobBahanPendukung;
use App\Support\UnitConverter;
use App\Services\BomSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $query = Produk::query();
        
        // Filter by nama produk
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
        }
        
        // Filter by harga BOM range
        if ($request->filled('harga_bom_min')) {
            $query->where('harga_bom', '>=', $request->harga_bom_min);
        }
        if ($request->filled('harga_bom_max')) {
            $query->where('harga_bom', '<=', $request->harga_bom_max);
        }
        
        $produks = $query->orderBy('nama_produk')->paginate(10)->withQueryString();
        
        // LOGIKA SEDERHANA YANG SUDAH BENAR
        $produkBiaya = [];
        
        foreach ($produks as $produk) {
            // Get BOM untuk produk ini
            $bom = Bom::with('details.bahanBaku')
                ->where('produk_id', $produk->id)
                ->first();
            
            // Get BomJobCosting untuk bahan pendukung
            $bomJobCosting = BomJobCosting::with('detailBahanPendukung.bahanPendukung')
                ->where('produk_id', $produk->id)
                ->first();
            
            if ($bom || $bomJobCosting) {
                // Hitung total dari BOM details (sudah tersimpan dengan benar)
                $totalBiayaBahanBaku = 0;
                if ($bom && $bom->details) {
                    $totalBiayaBahanBaku = $bom->details->sum('total_harga') ?? 0;
                }
                
                // Hitung total dari Bahan Pendukung
                $totalBiayaBahanPendukung = 0;
                if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
                    $totalBiayaBahanPendukung = $bomJobCosting->detailBahanPendukung->sum('subtotal') ?? 0;
                }
                
                // Total biaya bahan
                $totalBiayaBahan = $totalBiayaBahanBaku + $totalBiayaBahanPendukung;
                
                // Update harga_bom produk jika berbeda
                if ($produk->harga_bom != $totalBiayaBahan) {
                    $produk->update(['harga_bom' => $totalBiayaBahan]);
                }
                
                // Siapkan detail data dengan validasi
                $detailBahanBaku = [];
                if ($bom && $bom->details) {
                    $detailBahanBaku = $bom->details->map(function($detail) {
                        // Validasi data untuk menghindari error
                        $bahanBaku = $detail->bahanBaku;
                        if (!$bahanBaku) {
                            return [
                                'nama_bahan' => 'Unknown',
                                'qty' => $detail->jumlah ?? 0,
                                'satuan' => $detail->satuan ?? 'unit',
                                'harga_satuan' => $detail->harga_per_satuan ?? 0,
                                'subtotal' => $detail->total_harga ?? 0,
                                'tipe' => 'Bahan Baku'
                            ];
                        }
                        
                        return [
                            'nama_bahan' => is_string($bahanBaku->nama_bahan) ? $bahanBaku->nama_bahan : 'Unknown',
                            'qty' => $detail->jumlah ?? 0,
                            'satuan' => $detail->satuan ?? 'unit',
                            'harga_satuan' => $detail->harga_per_satuan ?? 0,
                            'subtotal' => $detail->total_harga ?? 0,
                            'tipe' => 'Bahan Baku'
                        ];
                    })->toArray() ?? [];
                }
                
                $detailBahanPendukung = [];
                if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
                    $detailBahanPendukung = $bomJobCosting->detailBahanPendukung->map(function($pendukung) {
                        // Validasi data untuk menghindari error
                        $bahanPendukung = $pendukung->bahanPendukung;
                        if (!$bahanPendukung) {
                            return [
                                'nama_bahan' => 'Unknown',
                                'qty' => $pendukung->jumlah ?? 0,
                                'satuan' => $pendukung->satuan ?? 'unit',
                                'harga_satuan' => $pendukung->harga_satuan ?? 0,
                                'subtotal' => $pendukung->subtotal ?? 0,
                                'tipe' => 'Bahan Pendukung'
                            ];
                        }
                        
                        return [
                            'nama_bahan' => is_string($bahanPendukung->nama_bahan) ? $bahanPendukung->nama_bahan : 'Unknown',
                            'qty' => $pendukung->jumlah ?? 0,
                            'satuan' => $pendukung->satuan ?? 'unit',
                            'harga_satuan' => $pendukung->harga_satuan ?? 0,
                            'subtotal' => $pendukung->subtotal ?? 0,
                            'tipe' => 'Bahan Pendukung'
                        ];
                    })->toArray() ?? [];
                }
                
                $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
                
                $produkBiaya[$produk->id] = [
                    'total_biaya' => $totalBiayaBahan,
                    'total_biaya_bahan_baku' => $totalBiayaBahanBaku,
                    'total_biaya_bahan_pendukung' => $totalBiayaBahanPendukung,
                    'detail_bahan' => $allDetails,
                    'detail_bahan_baku' => $detailBahanBaku,
                    'detail_bahan_pendukung' => $detailBahanPendukung
                ];
            } else {
                // Produk tanpa BOM
                $produkBiaya[$produk->id] = [
                    'total_biaya' => 0,
                    'total_biaya_bahan_baku' => 0,
                    'total_biaya_bahan_pendukung' => 0,
                    'detail_bahan' => [],
                    'detail_bahan_baku' => [],
                    'detail_bahan_pendukung' => []
                ];
            }
        }
        
        return view('master-data.biaya-bahan.index', compact('produks', 'produkBiaya'));
    }

    /**
     * Show the form for creating biaya bahan for a product
     */
    public function create($id)
    {
        $produk = Produk::with(['satuan'])->findOrFail($id);
        
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
        $bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->orderBy('nama_bahan')->get();
        
        // Get all satuan for dropdown
        $satuans = \App\Models\Satuan::orderBy('nama')->get();
        
        return view('master-data.biaya-bahan.create', compact(
            'produk',
            'bomDetails',
            'bomJobBahanPendukung',
            'bahanBakus',
            'bahanPendukungs',
            'satuans'
        ));
    }

    /**
     * Show biaya bahan details for a specific product
     */
    public function show($id)
    {
        $produk = Produk::with(['satuan'])->findOrFail($id);
        
        // Get BOM details
        $bom = Bom::where('produk_id', $produk->id)->first();
        
        if (!$bom) {
            return redirect()->route('master-data.biaya-bahan.index')
                ->with('error', 'Produk ini belum memiliki BOM. Silakan buat BOM terlebih dahulu.');
        }
        
        // Get BOM job costing
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)
            ->with(['detailBBB.bahanBaku.satuan', 'detailBahanPendukung.bahanPendukung.satuan'])
            ->first();
        
        if (!$bomJobCosting) {
            return redirect()->route('master-data.biaya-bahan.create', $produk->id)
                ->with('error', 'Belum ada data biaya bahan untuk produk ini. Silakan tambahkan biaya bahan terlebih dahulu.');
        }
        
        // Prepare detail data
        $detailBahanBaku = [];
        // Ambil data dari BomDetail (bahan baku)
        $bomDetails = BomDetail::with('bahanBaku.satuan')->where('bom_id', $bom->id)->get();
        if ($bomDetails) {
            $detailBahanBaku = $bomDetails->map(function($detail) {
                $bahanBaku = $detail->bahanBaku;
                return [
                    'id' => $detail->id,
                    'nama_bahan' => $bahanBaku->nama_bahan ?? 'Unknown',
                    'qty' => $detail->jumlah ?? 0,
                    'satuan' => $detail->satuan ?? 'unit',
                    'harga_satuan' => $detail->harga_per_satuan ?? 0,
                    'subtotal' => $detail->total_harga ?? 0,
                    'tipe' => 'Bahan Baku'
                ];
            })->toArray() ?? [];
        }
        
        $detailBahanPendukung = [];
        if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
            $detailBahanPendukung = $bomJobCosting->detailBahanPendukung->map(function($detail) {
                $bahanPendukung = $detail->bahanPendukung;
                return [
                    'id' => $detail->id,
                    'nama_bahan' => $bahanPendukung->nama_bahan ?? 'Unknown',
                    'qty' => $detail->jumlah ?? 0,
                    'satuan' => $detail->satuan ?? 'unit',
                    'harga_satuan' => $detail->harga_satuan ?? 0,
                    'subtotal' => $detail->subtotal ?? 0,
                    'tipe' => 'Bahan Pendukung'
                ];
            })->toArray() ?? [];
        }
        
        $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
        
        // Calculate totals
        $totalBiayaBahanBaku = array_sum(array_column($detailBahanBaku, 'subtotal'));
        $totalBiayaBahanPendukung = array_sum(array_column($detailBahanPendukung, 'subtotal'));
        $totalBiaya = $totalBiayaBahanBaku + $totalBiayaBahanPendukung;
        
        // Alias untuk view compatibility
        $totalBiayaBahan = $totalBiaya;
        
        return view('master-data.biaya-bahan.show', compact(
            'produk',
            'bom',
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
            $produk = Produk::findOrFail($id);

            $bahanBakuInput = $request->input('bahan_baku', []);
            $bahanPendukungInput = $request->input('bahan_pendukung', []);

            $validBahanBaku = [];
            foreach ((array) $bahanBakuInput as $item) {
                if (empty($item['id']) || empty($item['jumlah']) || (float) $item['jumlah'] <= 0 || empty($item['satuan'])) {
                    continue;
                }
                $validBahanBaku[] = $item;
            }

            $validBahanPendukung = [];
            foreach ((array) $bahanPendukungInput as $item) {
                if (empty($item['id']) || empty($item['jumlah']) || (float) $item['jumlah'] <= 0 || empty($item['satuan'])) {
                    continue;
                }
                $validBahanPendukung[] = $item;
            }

            if (count($validBahanBaku) === 0 && count($validBahanPendukung) === 0) {
                return back()->withInput()->withErrors([
                    'error' => 'Tidak ada data yang valid untuk disimpan! Pastikan pilih bahan, isi jumlah, dan pilih satuan.'
                ]);
            }

            DB::beginTransaction();

            $converter = new UnitConverter();
            $totalBiaya = 0;
            $savedCount = 0;

            // GET OR CREATE BOM
            $bom = Bom::where('produk_id', $produk->id)->first();
            if (!$bom) {
                $bom = new Bom();
                $bom->produk_id = $produk->id;
                $bom->kode_bom = 'BOM-' . str_pad($produk->id, 3, '0', STR_PAD_LEFT);
                $bom->total_biaya = 0;
                $bom->catatan = 'Bill of Materials untuk ' . $produk->nama_produk;
                $bom->save();
            }
            
            // GET OR CREATE BOM JOB COSTING
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
            if (!$bomJobCosting) {
                $bomJobCosting = new BomJobCosting();
                $bomJobCosting->produk_id = $produk->id;
                $bomJobCosting->jumlah_produk = 1;
                $bomJobCosting->save();
            }
            
            // HAPUS SEMUA DATA LAMA
            BomDetail::where('bom_id', $bom->id)->delete();
            BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            // SIMPAN BAHAN BAKU BARU
            if (count($validBahanBaku) > 0) {
                foreach ($validBahanBaku as $item) {
                    
                    $bahanBaku = BahanBaku::find($item['id']);
                    if (!$bahanBaku) continue;
                    
                    $jumlah = (float)$item['jumlah'];
                    $harga = (float)$bahanBaku->harga_satuan;

                    $satuanBaseObj = $bahanBaku->satuan;
                    $satuanBase = is_object($satuanBaseObj) ? ($satuanBaseObj->nama ?? 'unit') : ($satuanBaseObj ?: 'unit');
                    $satuanInput = (string)$item['satuan'];

                    $qtyBase = $jumlah;
                    $desc = $converter->describe($satuanInput, $satuanBase);
                    if ($desc !== 'konversi tidak dikenal' && !str_contains($desc, 'volume↔massa')) {
                        $qtyBase = $converter->convert($jumlah, $satuanInput, $satuanBase);
                    }

                    $subtotal = $harga * $qtyBase;
                    $totalBiaya += $subtotal;
                    
                    $bomDetail = new BomDetail();
                    $bomDetail->bom_id = $bom->id;
                    $bomDetail->bahan_baku_id = $bahanBaku->id;
                    $bomDetail->jumlah = $jumlah;
                    $bomDetail->satuan = $item['satuan'];
                    $bomDetail->harga_per_satuan = $harga;
                    $bomDetail->total_harga = $subtotal;
                    $bomDetail->save();
                    
                    $savedCount++;
                }
            }
            
            // SIMPAN BAHAN PENDUKUNG BARU
            if (count($validBahanPendukung) > 0) {
                foreach ($validBahanPendukung as $item) {
                    
                    $bahanPendukung = BahanPendukung::find($item['id']);
                    if (!$bahanPendukung) continue;
                    
                    $jumlah = (float)$item['jumlah'];
                    $harga = (float)$bahanPendukung->harga_satuan;
                    $subtotal = $harga * $jumlah;
                    $totalBiaya += $subtotal;
                    
                    $pendukungDetail = new BomJobBahanPendukung();
                    $pendukungDetail->bom_job_costing_id = $bomJobCosting->id;
                    $pendukungDetail->bahan_pendukung_id = $bahanPendukung->id;
                    $pendukungDetail->jumlah = $jumlah;
                    $pendukungDetail->satuan = $item['satuan'];
                    $pendukungDetail->harga_satuan = $harga;
                    $pendukungDetail->subtotal = $subtotal;
                    $pendukungDetail->save();
                    
                    $savedCount++;
                }
            }
            
            // UPDATE TOTALS
            $bom->update(['total_biaya' => $totalBiaya]);
            $produk->update(['harga_bom' => $totalBiaya]);
            
            // Sync all BOMs to ensure consistency
            BomSyncService::syncAllBoms();
            
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
        $produk = Produk::with(['satuan'])->findOrFail($id);
        
        // Get existing BOM details
        $bomDetails = BomDetail::with('bahanBaku.satuan')
            ->where('bom_id', function($query) use ($produk) {
                $query->select('id')->from('boms')->where('produk_id', $produk->id);
            })
            ->get();
        
        // Get existing Bahan Pendukung
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        $bomJobBahanPendukung = $bomJobCosting ? 
            BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->get() : [];
        
        // Get available bahan baku and bahan pendukung for selection with sub satuan
        $bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->orderBy('nama_bahan')->get();
        
        // Get all satuan for dropdown
        $satuans = \App\Models\Satuan::orderBy('nama')->get();
        
        return view('master-data.biaya-bahan.edit', compact(
            'produk',
            'bomDetails',
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
            $produk = Produk::findOrFail($id);

            $bahanBakuInput = $request->input('bahan_baku', []);
            $bahanPendukungInput = $request->input('bahan_pendukung', []);

            $validBahanBaku = [];
            foreach ((array) $bahanBakuInput as $item) {
                if (empty($item['id']) || empty($item['jumlah']) || (float) $item['jumlah'] <= 0 || empty($item['satuan'])) {
                    continue;
                }
                $validBahanBaku[] = $item;
            }

            $validBahanPendukung = [];
            foreach ((array) $bahanPendukungInput as $item) {
                if (empty($item['id']) || empty($item['jumlah']) || (float) $item['jumlah'] <= 0 || empty($item['satuan'])) {
                    continue;
                }
                $validBahanPendukung[] = $item;
            }

            if (count($validBahanBaku) === 0 && count($validBahanPendukung) === 0) {
                return back()->withInput()->withErrors([
                    'error' => 'Tidak ada data yang valid untuk disimpan! Pastikan pilih bahan, isi jumlah, dan pilih satuan.'
                ]);
            }

            DB::beginTransaction();

            $converter = new UnitConverter();
            $totalBiaya = 0;
            $savedCount = 0;
            
            // GET OR CREATE BOM
            $bom = Bom::where('produk_id', $produk->id)->first();
            if (!$bom) {
                $bom = new Bom();
                $bom->produk_id = $produk->id;
                $bom->kode_bom = 'BOM-' . str_pad($produk->id, 3, '0', STR_PAD_LEFT);
                $bom->total_biaya = 0;
                $bom->catatan = 'Bill of Materials untuk ' . $produk->nama_produk;
                $bom->save();
            }
            
            // GET OR CREATE BOM JOB COSTING
            $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
            if (!$bomJobCosting) {
                $bomJobCosting = new BomJobCosting();
                $bomJobCosting->produk_id = $produk->id;
                $bomJobCosting->save();
            }
            
            // HAPUS SEMUA DATA LAMA
            BomDetail::where('bom_id', $bom->id)->delete();
            BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            // SIMPAN BAHAN BAKU BARU
            if (count($validBahanBaku) > 0) {
                foreach ($validBahanBaku as $item) {
                    
                    $bahanBaku = BahanBaku::find($item['id']);
                    if (!$bahanBaku) continue;
                    
                    $jumlah = (float)$item['jumlah'];
                    $harga = (float)$bahanBaku->harga_satuan;

                    $satuanBaseObj = $bahanBaku->satuan;
                    $satuanBase = is_object($satuanBaseObj) ? ($satuanBaseObj->nama ?? 'unit') : ($satuanBaseObj ?: 'unit');
                    $satuanInput = (string)$item['satuan'];

                    $qtyBase = $jumlah;
                    $desc = $converter->describe($satuanInput, $satuanBase);
                    if ($desc !== 'konversi tidak dikenal' && !str_contains($desc, 'volume↔massa')) {
                        $qtyBase = $converter->convert($jumlah, $satuanInput, $satuanBase);
                    }

                    $subtotal = $harga * $qtyBase;
                    $totalBiaya += $subtotal;
                    
                    $bomDetail = new BomDetail();
                    $bomDetail->bom_id = $bom->id;
                    $bomDetail->bahan_baku_id = $bahanBaku->id;
                    $bomDetail->jumlah = $jumlah;
                    $bomDetail->satuan = $item['satuan'];
                    $bomDetail->harga_per_satuan = $harga;
                    $bomDetail->total_harga = $subtotal;
                    $bomDetail->save();
                    
                    $savedCount++;
                }
            }
            
            // SIMPAN BAHAN PENDUKUNG BARU
            if (count($validBahanPendukung) > 0) {
                foreach ($validBahanPendukung as $item) {
                    
                    $bahanPendukung = BahanPendukung::find($item['id']);
                    if (!$bahanPendukung) continue;
                    
                    $jumlah = (float)$item['jumlah'];
                    $harga = (float)$bahanPendukung->harga_satuan;

                    $satuanBaseObj = $bahanPendukung->satuan;
                    $satuanBase = is_object($satuanBaseObj) ? ($satuanBaseObj->nama ?? 'unit') : ($satuanBaseObj ?: 'unit');
                    $satuanInput = (string)$item['satuan'];

                    $qtyBase = $jumlah;
                    $desc = $converter->describe($satuanInput, $satuanBase);
                    if ($desc !== 'konversi tidak dikenal' && !str_contains($desc, 'volume↔massa')) {
                        $qtyBase = $converter->convert($jumlah, $satuanInput, $satuanBase);
                    }

                    $subtotal = $harga * $qtyBase;
                    $totalBiaya += $subtotal;
                    
                    $pendukungDetail = new BomJobBahanPendukung();
                    $pendukungDetail->bom_job_costing_id = $bomJobCosting->id;
                    $pendukungDetail->bahan_pendukung_id = $bahanPendukung->id;
                    $pendukungDetail->jumlah = $jumlah;
                    $pendukungDetail->satuan = $item['satuan'];
                    $pendukungDetail->harga_satuan = $harga;
                    $pendukungDetail->subtotal = $subtotal;
                    $pendukungDetail->save();
                    
                    $savedCount++;
                }
            }
            
            // UPDATE TOTAL BIAYA
            $produk->update(['harga_bom' => $totalBiaya]);
            $bom->update(['total_biaya' => $totalBiaya]);
            
            // Sync all BOMs to ensure consistency
            BomSyncService::syncAllBoms();

            if ($savedCount === 0) {
                DB::rollBack();
                return back()->withInput()->withErrors([
                    'error' => 'Tidak ada data yang valid untuk disimpan! Pastikan pilih bahan, isi jumlah, dan pilih satuan.'
                ]);
            }

            DB::commit();
            
            $message = "BERHASIL! {$savedCount} item biaya bahan diupdate untuk produk \"{$produk->nama_produk}\". Total biaya: Rp " . number_format($totalBiaya, 0, ',', '.');
            
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
            $produk = Produk::findOrFail($id);
            
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
                BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->delete();
                $bomJobCosting->delete();
            }
            
            // Reset harga_bom produk
            $produk->update(['harga_bom' => 0]);
            
            DB::commit();
            
            return redirect()->route('master-data.biaya-bahan.index')
                ->with('success', "Biaya bahan untuk produk \"{$produk->nama_produk}\" telah dihapus dengan sempurna!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'ERROR: ' . $e->getMessage());
        }
    }
}
