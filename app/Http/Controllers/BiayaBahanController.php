<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BiayaBahanBaku;
use App\Models\BahanBaku;
use App\Models\Satuan;
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
        try {
            // Get products for logged in user
            $produks = Produk::where('user_id', auth()->id())->orderBy('nama_produk')->get();
            
            $produkBiaya = [];
            
            foreach ($produks as $produk) {
                // Get biaya bahan baku data for this product
                $biayaBahanBaku = BiayaBahanBaku::where('user_id', auth()->id())
                    ->where('produk_id', $produk->id)
                    ->with(['bahanBaku.satuan'])
                    ->get();
                
                $totalBiayaBahanBaku = $biayaBahanBaku->sum('subtotal');
                
                $detailBahanBaku = $biayaBahanBaku->map(function($detail) {
                    return [
                        'nama_bahan' => $detail->bahanBaku->nama_bahan ?? 'Unknown',
                        'qty' => $detail->jumlah,
                        'satuan' => $detail->bahanBaku->satuan->nama ?? $detail->satuan ?? 'unit',
                        'harga_satuan' => $detail->harga_satuan,
                        'subtotal' => $detail->subtotal,
                        'tipe' => 'Bahan Baku',
                        'status' => 'aktif'
                    ];
                })->toArray();
                
                // Add to produkBiaya
                $produkBiaya[] = [
                    'produk' => $produk,
                    'total_biaya' => $totalBiayaBahanBaku,
                    'total_biaya_bahan_baku' => $totalBiayaBahanBaku,
                    'total_biaya_bahan_pendukung' => 0,
                    'detail_bahan' => $detailBahanBaku,
                    'detail_bahan_baku' => $detailBahanBaku,
                    'detail_bahan_pendukung' => [],
                    'total_biaya_bahan' => $totalBiayaBahanBaku,
                    'bom_job_costing' => null
                ];
            }
            
            return view('master-data.biaya-bahan.index', compact('produks', 'produkBiaya'));
            
        } catch (\Exception $e) {
            Log::error("Error in BiayaBahanController@index: " . $e->getMessage());
            
            return back()->withError('Error loading biaya bahan data: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating biaya bahan for a product
     */
    public function create($id)
    {
        $produk = Produk::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $bahanBakus = BahanBaku::where('user_id', auth()->id())->orderBy('nama_bahan')->get();
        $satuans = Satuan::where('user_id', auth()->id())->orderBy('nama')->get();
        
        // Get COAs for bahan baku (persediaan/inventory accounts)
        // Typically code 51 (BBB) or 114-116 (Persediaan)
        // FIXED: Column name is kode_akun not kode_coa
        $coas = \App\Models\Coa::where('user_id', auth()->id())
            ->where(function($query) {
                $query->where('kode_akun', 'LIKE', '51%')  // BBB accounts
                      ->orWhere('kode_akun', 'LIKE', '114%') // Persediaan Bahan Baku
                      ->orWhere('kode_akun', 'LIKE', '115%') // Persediaan Bahan Pendukung  
                      ->orWhere('kode_akun', 'LIKE', '116%'); // Persediaan Barang Jadi
            })
            ->orderBy('kode_akun')
            ->get();
        
        return view('master-data.biaya-bahan.create', compact('produk', 'bahanBakus', 'satuans', 'coas'));
    }

    /**
     * Store a newly created biaya bahan
     */
    public function store(Request $request)
    {
        // Filter out 'new' template row from bahan_baku array
        $bahanBakuData = collect($request->input('bahan_baku', []))
            ->filter(function($item, $key) {
                // Skip 'new' template row and empty rows
                return $key !== 'new' && 
                       !empty($item['id']) && 
                       !empty($item['jumlah']) && 
                       !empty($item['satuan']);
            })
            ->toArray();

        // Validate
        $request->merge(['bahan_baku' => $bahanBakuData]);
        
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'bahan_baku' => 'required|array|min:1',
            'bahan_baku.*.id' => 'required|exists:bahan_bakus,id',
            'bahan_baku.*.jumlah' => 'required|numeric|min:0.0001',
            'bahan_baku.*.satuan' => 'required|string|max:20',
            'bahan_baku.*.harga_satuan' => 'required|numeric|min:0',
        ], [
            'bahan_baku.required' => 'Minimal harus ada 1 bahan baku',
            'bahan_baku.min' => 'Minimal harus ada 1 bahan baku',
            'bahan_baku.*.id.required' => 'Bahan baku harus dipilih',
            'bahan_baku.*.jumlah.required' => 'Jumlah harus diisi',
            'bahan_baku.*.satuan.required' => 'Satuan harus dipilih',
            'bahan_baku.*.harga_satuan.required' => 'Harga satuan harus diisi',
        ]);

        // Check if product belongs to user
        $produk = Produk::where('id', $request->produk_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $savedCount = 0;
        
        foreach ($bahanBakuData as $key => $item) {
            // Check if bahan baku belongs to user and load relations
            $bahanBaku = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])
                ->where('id', $item['id'])
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$bahanBaku) {
                continue;
            }
            
            // Get harga satuan from bahan baku (harga per satuan utama)
            $hargaSatuanUtama = $bahanBaku->harga_rata_rata ?? $bahanBaku->harga_satuan;
            $satuanUtama = $bahanBaku->satuan->nama ?? 'unit';
            $satuanDipilih = $item['satuan'];
            
            // Calculate actual harga_satuan based on conversion
            $hargaSatuanAktual = $hargaSatuanUtama;
            
            // If different unit, apply conversion
            if (strtolower($satuanUtama) !== strtolower($satuanDipilih)) {
                // Check sub satuan for conversion
                $conversionFactor = 1;
                
                // Check sub_satuan_1
                if ($bahanBaku->subSatuan1 && 
                    strtolower($bahanBaku->subSatuan1->nama) === strtolower($satuanDipilih)) {
                    $nilai = $bahanBaku->sub_satuan_1_nilai ?? 1;
                    $conversionFactor = 1 / $nilai;
                }
                // Check sub_satuan_2
                elseif ($bahanBaku->subSatuan2 && 
                    strtolower($bahanBaku->subSatuan2->nama) === strtolower($satuanDipilih)) {
                    $nilai = $bahanBaku->sub_satuan_2_nilai ?? 1;
                    $conversionFactor = 1 / $nilai;
                }
                // Check sub_satuan_3
                elseif ($bahanBaku->subSatuan3 && 
                    strtolower($bahanBaku->subSatuan3->nama) === strtolower($satuanDipilih)) {
                    $nilai = $bahanBaku->sub_satuan_3_nilai ?? 1;
                    $conversionFactor = 1 / $nilai;
                }
                
                $hargaSatuanAktual = $hargaSatuanUtama * $conversionFactor;
            }
            
            // Calculate subtotal with converted price
            $subtotal = $item['jumlah'] * $hargaSatuanAktual;

            BiayaBahanBaku::create([
                'user_id' => auth()->id(),
                'produk_id' => $request->produk_id,
                'bahan_baku_id' => $item['id'],
                'coa_id' => $item['coa_id'] ?? null,
                'jumlah' => $item['jumlah'],
                'satuan' => $item['satuan'],
                'harga_satuan' => $hargaSatuanAktual, // Use converted price
                'subtotal' => $subtotal,
                'keterangan' => $item['keterangan'] ?? null
            ]);
            
            $savedCount++;
        }

        if ($savedCount > 0) {
            return redirect()->route('master-data.biaya-bahan.index')
                ->with('success', "Berhasil menyimpan {$savedCount} biaya bahan baku");
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada data yang disimpan. Pastikan Anda menambahkan minimal 1 bahan baku.');
        }
    }

    /**
     * Show the form for editing biaya bahan for a product
     */
    public function edit($produk_id)
    {
        // Get product with user filtering
        $produk = Produk::where('id', $produk_id)->where('user_id', auth()->id())->firstOrFail();
        
        // Get all biaya bahan baku for this product with COA relationship
        $biayaBahanList = BiayaBahanBaku::where('user_id', auth()->id())
            ->where('produk_id', $produk_id)
            ->with(['bahanBaku.satuan', 'coa'])
            ->get();
            
        $bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])
            ->where('user_id', auth()->id())
            ->orderBy('nama_bahan')
            ->get();
        $satuans = Satuan::where('user_id', auth()->id())->orderBy('nama')->get();
        
        // Get COAs for bahan baku
        $coas = \App\Models\Coa::where('user_id', auth()->id())
            ->where(function($query) {
                $query->where('kode_akun', 'LIKE', '51%')
                      ->orWhere('kode_akun', 'LIKE', '114%')
                      ->orWhere('kode_akun', 'LIKE', '115%')
                      ->orWhere('kode_akun', 'LIKE', '116%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        return view('master-data.biaya-bahan.edit', compact('produk', 'biayaBahanList', 'bahanBakus', 'satuans', 'coas'));
    }

    /**
     * Show the form for editing individual biaya bahan baku
     */
    public function editItem($id)
    {
        $biayaBahan = BiayaBahanBaku::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['produk', 'bahanBaku'])
            ->firstOrFail();
            
        $bahanBakus = BahanBaku::where('user_id', auth()->id())->orderBy('nama_bahan')->get();
        $satuans = Satuan::where('user_id', auth()->id())->orderBy('nama')->get();
        
        return view('master-data.biaya-bahan.edit-item', compact('biayaBahan', 'bahanBakus', 'satuans'));
    }

    /**
     * Update biaya bahan for a product (multiple records)
     */
    public function update(Request $request, $produk_id)
    {
        // DEBUG: Log received data
        Log::info("=== BIAYA BAHAN UPDATE DEBUG ===");
        Log::info("Product ID: " . $produk_id);
        Log::info("Request data: " . json_encode($request->all()));
        Log::info("Bahan baku data: " . json_encode($request->input('bahan_baku')));
        
        // Validate that product belongs to user
        $produk = Produk::where('id', $produk_id)->where('user_id', auth()->id())->firstOrFail();
        
        // Get bahan baku data from request
        $bahanBakuData = $request->input('bahan_baku', []);
        
        // Filter out empty entries
        $validBahanBakuData = [];
        foreach ($bahanBakuData as $key => $data) {
            if (!empty($data['id']) && !empty($data['jumlah']) && !empty($data['satuan'])) {
                $validBahanBakuData[$key] = $data;
            }
        }
        
        Log::info("Valid bahan baku data: " . json_encode($validBahanBakuData));
        
        // Check if we have any valid data
        if (empty($validBahanBakuData)) {
            return back()->withError('Tidak ada data bahan baku yang valid. Pastikan semua field terisi dengan benar.')
                ->withInput();
        }
        
        // Validate bahan baku data
        $request->validate([
            'bahan_baku' => 'required|array|min:1',
            'bahan_baku.*.id' => 'required|exists:bahan_bakus,id',
            'bahan_baku.*.jumlah' => 'required|numeric|min:0.0001',
            'bahan_baku.*.satuan' => 'required|string|max:20',
        ], [
            'bahan_baku.*.id.required' => 'Kolom bahan baku id wajib diisi.',
            'bahan_baku.*.id.exists' => 'Bahan baku yang dipilih tidak valid.',
            'bahan_baku.*.jumlah.required' => 'Kolom jumlah wajib diisi.',
            'bahan_baku.*.jumlah.numeric' => 'Jumlah harus berupa angka.',
            'bahan_baku.*.jumlah.min' => 'Jumlah minimal 0.0001.',
            'bahan_baku.*.satuan.required' => 'Kolom satuan wajib diisi.',
        ]);

        try {
            DB::beginTransaction();
            
            // Delete existing biaya bahan for this product
            BiayaBahanBaku::where('user_id', auth()->id())
                ->where('produk_id', $produk_id)
                ->delete();
            
            $createdCount = 0;
            
            // Create new records
            foreach ($validBahanBakuData as $bahanData) {
                // Get bahan baku with sub satuan relationships
                $bahanBaku = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])
                    ->where('id', $bahanData['id'])
                    ->where('user_id', auth()->id())
                    ->firstOrFail();
                
                // Get base price from bahan baku master data
                $hargaUtama = $bahanBaku->harga_rata_rata ?? $bahanBaku->harga_satuan;
                $satuanUtama = $bahanBaku->satuan->nama ?? 'Unit';
                $satuanDipilih = $bahanData['satuan'];
                
                // Calculate converted price based on unit
                $hargaSatuan = $hargaUtama; // Default to base price
                
                // Apply conversion if different units
                if ($satuanUtama !== $satuanDipilih) {
                    // Check for sub satuan conversions
                    $conversionFactor = 1;
                    
                    // Check sub_satuan_1
                    if ($bahanBaku->subSatuan1 && $bahanBaku->subSatuan1->nama === $satuanDipilih) {
                        $konversi = $bahanBaku->sub_satuan_1_konversi ?? 1;
                        $nilai = $bahanBaku->sub_satuan_1_nilai ?? 1;
                        $conversionFactor = $konversi / $nilai;
                    }
                    // Check sub_satuan_2
                    elseif ($bahanBaku->subSatuan2 && $bahanBaku->subSatuan2->nama === $satuanDipilih) {
                        $konversi = $bahanBaku->sub_satuan_2_konversi ?? 1;
                        $nilai = $bahanBaku->sub_satuan_2_nilai ?? 1;
                        $conversionFactor = $konversi / $nilai;
                    }
                    // Check sub_satuan_3
                    elseif ($bahanBaku->subSatuan3 && $bahanBaku->subSatuan3->nama === $satuanDipilih) {
                        $konversi = $bahanBaku->sub_satuan_3_konversi ?? 1;
                        $nilai = $bahanBaku->sub_satuan_3_nilai ?? 1;
                        $conversionFactor = $konversi / $nilai;
                    }
                    
                    $hargaSatuan = $hargaUtama * $conversionFactor;
                    
                    Log::info("Unit conversion applied: {$satuanUtama} -> {$satuanDipilih}, factor: {$conversionFactor}, price: {$hargaUtama} -> {$hargaSatuan}");
                }
                
                BiayaBahanBaku::create([
                    'user_id' => auth()->id(),
                    'produk_id' => $produk_id,
                    'bahan_baku_id' => $bahanData['id'],
                    'coa_id' => $bahanData['coa_id'] ?? null,
                    'jumlah' => $bahanData['jumlah'],
                    'satuan' => $bahanData['satuan'],
                    'harga_satuan' => $hargaSatuan,
                    'keterangan' => $bahanData['keterangan'] ?? null
                ]);
                
                $createdCount++;
            }
            
            DB::commit();
            
            Log::info("Successfully created {$createdCount} biaya bahan records");
            
            return redirect()->route('master-data.biaya-bahan.index')
                ->withSuccess("Biaya bahan baku berhasil diperbarui. {$createdCount} item disimpan.");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error updating biaya bahan: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return back()->withError('Gagal memperbarui biaya bahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update individual biaya bahan record
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|numeric|min:0.0001',
            'satuan' => 'required|string|max:20',
            'harga_satuan' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string'
        ]);

        $biayaBahan = BiayaBahanBaku::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if bahan baku belongs to user
        $bahanBaku = BahanBaku::where('id', $request->bahan_baku_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $biayaBahan->update([
            'bahan_baku_id' => $request->bahan_baku_id,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'harga_satuan' => $request->harga_satuan,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('master-data.biaya-bahan.index')
            ->withSuccess('Biaya bahan baku berhasil diperbarui');
    }

    /**
     * Remove biaya bahan
     */
    public function destroy($id)
    {
        $biayaBahan = BiayaBahanBaku::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $biayaBahan->delete();

        return redirect()->route('master-data.biaya-bahan.index')
            ->withSuccess('Biaya bahan baku berhasil dihapus');
    }

    /**
     * Show detail biaya bahan for a product
     */
    public function detail($id)
    {
        try {
            // Get product with user filtering
            $produk = Produk::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            
            // Get biaya bahan baku data for this product with COA relationship
            $bbbData = BiayaBahanBaku::where('user_id', auth()->id())
                ->where('produk_id', $id)
                ->with(['bahanBaku.satuan', 'coa'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Transform data to match expected format
            $bbbData = $bbbData->map(function($detail) {
                // Get COA info
                $coaInfo = 'Tidak ada COA';
                if ($detail->coa_id && $detail->coa) {
                    $coaInfo = $detail->coa->kode_akun . ' - ' . $detail->coa->nama_akun;
                } else {
                    $coaInfo = '<span class="text-muted">Default (1141)</span>';
                }
                
                return (object)[
                    'id' => $detail->id,
                    'nama_bahan' => $detail->bahanBaku->nama_bahan ?? 'Unknown',
                    'coa_info' => $coaInfo,
                    'coa_id' => $detail->coa_id,
                    'qty' => $detail->jumlah,
                    'satuan' => $detail->satuan, // Use the actual unit from biaya_bahan_baku
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                    'keterangan' => $detail->keterangan ?? '',
                    'created_at' => $detail->created_at,
                    'satuan_nama' => $detail->satuan // Use the actual unit, not the base unit
                ];
            });
            
            // Calculate totals
            $totalJumlah = $bbbData->sum('qty');
            $totalSubtotal = $bbbData->sum('subtotal');
            
            return view('master-data.biaya-bahan.detail', compact(
                'produk',
                'bbbData',
                'totalJumlah',
                'totalSubtotal'
            ));
            
        } catch (\Exception $e) {
            Log::error("Error in BiayaBahanController@detail: " . $e->getMessage());
            return back()->withError('Error loading detail biaya bahan: ' . $e->getMessage());
        }
    }
}
