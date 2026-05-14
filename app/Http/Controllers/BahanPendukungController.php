<?php

namespace App\Http\Controllers;

use App\Models\BahanPendukung;
use App\Models\KategoriBahanPendukung;
use App\Models\Satuan;
use App\Services\BomSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        
        // MULTI-TENANT: Filter by user_id to prevent data leakage
        $query = BahanPendukung::with(['satuan', 'kategoriBahanPendukung', 'subSatuan1', 'subSatuan2', 'subSatuan3', 'coaPembelian', 'coaPersediaan', 'coaHpp'])
            ->where('user_id', auth()->id());
        
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
        
        // Sort by created_at ascending (oldest to newest)
        $bahanPendukungs = $query->orderBy('created_at', 'asc')->paginate(15);
        
        // 🔒 SECURITY: Filter kategori by user_id for multi-tenant isolation
        $kategoris = KategoriBahanPendukung::where('user_id', auth()->id())
            ->active()
            ->orderBy('nama')
            ->get();
        
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
        // 🔒 SECURITY: Filter satuan and kategori by user_id for multi-tenant isolation
        $satuans = Satuan::where('user_id', auth()->id())->orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::where('user_id', auth()->id())
            ->active()
            ->orderBy('nama')
            ->get();
        
        // 🔒 SECURITY: Filter coa by user_id for multi-tenant isolation
        $coas = \App\Models\Coa::where('user_id', auth()->id())->get();
        
        return view('master-data.bahan-pendukung.create', compact('satuans', 'kategoris', 'coas'));
    }

    public function store(Request $request)
    {
        // Convert comma decimal inputs to dot format for validation and storage
        $this->convertCommaToDecimal($request);
        
        $validated = $request->validate([

            // CRITICAL: Add user_id to unique validation for multi-tenant isolation
            'nama_bahan' => 'required|string|max:255|unique:bahan_pendukungs,nama_bahan,NULL,id,user_id,' . auth()->id(),
'deskripsi' => 'nullable|string',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_satuan' => 'required|numeric|min:0',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori_bahan_pendukung,id,user_id,' . auth()->id(),
            'sub_satuan_1_id' => 'required|exists:satuans,id',
            'sub_satuan_1_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'required|numeric',
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
        
        // Map stok to saldo_awal
        $validated['saldo_awal'] = $request->stok ?? 0;
        

        // CRITICAL: Add user_id for multi-tenant isolation
        $validated['user_id'] = auth()->id();

        // Create bahan pendukung
        $bahanPendukung = BahanPendukung::create($validated);
        
        // Create initial stock movement if stock > 0
        if (($request->stok ?? 0) > 0) {
            \App\Models\StockMovement::create([
                'item_type' => 'support',
                'item_id' => $bahanPendukung->id,
                'tanggal' => now()->format('Y-m-d'),
                'direction' => 'in',
                'qty' => $request->stok,
                'unit' => $bahanPendukung->satuan->nama ?? 'Unit',
                'unit_cost' => $request->harga_satuan ?? 0,
                'total_cost' => ($request->stok ?? 0) * ($request->harga_satuan ?? 0),
                'ref_type' => 'initial_stock',
                'ref_id' => 0,
                'keterangan' => 'Stok awal ' . $request->nama_bahan,
            ]);
        }

        return redirect()->route('master-data.bahan-pendukung.index')
            ->with('success', 'Bahan pendukung berhasil ditambahkan');
    }

    public function show(BahanPendukung $bahanPendukung)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $bahanPendukung = BahanPendukung::where('user_id', auth()->id())
            ->findOrFail($bahanPendukung->id);
            
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
        $service = new \App\Services\BahanPendukungService();
        $subSatuanData = $service->getDirectSubSatuanPricesFromObject($bahanPendukung);
        $subSatuanPrices = $subSatuanData['sub_satuan_prices'] ?? [];
        
        return view('master-data.bahan-pendukung.show', compact('bahanPendukung', 'subSatuanPrices'));
    }

    public function edit(BahanPendukung $bahanPendukung)
    {
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        $bahanPendukung = BahanPendukung::where('user_id', auth()->id())
            ->findOrFail($bahanPendukung->id);
            
        // 🔒 SECURITY: Filter dropdown data by user_id for multi-tenant isolation
        $satuans = Satuan::where('user_id', auth()->id())->orderBy('nama')->get();
        $kategoris = KategoriBahanPendukung::where('user_id', auth()->id())
            ->active()
            ->orderBy('nama')
            ->get();
        $coas = \App\Models\Coa::where('user_id', auth()->id())->get();
        
        return view('master-data.bahan-pendukung.edit', compact('bahanPendukung', 'satuans', 'kategoris', 'coas'));
    }

    public function update(Request $request, BahanPendukung $bahanPendukung)
    {
        try {
            // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
            $bahanPendukung = BahanPendukung::where('user_id', auth()->id())
                ->findOrFail($bahanPendukung->id);
                
            \Log::info('BahanPendukung update started', [
                'id' => $bahanPendukung->id,
                'request_data' => $request->all(),
                'request_method' => $request->method(),
                'request_url' => $request->url()
            ]);
            
            // Convert comma decimal inputs to dot format for validation and storage
            $this->convertCommaToDecimal($request);
            
            \Log::info('After decimal conversion', [
                'converted_data' => $request->all(),
                'has_nama_bahan' => $request->has('nama_bahan'),
                'nama_bahan_value' => $request->input('nama_bahan'),
                'current_nama_bahan' => $bahanPendukung->nama_bahan,
                'kategori_id_value' => $request->input('kategori_id'),
                'has_kategori_id' => $request->has('kategori_id')
            ]);
            
            // Proper validation rules
            $validated = $request->validate([
                // CRITICAL: Add user_id to unique validation for multi-tenant isolation
                'nama_bahan' => 'required|string|max:255|unique:bahan_pendukungs,nama_bahan,' . $bahanPendukung->id . ',id,user_id,' . auth()->id(),
                'deskripsi' => 'nullable|string',
                'satuan_id' => 'required|exists:satuans,id',
                'harga_satuan' => 'required|numeric|min:0',
                'stok' => 'nullable|numeric|min:0',
                'stok_minimum' => 'nullable|numeric|min:0',
                'kategori_id' => 'required|exists:kategori_bahan_pendukung,id,user_id,' . auth()->id(),
                'sub_satuan_1_id' => 'required|exists:satuans,id',
                'sub_satuan_1_konversi' => 'required|numeric|min:0.01',
                'sub_satuan_1_nilai' => 'required|numeric|min:0.01',
                'sub_satuan_2_id' => 'nullable|exists:satuans,id',
                'sub_satuan_2_konversi' => 'nullable|numeric|min:0.01',
                'sub_satuan_2_nilai' => 'nullable|numeric|min:0.01',
                'sub_satuan_3_id' => 'nullable|exists:satuans,id',
                'sub_satuan_3_konversi' => 'nullable|numeric|min:0.01',
                'sub_satuan_3_nilai' => 'nullable|numeric|min:0.01',
                'coa_pembelian_id' => 'nullable|exists:coas,kode_akun',
                'coa_persediaan_id' => 'nullable|exists:coas,kode_akun',
                'coa_hpp_id' => 'nullable|exists:coas,kode_akun',
            ]);

            \Log::info('Validation passed', [
                'validated_data' => $validated
            ]);

            // Handle checkbox - tidak perlu validasi boolean
            $validated['is_active'] = $request->has('is_active');
            
            // Add COA fields to validated data
            $validated['coa_pembelian_id'] = $request->coa_pembelian_id;
            $validated['coa_persediaan_id'] = $request->coa_persediaan_id;
            $validated['coa_hpp_id'] = $request->coa_hpp_id;
            
            // ✅ PERBAIKAN: Track stock changes to update StockMovement
            $oldSaldoAwal = $bahanPendukung->saldo_awal;
            $newSaldoAwal = $request->stok ?? 0;
            $stockDifference = $newSaldoAwal - $oldSaldoAwal;
            
            // Map stok to saldo_awal
            $validated['saldo_awal'] = $newSaldoAwal;
            
            \Log::info('Before update', [
                'final_data' => $validated,
                'old_stock' => $oldSaldoAwal,
                'new_stock' => $newSaldoAwal,
                'stock_difference' => $stockDifference
            ]);
            
            $bahanPendukung->update($validated);
            
            // ✅ PERBAIKAN: Create StockMovement adjustment if stock changed
            if (abs($stockDifference) > 0.0001) {
                \App\Models\StockMovement::create([
                    'user_id' => auth()->id(),
                    'item_type' => 'material',
                    'item_id' => $bahanPendukung->id,
                    'direction' => $stockDifference > 0 ? 'in' : 'out',
                    'qty' => abs($stockDifference),
                    'tanggal' => now()->toDateString(),
                    'ref_type' => 'stock_adjustment',
                    'ref_id' => $bahanPendukung->id,
                    'keterangan' => 'Penyesuaian stok dari edit bahan pendukung: ' . $bahanPendukung->nama_bahan . ' (dari ' . $oldSaldoAwal . ' ke ' . $newSaldoAwal . ')',
                ]);
                
                \Log::info('Stock adjustment created for BahanPendukung', [
                    'id' => $bahanPendukung->id,
                    'nama_bahan' => $bahanPendukung->nama_bahan,
                    'old_stock' => $oldSaldoAwal,
                    'new_stock' => $newSaldoAwal,
                    'difference' => $stockDifference
                ]);
            }
            
            \Log::info('BahanPendukung updated successfully', [
                'id' => $bahanPendukung->id,
                'nama_bahan' => $bahanPendukung->nama_bahan,
                'updated_fields' => array_keys($validated)
            ]);
            
            // Sync BOM when bahan pendukung price changes
            BomSyncService::syncBomFromMaterialChange('bahan_pendukung', $bahanPendukung->id);
            
            return redirect()->route('master-data.bahan-pendukung.index')
                ->with('success', 'Bahan pendukung berhasil diperbarui');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in BahanPendukung update', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Error updating BahanPendukung', [
                'id' => $bahanPendukung->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat memperbarui bahan pendukung: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(BahanPendukung $bahanPendukung)
    {
        try {
            // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
            $bahanPendukung = BahanPendukung::where('user_id', auth()->id())
                ->findOrFail($bahanPendukung->id);
            
            // Check for foreign key constraints before deleting
            $constraints = [];
            
            // Check Pembelian Details references
            try {
                $pembelianDetailsCount = \DB::table('pembelian_details')->where('bahan_pendukung_id', $bahanPendukung->id)->count();
                if ($pembelianDetailsCount > 0) {
                    $constraints[] = "Pembelian Details ({$pembelianDetailsCount} record(s))";
                }
            } catch (\Exception $e) {
                \Log::info('Pembelian Details table check skipped', ['error' => $e->getMessage()]);
            }
            
            // Check Produksi Details references
            try {
                $produksiDetailsCount = \DB::table('produksi_details')->where('bahan_pendukung_id', $bahanPendukung->id)->count();
                if ($produksiDetailsCount > 0) {
                    $constraints[] = "Produksi Details ({$produksiDetailsCount} record(s))";
                }
            } catch (\Exception $e) {
                \Log::info('Produksi Details table check skipped', ['error' => $e->getMessage()]);
            }
            
            // If there are constraints, prevent deletion and show error
            if (!empty($constraints)) {
                $constraintList = implode(', ', $constraints);
                return redirect()->route('master-data.bahan-pendukung.index')
                    ->with('error', "Tidak dapat menghapus bahan pendukung '{$bahanPendukung->nama_bahan}' karena masih digunakan di: {$constraintList}. Hapus data terkait terlebih dahulu.");
            }
            
            // If no constraints, proceed with deletion
            $bahanPendukung->delete();
            
            return redirect()->route('master-data.bahan-pendukung.index')
                ->with('success', "Data bahan pendukung '{$bahanPendukung->nama_bahan}' berhasil dihapus!");
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle any other database constraint errors
            if ($e->getCode() == '23000') {
                return redirect()->route('master-data.bahan-pendukung.index')
                    ->with('error', 'Tidak dapat menghapus data karena masih digunakan di tabel lain. Hapus data terkait terlebih dahulu.');
            }
            
            return redirect()->route('master-data.bahan-pendukung.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->route('master-data.bahan-pendukung.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
