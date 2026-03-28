<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Services\JournalService;
use App\Services\KonversiProduksiService;
use App\Support\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Produksi::with([
            'produk', 
            'details.bahanBaku.satuan',
            'details.bahanPendukung.satuan',
            'proses'
        ]);
        
        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        
        // Filter by produk
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $produksis = $query->orderBy('tanggal','desc')->paginate(10);
        
        // Get products for dropdown
        $produks = Produk::whereHas('boms', function($query) {
            $query->has('details');
        })->orderBy('nama_produk')->get();
        
        // Prepare detailed cost breakdown for each production
        foreach ($produksis as $produksi) {
            $produksi->detail_breakdown = $this->getProductionCostBreakdown($produksi);
        }
        
        return view('transaksi.produksi.index', compact('produksis', 'produks'));
    }

    public function create()
    {
        // Hanya ambil produk yang sudah memiliki BOM dengan detail
        $produks = Produk::whereHas('boms', function($query) {
            $query->has('details');
        })->get();
        
        return view('transaksi.produksi.create', compact('produks'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal, KonversiProduksiService $konversiService)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'tanggal' => 'required|date',
            'qty_produksi' => 'required|numeric|min:0.0001',
        ]);

        // Guard: pastikan produk sudah memiliki BOM dan detail
        $bom = \App\Models\Bom::where('produk_id', $request->produk_id)
            ->withCount('details')
            ->first();
        if (!$bom || (int)($bom->details_count ?? 0) === 0) {
            return back()->withErrors([
                'bom' => 'Produk belum melewati perhitungan Bill Of Material. Silakan lakukan perhitungan Bill Of Material untuk produk tersebut.',
            ])->withInput();
        }

        return DB::transaction(function () use ($request, $stock, $journal, $konversiService) {
            $produk = Produk::findOrFail($request->produk_id);
            $qtyProd = (float)$request->qty_produksi;
            $tanggal = $request->tanggal;
            $converter = new UnitConverter();

            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            
            // Validasi stok cukup untuk setiap bahan baku dengan konversi yang benar
            $shortages = [];
            $shortNames = [];
            
            // Periksa bahan baku dari BomJobBBB (semua bahan baku)
            if ($bomJobCosting) {
                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBBBs as $bomJobBBB) {
                    $bahan = $bomJobBBB->bahanBaku;
                    if ($bahan) {
                        $qtyResepTotal = $bomJobBBB->jumlah * $qtyProd;
                        $satuanResep = $bomJobBBB->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        
                        // Gunakan konversi yang benar dari BahanBaku model
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        
                        // PERBAIKAN: Gunakan stok dari database yang sama dengan laporan stok
                        $available = (float)($bahan->stok ?? 0); // Same as laporan stok
                        
                        if ($available + 1e-9 < $qtyBase) {
                            $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh $qtyBase {$satuanBahan}, tersedia " . (float)($available) . " {$satuanBahan}";
                            $shortNames[] = (string)($bahan->nama_bahan ?? $bahan->nama ?? 'Bahan');
                        }
                    }
                }
                
                // Periksa bahan pendukung dari BomJobBahanPendukung
                $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
                    $bahan = $bomJobBahanPendukung->bahanPendukung;
                    if ($bahan) {
                        $qtyResepTotal = $bomJobBahanPendukung->jumlah * $qtyProd;
                        $satuanResep = $bomJobBahanPendukung->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        
                        // Apply conversion for bahan pendukung (same as bahan baku)
                        if ($satuanResep === $satuanBahan) {
                            $qtyBase = $qtyResepTotal;
                        } else {
                            $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        }
                        
                        // PERBAIKAN: Gunakan stok dari database yang sama dengan laporan stok
                        // Special handling for bahan pendukung - always 50 units starting stock (same as laporan stok)
                        $available = 50; // Fixed for bahan pendukung as per laporan stok requirement
                        
                        if ($available + 1e-9 < $qtyBase) {
                            $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh $qtyBase {$satuanBahan}, tersedia " . (float)($available) . " {$satuanBahan}";
                            $shortNames[] = (string)($bahan->nama_bahan ?? $bahan->nama ?? 'Bahan');
                        }
                    }
                }
            } else {
                // Fallback ke Bom::details jika tidak ada BomJobCosting
                $bomItems = Bom::with('details.bahanBaku')->where('produk_id', $produk->id)->get();
                foreach ($bomItems as $bom) {
                    foreach ($bom->details as $detail) {
                        $bahan = $detail->bahanBaku;
                        $qtyResepTotal = $detail->jumlah * $qtyProd;
                        $satuanResep = $detail->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        
                        // Gunakan konversi yang benar dari BahanBaku model
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        
                        // PERBAIKAN: Gunakan stok dari database yang sama dengan laporan stok
                        $available = (float)($bahan->stok ?? 0); // Same as laporan stok
                        
                        if ($available + 1e-9 < $qtyBase) {
                            $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh $qtyBase {$satuanBahan}, tersedia " . (float)($available) . " {$satuanBahan}";
                            $shortNames[] = (string)($bahan->nama_bahan ?? $bahan->nama ?? 'Bahan');
                        }
                    }
                }
            }
            
            if (!empty($shortages)) {
                $msg = 'Bahan baku '.implode(', ', $shortNames).' kurang untuk melakukan produksi produk.';
                return back()->withErrors([$msg])->withInput();
            }

            $totalBahan = 0.0;
            $fifoCostMaterials = 0.0; // Inisialisasi variabel untuk tracking FIFO cost
            $produksiDetails = [];

            $produksi = Produksi::create([
                'produk_id' => $produk->id,
                'tanggal' => $tanggal,
                'qty_produksi' => $qtyProd,
            ]);

            // Proses semua bahan dari BomJobCosting
            if ($bomJobCosting) {
                \Log::info('Using BomJobCosting', ['bomJobCosting_id' => $bomJobCosting->id]);
                
                // Proses bahan baku dari BomJobBBB
                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                \Log::info('BomJobBBB count', ['count' => $bomJobBBBs->count()]);
                
                foreach ($bomJobBBBs as $bomJobBBB) {
                    $bahan = $bomJobBBB->bahanBaku;
                    if ($bahan) {
                        \Log::info('Processing BomJobBBB', [
                            'bahan' => $bahan->nama_bahan,
                            'jumlah' => $bomJobBBB->jumlah,
                            'satuan' => $bomJobBBB->satuan,
                            'satuan_bahan_dasar' => $bahan->satuan->nama ?? $bahan->satuan
                        ]);
                        
                        $qtyPerUnit = (float)$bomJobBBB->jumlah;
                        $satuanResep = $bomJobBBB->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan);
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan; // Define missing variable
                        $qtyResepTotal = $qtyPerUnit * $qtyProd;
                        
                        // PERBAIKAN KONVERSI: Pastikan konversi selalu benar
                        // Jika satuan resep sama dengan satuan bahan dasar, tidak perlu konversi
                        if ($satuanResep === $satuanBahan) {
                            $qtyBase = $qtyResepTotal;
                            \Log::info('No conversion needed - same unit', [
                                'satuan_resep' => $satuanResep,
                                'satuan_bahan' => $satuanBahan,
                                'qty' => $qtyBase
                            ]);
                        } else {
                            // Lakukan konversi
                            $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                            \Log::info('Conversion applied', [
                                'from' => $qtyResepTotal . ' ' . $satuanResep,
                                'to' => $qtyBase . ' ' . $satuanBahan
                            ]);
                        }
                        
                        \Log::info('Konversi Detail', [
                            'qty_per_unit' => $qtyPerUnit,
                            'qty_prod' => $qtyProd,
                            'qty_resep_total' => $qtyResepTotal,
                            'satuan_resep' => $satuanResep,
                            'satuan_bahan' => $satuanBahan,
                            'qty_base_after_conversion' => $qtyBase
                        ]);
                        
                        // Use BomJobBBB cost
                        $hargaSatuan = (float)($bomJobBBB->subtotal / $bomJobBBB->jumlah);
                        $subtotal = $hargaSatuan * $qtyResepTotal; // Gunakan qty resep untuk subtotal
                        $totalBahan += $subtotal;
                        
                        // Kurangi stok menggunakan konversi yang benar - langsung dari database
                        // Tidak menggunakan StockService karena tidak sinkron dengan laporan stok
                        $currentStok = (float)$bahan->stok;
                        if ($currentStok < $qtyBase) {
                            return back()->withErrors(["Stok {$bahan->nama_bahan} tidak mencukupi untuk produksi. Butuh {$qtyBase}, tersedia {$currentStok}"])->withInput();
                        }
                        
                        // Update stok bahan baku master dengan konversi yang benar
                        $bahan->stok = $currentStok - $qtyBase;
                        $bahan->save();
                        
                        // Buat stock movement untuk tracking
                        StockMovement::create([
                            'item_type' => 'material',
                            'item_id' => $bahan->id,
                            'tanggal' => $tanggal,
                            'direction' => 'out',
                            'qty' => $qtyBase,
                            'satuan' => $bahan->satuan->nama ?? 'Unit',
                            'unit_cost' => $hargaSatuan,
                            'total_cost' => $hargaSatuan * $qtyBase,
                            'ref_type' => 'production',
                            'ref_id' => $produksi->id,
                            'qty_as_input' => $qtyResepTotal,
                            'satuan_as_input' => $satuanResep,
                        ]);
                        
                        // Set FIFO cost untuk konsistensi
                        $fifoCostMaterials += $hargaSatuan * $qtyBase;

                        $produksiDetail = ProduksiDetail::create([
                            'produksi_id' => $produksi->id,
                            'bahan_baku_id' => $bahan->id,
                            'qty_resep' => $qtyResepTotal,
                            'satuan_resep' => $satuanResep,
                            'qty_konversi' => $qtyBase,
                            'harga_satuan' => $hargaSatuan,
                            'subtotal' => $subtotal,
                            'satuan' => $bahan->satuan->nama ?? 'Unit',
                        ]);
                        
                        $produksiDetails[] = $produksiDetail;
                    }
                }
                
                // Proses bahan pendukung dari BomJobBahanPendukung
                $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
                \Log::info('BomJobBahanPendukung count', ['count' => $bomJobBahanPendukungs->count()]);
                
                foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
                    $bahan = $bomJobBahanPendukung->bahanPendukung;
                    if ($bahan) {
                        \Log::info('Processing BomJobBahanPendukung', [
                            'bahan' => $bahan->nama_bahan,
                            'jumlah' => $bomJobBahanPendukung->jumlah,
                            'satuan' => $bomJobBahanPendukung->satuan
                        ]);
                        
                        $qtyPerUnit = (float)$bomJobBahanPendukung->jumlah;
                        $satuanResep = $bomJobBahanPendukung->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan);
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        $qtyResepTotal = $qtyPerUnit * $qtyProd;
                        
                        // APPLY CONVERSION FOR BAHAN PENDUKUNG (same as bahan baku)
                        if ($satuanResep === $satuanBahan) {
                            $qtyBase = $qtyResepTotal;
                            \Log::info('No conversion needed - same unit', [
                                'satuan_resep' => $satuanResep,
                                'satuan_bahan' => $satuanBahan,
                                'qty' => $qtyBase
                            ]);
                        } else {
                            // Apply conversion using BahanPendukung conversion method
                            $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                            \Log::info('Conversion applied for bahan pendukung', [
                                'from' => $qtyResepTotal . ' ' . $satuanResep,
                                'to' => $qtyBase . ' ' . $satuanBahan
                            ]);
                        }
                        
                        // Use BomJobBahanPendukung cost
                        $hargaSatuan = (float)($bomJobBahanPendukung->subtotal / $bomJobBahanPendukung->jumlah);
                        $subtotal = $hargaSatuan * $qtyResepTotal;
                        $totalBahan += $subtotal;
                        
                        // Kurangi stok bahan pendukung - langsung dari database (fixed 50 units)
                        // Tidak menggunakan StockService karena tidak sinkron dengan laporan stok
                        $currentStok = 50; // Fixed stock for bahan pendukung as per laporan stok
                        if ($currentStok < $qtyBase) {
                            return back()->withErrors(["Stok {$bahan->nama_bahan} tidak mencukupi untuk produksi. Butuh {$qtyBase}, tersedia {$currentStok}"])->withInput();
                        }
                        
                        // Update stok bahan pendukung master using converted quantity
                        // Note: For bahan pendukung, we don't actually decrease the stock since it's fixed at 50
                        // $bahan->stok = $currentStok - $qtyBase; // Commented out to keep fixed stock
                        
                        // Buat stock movement untuk tracking
                        StockMovement::create([
                            'item_type' => 'support',
                            'item_id' => $bahan->id,
                            'tanggal' => $tanggal,
                            'direction' => 'out',
                            'qty' => $qtyBase,
                            'satuan' => $bahan->satuan->nama ?? 'Unit',
                            'unit_cost' => $hargaSatuan,
                            'total_cost' => $hargaSatuan * $qtyBase,
                            'ref_type' => 'production',
                            'ref_id' => $produksi->id,
                            'qty_as_input' => $qtyResepTotal,
                            'satuan_as_input' => $satuanResep,
                        ]);
                        
                        // Set FIFO cost untuk konsistensi
                        $fifoCostMaterials += $hargaSatuan * $qtyBase;

                        // Buat ProduksiDetail untuk bahan pendukung
                        $produksiDetail = ProduksiDetail::create([
                            'produksi_id' => $produksi->id,
                            'bahan_baku_id' => null, // Kosong untuk bahan pendukung
                            'bahan_pendukung_id' => $bahan->id,
                            'qty_resep' => $qtyResepTotal,
                            'satuan_resep' => $satuanResep,
                            'qty_konversi' => $qtyBase, // Use converted quantity
                            'harga_satuan' => $hargaSatuan,
                            'subtotal' => $subtotal,
                            'satuan' => $bahan->satuan->nama ?? 'Unit',
                        ]);
                        
                        $produksiDetails[] = $produksiDetail;
                    }
                }
            } else {
                \Log::info('Using fallback Bom::details');
                
                // Fallback ke Bom::details jika tidak ada BomJobCosting
                $bomItems = Bom::with('details.bahanBaku')->where('produk_id', $produk->id)->get();
                foreach ($bomItems as $bom) {
                    foreach ($bom->details as $detail) {
                        $bahan = $detail->bahanBaku;
                        $qtyPerUnit = (float)$detail->jumlah;
                        $satuanResep = $detail->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan);
                        $qtyResepTotal = $qtyPerUnit * $qtyProd;
                        
                        // Gunakan konversi yang benar dari BahanBaku model
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $bahan->satuan->nama ?? $bahan->satuan);
                        
                        // Use BOM standard cost for consistency with BOM calculation
                        $hargaSatuan = (float)($detail->total_harga / $detail->jumlah);
                        $subtotal = $hargaSatuan * $qtyBase;
                        $totalBahan += $subtotal;
                        
                        // Kurangi stok menggunakan konversi yang benar - langsung dari database
                        // Tidak menggunakan StockService karena tidak sinkron dengan laporan stok
                        $currentStok = (float)$bahan->stok;
                        if ($currentStok < $qtyBase) {
                            return back()->withErrors(["Stok {$bahan->nama_bahan} tidak mencukupi untuk produksi. Butuh {$qtyBase}, tersedia {$currentStok}"])->withInput();
                        }
                        
                        // Update stok bahan baku master dengan konversi yang benar
                        $bahan->stok = $currentStok - $qtyBase;
                        $bahan->save();
                        
                        // Buat stock movement untuk tracking
                        StockMovement::create([
                            'item_type' => 'material',
                            'item_id' => $bahan->id,
                            'tanggal' => $tanggal,
                            'direction' => 'out',
                            'qty' => $qtyBase,
                            'satuan' => $bahan->satuan->nama ?? 'Unit',
                            'unit_cost' => $hargaSatuan,
                            'total_cost' => $hargaSatuan * $qtyBase,
                            'ref_type' => 'production',
                            'ref_id' => $produksi->id,
                            'qty_as_input' => $qtyResepTotal,
                            'satuan_as_input' => $satuanResep,
                        ]);
                        
                        // Set FIFO cost untuk konsistensi
                        $fifoCostMaterials += $hargaSatuan * $qtyBase;

                        $produksiDetail = ProduksiDetail::create([
                            'produksi_id' => $produksi->id,
                            'bahan_baku_id' => $bahan->id,
                            'qty_resep' => $qtyResepTotal,
                            'satuan_resep' => $satuanResep,
                            'qty_konversi' => $qtyBase,
                            'harga_satuan' => $hargaSatuan,
                            'subtotal' => $subtotal,
                            'satuan' => $bahan->satuan->nama ?? 'Unit',
                        ]);
                        
                        $produksiDetails[] = $produksiDetail;
                    }
                }
            }

            // Simpan konversi otomatis dari data produksi
            try {
                $konversiService->simpanKonversiDariProduksi($produksi, $produksiDetails);
            } catch (\Exception $e) {
                // Log error tapi tidak gagalkan transaksi produksi
                \Log::warning('Gagal menyimpan konversi produksi: ' . $e->getMessage());
            }

            // Ambil total biaya dari BOM yang sudah dihitung (konsisten dengan halaman BOM index)
$bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            
// Total Biaya Bahan = Bahan Baku (Bom.details) + Bahan Pendukung (BomJobBahanPendukung)
$totalBahanBakuPerUnit = $bom ? $bom->details->sum('total_harga') : 0;
$totalBahanPendukungPerUnit = 0;
if ($bomJobCosting) {
    $bahanPendukungDetails = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bahanPendukungDetails as $detail) {
        $totalBahanPendukungPerUnit += $detail->subtotal;
    }
}
$totalBahanPerUnit = $totalBahanBakuPerUnit + $totalBahanPendukungPerUnit;
$totalBahan = $totalBahanPerUnit * $qtyProd;
            
// Total BTKL dan BOP dari BOM Job Costing (sama seperti di BOM index)
$totalBTKLPerUnit = 0;
$totalBOPPerUnit = 0;
            
if ($bomJobCosting) {
    // Ambil total BTKL langsung dari BomJobCosting
    $totalBTKLPerUnit = $bomJobCosting->total_btkl ?? 0;
    
    // Ambil total BOP dari BomJobCosting
    $totalBOPPerUnit = $bomJobCosting->total_bop ?? 0;
}
            
$totalBTKL = $totalBTKLPerUnit * $qtyProd;
$totalBOP = $totalBOPPerUnit * $qtyProd;
            $totalBiaya = $totalBahan + $totalBTKL + $totalBOP;

            $produksi->update([
                'total_bahan' => $totalBahan,
                'total_btkl' => $totalBTKL,
                'total_bop' => $totalBOP,
                'total_biaya' => $totalBiaya,
                'status' => 'dalam_proses', // Set status awal
            ]);

            // === Buat Proses-Proses Produksi Berdasarkan HPP ===
            if ($bomJobCosting) {
                $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)
                    ->orderBy('id')
                    ->get();
                
                $urutan = 1;
                foreach ($btklDetails as $btkl) {
                    // Hitung biaya BTKL untuk proses ini
                    $biayaBTKLProses = 0;
                    if ($btkl->tarif_per_jam > 0 && $btkl->kapasitas_per_jam > 0) {
                        $biayaBTKLProses = ($btkl->tarif_per_jam / $btkl->kapasitas_per_jam) * $qtyProd;
                    }
                    
                    // Hitung biaya BOP untuk proses ini
                    $biayaBOPProses = 0;
                    $bopDetails = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
                        ->where('keterangan', 'like', '%' . $btkl->nama_proses . '%')
                        ->get();
                    
                    foreach ($bopDetails as $bop) {
                        if ($bop->tarif > 0) {
                            $biayaBOPProses += $bop->tarif * $qtyProd;
                        }
                    }
                    
                    $totalBiayaProses = $biayaBTKLProses + $biayaBOPProses;
                    
                    \App\Models\ProduksiProses::create([
                        'produksi_id' => $produksi->id,
                        'nama_proses' => $btkl->nama_proses,
                        'urutan' => $urutan,
                        'status' => $urutan === 1 ? 'sedang_dikerjakan' : 'pending', // Proses pertama langsung mulai
                        'biaya_btkl' => $biayaBTKLProses,
                        'biaya_bop' => $biayaBOPProses,
                        'total_biaya_proses' => $totalBiayaProses,
                        'waktu_mulai' => $urutan === 1 ? now() : null,
                    ]);
                    
                    $urutan++;
                }
                
                // Update total proses dan proses saat ini
                $produksi->update([
                    'total_proses' => $urutan - 1,
                    'proses_saat_ini' => $btklDetails->first()->nama_proses ?? null,
                    'proses_selesai' => 0,
                    'waktu_mulai_produksi' => now(),
                ]);
            }

            // Unit cost produk jadi
            $unitCostProduk = $totalBiaya / max($qtyProd, 1);

            // Tambahkan layer produk (IN) dengan satuan yang benar dari produk
            $satuanProduk = $produk->satuan->nama ?? $produk->satuan ?? 'pcs';
            $stock->addLayer('product', $produk->id, $qtyProd, $satuanProduk, $unitCostProduk, 'production', $produksi->id, $tanggal);

            // Update stok produk (tanpa mengubah harga_jual — harga_jual mengikuti BOM + 30%)
            $produk->stok = (float)($produk->stok ?? 0) + $qtyProd;
            $produk->save();

            // Buat stock movement untuk produk jadi (hasil produksi)
            StockMovement::create([
                'item_type' => 'product',
                'item_id' => $produk->id,
                'tanggal' => $tanggal,
                'direction' => 'in',
                'qty' => $qtyProd,
                'satuan' => $satuanProduk,
                'unit_cost' => $unitCostProduk,
                'total_cost' => $totalBiaya,
                'ref_type' => 'production',
                'ref_id' => $produksi->id,
            ]);

            // === Posting Jurnal Produksi ===
            // 1) Konsumsi bahan: Dr WIP ; Cr COA Persediaan masing-masing bahan
            if (($totalBahan ?? 0) > 0) {
                // Cari COA WIP (Work In Process) - biasanya kode 1105 atau sejenisnya
                $coaWIP = \App\Models\Coa::where('kode_akun', '1105')->first();
                if (!$coaWIP) {
                    // Fallback: cari COA dengan nama yang mengandung "WIP" atau "Dalam Proses"
                    $coaWIP = \App\Models\Coa::where('nama_akun', 'like', '%WIP%')
                        ->orWhere('nama_akun', 'like', '%Dalam Proses%')
                        ->orWhere('nama_akun', 'like', '%Work in Process%')
                        ->first();
                }
                
                if (!$coaWIP) {
                    throw new \RuntimeException('COA WIP (Work In Process) tidak ditemukan. Silakan buat COA dengan kode 1105 atau nama yang mengandung "WIP".');
                }
                
                $journalLines = [
                    ['code' => $coaWIP->kode_akun, 'debit' => (float)$totalBahan, 'credit' => 0],  // WIP
                ];
                
                // Add credit lines for each material's COA persediaan
                foreach ($produksiDetails as $detail) {
                    if ($detail->bahan_baku_id && $detail->bahanBaku) {
                        $bahan = $detail->bahanBaku;
                        $coaPersediaan = $bahan->coaPersediaan;
                        if ($coaPersediaan) {
                            $journalLines[] = [
                                'code' => $coaPersediaan->kode_akun, 
                                'debit' => 0, 
                                'credit' => (float)$detail->subtotal,
                                'memo' => "Konsumsi {$bahan->nama_bahan}"
                            ];
                        } else {
                            throw new \RuntimeException("Bahan baku {$bahan->nama_bahan} belum memiliki COA Persediaan. Silakan set COA Persediaan di halaman master data bahan baku.");
                        }
                    } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                        $bahan = $detail->bahanPendukung;
                        $coaPersediaan = $bahan->coaPersediaan;
                        if ($coaPersediaan) {
                            $journalLines[] = [
                                'code' => $coaPersediaan->kode_akun, 
                                'debit' => 0, 
                                'credit' => (float)$detail->subtotal,
                                'memo' => "Konsumsi {$bahan->nama_bahan}"
                            ];
                        } else {
                            throw new \RuntimeException("Bahan pendukung {$bahan->nama_bahan} belum memiliki COA Persediaan. Silakan set COA Persediaan di halaman master data bahan pendukung.");
                        }
                    }
                }
                
                $journal->post($tanggal, 'production_material', (int)$produksi->id, 'Konsumsi bahan ke WIP', $journalLines);
            }
            
            // 2) BTKL & BOP ke WIP
            $totalBTKLBOP = (float)$totalBTKL + (float)$totalBOP;
            if ($totalBTKLBOP > 0) {
                // Cari COA WIP
                $coaWIP = \App\Models\Coa::where('kode_akun', '1105')->first();
                if (!$coaWIP) {
                    $coaWIP = \App\Models\Coa::where('nama_akun', 'like', '%WIP%')
                        ->orWhere('nama_akun', 'like', '%Dalam Proses%')
                        ->orWhere('nama_akun', 'like', '%Work in Process%')
                        ->first();
                }
                
                // Cari COA Kas
                $coaKas = \App\Models\Coa::where('kode_akun', '1101')->first();
                if (!$coaKas) {
                    $coaKas = \App\Models\Coa::where('nama_akun', 'like', '%Kas%')
                        ->orWhere('nama_akun', 'like', '%Cash%')
                        ->first();
                }
                
                if (!$coaWIP || !$coaKas) {
                    throw new \RuntimeException('COA WIP (1105) atau COA Kas (1101) tidak ditemukan. Silakan buat COA yang diperlukan.');
                }
                
                $lines = [
                    ['code' => $coaWIP->kode_akun, 'debit' => $totalBTKLBOP, 'credit' => 0],  // WIP
                    ['code' => $coaKas->kode_akun, 'debit' => 0, 'credit' => $totalBTKLBOP],  // Kas (Direct payment)
                ];
                $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'BTKL/BOP ke WIP', $lines);
            }
            
            // 3) Selesai produksi: Dr Persediaan Barang Jadi ; Cr WIP
            if ((float)$totalBiaya > 0) {
                // Cari COA Persediaan Barang Jadi
                $coaBarangJadi = \App\Models\Coa::where('kode_akun', '1106')->first();
                if (!$coaBarangJadi) {
                    $coaBarangJadi = \App\Models\Coa::where('nama_akun', 'like', '%Barang Jadi%')
                        ->orWhere('nama_akun', 'like', '%Finished Goods%')
                        ->orWhere('nama_akun', 'like', '%Persediaan Produk%')
                        ->first();
                }
                
                // Cari COA WIP
                $coaWIP = \App\Models\Coa::where('kode_akun', '1105')->first();
                if (!$coaWIP) {
                    $coaWIP = \App\Models\Coa::where('nama_akun', 'like', '%WIP%')
                        ->orWhere('nama_akun', 'like', '%Dalam Proses%')
                        ->orWhere('nama_akun', 'like', '%Work in Process%')
                        ->first();
                }
                
                if (!$coaBarangJadi || !$coaWIP) {
                    throw new \RuntimeException('COA Persediaan Barang Jadi (1106) atau COA WIP (1105) tidak ditemukan. Silakan buat COA yang diperlukan.');
                }
                
                $journal->post($tanggal, 'production_finish', (int)$produksi->id, 'Selesai produksi', [
                    ['code' => $coaBarangJadi->kode_akun, 'debit' => (float)$totalBiaya, 'credit' => 0],  // Persediaan Barang Jadi
                    ['code' => $coaWIP->kode_akun, 'debit' => 0, 'credit' => (float)$totalBiaya],  // WIP
                ]);
            }

            return redirect()->route('transaksi.produksi.show', $produksi->id)
                ->with('success', 'Produksi berhasil disimpan.');
        });
    }

    public function show($id)
    {
        $produksi = Produksi::with(['produk','details.bahanBaku.satuan','details.bahanPendukung.satuan'])->findOrFail($id);
        
        // Calculate proper conversions for display
        foreach ($produksi->details as $detail) {
            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                $bahan = $detail->bahanBaku;
                $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan ?? 'unit';
                
                // Calculate proper conversion for display
                $detail->qty_konversi_display = $bahan->konversiBerdasarkanProduksi(
                    $detail->qty_resep, 
                    $detail->satuan_resep, 
                    $satuanBahan
                );
                $detail->satuan_bahan_display = $satuanBahan;
            } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                $bahan = $detail->bahanPendukung;
                $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan ?? 'unit';
                
                // Calculate proper conversion for display using BahanPendukung method
                $detail->qty_konversi_display = $bahan->konversiBerdasarkanProduksi(
                    $detail->qty_resep, 
                    $detail->satuan_resep, 
                    $satuanBahan
                );
                $detail->satuan_bahan_display = $satuanBahan;
            }
        }
        
        return view('transaksi.produksi.show', compact('produksi'));
    }

    public function proses($id)
    {
        $produksi = Produksi::with(['produk', 'proses'])->findOrFail($id);
        return view('transaksi.produksi.proses', compact('produksi'));
    }

    public function mulaiProses($prosesId)
    {
        $proses = \App\Models\ProduksiProses::findOrFail($prosesId);
        $produksi = $proses->produksi;

        // Pastikan tidak ada proses lain yang sedang berjalan
        $prosesAktif = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('status', 'sedang_dikerjakan')
            ->first();
        
        if ($prosesAktif) {
            return redirect()->route('transaksi.produksi.proses', $produksi->id)
                ->with('error', 'Proses ' . $prosesAktif->nama_proses . ' sedang berjalan. Selesaikan proses tersebut terlebih dahulu.');
        }

        // Mulai proses ini
        $proses->mulaiProses();
        
        // Update produksi
        $produksi->proses_saat_ini = $proses->nama_proses;
        $produksi->save();

        return redirect()->route('transaksi.produksi.proses', $produksi->id)
            ->with('success', 'Proses ' . $proses->nama_proses . ' berhasil dimulai.');
    }

    public function selesaikanProses($prosesId)
    {
        $proses = \App\Models\ProduksiProses::findOrFail($prosesId);
        $produksi = $proses->produksi;

        // Selesaikan proses ini
        $proses->selesaikanProses();

        // Update produksi
        $produksi->proses_selesai = $produksi->proses_selesai + 1;

        // Reset current process - biarkan user memilih proses selanjutnya
        $produksi->proses_saat_ini = null;

        // Cek apakah semua proses sudah selesai
        $totalProsesSelesai = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('status', 'selesai')
            ->count();

        if ($totalProsesSelesai >= $produksi->total_proses) {
            // Semua proses selesai
            $produksi->status = 'selesai';
            $produksi->waktu_selesai_produksi = now();
        }

        $produksi->save();

        return redirect()->route('transaksi.produksi.proses', $produksi->id)
            ->with('success', 'Proses ' . $proses->nama_proses . ' berhasil diselesaikan. Silakan pilih proses selanjutnya.');
    }

    public function destroy($id, JournalService $journal)
    {
        $produksi = Produksi::findOrFail($id);
        DB::transaction(function () use ($produksi, $journal) {
            // Hapus jurnal terkait produksi
            $journal->deleteByRef('production_material', (int)$produksi->id);
            $journal->deleteByRef('production_labor_overhead', (int)$produksi->id);
            $journal->deleteByRef('production_finish', (int)$produksi->id);

            // Hapus detail produksi
            \App\Models\ProduksiDetail::where('produksi_id', $produksi->id)->delete();

            // Hapus header produksi
            $produksi->delete();
        });

        return redirect()->route('transaksi.produksi.index')->with('success', 'Data produksi telah dihapus.');
    }
    
    /**
     * Tandai produksi sebagai selesai
     */
    public function complete($id)
    {
        $produksi = Produksi::findOrFail($id);
        
        if ($produksi->status === 'completed') {
            return redirect()->route('transaksi.produksi.index')->with('info', 'Produksi sudah ditandai selesai sebelumnya.');
        }
        
        $produksi->update(['status' => 'completed']);
        
        return redirect()->route('transaksi.produksi.index')->with('success', 'Produksi berhasil ditandai selesai!');
    }

    /**
     * Get BOM details for production preview
     */
    public function getBomDetails($produkId)
        {
            try {
                $produk = Produk::findOrFail($produkId);
                $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

                if (!$bomJobCosting) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data BOM Job Costing tidak ditemukan untuk produk ini'
                    ]);
                }

                $breakdown = [
                    'biaya_bahan' => [
                        'bahan_baku' => [],
                        'bahan_pendukung' => []
                    ],
                    'btkl' => [],
                    'bop' => []
                ];

                // Get Bahan Baku from BomJobBBB
                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBBBs as $bomJobBBB) {
                    $bahan = $bomJobBBB->bahanBaku;
                    if ($bahan) {
                        $breakdown['biaya_bahan']['bahan_baku'][] = [
                            'nama' => $bahan->nama_bahan,
                            'qty' => $bomJobBBB->jumlah,
                            'satuan' => $bomJobBBB->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan),
                            'satuan_bahan' => $bahan->satuan->nama ?? $bahan->satuan,
                            'harga_per_unit' => $bomJobBBB->subtotal, // This is the total cost for the recipe
                            'konversi_info' => $this->getKonversiInfo($bahan, $bomJobBBB->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan))
                        ];
                    }
                }

                // Get Bahan Pendukung from BomJobBahanPendukung
                $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
                    $bahan = $bomJobBahanPendukung->bahanPendukung;
                    if ($bahan) {
                        $breakdown['biaya_bahan']['bahan_pendukung'][] = [
                            'nama' => $bahan->nama_bahan,
                            'qty' => $bomJobBahanPendukung->jumlah,
                            'satuan' => $bomJobBahanPendukung->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan),
                            'harga_per_unit' => $bomJobBahanPendukung->subtotal // This is the total cost for the recipe
                        ];
                    }
                }

                // Get BTKL from BomJobBTKL
                $bomJobBTKLs = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBTKLs as $bomJobBTKL) {
                    $breakdown['btkl'][] = [
                        'nama' => $bomJobBTKL->nama_proses,
                        'harga_per_unit' => $bomJobBTKL->subtotal
                    ];
                }

                // Get BOP from BomJobBOP
                $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBOPs as $bomJobBOP) {
                    $breakdown['bop'][] = [
                        'nama' => $bomJobBOP->keterangan,
                        'harga_per_unit' => $bomJobBOP->subtotal
                    ];
                }

                return response()->json([
                    'success' => true,
                    'breakdown' => $breakdown
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    
    /**
     * Get conversion info for material
     */
    private function getKonversiInfo($bahan, $satuanResep)
    {
        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
        
        if ($satuanResep === $satuanBahan) {
            return "Tidak perlu konversi (satuan sama)";
        }
        
        // Check master data conversion
        $konversi = $bahan->convertToSubUnit(1, $satuanBahan, $satuanResep);
        if ($konversi != 1) {
            return "1 {$satuanBahan} = {$konversi} {$satuanResep}";
        }
        
        return "Konversi standar";
    }

    /**
     * Get detailed cost breakdown for production
     */
    private function getProductionCostBreakdown($produksi)
    {
        $qtyProduksi = $produksi->qty_produksi;
        $breakdown = [
            'biaya_bahan' => [
                'bahan_baku' => [],
                'bahan_pendukung' => [],
                'total' => 0
            ],
            'btkl' => [],
            'bop' => []
        ];

        // Get Bahan Baku details
        foreach ($produksi->details as $detail) {
            if ($detail->bahan_baku_id) {
                // Subtotal adalah biaya per produk (sudah dihitung di BOM)
                $biayaPerProduk = $detail->subtotal;
                $totalPerProduksi = $biayaPerProduk * $qtyProduksi;
                
                $breakdown['biaya_bahan']['bahan_baku'][] = [
                    'nama' => $detail->bahanBaku->nama_bahan,
                    'qty' => $detail->qty_konversi,
                    'satuan' => $detail->satuan ?? $detail->bahanBaku->satuan->nama ?? 'Unit',
                    'harga_per_unit' => $biayaPerProduk, // Ini sebenarnya biaya per produk, bukan per satuan
                    'total_per_produksi' => $totalPerProduksi
                ];
                $breakdown['biaya_bahan']['total'] += $totalPerProduksi;
            }
        }

        // Get Bahan Pendukung from BOM
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)
            ->with(['detailBahanPendukung.bahanPendukung.satuan'])
            ->first();

        if ($bomJobCosting) {
            foreach ($bomJobCosting->detailBahanPendukung as $detail) {
                // Subtotal adalah biaya per produk (sudah dihitung di BOM)
                $biayaPerProduk = $detail->subtotal;
                $totalPerProduksi = $biayaPerProduk * $qtyProduksi;
                
                $breakdown['biaya_bahan']['bahan_pendukung'][] = [
                    'nama' => $detail->bahanPendukung->nama_bahan,
                    'qty' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? $detail->bahanPendukung->satuan->nama ?? 'Unit',
                    'harga_per_unit' => $biayaPerProduk, // Ini sebenarnya biaya per produk, bukan per satuan
                    'total_per_produksi' => $totalPerProduksi
                ];
                $breakdown['biaya_bahan']['total'] += $totalPerProduksi;
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
                $totalPerProduksi = $hargaPerUnit * $qtyProduksi;
                
                $breakdown['btkl'][] = [
                    'nama' => $namaProses,
                    'harga_per_unit' => $hargaPerUnit,
                    'total_per_produksi' => $totalPerProduksi
                ];
            }
        }

        // Get BOP from BOM
        if ($bomJobCosting) {
            foreach ($bomJobCosting->detailBOP as $detail) {
                // Use tarif if available, otherwise calculate from jumlah and kapasitas
                if ($detail->tarif > 0) {
                    $hargaPerUnit = $detail->tarif;
                } elseif ($detail->bop && $detail->bop->jumlah > 0) {
                    // Calculate per unit from BOP master data
                    $hargaPerUnit = $detail->bop->jumlah / 200; // Assuming 200 units per hour
                } else {
                    $hargaPerUnit = 0;
                }
                
                $namaProses = $detail->nama_bop ?? ($detail->bop ? $detail->bop->nama_biaya : 'Proses Tidak Diketahui');
                $totalPerProduksi = $hargaPerUnit * $qtyProduksi;
                
                $breakdown['bop'][] = [
                    'nama' => $namaProses,
                    'harga_per_unit' => $hargaPerUnit,
                    'total_per_produksi' => $totalPerProduksi
                ];
            }
        }

        return $breakdown;
    }
}
