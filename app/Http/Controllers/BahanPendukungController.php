<?php

namespace App\Http\Controllers;

use App\Models\BahanPendukung;
use App\Models\KategoriBahanPendukung;
use App\Models\Satuan;
use App\Services\BomSyncService;
use Illuminate\Http\Request;

class BahanPendukungController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Simple debug
        \Log::info('Bahan Pendukung Index Called');
        
        $query = BahanPendukung::with(['satuan', 'kategoriBahanPendukung', 'subSatuan1', 'subSatuan2', 'subSatuan3', 'coaPembelian', 'coaPersediaan', 'coaHpp']);
        
        // Filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_bahan', 'like', '%' . $request->search . '%');
            });
        }
        
        $bahanPendukungs = $query->orderBy('nama_bahan')->paginate(15);
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        
        \Log::info('Bahan Pendukung loaded', [
            'count' => $bahanPendukungs->count(),
            'page' => $bahanPendukungs->currentPage()
        ]);
        
        // Calculate average prices for each bahan pendukung
        foreach ($bahanPendukungs as $bahanPendukung) {
            // Debug logging
            \Log::info('Processing Bahan Pendukung: ' . $bahanPendukung->nama_bahan, [
                'id' => $bahanPendukung->id,
                'default_harga' => $bahanPendukung->harga_satuan
            ]);
            
            // Get all pembelian detail records for this bahan pendukung
            $pembelianDetails = \App\Models\PembelianDetail::where('bahan_pendukung_id', $bahanPendukung->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            \Log::info('Pembelian Details found: ' . $pembelianDetails->count(), [
                'bahan_pendukung_id' => $bahanPendukung->id
            ]);
            
            // Debug: Show pembelian details
            foreach ($pembelianDetails as $detail) {
                \Log::info('Pembelian Detail', [
                    'id' => $detail->id,
                    'pembelian_id' => $detail->pembelian_id,
                    'jumlah' => $detail->jumlah,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => ($detail->jumlah * $detail->harga_satuan)
                ]);
            }
            
            if ($pembelianDetails->count() > 0) {
                // Calculate average price from pembelian details
                $totalHarga = 0;
                $totalQty = 0;
                
                foreach ($pembelianDetails as $detail) {
                    $totalHarga += ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                    $totalQty += ($detail->jumlah ?? 0);
                }
                
                $avgPrice = $totalQty > 0 ? $totalHarga / $totalQty : 0;
                
                \Log::info('Average Price Calculated', [
                    'total_harga' => $totalHarga,
                    'total_qty' => $totalQty,
                    'avg_price' => $avgPrice
                ]);
                
                // Add display property (jangan update database)
                $bahanPendukung->harga_satuan_display = $avgPrice;
            } else {
                // Use default price if no pembelian details
                $bahanPendukung->harga_satuan_display = $bahanPendukung->harga_satuan;
                
                \Log::info('No Pembelian Details, using default price', [
                    'harga_satuan_display' => $bahanPendukung->harga_satuan_display
                ]);
            }
        }
        
        \Log::info('Bahan Pendukung Index Completed');
        
        return view('master-data.bahan-pendukung.index', compact('bahanPendukungs', 'kategoris'));
    }

    public function create()
    {
        $satuans = Satuan::orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        $coas = \App\Models\Coa::all();
        return view('master-data.bahan-pendukung.create', compact('satuans', 'kategoris', 'coas'));
    }

    public function store(Request $request)
    {
        // Convert comma decimal inputs to dot format for validation and storage
        $this->convertCommaToDecimal($request);
        
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255|unique:bahan_pendukungs,nama_bahan',
            'deskripsi' => 'nullable|string',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_satuan' => 'required|numeric|min:0',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori_bahan_pendukung,id',
            'sub_satuan_1_id' => 'nullable|exists:satuans,id',
            'sub_satuan_1_konversi' => 'nullable|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'nullable|numeric',
            'sub_satuan_2_id' => 'nullable|exists:satuans,id',
            'sub_satuan_2_konversi' => 'nullable|numeric|min:0.01',
            'sub_satuan_2_nilai' => 'nullable|numeric',
            'sub_satuan_3_id' => 'nullable|exists:satuans,id',
            'sub_satuan_3_konversi' => 'nullable|numeric|min:0.01',
            'sub_satuan_3_nilai' => 'nullable|numeric',
            'coa_pembelian_id' => 'nullable|exists:coas,kode_akun',
            'coa_persediaan_id' => 'nullable|exists:coas,kode_akun',
            'coa_hpp_id' => 'nullable|exists:coas,kode_akun',
        ]);

        // Add COA fields to validated data
        $validated['coa_pembelian_id'] = $request->coa_pembelian_id;
        $validated['coa_persediaan_id'] = $request->coa_persediaan_id;
        $validated['coa_hpp_id'] = $request->coa_hpp_id;

        // Create bahan pendukung
        BahanPendukung::create($validated);

        return redirect()->route('master-data.bahan-pendukung.index')
            ->with('success', 'Bahan pendukung berhasil ditambahkan');
    }

    public function show(BahanPendukung $bahanPendukung)
    {
        $bahanPendukung->load(['satuan', 'kategoriBahanPendukung', 'subSatuan1', 'subSatuan2', 'subSatuan3', 'coaPembelian', 'coaPersediaan', 'coaHpp']);
        
        // Hitung harga rata-rata untuk display
        $averageHarga = $this->getAverageHargaSatuan($bahanPendukung->id);
        
        // Jika ada harga rata-rata, gunakan itu. Jika tidak, gunakan harga default
        if ($averageHarga > 0) {
            $bahanPendukung->harga_satuan_display = $averageHarga;
        } else {
            $bahanPendukung->harga_satuan_display = $bahanPendukung->harga_satuan;
        }
        
        // Hitung sub satuan prices dengan formula yang benar
        $subSatuanData = $this->getDirectSubSatuanPrices($bahanPendukung);
        $subSatuanPrices = $subSatuanData['sub_satuan_prices'] ?? [];
        
        return view('master-data.bahan-pendukung.show', compact('bahanPendukung', 'subSatuanPrices'));
    }

    public function edit(BahanPendukung $bahanPendukung)
    {
        $satuans = Satuan::orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        $coas = \App\Models\Coa::all();
        return view('master-data.bahan-pendukung.edit', compact('bahanPendukung', 'satuans', 'kategoris', 'coas'));
    }

    public function update(Request $request, BahanPendukung $bahanPendukung)
    {
        // Convert comma decimal inputs to dot format for validation and storage
        $this->convertCommaToDecimal($request);
        
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_satuan' => 'required|numeric|min:0',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori_bahan_pendukung,id',
            'sub_satuan_1_id' => 'nullable|exists:satuans,id',
            'sub_satuan_1_konversi' => 'nullable|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'nullable|numeric',
            'sub_satuan_2_id' => 'nullable|exists:satuans,id',
            'sub_satuan_2_konversi' => 'nullable|numeric|min:0.01',
            'sub_satuan_2_nilai' => 'nullable|numeric',
            'sub_satuan_3_id' => 'nullable|exists:satuans,id',
            'sub_satuan_3_konversi' => 'nullable|numeric|min:0.01',
            'sub_satuan_3_nilai' => 'nullable|numeric',
            'coa_pembelian_id' => 'nullable|exists:coas,kode_akun',
            'coa_persediaan_id' => 'nullable|exists:coas,kode_akun',
            'coa_hpp_id' => 'nullable|exists:coas,kode_akun',
        ]);

        // Handle checkbox - tidak perlu validasi boolean
        $validated['is_active'] = $request->has('is_active');
        
        // Add COA fields to validated data
        $validated['coa_pembelian_id'] = $request->coa_pembelian_id;
        $validated['coa_persediaan_id'] = $request->coa_persediaan_id;
        $validated['coa_hpp_id'] = $request->coa_hpp_id;
        
        $bahanPendukung->update($validated);
        
        // Sync BOM when bahan pendukung price changes
        BomSyncService::syncBomFromMaterialChange('bahan_pendukung', $bahanPendukung->id);
        
        return redirect()->route('master-data.bahan-pendukung.index')
            ->with('success', 'Bahan pendukung berhasil diperbarui');
    }

    public function destroy(BahanPendukung $bahanPendukung)
    {
        try {
            $bahanPendukung->delete();
            return redirect()->route('master-data.bahan-pendukung.index')
                ->with('success', 'Bahan pendukung berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * Convert comma decimal inputs to dot format for proper validation and storage
     */
    private function convertCommaToDecimal(Request $request)
    {
        $fieldsToConvert = [
            'harga_satuan', 'stok', 'stok_minimum',
            'sub_satuan_1_konversi', 'sub_satuan_1_nilai',
            'sub_satuan_2_konversi', 'sub_satuan_2_nilai', 
            'sub_satuan_3_konversi', 'sub_satuan_3_nilai'
        ];
        
        foreach ($fieldsToConvert as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $value = $request->input($field);
                // Remove thousand separators (dots) and convert comma to dot for decimal
                $convertedValue = str_replace(['.', ','], ['', '.'], $value);
                $request->merge([$field => $convertedValue]);
            }
        }
    }

    /**
     * Get average harga satuan untuk bahan pendukung
     */
    public function getAverageHargaSatuan($bahanPendukungId)
    {
        $bahanPendukung = BahanPendukung::findOrFail($bahanPendukungId);
        
        // Ambil semua pembelian detail untuk bahan pendukung ini
        $details = \App\Models\PembelianDetail::where('bahan_pendukung_id', $bahanPendukungId)
            ->with(['pembelian'])
            ->get();
        
        if ($details->isEmpty()) {
            return 0;
        }
        
        // Hitung total harga dan total quantity
        $totalHarga = 0;
        $totalQuantity = 0;
        
        foreach ($details as $detail) {
            $totalHarga += ($detail->harga_satuan ?? 0) * ($detail->jumlah ?? 0);
            $totalQuantity += ($detail->jumlah ?? 0);
        }
        
        // Hitung harga rata-rata
        $averageHarga = $totalQuantity > 0 ? $totalHarga / $totalQuantity : 0;
        
        return $averageHarga;
    }

    /**
     * Hitung harga sub satuan berdasarkan konversi dengan formula yang benar
     */
    private function calculateSubSatuanPrices($bahanPendukung)
    {
        $subSatuanPrices = [];
        $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan;

        // Sub Satuan 1
        if ($bahanPendukung->sub_satuan_1_id && $bahanPendukung->sub_satuan_1_konversi > 0) {
            $hargaPerUnit = $hargaUtama / $bahanPendukung->sub_satuan_1_nilai;
            
            $subSatuanPrices[] = [
                'satuan_nama' => $bahanPendukung->subSatuan1->nama,
                'konversi_nilai' => $bahanPendukung->sub_satuan_1_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => "Rp " . number_format($hargaUtama, 0, ',', '.') . 
                                " ÷ " . number_format($bahanPendukung->sub_satuan_1_nilai, 0, ',', '.') . 
                                " = Rp " . number_format($hargaPerUnit, 0, ',', '.'),
                'konversi_text' => number_format($bahanPendukung->sub_satuan_1_konversi, 0, ',', '.') . " " . 
                                 "{$bahanPendukung->satuan->nama} = " . 
                                 number_format($bahanPendukung->sub_satuan_1_nilai, 0, ',', '.') . 
                                 " {$bahanPendukung->subSatuan1->nama}"
            ];
        }

        // Sub Satuan 2
        if ($bahanPendukung->sub_satuan_2_id && $bahanPendukung->sub_satuan_2_konversi > 0) {
            $hargaPerUnit = $hargaUtama / $bahanPendukung->sub_satuan_2_nilai;
            
            $subSatuanPrices[] = [
                'satuan_nama' => $bahanPendukung->subSatuan2->nama,
                'konversi_nilai' => $bahanPendukung->sub_satuan_2_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => "Rp " . number_format($hargaUtama, 0, ',', '.') . 
                                " ÷ " . number_format($bahanPendukung->sub_satuan_2_nilai, 0, ',', '.') . 
                                " = Rp " . number_format($hargaPerUnit, 0, ',', '.'),
                'konversi_text' => number_format($bahanPendukung->sub_satuan_2_konversi, 0, ',', '.') . " " . 
                                 "{$bahanPendukung->satuan->nama} = " . 
                                 number_format($bahanPendukung->sub_satuan_2_nilai, 0, ',', '.') . 
                                 " {$bahanPendukung->subSatuan2->nama}"
            ];
        }

        // Sub Satuan 3
        if ($bahanPendukung->sub_satuan_3_id && $bahanPendukung->sub_satuan_3_konversi > 0) {
            $hargaPerUnit = $hargaUtama / $bahanPendukung->sub_satuan_3_nilai;
            
            $subSatuanPrices[] = [
                'satuan_nama' => $bahanPendukung->subSatuan3->nama,
                'konversi_nilai' => $bahanPendukung->sub_satuan_3_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => "Rp " . number_format($hargaUtama, 0, ',', '.') . 
                                " ÷ " . number_format($bahanPendukung->sub_satuan_3_nilai, 0, ',', '.') . 
                                " = Rp " . number_format($hargaPerUnit, 0, ',', '.'),
                'konversi_text' => number_format($bahanPendukung->sub_satuan_3_konversi, 0, ',', '.') . " " . 
                                 "{$bahanPendukung->satuan->nama} = " . 
                                 number_format($bahanPendukung->sub_satuan_3_nilai, 0, ',', '.') . 
                                 " {$bahanPendukung->subSatuan3->nama}"
            ];
        }

        return $subSatuanPrices;
    }

    /**
     * Get direct sub satuan prices from bahan_pendukungs table
     */
    private function getDirectSubSatuanPrices($bahanPendukung)
    {
        $service = new \App\Services\BahanPendukungService();
        return $service->getDirectSubSatuanPrices($bahanPendukung->id);
    }
}
