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
        
        $query = BahanPendukung::with(['satuan', 'kategoriBahanPendukung', 'subSatuan1', 'subSatuan2', 'subSatuan3']);
        
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
        return view('master-data.bahan-pendukung.create', compact('satuans', 'kategoris'));
    }

    public function store(Request $request)
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
            'sub_satuan_1_id' => 'required|exists:satuans,id',
            'sub_satuan_1_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_2_id' => 'required|exists:satuans,id',
            'sub_satuan_2_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_2_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_3_id' => 'required|exists:satuans,id',
            'sub_satuan_3_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_3_nilai' => 'required|numeric|min:0.01',
        ]);

        // Create bahan pendukung
        BahanPendukung::create($validated);

        return redirect()->route('master-data.bahan-pendukung.index')
            ->with('success', 'Bahan pendukung berhasil ditambahkan');
    }

    public function show(BahanPendukung $bahanPendukung)
    {
        $bahanPendukung->load(['satuan', 'kategoriBahanPendukung']);
        return view('master-data.bahan-pendukung.show', compact('bahanPendukung'));
    }

    public function edit(BahanPendukung $bahanPendukung)
    {
        $satuans = Satuan::orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::active()->orderBy('nama')->get();
        return view('master-data.bahan-pendukung.edit', compact('bahanPendukung', 'satuans', 'kategoris'));
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
            'sub_satuan_1_id' => 'required|exists:satuans,id',
            'sub_satuan_1_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_2_id' => 'required|exists:satuans,id',
            'sub_satuan_2_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_2_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_3_id' => 'required|exists:satuans,id',
            'sub_satuan_3_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_3_nilai' => 'required|numeric|min:0.01',
        ]);

        // Handle checkbox - tidak perlu validasi boolean
        $validated['is_active'] = $request->has('is_active');
        
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
}
