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
            'jumlah_produksi_bulanan' => 'required|numeric|min:0.0001',
            'hari_produksi_bulanan' => 'required|integer|min:1|max:31',
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
            $tanggal = now()->toDateString(); // Use current date for process costing

            // For process costing, we don't validate stock here - just save as draft
            // Stock validation will happen when "Mulai Produksi" is clicked

            // Create production plan without consuming materials
            $produksi = Produksi::create([
                'produk_id' => $produk->id,
                'tanggal' => $tanggal,
                'jumlah_produksi_bulanan' => $request->jumlah_produksi_bulanan,
                'hari_produksi_bulanan' => $request->hari_produksi_bulanan,
                'qty_produksi' => $qtyProd,
                'status' => 'draft', // Use draft status for production plan
            ]);

            // Calculate total costs from BOM data (without consuming materials)
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
            ]);

            // Don't process production or consume materials - only save the plan
            // Material consumption will happen when "Mulai Produksi" button is clicked

            return redirect()->route('transaksi.produksi.index')
                ->with('success', 'Rencana produksi berhasil disimpan. Klik "Mulai Produksi" untuk memulai proses produksi.');
        });
    }

    /**
     * Mulai produksi - cek stok dan mulai proses
     */
    public function mulaiProduksi($id, StockService $stock, JournalService $journal)
    {
        $produksi = Produksi::findOrFail($id);
        
        if ($produksi->status !== 'draft') {
            return redirect()->back()->with('error', 'Produksi tidak dalam status draft (siap untuk dimulai).');
        }

        return DB::transaction(function () use ($produksi, $stock, $journal) {
            $produk = $produksi->produk;
            $qtyProd = $produksi->qty_produksi;
            $tanggal = now()->toDateString();

            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            
            // Validasi stok cukup untuk setiap bahan baku
            $shortages = [];
            $shortNames = [];
            
            if ($bomJobCosting) {
                // Periksa bahan baku
                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBBBs as $bomJobBBB) {
                    $bahan = $bomJobBBB->bahanBaku;
                    if ($bahan) {
                        $qtyResepTotal = $bomJobBBB->jumlah * $qtyProd;
                        $satuanResep = $bomJobBBB->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        $available = (float)($bahan->stok ?? 0);
                        
                        if ($available + 1e-9 < $qtyBase) {
                            $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh " . number_format($qtyBase, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2) . " {$satuanBahan}";
                            $shortNames[] = $bahan->nama_bahan;
                        }
                    }
                }
                
                // Periksa bahan pendukung
                $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
                    $bahan = $bomJobBahanPendukung->bahanPendukung;
                    if ($bahan) {
                        $qtyResepTotal = $bomJobBahanPendukung->jumlah * $qtyProd;
                        $satuanResep = $bomJobBahanPendukung->satuan ?? $bahan->satuan->nama ?? $bahan->satuan;
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        $available = 200; // Fixed stock for bahan pendukung
                        
                        if ($available + 1e-9 < $qtyBase) {
                            $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh " . number_format($qtyBase, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2) . " {$satuanBahan}";
                            $shortNames[] = $bahan->nama_bahan;
                        }
                    }
                }
            }
            
            if (!empty($shortages)) {
                return redirect()->back()->with('error', 'Tidak dapat memulai produksi. Bahan yang kurang: ' . implode(', ', $shortNames) . '. Detail: ' . implode(' | ', $shortages));
            }

            // Jika stok cukup, mulai produksi - proses material consumption
            $produksiDetails = [];
            
            // Proses semua bahan dari BomJobCosting
            if ($bomJobCosting) {
                \Log::info('Starting production with BomJobCosting', ['bomJobCosting_id' => $bomJobCosting->id]);
                
                // Proses bahan baku dari BomJobBBB
                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                \Log::info('Processing BomJobBBB count', ['count' => $bomJobBBBs->count()]);
                
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
                        $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                        $qtyResepTotal = $qtyPerUnit * $qtyProd;
                        
                        // Konversi ke satuan dasar bahan
                        if ($satuanResep === $satuanBahan) {
                            $qtyBase = $qtyResepTotal;
                        } else {
                            $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        }
                        
                        // Use BomJobBBB cost
                        $hargaSatuan = (float)($bomJobBBB->subtotal / $bomJobBBB->jumlah);
                        $subtotal = $hargaSatuan * $qtyResepTotal;
                        
                        // Kurangi stok bahan baku
                        $currentStok = (float)$bahan->stok;
                        if ($currentStok < $qtyBase) {
                            return redirect()->back()->with('error', "Stok {$bahan->nama_bahan} tidak mencukupi untuk produksi. Butuh {$qtyBase}, tersedia {$currentStok}");
                        }
                        
                        // Update stok bahan baku master
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

                        // Update existing ProduksiDetail if exists, or create new one
                        $produksiDetail = ProduksiDetail::updateOrCreate([
                            'produksi_id' => $produksi->id,
                            'bahan_baku_id' => $bahan->id,
                        ], [
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
                \Log::info('Processing BomJobBahanPendukung count', ['count' => $bomJobBahanPendukungs->count()]);
                
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
                        
                        // Apply conversion for bahan pendukung
                        if ($satuanResep === $satuanBahan) {
                            $qtyBase = $qtyResepTotal;
                        } else {
                            $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                        }
                        
                        // Use BomJobBahanPendukung cost
                        $hargaSatuan = (float)($bomJobBahanPendukung->subtotal / $bomJobBahanPendukung->jumlah);
                        $subtotal = $hargaSatuan * $qtyResepTotal;
                        
                        // Buat stock movement untuk tracking (bahan pendukung tidak dikurangi stoknya)
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

                        // Update existing ProduksiDetail if exists, or create new one
                        $produksiDetail = ProduksiDetail::updateOrCreate([
                            'produksi_id' => $produksi->id,
                            'bahan_pendukung_id' => $bahan->id,
                        ], [
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

            // JANGAN tambahkan stok produk jadi di sini - hanya setelah semua proses selesai
            // Produk jadi akan ditambahkan di selesaikanProses() ketika semua proses selesai

            // Create journal entries for material consumption only
            $this->createMaterialJournals($produksi, $journal, $produksiDetails);

            // Create production processes for manual execution
            $this->createProductionProcesses($produksi);

            // Update status produksi ke dalam_proses - bukan selesai
            $produksi->update([
                'status' => 'dalam_proses',
                'waktu_mulai_produksi' => now(),
                // waktu_selesai_produksi akan diset ketika semua proses selesai
            ]);

            return redirect()->route('transaksi.produksi.proses', $produksi->id)
                ->with('success', 'Material berhasil dikonsumsi. Silakan mulai proses produksi secara bertahap.');
        });
    }

    public function show($id)
    {
        $produksi = Produksi::with(['produk','details.bahanBaku.satuan','details.bahanPendukung.satuan'])->findOrFail($id);
        
        // If production is still in draft status, fetch BOM breakdown data
        if ($produksi->status === 'draft') {
            // Get BOM breakdown similar to create page
            $bomBreakdown = $this->getProductionCostBreakdown($produksi);
            $produksi->bomBreakdown = $bomBreakdown;
        } else {
            // Calculate proper conversions for display from existing details
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
            // Semua proses selesai - SEKARANG baru tambahkan stok produk jadi
            $this->completeProduction($produksi);
        }

        $produksi->save();

        return redirect()->route('transaksi.produksi.proses', $produksi->id)
            ->with('success', 'Proses ' . $proses->nama_proses . ' berhasil diselesaikan. ' . 
                   ($totalProsesSelesai >= $produksi->total_proses ? 'Produksi telah selesai!' : 'Silakan pilih proses selanjutnya.'));
    }

    /**
     * Complete production when all processes are finished
     */
    private function completeProduction($produksi)
    {
        $produk = $produksi->produk;
        $qtyProd = $produksi->qty_produksi;
        $tanggal = now()->toDateString();

        // Tambahkan stok produk jadi SEKARANG
        $produk->stok = ($produk->stok ?? 0) + $qtyProd;
        $produk->save();

        // Buat stock movement untuk produk jadi
        StockMovement::create([
            'item_type' => 'product',
            'item_id' => $produk->id,
            'tanggal' => $tanggal,
            'direction' => 'in',
            'qty' => $qtyProd,
            'satuan' => $produk->satuan->nama ?? 'Unit',
            'unit_cost' => $produksi->total_biaya / $qtyProd,
            'total_cost' => $produksi->total_biaya,
            'ref_type' => 'production',
            'ref_id' => $produksi->id,
        ]);

        // Create remaining journal entries (labor/overhead and finished goods)
        $this->createLaborOverheadJournals($produksi);
        $this->transferWipToFinishedGoods($produksi);

        // Update status produksi ke selesai
        $produksi->update([
            'status' => 'selesai',
            'waktu_selesai_produksi' => now(),
        ]);
    }

    /**
     * Create labor and overhead journal entries
     */
    private function createLaborOverheadJournals($produksi)
    {
        $journal = app(\App\Services\JournalService::class);
        $tanggal = $produksi->tanggal;
        
        // 2. Journal for Labor and Overhead (BTKL & BOP → WIP)
        $laborOverheadEntries = [];
        $totalLaborOverhead = $produksi->total_btkl + $produksi->total_bop;
        
        if ($totalLaborOverhead > 0) {
            $coaWIP = \App\Models\Coa::where('kode_akun', '1301')->first(); // Barang Dalam Proses
            $coaBTKL = \App\Models\Coa::where('kode_akun', '5201')->first(); // Biaya Tenaga Kerja Langsung
            $coaBOP = \App\Models\Coa::where('kode_akun', '5301')->first(); // Biaya Overhead Pabrik
            
            if ($coaWIP) {
                $laborOverheadEntries[] = [
                    'code' => $coaWIP->kode_akun,
                    'debit' => $totalLaborOverhead,
                    'credit' => 0,
                    'memo' => 'Transfer BTKL & BOP ke WIP'
                ];
            }
            
            if ($coaBTKL && $produksi->total_btkl > 0) {
                $laborOverheadEntries[] = [
                    'code' => $coaBTKL->kode_akun,
                    'debit' => 0,
                    'credit' => $produksi->total_btkl,
                    'memo' => 'Alokasi BTKL ke produksi'
                ];
            }
            
            if ($coaBOP && $produksi->total_bop > 0) {
                $laborOverheadEntries[] = [
                    'code' => $coaBOP->kode_akun,
                    'debit' => 0,
                    'credit' => $produksi->total_bop,
                    'memo' => 'Alokasi BOP ke produksi'
                ];
            }
            
            if (!empty($laborOverheadEntries)) {
                $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'Alokasi BTKL & BOP ke Produksi', $laborOverheadEntries);
            }
        }
    }
    
    /**
     * Transfer WIP ke Barang Jadi saat produksi selesai
     */
    private function transferWipToFinishedGoods($produksi)
    {
        $journal = app(\App\Services\JournalService::class);
        $totalBiaya = (float)$produksi->total_biaya;
        
        if ($totalBiaya > 0) {
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
            
            $journal->post($produksi->tanggal, 'production_finish', (int)$produksi->id, 'Transfer WIP ke Barang Jadi', [
                ['code' => $coaBarangJadi->kode_akun, 'debit' => $totalBiaya, 'credit' => 0],  // Persediaan Barang Jadi
                ['code' => $coaWIP->kode_akun, 'debit' => 0, 'credit' => $totalBiaya],  // WIP
            ]);
        }
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
        
        if ($produksi->status === 'completed' || $produksi->status === 'selesai') {
            return redirect()->route('transaksi.produksi.index')->with('info', 'Produksi sudah ditandai selesai sebelumnya.');
        }
        
        // Update status dan transfer WIP ke Barang Jadi
        $produksi->update([
            'status' => 'selesai',
            'waktu_selesai_produksi' => now()
        ]);
        
        // PERBAIKAN WIP ACCOUNTING: Transfer WIP ke Barang Jadi saat produksi selesai
        $this->transferWipToFinishedGoods($produksi);
        
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
                    // Get capacity per hour from BomJobBTKL or related BTKL/ProsesProduksi
                    $kapasitasPerJam = $bomJobBTKL->kapasitas_per_jam;
                    
                    // If not available in BomJobBTKL, try to get from related BTKL
                    if (!$kapasitasPerJam && $bomJobBTKL->btkl) {
                        $kapasitasPerJam = $bomJobBTKL->btkl->kapasitas_per_jam ?? 0;
                    }
                    
                    // If still not available, try to get from ProsesProduksi by name
                    if (!$kapasitasPerJam) {
                        $prosesProduksi = \App\Models\ProsesProduksi::where('nama_proses', $bomJobBTKL->nama_proses)->first();
                        if ($prosesProduksi) {
                            $kapasitasPerJam = $prosesProduksi->kapasitas_per_jam ?? 0;
                        }
                    }
                    
                    // Default to 1 if still not found
                    $kapasitasPerJam = $kapasitasPerJam ?: 1;
                    
                    $breakdown['btkl'][] = [
                        'nama' => $bomJobBTKL->nama_proses,
                        'harga_per_unit' => $bomJobBTKL->subtotal,
                        'kapasitas_per_jam' => $kapasitasPerJam,
                        'tarif_per_jam' => $bomJobBTKL->tarif_per_jam ?? 0
                    ];
                }

                // Get BOP from BomJobBOP - Group by process like in detail page
                $bopByProcess = [];
                
                $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
                
                // Group BOP components by process
                foreach ($bomJobBOPs as $bomJobBOP) {
                    $namaProses = 'Umum';
                    $namaBiaya = strtolower($bomJobBOP->nama_bop ?? '');
                    
                    if (stripos($namaBiaya, 'penggorengan') !== false) {
                        $namaProses = 'Penggorengan';
                    } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                        $namaProses = 'Perbumbuan';
                    } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                        $namaProses = 'Pengemasan';
                    }
                    
                    if (!isset($bopByProcess[$namaProses])) {
                        $bopByProcess[$namaProses] = 0;
                    }
                    
                    $bopByProcess[$namaProses] += $bomJobBOP->subtotal ?? 0;
                }
                
                // Convert to array format expected by frontend
                foreach ($bopByProcess as $processName => $totalCost) {
                    $breakdown['bop'][] = [
                        'nama' => $processName,
                        'harga_per_unit' => $totalCost
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
        $produk = $produksi->produk;
        $qtyProd = $produksi->qty_produksi;
        
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        $breakdown = [
            'biaya_bahan' => [
                'bahan_baku' => [],
                'bahan_pendukung' => []
            ],
            'btkl' => [],
            'bop' => []
        ];

        if (!$bomJobCosting) {
            return $breakdown;
        }

        // Get Bahan Baku from BomJobBBB
        $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
        foreach ($bomJobBBBs as $bomJobBBB) {
            $bahan = $bomJobBBB->bahanBaku;
            if ($bahan) {
                $qtyResepTotal = $bomJobBBB->jumlah * $qtyProd;
                $satuanResep = $bomJobBBB->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan);
                $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                
                // Calculate conversion
                $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                
                $hargaSatuan = (float)($bomJobBBB->subtotal / $bomJobBBB->jumlah);
                $subtotal = $hargaSatuan * $qtyResepTotal;
                
                $breakdown['biaya_bahan']['bahan_baku'][] = [
                    'nama' => $bahan->nama_bahan,
                    'qty_resep' => $qtyResepTotal,
                    'satuan_resep' => $satuanResep,
                    'qty_konversi' => $qtyBase,
                    'satuan_bahan' => $satuanBahan,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'konversi_info' => $this->getKonversiInfo($bahan, $satuanResep)
                ];
            }
        }

        // Get Bahan Pendukung from BomJobBahanPendukung
        $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
        foreach ($bomJobBahanPendukungs as $bomJobBahanPendukung) {
            $bahan = $bomJobBahanPendukung->bahanPendukung;
            if ($bahan) {
                $qtyResepTotal = $bomJobBahanPendukung->jumlah * $qtyProd;
                $satuanResep = $bomJobBahanPendukung->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan);
                $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                
                // Calculate conversion
                if ($satuanResep === $satuanBahan) {
                    $qtyBase = $qtyResepTotal;
                } else {
                    $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                }
                
                $hargaSatuan = (float)($bomJobBahanPendukung->subtotal / $bomJobBahanPendukung->jumlah);
                $subtotal = $hargaSatuan * $qtyResepTotal;
                
                $breakdown['biaya_bahan']['bahan_pendukung'][] = [
                    'nama' => $bahan->nama_bahan,
                    'qty_resep' => $qtyResepTotal,
                    'satuan_resep' => $satuanResep,
                    'qty_konversi' => $qtyBase,
                    'satuan_bahan' => $satuanBahan,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal
                ];
            }
        }

        // Get BTKL from BomJobBTKL
        $bomJobBTKLs = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
        foreach ($bomJobBTKLs as $bomJobBTKL) {
            $totalPerProduksi = $bomJobBTKL->subtotal * $qtyProd;
            
            $breakdown['btkl'][] = [
                'nama' => $bomJobBTKL->nama_proses,
                'biaya_per_unit' => $bomJobBTKL->subtotal,
                'total_biaya' => $totalPerProduksi
            ];
        }

        // Get BOP from BomJobBOP - Group by process
        $bopByProcess = [];
        $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
        
        foreach ($bomJobBOPs as $bomJobBOP) {
            $namaProses = 'Umum';
            $namaBiaya = strtolower($bomJobBOP->nama_bop ?? '');
            
            if (stripos($namaBiaya, 'penggorengan') !== false) {
                $namaProses = 'Penggorengan';
            } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                $namaProses = 'Perbumbuan';
            } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                $namaProses = 'Pengemasan';
            }
            
            if (!isset($bopByProcess[$namaProses])) {
                $bopByProcess[$namaProses] = 0;
            }
            
            $bopByProcess[$namaProses] += $bomJobBOP->subtotal ?? 0;
        }
        
        foreach ($bopByProcess as $processName => $costPerUnit) {
            $totalPerProduksi = $costPerUnit * $qtyProd;
            
            $breakdown['bop'][] = [
                'nama' => $processName,
                'biaya_per_unit' => $costPerUnit,
                'total_biaya' => $totalPerProduksi
            ];
        }

        return $breakdown;
    }

    /**
     * Create material consumption journals only (not labor/overhead/finished goods)
     */
    private function createMaterialJournals($produksi, $journal, $produksiDetails)
    {
        $tanggal = $produksi->tanggal;
        
        // 1. Journal for Material Consumption (Material → WIP)
        $materialEntries = [];
        $totalMaterialCost = 0;
        
        foreach ($produksiDetails as $detail) {
            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                $bahan = $detail->bahanBaku;
                $coaPersediaan = \App\Models\Coa::where('kode_akun', $bahan->coa_persediaan_id ?? '1101')->first();
                
                if ($coaPersediaan && $detail->subtotal > 0) {
                    $materialEntries[] = [
                        'code' => $coaPersediaan->kode_akun,
                        'debit' => 0,
                        'credit' => $detail->subtotal,
                        'memo' => "Konsumsi {$bahan->nama_bahan}"
                    ];
                    $totalMaterialCost += $detail->subtotal;
                }
            }
            
            if ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                $bahan = $detail->bahanPendukung;
                $coaPersediaan = \App\Models\Coa::where('kode_akun', $bahan->coa_persediaan_id ?? '1150')->first();
                
                if ($coaPersediaan && $detail->subtotal > 0) {
                    $materialEntries[] = [
                        'code' => $coaPersediaan->kode_akun,
                        'debit' => 0,
                        'credit' => $detail->subtotal,
                        'memo' => "Konsumsi {$bahan->nama_bahan}"
                    ];
                    $totalMaterialCost += $detail->subtotal;
                }
            }
        }
        
        // Add WIP debit entry for materials
        if ($totalMaterialCost > 0) {
            $coaWIP = \App\Models\Coa::where('kode_akun', '1301')->first(); // Barang Dalam Proses
            if ($coaWIP) {
                array_unshift($materialEntries, [
                    'code' => $coaWIP->kode_akun,
                    'debit' => $totalMaterialCost,
                    'credit' => 0,
                    'memo' => 'Transfer material ke WIP'
                ]);
                
                $journal->post($tanggal, 'production_material', (int)$produksi->id, 'Konsumsi Material untuk Produksi', $materialEntries);
            }
        }
    }

    /**
     * Create production processes for manual execution
     */
    private function createProductionProcesses($produksi)
    {
        // Get BOM Job Costing to determine processes
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();
        
        if (!$bomJobCosting) {
            // If no BOM Job Costing, create a default process
            \App\Models\ProduksiProses::updateOrCreate([
                'produksi_id' => $produksi->id,
                'nama_proses' => 'Produksi ' . $produksi->produk->nama_produk,
            ], [
                'urutan' => 1,
                'status' => 'pending', // Use 'pending' instead of 'belum_dimulai'
                'biaya_btkl' => $produksi->total_btkl,
                'biaya_bop' => $produksi->total_bop,
                'total_biaya_proses' => $produksi->total_btkl + $produksi->total_bop,
            ]);
            
            $produksi->update([
                'total_proses' => 1,
                'proses_selesai' => 0,
            ]);
            return;
        }

        // Get BTKL processes
        $bomJobBTKLs = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
        
        if ($bomJobBTKLs->count() == 0) {
            // If no BTKL processes, create a default process
            \App\Models\ProduksiProses::updateOrCreate([
                'produksi_id' => $produksi->id,
                'nama_proses' => 'Produksi ' . $produksi->produk->nama_produk,
            ], [
                'urutan' => 1,
                'status' => 'pending', // Use 'pending' instead of 'belum_dimulai'
                'biaya_btkl' => $produksi->total_btkl,
                'biaya_bop' => $produksi->total_bop,
                'total_biaya_proses' => $produksi->total_btkl + $produksi->total_bop,
            ]);
            
            $produksi->update([
                'total_proses' => 1,
                'proses_selesai' => 0,
            ]);
            return;
        }
        
        $prosesOrder = 1;
        foreach ($bomJobBTKLs as $bomJobBTKL) {
            // Create production process record
            \App\Models\ProduksiProses::updateOrCreate([
                'produksi_id' => $produksi->id,
                'nama_proses' => $bomJobBTKL->proses_produksi ?? 'Proses ' . $prosesOrder,
            ], [
                'urutan' => $prosesOrder,
                'status' => 'pending', // Use 'pending' instead of 'belum_dimulai'
                'biaya_btkl' => $bomJobBTKL->subtotal ?? 0,
                'biaya_bop' => 0, // BOP will be calculated separately
                'total_biaya_proses' => $bomJobBTKL->subtotal ?? 0,
            ]);
            
            $prosesOrder++;
        }

        // Update total proses count
        $produksi->update([
            'total_proses' => $prosesOrder - 1,
            'proses_selesai' => 0,
        ]);
    }

    /**
     * Create journal entries for production
     */
    private function createProductionJournals($produksi, $journal, $produksiDetails)
    {
        $tanggal = $produksi->tanggal;
        
        // 1. Journal for Material Consumption (Material → WIP)
        $materialEntries = [];
        $totalMaterialCost = 0;
        
        foreach ($produksiDetails as $detail) {
            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                $bahan = $detail->bahanBaku;
                $coaPersediaan = \App\Models\Coa::where('kode_akun', $bahan->coa_persediaan_id ?? '1101')->first();
                
                if ($coaPersediaan && $detail->subtotal > 0) {
                    $materialEntries[] = [
                        'code' => $coaPersediaan->kode_akun,
                        'debit' => 0,
                        'credit' => $detail->subtotal,
                        'memo' => "Konsumsi {$bahan->nama_bahan}"
                    ];
                    $totalMaterialCost += $detail->subtotal;
                }
            }
            
            if ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                $bahan = $detail->bahanPendukung;
                $coaPersediaan = \App\Models\Coa::where('kode_akun', $bahan->coa_persediaan_id ?? '1150')->first();
                
                if ($coaPersediaan && $detail->subtotal > 0) {
                    $materialEntries[] = [
                        'code' => $coaPersediaan->kode_akun,
                        'debit' => 0,
                        'credit' => $detail->subtotal,
                        'memo' => "Konsumsi {$bahan->nama_bahan}"
                    ];
                    $totalMaterialCost += $detail->subtotal;
                }
            }
        }
        
        // Add WIP debit entry for materials
        if ($totalMaterialCost > 0) {
            $coaWIP = \App\Models\Coa::where('kode_akun', '1301')->first(); // Barang Dalam Proses
            if ($coaWIP) {
                array_unshift($materialEntries, [
                    'code' => $coaWIP->kode_akun,
                    'debit' => $totalMaterialCost,
                    'credit' => 0,
                    'memo' => 'Transfer material ke WIP'
                ]);
                
                $journal->post($tanggal, 'production_material', (int)$produksi->id, 'Konsumsi Material untuk Produksi', $materialEntries);
            }
        }
        
        // 2. Journal for Labor and Overhead (BTKL & BOP → WIP)
        $laborOverheadEntries = [];
        $totalLaborOverhead = $produksi->total_btkl + $produksi->total_bop;
        
        if ($totalLaborOverhead > 0) {
            $coaWIP = \App\Models\Coa::where('kode_akun', '1301')->first(); // Barang Dalam Proses
            $coaBTKL = \App\Models\Coa::where('kode_akun', '5201')->first(); // Biaya Tenaga Kerja Langsung
            $coaBOP = \App\Models\Coa::where('kode_akun', '5301')->first(); // Biaya Overhead Pabrik
            
            if ($coaWIP) {
                $laborOverheadEntries[] = [
                    'code' => $coaWIP->kode_akun,
                    'debit' => $totalLaborOverhead,
                    'credit' => 0,
                    'memo' => 'Transfer BTKL & BOP ke WIP'
                ];
            }
            
            if ($coaBTKL && $produksi->total_btkl > 0) {
                $laborOverheadEntries[] = [
                    'code' => $coaBTKL->kode_akun,
                    'debit' => 0,
                    'credit' => $produksi->total_btkl,
                    'memo' => 'Alokasi BTKL ke produksi'
                ];
            }
            
            if ($coaBOP && $produksi->total_bop > 0) {
                $laborOverheadEntries[] = [
                    'code' => $coaBOP->kode_akun,
                    'debit' => 0,
                    'credit' => $produksi->total_bop,
                    'memo' => 'Alokasi BOP ke produksi'
                ];
            }
            
            if (!empty($laborOverheadEntries)) {
                $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'Alokasi BTKL & BOP ke Produksi', $laborOverheadEntries);
            }
        }
        
        // 3. Journal for Finished Goods (WIP → Finished Goods)
        $finishedGoodsEntries = [];
        $totalProductionCost = $produksi->total_biaya;
        
        if ($totalProductionCost > 0) {
            $coaWIP = \App\Models\Coa::where('kode_akun', '1301')->first(); // Barang Dalam Proses
            $coaFinishedGoods = \App\Models\Coa::where('kode_akun', '1201')->first(); // Persediaan Barang Jadi
            
            if ($coaFinishedGoods && $coaWIP) {
                $finishedGoodsEntries = [
                    [
                        'code' => $coaFinishedGoods->kode_akun,
                        'debit' => $totalProductionCost,
                        'credit' => 0,
                        'memo' => 'Transfer ke Barang Jadi'
                    ],
                    [
                        'code' => $coaWIP->kode_akun,
                        'debit' => 0,
                        'credit' => $totalProductionCost,
                        'memo' => 'Selesai produksi'
                    ]
                ];
                
                $journal->post($tanggal, 'production_finish', (int)$produksi->id, 'Transfer WIP ke Barang Jadi', $finishedGoodsEntries);
            }
        }
    }
}