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
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $query = Produksi::with([
            'produk', 
            'details.bahanBaku.satuan',
            'details.bahanPendukung',
            'proses'
        ])->where('user_id', auth()->id());
        
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
        
        // Get riwayat produksi (executions)
        $produksis = $query->orderBy('tanggal','desc')->paginate(10);
        
        // Get products for dropdown
        $produks = Produk::where('user_id', auth()->id())
            ->orderBy('nama_produk')->get();
        
        // ========================================
        // NEW: Get Data Produk (Master/Template)
        // ========================================
        // Get unique products that have been setup for production (have template)
        $dataProdukQuery = Produksi::select('produk_id', 
                DB::raw('MAX(id) as latest_template_id'),
                DB::raw('MAX(jumlah_produksi_bulanan) as jumlah_produksi_bulanan'),
                DB::raw('MAX(hari_produksi_bulanan) as hari_produksi_bulanan'),
                DB::raw('MAX(qty_produksi) as qty_per_hari'),
                DB::raw('MAX(total_biaya) as total_biaya_per_hari'),
                DB::raw('MAX(created_at) as last_created'))
            ->where('user_id', auth()->id())
            ->groupBy('produk_id')
            ->orderBy('last_created', 'desc')
            ->get();
        
        // Enhance data with product info and template
        $dataProduk = $dataProdukQuery->map(function($item) {
            $produk = Produk::find($item->produk_id);
            $template = Produksi::with(['details.bahanBaku', 'details.bahanPendukung'])
                ->find($item->latest_template_id);
            
            return (object)[
                'produk_id' => $item->produk_id,
                'produk' => $produk,
                'lastTemplate' => $template,
                'jumlah_produksi_bulanan' => $item->jumlah_produksi_bulanan,
                'hari_produksi_bulanan' => $item->hari_produksi_bulanan,
                'qty_per_hari' => $item->qty_per_hari,
                'total_biaya_per_hari' => $item->total_biaya_per_hari,
                'last_created' => $item->last_created,
            ];
        });
        
        return view('transaksi.produksi.index', compact('produksis', 'produks', 'dataProduk'));
    }

    public function create()
    {
        // Get products that have HPP data (BBB is product-specific)
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $produks = Produk::where('user_id', auth()->id())
            ->whereHas('biayaBahanBaku', function($query) {
                $query->where('user_id', auth()->id())
                    ->whereHas('hargaPokokProduksiBiayaBahanBaku', function($q) {
                        $q->where('user_id', auth()->id());
                    });
            })
            ->with(['satuan'])
            ->orderBy('nama_produk')
            ->get();
        
        return view('transaksi.produksi.create', compact('produks'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal, KonversiProduksiService $konversiService)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'coa_persediaan_barang_jadi_id' => 'nullable|exists:coas,id',
            'jumlah_produksi_bulanan' => 'required|numeric|min:0.0001',
            'hari_produksi_bulanan' => 'required|integer|min:1|max:31',
            'qty_produksi' => 'required|numeric|min:0.0001',
        ]);

        $user_id = auth()->id();
        $produk = Produk::findOrFail($request->produk_id);

        // Guard: pastikan produk sudah memiliki HPP data
        $hasHppData = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
            ->whereHas('biayaBahanBaku', function($q) use ($produk) {
                $q->where('produk_id', $produk->id);
            })
            ->exists();

        if (!$hasHppData) {
            return back()->withErrors([
                'hpp' => 'Produk belum memiliki data Harga Pokok Produksi. Silakan buat HPP terlebih dahulu di menu Master Data > Harga Pokok Produksi.',
            ])->withInput();
        }

        return DB::transaction(function () use ($request, $produk, $user_id, $journal) {
            $qtyProd = (float)$request->qty_produksi;
            $tanggal = now();

            // Get HPP breakdown data (sama seperti di getBomDetails)
            $hppData = $this->getHppBreakdownForProduction($produk->id, $user_id);

            // Calculate totals
            $totalBBB = 0;
            foreach ($hppData['bbb'] as $bbb) {
                $totalBBB += $bbb['subtotal'];
            }

            $totalBTKL = 0;
            foreach ($hppData['btkl'] as $btkl) {
                $totalBTKL += $btkl['subtotal'];
            }

            $totalBOP = 0;
            foreach ($hppData['bop'] as $bop) {
                $totalBOP += $bop['subtotal'];
            }

            $totalBahan = $totalBBB * $qtyProd;
            $totalBTKLTotal = $totalBTKL * $qtyProd;
            $totalBOPTotal = $totalBOP * $qtyProd;
            $totalBiaya = $totalBahan + $totalBTKLTotal + $totalBOPTotal;

            // Determine COA Persediaan Barang Jadi
            // Priority: 1) User selection, 2) Product's COA, 3) Default COA 1161
            $coaBarangJadiId = $request->coa_persediaan_barang_jadi_id;
            if (!$coaBarangJadiId && $produk->coa_persediaan_id) {
                $coaBarangJadiId = $produk->coa_persediaan_id; // Use product's COA
            }
            if (!$coaBarangJadiId) {
                // Fallback to default: find COA with code 1161 or 116
                $coaBarangJadiId = $this->getCoaIdByKodeForUser('1161', $user_id) 
                    ?? $this->getCoaIdByKodeForUser('116', $user_id);
            }

            // Create production record
            $produksi = Produksi::create([
                'produk_id' => $produk->id,
                'coa_persediaan_barang_jadi_id' => $coaBarangJadiId,
                'tanggal' => $tanggal,
                'jumlah_produksi_bulanan' => $request->jumlah_produksi_bulanan,
                'hari_produksi_bulanan' => $request->hari_produksi_bulanan,
                'qty_produksi' => $qtyProd,

                'total_bahan' => $totalBahan,
                'total_btkl' => $totalBTKLTotal,
                'total_bop' => $totalBOPTotal,
                'total_biaya' => $totalBiaya,
                'status' => 'draft',
                'user_id' => $user_id,
            ]);

            // Save production details (BBB, BTKL, BOP)
            $this->saveProductionDetails($produksi, $hppData, $qtyProd);

            // Update total_proses after creating proses records
            $produksi->total_proses = $produksi->proses()->count();
            $produksi->save();

            // NOTE: Journal entries will be created when production is COMPLETED
            // NOT when it's first created (status = 'draft')
            // See completeProduction() method

            return redirect()->route('transaksi.produksi.index')
                ->with('success', 'Produksi berhasil disimpan. Silakan mulai produksi untuk memproses bahan baku.');
        });
    }

    /**
     * Mulai produksi - cek stok dan mulai proses
     */
    public function mulaiProduksi($id, StockService $stock, JournalService $journal)
    {
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $produksi = Produksi::where('user_id', auth()->id())->findOrFail($id);
        
        if ($produksi->status !== 'draft') {
            return redirect()->back()->with('error', 'Produksi tidak dalam status draft (siap untuk dimulai).');
        }

        return DB::transaction(function () use ($produksi, $stock, $journal) {
            $produk = $produksi->produk;
            $qtyProd = $produksi->qty_produksi;
            $tanggal = $produksi->tanggal->format('Y-m-d');

            // Validasi stok cukup untuk setiap bahan
            $shortages = [];
            $shortNames = [];
            
            // Periksa bahan baku dari produksi_details
            foreach ($produksi->details as $detail) {
                // Check Bahan Baku - USING QUANTITY not NOMINAL
                if ($detail->bahanBaku) {
                    $bahan = $detail->bahanBaku;
                    
                    // QUANTITY yang dibutuhkan dari detail  
                    $qtyButuh = (float)$detail->qty_resep;
                    $satuanResep = $detail->satuan_resep;
                    $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                    
                    // Convert qty needed ke satuan bahan jika berbeda
                    if ($satuanResep !== $satuanBahan) {
                        $qtyButuh = $bahan->konversiBerdasarkanProduksi($qtyButuh, $satuanResep, $satuanBahan);
                    }
                    
                    // Get ACTUAL stock quantity using stok_real_time
                    $qtyTersedia = (float)$bahan->stok_real_time;
                    
                    // DEBUGGING
                    \Log::info("VALIDATION BAHAN BAKU - {$bahan->nama_bahan}:", [
                        'bahan_id' => $bahan->id,
                        'qty_butuh' => $qtyButuh,
                        'satuan_resep' => $satuanResep,
                        'satuan_bahan' => $satuanBahan,
                        'qty_tersedia' => $qtyTersedia,
                        'cukup' => ($qtyTersedia >= $qtyButuh) ? 'YA' : 'TIDAK'
                    ]);
                    
                    // Validate based on QUANTITY (kg, ekor, etc) - NOT NOMINAL
                    if ($qtyTersedia < $qtyButuh) {
                        $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh " . number_format($qtyButuh, 2) . " {$satuanBahan}, tersedia " . number_format($qtyTersedia, 2) . " {$satuanBahan}";
                        $shortNames[] = $bahan->nama_bahan;
                    }
                }
                
                // Check Bahan Pendukung - USING QUANTITY not NOMINAL
                if ($detail->bahanPendukung) {
                    $bahan = $detail->bahanPendukung;
                    
                    // QUANTITY yang dibutuhkan dari detail
                    $qtyButuh = (float)$detail->qty_resep;
                    $satuanResep = $detail->satuan_resep;
                    $satuanBahan = $bahan->satuan->nama ?? 'unit';
                    
                    // Convert qty needed ke satuan bahan jika berbeda
                    if ($satuanResep !== $satuanBahan) {
                        $qtyButuh = $bahan->konversiBerdasarkanProduksi($qtyButuh, $satuanResep, $satuanBahan);
                    }
                    
                    // Get ACTUAL stock quantity using stok_real_time
                    $qtyTersedia = (float)$bahan->stok_real_time;
                    
                    // DEBUGGING
                    \Log::info("VALIDATION BAHAN PENDUKUNG - {$bahan->nama_bahan}:", [
                        'bahan_id' => $bahan->id,
                        'qty_butuh' => $qtyButuh,
                        'satuan_resep' => $satuanResep,
                        'satuan_bahan' => $satuanBahan,
                        'qty_tersedia' => $qtyTersedia,
                        'cukup' => ($qtyTersedia >= $qtyButuh) ? 'YA' : 'TIDAK'
                    ]);
                    
                    // Validate based on QUANTITY - NOT NOMINAL
                    if ($qtyTersedia < $qtyButuh) {
                        $shortages[] = "Stok {$bahan->nama_bahan} (Bahan Pendukung) tidak cukup. Butuh " . number_format($qtyButuh, 2) . " {$satuanBahan}, tersedia " . number_format($qtyTersedia, 2) . " {$satuanBahan}";
                        $shortNames[] = $bahan->nama_bahan;
                    }
                }
            }
            
            if (!empty($shortages)) {
                return redirect()->back()->with('error', 'Tidak dapat memulai produksi. Bahan yang kurang: ' . implode(', ', $shortNames) . '. Detail: ' . implode(' | ', $shortages));
            }

            // Jika stok cukup, mulai produksi - kurangi stok bahan
            foreach ($produksi->details as $detail) {
                // Reduce Bahan Baku stock - USING NOMINAL/VALUE (same as Bahan Pendukung)
                if ($detail->bahanBaku) {
                    $bahan = $detail->bahanBaku;
                    $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                    
                    // IMPORTANT: Use NOMINAL from detail subtotal (already calculated from HPP)
                    $nominalDigunakan = $detail->subtotal; // Total nilai yang digunakan (Rp) from production plan
                    
                    // Get current price from master data
                    $hargaSatuanAktual = $bahan->harga_rata_rata ?? $bahan->harga_satuan ?? 0; // Current avg price per unit
                    
                    // Calculate qty to deduct based on nominal and current price
                    // Formula: Qty = Nominal / Harga Satuan
                    if ($hargaSatuanAktual > 0) {
                        $qtyDikurangi = $nominalDigunakan / $hargaSatuanAktual; // Convert nominal to qty using current price
                    } else {
                        // Fallback: if no price, use qty from detail
                        $qtyDikurangi = $detail->qty_resep;
                    }
                    
                    // IMPORTANT: Update stok directly in database
                    \DB::table('bahan_bakus')
                        ->where('id', $bahan->id)
                        ->decrement('stok', $qtyDikurangi);
                    
                    // Refresh model
                    $bahan->refresh();
                    
                    // Record stock movement with calculated qty
                    \App\Models\StockMovement::create([
                        'item_type' => 'material',
                        'item_id' => $bahan->id,
                        'tanggal' => now()->format('Y-m-d'),
                        'direction' => 'out',
                        'qty' => $qtyDikurangi,
                        'satuan' => $satuanBahan,
                        'unit_cost' => $hargaSatuanAktual,
                        'total_cost' => $nominalDigunakan, // Use nominal from detail as total cost
                        'keterangan' => "Produksi {$produk->nama_produk} - Nominal Rp " . number_format($nominalDigunakan, 0, ',', '.') . " @ Rp " . number_format($hargaSatuanAktual, 0, ',', '.') . "/{$satuanBahan} - {$produksi->id}",
                        'ref_type' => 'produksi',
                        'ref_id' => $produksi->id,
                    ]);
                }
                
                // Reduce Bahan Pendukung stock (NEW) - Based on NOMINAL/VALUE from detail subtotal
                if ($detail->bahanPendukung) {
                    $bahan = $detail->bahanPendukung;
                    
                    // IMPORTANT: Use subtotal from detail (already calculated from HPP)
                    $nominalDigunakan = $detail->subtotal; // Total nilai yang digunakan (Rp) from production plan
                    
                    // Get current price from master data
                    $hargaSatuanAktual = $bahan->harga_satuan ?? 0; // Current price per unit
                    
                    // Calculate qty to deduct based on nominal and current price
                    if ($hargaSatuanAktual > 0) {
                        $qtyDikurangi = $nominalDigunakan / $hargaSatuanAktual; // Convert nominal to qty using current price
                    } else {
                        // Fallback: if no price, use qty from detail
                        $qtyDikurangi = $detail->qty_resep;
                    }
                    
                    // IMPORTANT: Update saldo_awal (stock) directly in database
                    \DB::table('bahan_pendukungs')
                        ->where('id', $bahan->id)
                        ->decrement('saldo_awal', $qtyDikurangi);
                    
                    // Refresh model
                    $bahan->refresh();
                    
                    // Record stock movement for bahan pendukung
                    \App\Models\StockMovement::create([
                        'item_type' => 'support',
                        'item_id' => $bahan->id,
                        'tanggal' => now()->format('Y-m-d'),
                        'direction' => 'out',
                        'qty' => $qtyDikurangi,
                        'satuan' => $bahan->satuanRelation->nama ?? 'Unit', // Get proper unit name
                        'unit_cost' => $hargaSatuanAktual,
                        'total_cost' => $nominalDigunakan, // Use nominal from detail as total cost
                        'keterangan' => "Produksi {$produk->nama_produk} (BOP) - Nominal Rp " . number_format($nominalDigunakan, 0, ',', '.') . " @ Rp " . number_format($hargaSatuanAktual, 0, ',', '.') . "/unit - {$produksi->id}",
                        'ref_type' => 'produksi',
                        'ref_id' => $produksi->id,
                    ]);
                }
            }

            // Update status produksi
            $produksi->update([
                'status' => 'dalam_proses', // Status dalam proses, akan selesai setelah semua proses selesai
                'tanggal_mulai' => now(),
            ]);

            // Tambah stok barang jadi
            $produk->stok = (float)($produk->stok ?? 0) + $qtyProd;
            $produk->save();

            return redirect()->route('transaksi.produksi.proses', $produksi->id)
                ->with('success', 'Produksi berhasil dimulai. Stok bahan baku telah dikurangi dan stok barang jadi telah ditambahkan. Silakan kelola proses produksi.');
        });
    }

    /**
     * Mulai produksi hari ini dari template yang sudah ada
     */
    public function mulaiHariIni(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:produksis,id',
        ]);

        return DB::transaction(function () use ($request) {
            $user_id = auth()->id();
            
            // Get template (must be owned by user)
            $template = Produksi::where('user_id', $user_id)
                ->with(['details.bahanBaku', 'details.bahanPendukung', 'proses'])
                ->findOrFail($request->template_id);
            
            $produk = $template->produk;
            
            // Create new production record based on template
            $newProduksi = Produksi::create([
                'produk_id' => $template->produk_id,
                'coa_persediaan_barang_jadi_id' => $template->coa_persediaan_barang_jadi_id,
                'tanggal' => now(),
                'jumlah_produksi_bulanan' => $template->jumlah_produksi_bulanan,
                'hari_produksi_bulanan' => $template->hari_produksi_bulanan,
                'qty_produksi' => $template->qty_produksi,
                'total_bahan' => $template->total_bahan,
                'total_btkl' => $template->total_btkl,
                'total_bop' => $template->total_bop,
                'total_biaya' => $template->total_biaya,
                'status' => 'draft', // Start as draft, will be started manually
                'user_id' => $user_id,
            ]);
            
            // Copy production details (BBB + Bahan Pendukung)
            foreach ($template->details as $detail) {
                \App\Models\ProduksiDetail::create([
                    'produksi_id' => $newProduksi->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'bahan_pendukung_id' => $detail->bahan_pendukung_id,
                    'qty_resep' => $detail->qty_resep,
                    'satuan_resep' => $detail->satuan_resep,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                    'user_id' => $user_id,
                ]);
            }
            
            // Copy production processes (BTKL + BOP allocation)
            foreach ($template->proses as $proses) {
                \App\Models\ProduksiProses::create([
                    'produksi_id' => $newProduksi->id,
                    'proses_produksi_id' => $proses->proses_produksi_id,
                    'nama_proses' => $proses->nama_proses,
                    'urutan' => $proses->urutan,
                    'biaya_btkl' => $proses->biaya_btkl,
                    'biaya_bop' => $proses->biaya_bop,
                    'total_biaya_proses' => $proses->total_biaya_proses,
                    'status' => 'pending',
                    'user_id' => $user_id,
                ]);
            }
            
            // Update total_proses
            $newProduksi->total_proses = $newProduksi->proses()->count();
            $newProduksi->save();
            
            return redirect()->route('transaksi.produksi.index', ['tab' => 'riwayat'])
                ->with('success', "Produksi baru untuk {$produk->nama_produk} berhasil dibuat. Qty: " . number_format($newProduksi->qty_produksi, 2) . ". Klik 'Mulai' untuk memulai produksi.");
        });
    }

    public function show($id)
    {

        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $produksi = Produksi::with([
            'produk',
            'details.bahanBaku.satuan',
            'details.bahanPendukung.satuan',
            'proses'
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Always fetch breakdown data for display
        $breakdown = $this->getProductionCostBreakdown($produksi);
        $produksi->detail_breakdown = $breakdown;
        
        return view('transaksi.produksi.show', compact('produksi'));
    }

    public function proses($id)
    {
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $produksi = Produksi::with(['produk', 'proses'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        return view('transaksi.produksi.proses', compact('produksi'));
    }

    public function mulaiProses($prosesId)
    {
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $proses = \App\Models\ProduksiProses::where('user_id', auth()->id())->findOrFail($prosesId);
        $produksi = $proses->produksi;

        // Pastikan tidak ada proses lain yang sedang berjalan
        $prosesAktif = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('user_id', auth()->id())
            ->where('status', 'sedang_dikerjakan')
            ->first();
        
        if ($prosesAktif) {
            return redirect()->route('transaksi.produksi.proses', $produksi->id)
                ->with('error', 'Proses ' . $prosesAktif->nama_proses . ' sedang berjalan. Selesaikan proses tersebut terlebih dahulu.');
        }

        // Mulai proses ini
        $proses->mulaiProses();
        
        // Refresh data to ensure we have the latest timestamp
        $proses->refresh();
        
        // Update produksi
        $produksi->proses_saat_ini = $proses->nama_proses;
        $produksi->save();

        return redirect()->route('transaksi.produksi.proses', $produksi->id)
            ->with('success', 'Proses ' . $proses->nama_proses . ' berhasil dimulai.');
    }

    public function selesaikanProses($prosesId)
    {
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $proses = \App\Models\ProduksiProses::where('user_id', auth()->id())->findOrFail($prosesId);
        $produksi = $proses->produksi;

        // Selesaikan proses ini
        $proses->selesaikanProses();

        // Update produksi
        // Hitung ulang proses selesai berdasarkan data aktual
        $totalProsesSelesai = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('user_id', auth()->id())
            ->where('status', 'selesai')
            ->count();
        
        $produksi->proses_selesai = $totalProsesSelesai;

        // Reset current process - biarkan user memilih proses selanjutnya
        $produksi->proses_saat_ini = null;

        // Cek apakah semua proses sudah selesai
        $totalProsesSelesai = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('user_id', auth()->id())
            ->where('status', 'selesai')
            ->count();
        
        $totalProsesKeseluruhan = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('user_id', auth()->id())
            ->count();

        // Log for debugging
        \Log::info('Checking production completion', [
            'produksi_id' => $produksi->id,
            'total_proses_selesai' => $totalProsesSelesai,
            'total_proses_keseluruhan' => $totalProsesKeseluruhan,
            'total_proses_field' => $produksi->total_proses,
            'current_status' => $produksi->status
        ]);

        // Update total_proses jika tidak sesuai dengan proses yang sebenarnya ada
        if ($produksi->total_proses != $totalProsesKeseluruhan) {
            $produksi->total_proses = $totalProsesKeseluruhan;
        }

        // Complete production if all processes are done
        if ($totalProsesKeseluruhan > 0 && $totalProsesSelesai >= $totalProsesKeseluruhan) {
            // Semua proses selesai - SEKARANG baru tambahkan stok produk jadi
            $this->completeProduction($produksi);
        }

        $produksi->save();

        return redirect()->route('transaksi.produksi.proses', $produksi->id)
            ->with('success', 'Proses ' . $proses->nama_proses . ' berhasil diselesaikan. ' . 
                   ($produksi->total_proses > 0 && $totalProsesSelesai >= $produksi->total_proses ? 'Produksi telah selesai!' : 'Silakan pilih proses selanjutnya.'));
    }

    /**
     * Complete production when all processes are finished
     */
    private function completeProduction($produksi)
    {
        $produk = $produksi->produk;
        $qtyProd = $produksi->qty_produksi;
        $tanggal = $produksi->tanggal->format('Y-m-d');

        // Tambahkan stok produk jadi SEKARANG (already added in mulaiProduksi, but we keep this for safety)
        // Note: Stock was already added in mulaiProduksi, so we skip this to avoid double counting
        // $produk->stok = ($produk->stok ?? 0) + $qtyProd;
        // $produk->save();

        // Buat stock movement untuk produk jadi (if not already created)
        $existingMovement = StockMovement::where('ref_type', 'production')
            ->where('ref_id', $produksi->id)
            ->where('item_type', 'product')
            ->where('item_id', $produk->id)
            ->exists();
        
        if (!$existingMovement) {
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
        }

        // IMPORTANT: Create journal entries ONLY when production is completed
        // Check if journals already exist to avoid duplicates
        $existingJournal = \DB::table('jurnal_umum')
            ->where('tipe_referensi', 'produksi_bbb')
            ->where('referensi', $produksi->id)
            ->where('user_id', $produksi->user_id)
            ->exists();
        
        if (!$existingJournal) {
            // Get HPP data for journal creation
            $hppData = $this->getHppBreakdownForProduction($produk->id, $produksi->user_id);
            
            // Create journal entries
            $journal = app(\App\Services\JournalService::class);
            $this->createProductionJournals($produksi, $hppData, $qtyProd, $tanggal, $journal);
            
            \Log::info('Production journals created on completion', [
                'produksi_id' => $produksi->id,
                'user_id' => $produksi->user_id,
                'status' => 'selesai'
            ]);
        }

        // Update status produksi ke selesai
        $produksi->update([
            'status' => 'selesai',
            'waktu_selesai_produksi' => now(),
        ]);
    }

    /**
     * Start production again for completed products using last production data
     * Menggunakan data HPP yang sama seperti store() - tidak perlu BomJobCosting
     */
    public function mulaiLagi(Request $request, StockService $stock, JournalService $journal, KonversiProduksiService $konversiService)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
        ]);

        $user_id = auth()->id();
        $produk = Produk::where('user_id', $user_id)->findOrFail($request->produk_id);

        // Ambil data produksi terakhir yang selesai untuk referensi qty
        $lastProduction = Produksi::where('produk_id', $request->produk_id)
            ->where('user_id', $user_id)
            ->whereIn('status', ['selesai', 'completed'])
            ->orderBy('tanggal', 'desc')
            ->first();

        if (!$lastProduction) {
            return back()->withErrors([
                'produk_id' => 'Produk ini belum pernah menyelesaikan produksi sebelumnya.',
            ])->withInput();
        }

        // Guard: pastikan produk sudah memiliki HPP data
        $hasHppData = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
            ->whereHas('biayaBahanBaku', function($q) use ($produk) {
                $q->where('produk_id', $produk->id);
            })
            ->exists();

        if (!$hasHppData) {
            return back()->withErrors([
                'hpp' => 'Produk belum memiliki data Harga Pokok Produksi. Silakan buat HPP terlebih dahulu.',
            ])->withInput();
        }

        return DB::transaction(function () use ($request, $produk, $user_id, $lastProduction, $journal) {
            $qtyProd = $lastProduction->qty_produksi;
            $tanggal = now();

            // Gunakan getHppBreakdownForProduction - SAMA PERSIS dengan store()
            $hppData = $this->getHppBreakdownForProduction($produk->id, $user_id);

            // Hitung total biaya
            $totalBBB = 0;
            foreach ($hppData['bbb'] as $bbb) {
                $totalBBB += $bbb['subtotal'];
            }
            $totalBTKL = 0;
            foreach ($hppData['btkl'] as $btkl) {
                $totalBTKL += $btkl['subtotal'];
            }
            $totalBOP = 0;
            foreach ($hppData['bop'] as $bop) {
                $totalBOP += $bop['subtotal'];
            }

            $totalBahan    = $totalBBB  * $qtyProd;
            $totalBTKLTotal = $totalBTKL * $qtyProd;
            $totalBOPTotal  = $totalBOP  * $qtyProd;
            $totalBiaya    = $totalBahan + $totalBTKLTotal + $totalBOPTotal;

            // Buat record produksi baru
            $produksi = Produksi::create([
                'produk_id'                    => $produk->id,
                'coa_persediaan_barang_jadi_id' => $lastProduction->coa_persediaan_barang_jadi_id,
                'tanggal'                      => $tanggal,
                'jumlah_produksi_bulanan'      => $lastProduction->jumlah_produksi_bulanan,
                'hari_produksi_bulanan'        => $lastProduction->hari_produksi_bulanan,
                'qty_produksi'                 => $qtyProd,
                'total_bahan'                  => $totalBahan,
                'total_btkl'                   => $totalBTKLTotal,
                'total_bop'                    => $totalBOPTotal,
                'total_biaya'                  => $totalBiaya,
                'status'                       => 'draft',
                'user_id'                      => $user_id,
            ]);

            // Simpan detail produksi (BBB, BTKL, BOP) - sama seperti store()
            $this->saveProductionDetails($produksi, $hppData, $qtyProd);

            // Update total_proses
            $produksi->total_proses = $produksi->proses()->count();
            $produksi->save();

            return redirect()->route('transaksi.produksi.index')
                ->with('success', 'Produksi baru untuk ' . $produk->nama_produk . ' berhasil dibuat (' . number_format($qtyProd, 0, ',', '.') . ' pcs). Klik "Mulai Produksi" untuk memulai proses.');
        });
    }

    public function destroy($id, JournalService $journal)
    {
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $produksi = Produksi::where('user_id', auth()->id())->findOrFail($id);
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
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $produksi = Produksi::where('user_id', auth()->id())->findOrFail($id);
        
        if ($produksi->status === 'completed' || $produksi->status === 'selesai') {
            return redirect()->route('transaksi.produksi.index')->with('info', 'Produksi sudah ditandai selesai sebelumnya.');
        }
        
        // Update status (jurnal sudah dibuat saat produksi disimpan)
        $produksi->update([
            'status' => 'selesai',
            'waktu_selesai_produksi' => now()
        ]);
        
        // Note: Jurnal sudah dibuat saat produksi disimpan di method store()
        // Tidak perlu membuat jurnal lagi di sini
        
        return redirect()->route('transaksi.produksi.index')->with('success', 'Produksi berhasil ditandai selesai!');
    }

    /**
     * Get HPP details for production preview (using new HPP system)
     */
    public function getBomDetails($produkId)
    {
        try {
            $produk = Produk::findOrFail($produkId);
            $user_id = auth()->id();

            // Check if product has HPP data
            $hasHppData = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
                ->whereHas('biayaBahanBaku', function($q) use ($produkId) {
                    $q->where('produk_id', $produkId);
                })
                ->exists();


            if (!$hasHppData) {
return response()->json([
                    'success' => false,
                    'message' => 'Data Harga Pokok Produksi tidak ditemukan untuk produk ini. Silakan buat HPP terlebih dahulu di menu Master Data > Harga Pokok Produksi.'
                ]);
            }

            $breakdown = [
                'biaya_bahan' => [
                    'bahan_baku' => [],
                    'bahan_pendukung' => [] // Always empty - no longer used in new HPP system
                ],
                'btkl' => [],
                'bop' => [],
                'bop_komponen' => []
            ];

            // Get Bahan Baku from new HPP system
            $selectedBbb = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
                ->whereHas('biayaBahanBaku', function($q) use ($produkId) {
                    $q->where('produk_id', $produkId);
                })
                ->with('biayaBahanBaku.bahanBaku.satuan')
                ->get();

            foreach ($selectedBbb as $hpp) {
                $biayaBahan = $hpp->biayaBahanBaku;
                if ($biayaBahan && $biayaBahan->bahanBaku) {
                    $bahan = $biayaBahan->bahanBaku;
                    $breakdown['biaya_bahan']['bahan_baku'][] = [
                        'nama' => $bahan->nama_bahan,
                        'qty' => $biayaBahan->jumlah,
                        'satuan' => $biayaBahan->satuan ?: ($bahan->satuan->nama ?? 'Unit'),
                        'satuan_bahan' => $bahan->satuan->nama ?? 'Unit',
                        'harga_per_unit' => $biayaBahan->subtotal,
                        'coa_persediaan_kode' => $bahan->coa_persediaan_id ?? '1141',
                        'coa_persediaan_nama' => optional(\App\Models\Coa::where('kode_akun', $bahan->coa_persediaan_id)->first())->nama_akun ?? 'Pers. Bahan Baku',
                        'konversi_info' => $this->getKonversiInfo($bahan, $biayaBahan->satuan ?: ($bahan->satuan->nama ?? 'Unit'))
                    ];
                }
            }

            // Get BTKL from new HPP system
            $selectedBtkl = \App\Models\HargaPokokProduksiBtkl::where('user_id', $user_id)
                ->with('prosesProduksi')
                ->get();

            foreach ($selectedBtkl as $hpp) {
                $proses = $hpp->prosesProduksi;
                if ($proses) {
                    $tarifPerProduk = $proses->tarif_per_produk ?? 0;
                    $jumlahPegawai = $proses->jumlah_pegawai ?? 1;
                    $tarif = $tarifPerProduk * $jumlahPegawai;

                    $breakdown['btkl'][] = [
                        'nama' => $proses->nama_proses ?? 'Proses Produksi',
                        'harga_per_unit' => $tarif
                    ];
                }
            }

            // Get BOP from new HPP system with NEW STRUCTURE
            $selectedBop = \App\Models\HargaPokokProduksiBop::where('user_id', $user_id)
                ->with('bopProses') // Remove .prosesProduksi - no longer exists
                ->get();

            foreach ($selectedBop as $hpp) {
                $bopProses = $hpp->bopProses;
                if ($bopProses) {
                    $namaProses = $bopProses->nama_bop_proses ?? 'BOP'; // Use nama_bop_proses instead
                    $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;

                    // ========================================
                    // NEW STRUCTURE: Process both komponen types
                    // ========================================
                    
                    // 1. Process Bahan Pendukung (will reduce stock)
                    $komponenBahanPendukung = $bopProses->komponen_bahan_pendukung ?? [];
                    if (is_string($komponenBahanPendukung)) {
                        $komponenBahanPendukung = json_decode($komponenBahanPendukung, true) ?? [];
                    }
                    
                    if (is_array($komponenBahanPendukung) && count($komponenBahanPendukung) > 0) {
                        foreach ($komponenBahanPendukung as $komponen) {
                            $namaBahan = $komponen['nama'] ?? 'Bahan Pendukung';
                            $bahanPendukungId = $komponen['bahan_pendukung_id'] ?? null;
                            $qtyPerProduk = $komponen['qty_per_produk'] ?? 1;
                            $hargaSatuan = $komponen['harga_satuan'] ?? 0;
                            $total = $komponen['total'] ?? 0;
                            $coaDebit = $komponen['coa_debit'] ?? '1173'; // WIP
                            $coaKredit = $komponen['coa_kredit'] ?? '530'; // Pers Bahan Pendukung
                            
                            // Get COA info for kredit (source - bahan pendukung account)
                            $coaKreditObj = \App\Models\Coa::where('kode_akun', $coaKredit)
                                ->where('user_id', $user_id)
                                ->first();
                            
                            // Add to BOP display array
                            $breakdown['bop'][] = [
                                'nama_proses' => $namaProses,
                                'nama_komponen' => $namaBahan,
                                'harga_per_unit' => $total,
                                'coa_kode' => $coaKredit,
                                'coa_nama' => $coaKreditObj ? $coaKreditObj->nama_akun : 'Pers. Bahan Pendukung',
                                'is_bahan_pendukung' => true,
                                'bahan_pendukung_id' => $bahanPendukungId,
                                'qty_per_produk' => $qtyPerProduk
                            ];

                            // Add to BOP komponen for jurnal (will reduce bahan pendukung stock)
                            $breakdown['bop_komponen'][] = [
                                'nama_bop' => $namaProses . ' - ' . $namaBahan,
                                'nama_komponen' => $namaBahan,
                                'keterangan' => 'Bahan Pendukung',
                                'subtotal' => $total,
                                'coa_debit' => $coaDebit, // WIP
                                'coa_kredit' => $coaKredit, // Pers Bahan Pendukung
                                'coa_kode' => $coaKredit,
                                'coa_nama' => $coaKreditObj ? $coaKreditObj->nama_akun : 'Pers. Bahan Pendukung',
                                'is_bahan_pendukung' => true,
                                'bahan_pendukung_id' => $bahanPendukungId,
                                'qty_per_produk' => $qtyPerProduk
                            ];
                        }
                    }
                    
                    // 2. Process Komponen Lainnya (overhead costs - no stock reduction)
                    $komponenLainnya = $bopProses->komponen_lainnya ?? [];
                    if (is_string($komponenLainnya)) {
                        $komponenLainnya = json_decode($komponenLainnya, true) ?? [];
                    }
                    
                    if (is_array($komponenLainnya) && count($komponenLainnya) > 0) {
                        foreach ($komponenLainnya as $komponen) {
                            $namaKomponen = $komponen['nama_komponen'] ?? 'Overhead';
                            $nilaiPerProduk = $komponen['nilai_per_produk'] ?? 0;
                            $coaDebit = $komponen['coa_debit'] ?? '1173'; // WIP
                            $coaKredit = $komponen['coa_kredit'] ?? '550'; // Beban overhead
                            
                            // Get COA info for kredit
                            $coaKreditObj = \App\Models\Coa::where('kode_akun', $coaKredit)
                                ->where('user_id', $user_id)
                                ->first();
                            
                            // Add to BOP display array
                            $breakdown['bop'][] = [
                                'nama_proses' => $namaProses,
                                'nama_komponen' => $namaKomponen,
                                'harga_per_unit' => $nilaiPerProduk,
                                'coa_kode' => $coaKredit,
                                'coa_nama' => $coaKreditObj ? $coaKreditObj->nama_akun : 'BOP - ' . $namaKomponen,
                                'is_bahan_pendukung' => false
                            ];

                            // Add to BOP komponen for jurnal (overhead only - no stock)
                            $breakdown['bop_komponen'][] = [
                                'nama_bop' => $namaProses . ' - ' . $namaKomponen,
                                'nama_komponen' => $namaKomponen,
                                'keterangan' => 'Overhead',
                                'subtotal' => $nilaiPerProduk,
                                'coa_debit' => $coaDebit, // WIP
                                'coa_kredit' => $coaKredit, // Beban overhead
                                'coa_kode' => $coaKredit,
                                'coa_nama' => $coaKreditObj ? $coaKreditObj->nama_akun : 'BOP - ' . $namaKomponen,
                                'is_bahan_pendukung' => false
                            ];
                        }
                    }
                }
            }

            // Add produk COA info
            $breakdown['produk'] = [
                'nama' => $produk->nama_produk,
                'coa_persediaan_kode' => $produk->coa_persediaan_id ?? '1161',
                'coa_persediaan_nama' => optional(\App\Models\Coa::where('kode_akun', $produk->coa_persediaan_id)->first())->nama_akun ?? 'Pers. Barang Jadi ' . $produk->nama_produk,
            ];
            
            // Populate bop_komponen for journal creation (combine bahan_pendukung and lainnya)
            $breakdown['bop_komponen'] = [];
            foreach ($breakdown['bop'] as $bopItem) {
                $breakdown['bop_komponen'][] = [
                    'nama_bop' => $bopItem['nama_proses'],
                    'nama_komponen' => $bopItem['nama_komponen'],
                    'keterangan' => $bopItem['is_bahan_pendukung'] ? 'Bahan Pendukung' : 'Overhead',
                    'subtotal' => $bopItem['harga_per_unit'],
                    'coa_kode' => $bopItem['coa_kode'],
                    'coa_nama' => $bopItem['coa_nama'],
                    'is_bahan_pendukung' => $bopItem['is_bahan_pendukung'],
                ];
            }

            return response()->json([
                'success' => true,
                'breakdown' => $breakdown
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getBomDetails: ' . $e->getMessage(), [
                'produk_id' => $produkId,
                'trace' => $e->getTraceAsString()
            ]);
            
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
        if ($satuanResep === $satuanBahan) return "Tidak perlu konversi (satuan sama)";
        $konversi = $bahan->convertToSubUnit(1, $satuanBahan, $satuanResep);
        if ($konversi != 1) return "1 {$satuanBahan} = {$konversi} {$satuanResep}";
        return "Konversi standar";
    }

    /**

     * Determine COA for BOP component based on keyword matching
     */
    private function determineBopCoaByKeyword($namaKomponen)
    {
        $namaLower = strtolower($namaKomponen);

        // Mapping kata kunci ke COA (urutan penting - yang lebih spesifik di atas)
        $mappings = [
            // Listrik
            ['keywords' => ['listrik', 'electricity', 'power', 'electric'], 'kode' => '550', 'nama' => 'BOP - Listrik'],

            // Gas/BBM
            ['keywords' => ['gas', 'bbm', 'bahan bakar', 'fuel', 'lpg', 'bensin', 'solar'], 'kode' => '552', 'nama' => 'BOP - Gas'],

            // Air
            ['keywords' => ['air', 'water', 'pdam'], 'kode' => '551', 'nama' => 'BOP - Air'],

            // Susu
            ['keywords' => ['susu', 'milk'], 'kode' => '531', 'nama' => 'BOP - Susu'],

            // Keju
            ['keywords' => ['keju', 'cheese'], 'kode' => '533', 'nama' => 'BOP - Keju'],

            // Kemasan
            ['keywords' => ['kemasan', 'packaging', 'packing', 'bungkus', 'plastik', 'kardus', 'box', 'cup', 'gelas'], 'kode' => '532', 'nama' => 'BOP - Kemasan'],

            // Penyusutan Mesin
            ['keywords' => ['penyusutan', 'depresiasi', 'depreciation', 'mesin', 'machine', 'equipment'], 'kode' => '533', 'nama' => 'Biaya Penyusutan Mesin'],

            // Maintenance/Pemeliharaan
            ['keywords' => ['maintenance', 'pemeliharaan', 'perawatan', 'repair', 'service'], 'kode' => '534', 'nama' => 'Biaya Maintenance'],

            // Gaji Mandor/Supervisor
            ['keywords' => ['mandor', 'supervisor', 'gaji', 'salary', 'upah'], 'kode' => '535', 'nama' => 'Gaji Mandor/Supervisor'],

            // Kebersihan
            ['keywords' => ['kebersihan', 'cleaning', 'sanitasi'], 'kode' => '536', 'nama' => 'Biaya Air & Kebersihan'],

            // Sewa
            ['keywords' => ['sewa', 'rent', 'rental', 'lease'], 'kode' => '537', 'nama' => 'Biaya Sewa'],

            // Asuransi
            ['keywords' => ['asuransi', 'insurance'], 'kode' => '538', 'nama' => 'Biaya Asuransi'],

            // Topping/Coklat/Meses/Sprinkle (fallback to kemasan)
            ['keywords' => ['topping', 'coklat', 'chocolate', 'meses', 'sprinkle'], 'kode' => '532', 'nama' => 'BOP - Kemasan'],

            // Transportasi
            ['keywords' => ['transport', 'angkut', 'kirim', 'delivery'], 'kode' => '540', 'nama' => 'Biaya Transportasi'],
        ];
        
        // Cari matching keyword
        foreach ($mappings as $mapping) {
            foreach ($mapping['keywords'] as $keyword) {
                if (strpos($namaLower, $keyword) !== false) {
                    return [
                        'kode' => $mapping['kode'],
                        'nama' => $mapping['nama']
                    ];
                }
            }
        }
        
        // Default: Hutang Usaha untuk BOP lain-lain
        return [
            'kode' => '210',
            'nama' => 'Hutang Usaha (BOP Lain-lain)'
];
    }

    /**

     * Get HPP breakdown for production (BBB, BTKL, BOP with components)
     */
    private function getHppBreakdownForProduction($produk_id, $user_id)
    {
        $breakdown = [
            'bbb' => [],
            'btkl' => [],
            'bop' => [],
            'bop_komponen' => []
        ];

        // Get BBB (Biaya Bahan Baku)
        $selectedBbb = \App\Models\HargaPokokProduksiBiayaBahanBaku::where('user_id', $user_id)
            ->whereHas('biayaBahanBaku', function($q) use ($produk_id) {
                $q->where('produk_id', $produk_id);
            })
            ->with('biayaBahanBaku.bahanBaku')
            ->get();

        foreach ($selectedBbb as $hpp) {
            $biayaBahan = $hpp->biayaBahanBaku;
            if ($biayaBahan && $biayaBahan->bahanBaku) {
                $breakdown['bbb'][] = [
                    'bahan_baku_id' => $biayaBahan->bahan_baku_id,
                    'nama' => $biayaBahan->bahanBaku->nama_bahan,
                    'jumlah' => $biayaBahan->jumlah,
                    'satuan' => $biayaBahan->satuan,
                    'harga_satuan' => $biayaBahan->harga_satuan,
                    'subtotal' => $biayaBahan->subtotal,
                ];
            }
        }

        // Get BTKL
        $selectedBtkl = \App\Models\HargaPokokProduksiBtkl::where('user_id', $user_id)
            ->with('prosesProduksi')
            ->get();

        foreach ($selectedBtkl as $hpp) {
            $proses = $hpp->prosesProduksi;
            if ($proses) {
                $tarifPerProduk = $proses->tarif_per_produk ?? 0;
                $jumlahPegawai = $proses->jumlah_pegawai ?? 1;
                $tarif = $tarifPerProduk * $jumlahPegawai;

                $breakdown['btkl'][] = [
                    'proses_produksi_id' => $proses->id,
                    'nama_proses' => $proses->nama_proses,
                    'subtotal' => $tarif,
                ];
            }
        }

        // Get BOP with NEW STRUCTURE (komponen_bahan_pendukung & komponen_lainnya)
        $selectedBop = \App\Models\HargaPokokProduksiBop::where('user_id', $user_id)
            ->with('bopProses') // Remove .prosesProduksi - no longer exists
            ->get();

        foreach ($selectedBop as $hpp) {
            $bopProses = $hpp->bopProses;
            if ($bopProses) {
                $namaProses = $bopProses->nama_bop_proses ?? 'BOP';
                $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;

                // Process Bahan Pendukung
                $komponenBahanPendukung = $bopProses->komponen_bahan_pendukung ?? [];
                if (is_string($komponenBahanPendukung)) {
                    $komponenBahanPendukung = json_decode($komponenBahanPendukung, true) ?? [];
                }
                
                if (is_array($komponenBahanPendukung)) {
                    foreach ($komponenBahanPendukung as $komponen) {
                        $namaKomponen = $komponen['nama'] ?? 'Bahan Pendukung';
                        $subtotal = $komponen['total'] ?? 0;
                        $coaDebit = $komponen['coa_debit'] ?? '1173';
                        $coaKredit = $komponen['coa_kredit'] ?? '530';
                        
                        // Add to BOP array
                        $breakdown['bop'][] = [
                            'nama_proses' => $namaProses,
                            'nama_komponen' => $namaKomponen,
                            'subtotal' => $subtotal,
                            'is_bahan_pendukung' => true,
                            'bahan_pendukung_id' => $komponen['bahan_pendukung_id'] ?? null,
                            'qty_per_produk' => $komponen['qty_per_produk'] ?? 1,
                            'harga_per_unit' => $subtotal,
                            'coa_debit' => $coaDebit,
                            'coa_kredit' => $coaKredit,
                        ];
                        
                        // ✅ ADD TO bop_komponen for journal creation
                        $breakdown['bop_komponen'][] = [
                            'nama_bop' => $namaProses,
                            'nama_komponen' => $namaKomponen,
                            'keterangan' => 'Bahan Pendukung',
                            'subtotal' => $subtotal,
                            'coa_kode' => $coaKredit,
                            'coa_nama' => $namaKomponen,
                            'is_bahan_pendukung' => true,
                        ];
                    }
                }
                
                // Process Komponen Lainnya
                $komponenLainnya = $bopProses->komponen_lainnya ?? [];
                if (is_string($komponenLainnya)) {
                    $komponenLainnya = json_decode($komponenLainnya, true) ?? [];
                }
                
                if (is_array($komponenLainnya)) {
                    foreach ($komponenLainnya as $komponen) {
                        $namaKomponen = $komponen['nama_komponen'] ?? 'Overhead';
                        $subtotal = $komponen['nilai_per_produk'] ?? 0;
                        $coaDebit = $komponen['coa_debit'] ?? '1173';
                        $coaKredit = $komponen['coa_kredit'] ?? '550';
                        
                        // Add to BOP array
                        $breakdown['bop'][] = [
                            'nama_proses' => $namaProses,
                            'nama_komponen' => $namaKomponen,
                            'subtotal' => $subtotal,
                            'is_bahan_pendukung' => false,
                            'coa_debit' => $coaDebit,
                            'coa_kredit' => $coaKredit,
                        ];
                        
                        // ✅ ADD TO bop_komponen for journal creation
                        $breakdown['bop_komponen'][] = [
                            'nama_bop' => $namaProses,
                            'nama_komponen' => $namaKomponen,
                            'keterangan' => 'Overhead',
                            'subtotal' => $subtotal,
                            'coa_kode' => $coaKredit,
                            'coa_nama' => $namaKomponen,
                            'is_bahan_pendukung' => false,
                        ];
                    }
                }
            }
        }

        return $breakdown;
    }

    /**
     * Save production details (BBB, BTKL, BOP)
     */
    private function saveProductionDetails($produksi, $hppData, $qtyProd)
    {
        // Save BBB details
        foreach ($hppData['bbb'] as $bbb) {
            $qtyResep = $bbb['jumlah'] * $qtyProd;
            $hargaSatuan = $bbb['harga_satuan'];
            
            // Hitung ulang subtotal untuk menghindari error pembulatan
            $subtotal = $qtyResep * $hargaSatuan;
            
            \App\Models\ProduksiDetail::create([
                'produksi_id' => $produksi->id,
                'bahan_baku_id' => $bbb['bahan_baku_id'],
                'qty_resep' => $qtyResep,
                'satuan_resep' => $bbb['satuan'],
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
                'user_id' => $produksi->user_id,
            ]);
        }
        
        // Save Bahan Pendukung from BOP (NEW - will be used for stock reduction)
        foreach ($hppData['bop'] as $bop) {
            // Only save if it's bahan pendukung (has bahan_pendukung_id)
            if (!empty($bop['is_bahan_pendukung']) && !empty($bop['bahan_pendukung_id'])) {
                $qtyResep = ($bop['qty_per_produk'] ?? 1) * $qtyProd;
                $hargaSatuan = $bop['harga_per_unit'] / ($bop['qty_per_produk'] ?? 1); // per unit bahan
                $subtotal = $bop['subtotal'] * $qtyProd;
                
                \App\Models\ProduksiDetail::create([
                    'produksi_id' => $produksi->id,
                    'bahan_pendukung_id' => $bop['bahan_pendukung_id'],
                    'qty_resep' => $qtyResep,
                    'satuan_resep' => 'Unit', // Default, akan diambil dari bahan pendukung saat kurangi stok
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'user_id' => $produksi->user_id,
                ]);
            }
        }

        // Save BTKL as produksi_proses records
        $urutan = 1;
        $prosesMapByName = []; // Map nama_proses to ProduksiProses record
        $prosesMapById = []; // Map proses_produksi_id to ProduksiProses record
        
        foreach ($hppData['btkl'] as $btkl) {
            $biayaBtkl = $btkl['subtotal'] * $qtyProd;
            
            $proses = \App\Models\ProduksiProses::create([
                'produksi_id' => $produksi->id,
                'proses_produksi_id' => $btkl['proses_produksi_id'],
                'nama_proses' => $btkl['nama_proses'],
                'urutan' => $urutan++,
                'biaya_btkl' => $biayaBtkl,
                'biaya_bop' => 0,
                'total_biaya_proses' => $biayaBtkl,
                'status' => 'pending',
                'user_id' => $produksi->user_id,
            ]);
            
            // Store in both maps for BOP update
            $prosesMapByName[$btkl['nama_proses']] = $proses;
            $prosesMapById[$btkl['proses_produksi_id']] = $proses;
        }

        // Group BOP by proses_id (proses_produksi_id) and sum the subtotals
        $bopByProsesId = [];
        foreach ($hppData['bop'] as $bop) {
            // Use proses_id if available, otherwise fallback to nama_proses
            $prosesId = $bop['proses_id'] ?? null;
            $namaProses = $bop['nama_proses'] ?? null;
            
            if ($prosesId) {
                if (!isset($bopByProsesId[$prosesId])) {
                    $bopByProsesId[$prosesId] = 0;
                }
                $bopByProsesId[$prosesId] += $bop['subtotal'];
            } elseif ($namaProses) {
                // Fallback: group by nama_proses
                if (!isset($bopByProsesId['name_' . $namaProses])) {
                    $bopByProsesId['name_' . $namaProses] = 0;
                }
                $bopByProsesId['name_' . $namaProses] += $bop['subtotal'];
            }
        }

        // Update BOP costs in produksi_proses
        foreach ($bopByProsesId as $key => $totalBopPerUnit) {
            $proses = null;
            
            // Check if key is proses_id (numeric) or nama_proses (prefixed with 'name_')
            if (is_numeric($key) && isset($prosesMapById[$key])) {
                $proses = $prosesMapById[$key];
            } elseif (strpos($key, 'name_') === 0) {
                $namaProses = substr($key, 5); // Remove 'name_' prefix
                if (isset($prosesMapByName[$namaProses])) {
                    $proses = $prosesMapByName[$namaProses];
                }
            }
            
            if ($proses) {
                $biayaBop = $totalBopPerUnit * $qtyProd;
                $proses->biaya_bop = $biayaBop;
                $proses->total_biaya_proses = $proses->biaya_btkl + $biayaBop;
                $proses->save();
            }
        }
    }

    /**
     * Create production journals (BBB, BTKL, BOP, Transfer to Finished Goods)
     */
    private function createProductionJournals($produksi, $hppData, $qtyProd, $tanggal, $journal)
    {
        $user_id = $produksi->user_id;
        
        // VALIDATE: Only check required structural COAs (1171, 1172, 1173, 211)
        // BOP component COAs are optional - use fallback if not found
        $requiredValidation = \App\Helpers\ProductionCoaValidator::validateRequiredCoas($user_id);
        if (!$requiredValidation['valid']) {
            throw new \Exception($requiredValidation['message']);
        }
        
        $totalBBB = $produksi->total_bahan;
        $totalBTKL = $produksi->total_btkl;
        $totalBOP = $produksi->total_bop;
        $totalHPP = $produksi->total_biaya;

        // JURNAL 1: BBB → Pers. Barang Dalam Proses - BBB
        if ($totalBBB > 0) {
            // DEBIT: Pers. Barang Dalam Proses - BBB (1171)
            \App\Models\JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1171'),
                'tanggal' => $tanggal,
                'keterangan' => 'Konsumsi BBB untuk Produksi ' . $produksi->produk->nama_produk,
                'debit' => $totalBBB,
                'kredit' => 0,
                'referensi' => (string) $produksi->id,
                'tipe_referensi' => 'produksi_bbb',
                'created_by' => $user_id,
            ]);

            // KREDIT: Setiap bahan baku
            foreach ($hppData['bbb'] as $bbb) {
                $totalBahan = $bbb['subtotal'] * $qtyProd;
                if ($totalBahan > 0) {
                    // Get COA from bahan baku or use default
                    $coaId = $this->getCoaIdByKode('1141'); // Default: Persediaan Bahan Baku

                    \App\Models\JurnalUmum::create([
                        'user_id' => $user_id,
                        'coa_id' => $coaId,
                        'tanggal' => $tanggal,
                        'keterangan' => 'Konsumsi ' . $bbb['nama'] . ' untuk Produksi',
                        'debit' => 0,
                        'kredit' => $totalBahan,
                        'referensi' => (string) $produksi->id,
                        'tipe_referensi' => 'produksi_bbb',
                        'created_by' => $user_id,
                    ]);
                }
            }
        }

        // JURNAL 2: BTKL → Pers. Barang Dalam Proses - BTKL
        if ($totalBTKL > 0) {
            // DEBIT: Pers. Barang Dalam Proses - BTKL (1172)
            \App\Models\JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1172'),
                'tanggal' => $tanggal,
                'keterangan' => 'Alokasi BTKL untuk Produksi ' . $produksi->produk->nama_produk,
                'debit' => $totalBTKL,
                'kredit' => 0,
                'referensi' => (string) $produksi->id,
                'tipe_referensi' => 'produksi_btkl',
                'created_by' => $user_id,
            ]);

            // KREDIT: Hutang Gaji (211)
            \App\Models\JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('211'),
                'tanggal' => $tanggal,
                'keterangan' => 'Hutang Gaji untuk Produksi',
                'debit' => 0,
                'kredit' => $totalBTKL,
                'referensi' => (string) $produksi->id,
                'tipe_referensi' => 'produksi_btkl',
                'created_by' => $user_id,
            ]);
        }

        // JURNAL 3: BOP → Pers. Barang Dalam Proses - BOP
        if ($totalBOP > 0) {
            // DEBIT: Barang Dalam Proses BOP (1173)
            \App\Models\JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1173'),
                'tanggal' => $tanggal,
                'keterangan' => 'Alokasi BOP untuk Produksi ' . $produksi->produk->nama_produk,
                'debit' => $totalBOP,
                'kredit' => 0,
                'referensi' => (string) $produksi->id,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
            ]);

            // KREDIT: Per komponen BOP - MUST RECORD ALL COMPONENTS
            $totalKreditBOP = 0;
            $skippedComponents = [];
            
            foreach ($hppData['bop_komponen'] as $komponen) {
                $totalKomponen = $komponen['subtotal'] * $qtyProd;
                if ($totalKomponen > 0) {
                    // Coba COA spesifik komponen, fallback ke COA BOP umum (530)
                    $coaKode = $komponen['coa_kode'] ?? null;
                    $coaId = null;
                    
                    if ($coaKode) {
                        $coaId = $this->getCoaIdByKodeForUser($coaKode, $user_id);
                    }
                    
                    // Fallback: cari COA BOP umum milik user
                    if (!$coaId) {
                        $coaId = $this->getCoaIdByKodeForUser('530', $user_id)
                               ?? $this->getCoaIdByKodeForUser('531', $user_id)
                               ?? $this->getCoaIdByKodeForUser('532', $user_id)
                               ?? $this->getCoaIdByKode('530'); // last resort
                    }
                    
                    if (!$coaId) {
                        $skippedComponents[] = [
                            'nama' => $komponen['nama_komponen'],
                            'coa_kode' => $coaKode,
                            'subtotal' => $totalKomponen
                        ];
                        
                        \Log::error("CRITICAL: BOP COA not found - journal will be INCOMPLETE!", [
                            'user_id' => $user_id,
                            'produksi_id' => $produksi->id,
                            'komponen' => $komponen['nama_komponen'],
                            'coa_kode' => $coaKode,
                            'subtotal' => $totalKomponen,
                        ]);
                        continue;
                    }

                    \App\Models\JurnalUmum::create([
                        'user_id' => $user_id,
                        'coa_id' => $coaId,
                        'tanggal' => $tanggal,
                        'keterangan' => 'BOP - ' . $komponen['nama_komponen'],
                        'debit' => 0,
                        'kredit' => $totalKomponen,
                        'referensi' => (string) $produksi->id,
                        'tipe_referensi' => 'produksi_bop',
                        'created_by' => $user_id,
                    ]);
                    
                    $totalKreditBOP += $totalKomponen;
                }
            }
            
            // CRITICAL VALIDATION: Total kredit BOP MUST equal total BOP
            if (abs($totalKreditBOP - $totalBOP) > 1) { // Allow 1 rupiah for rounding
                $errorMsg = "CRITICAL ERROR: BOP journal tidak balance!\n";
                $errorMsg .= "Total BOP: Rp " . number_format($totalBOP, 2) . "\n";
                $errorMsg .= "Total Journal Kredit: Rp " . number_format($totalKreditBOP, 2) . "\n";
                $errorMsg .= "Selisih: Rp " . number_format($totalBOP - $totalKreditBOP, 2) . "\n";
                
                if (count($skippedComponents) > 0) {
                    $errorMsg .= "\nKomponen BOP yang tidak tercatat (COA tidak ditemukan):\n";
                    foreach ($skippedComponents as $skipped) {
                        $errorMsg .= "  - {$skipped['nama']} (COA: {$skipped['coa_kode']}): Rp " . number_format($skipped['subtotal'], 0) . "\n";
                    }
                    $errorMsg .= "\nSilakan pastikan COA untuk komponen BOP sudah dibuat di Master COA.";
                }
                
                \Log::error("BOP Journal Validation Failed", [
                    'produksi_id' => $produksi->id,
                    'total_bop' => $totalBOP,
                    'total_kredit' => $totalKreditBOP,
                    'difference' => $totalBOP - $totalKreditBOP,
                    'skipped_components' => $skippedComponents,
                ]);
                
                throw new \Exception($errorMsg);
            }
            
            \Log::info("BOP journals created successfully", [
                'produksi_id' => $produksi->id,
                'total_bop' => $totalBOP,
                'total_kredit' => $totalKreditBOP,
                'components_count' => count($hppData['bop_komponen']),
                'skipped_count' => count($skippedComponents),
            ]);
        }

        // JURNAL 4: Transfer ke Barang Jadi
        if ($totalHPP > 0) {
            $coaBarangJadi = $produksi->coa_persediaan_barang_jadi_id ?? $this->getCoaIdByKode('1161');

            // DEBIT: Pers. Barang Jadi
            \App\Models\JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $coaBarangJadi,
                'tanggal' => $tanggal,
                'keterangan' => 'Transfer WIP ke Barang Jadi - ' . $produksi->produk->nama_produk,
                'debit' => $totalHPP,
                'kredit' => 0,
                'referensi' => (string) $produksi->id,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
            ]);

            // KREDIT: WIP accounts
            if ($totalBBB > 0) {
                \App\Models\JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1171'),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BBB ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBBB,
                    'referensi' => (string) $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }

            if ($totalBTKL > 0) {
                \App\Models\JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1172'),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BTKL ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBTKL,
                    'referensi' => (string) $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }

            if ($totalBOP > 0) {
                // KREDIT: Pers. Barang Dalam Proses - BOP
                \App\Models\JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1173'),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BOP ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBOP,
                    'referensi' => (string) $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }
        }
    }

    /**
     * Create BOP journal entries using specific COA from BOP proses setup
     * Multi-tenant support: Uses COA from bop_proses table for each user
     */
    private function createBOPJournalEntries($produksi, $tanggal, $user_id)
    {
        // Get all BOP proses for this user (multi-tenant support)
        $bopProsesList = \App\Models\BopProses::where('user_id', $user_id)
            ->where('is_active', true)
            ->get();

        foreach ($bopProsesList as $bopProses) {
            // Get komponen_bop data from database
            $komponenBop = $bopProses->komponen_bop;
            if (is_string($komponenBop)) {
                $komponenBop = json_decode($komponenBop, true) ?? [];
            } elseif (!is_array($komponenBop)) {
                $komponenBop = [];
            }

            // Calculate total BOP for this proses
            $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;
            $totalBopAmount = $totalBopPerProduk * $produksi->qty_produksi;

            if ($totalBopAmount > 0) {
                // Create journal entry for each component with its specific COA from database
                foreach ($komponenBop as $komp) {
                    $componentName = $komp['component'] ?? 'BOP';
                    $ratePerHour = $komp['rate_per_hour'] ?? 0;
                    // Use COA from bop_proses table (multi-tenant aware)
                    $coaDebit = $komp['coa_debit'] ?? '1173';
                    $coaKredit = $komp['coa_kredit'] ?? '510';
                    $description = $komp['description'] ?? '';

                    // Calculate proportional amount for this component
                    $totalRate = array_sum(array_column($komponenBop, 'rate_per_hour'));
                    $componentAmount = $totalRate > 0 ? ($ratePerHour / $totalRate) * $totalBopAmount : 0;

                    if ($componentAmount > 0) {
                        // Create debit entry (WIP BOP account) with user-specific COA
                        $coaDebitId = $this->getCoaIdByKode($coaDebit, $user_id);
                        \App\Models\JurnalUmum::create([
                            'user_id' => $user_id,
                            'coa_id' => $coaDebitId,
                            'tanggal' => $tanggal,
                            'keterangan' => "Alokasi BOP - {$bopProses->nama_bop_proses} ({$componentName}) ke Barang WIP BOP",
                            'debit' => $componentAmount,
                            'kredit' => 0,
                            'referensi' => $produksi->id,
                            'tipe_referensi' => 'produksi_bop',
                            'created_by' => $user_id,
                        ]);

                        // Create credit entry (expense account) with user-specific COA
                        $coaKreditId = $this->getCoaIdByKode($coaKredit, $user_id);
                        \App\Models\JurnalUmum::create([
                            'user_id' => $user_id,
                            'coa_id' => $coaKreditId,
                            'tanggal' => $tanggal,
                            'keterangan' => "Alokasi BOP - {$bopProses->nama_bop_proses} ({$componentName}) ke Barang WIP BOP",
                            'debit' => 0,
                            'kredit' => $componentAmount,
                            'referensi' => $produksi->id,
                            'tipe_referensi' => 'produksi_bop',
                            'created_by' => $user_id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get COA ID by kode_akun - STRICT MODE (no fallback)
     * Throws exception if COA not found to prevent incorrect journal entries
     * Multi-tenant support: Uses specific user_id parameter
     */
    private function getCoaIdByKode($kodeAkun, $user_id = null)
    {
        // Use provided user_id or current authenticated user
        $user_id = $user_id ?? auth()->id();
        
        // Try to find the COA with user_id filter
        $coa = \App\Models\Coa::where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->orderByRaw('RPAD(kode_akun, 10, "0"), LENGTH(kode_akun)')
            ->first();
        
        if ($coa) {
            return $coa->id;
        }
        
        // NO FALLBACK! Throw error immediately to prevent incorrect journal entries
        // This ensures that missing COAs are caught early rather than creating wrong journals
        throw new \Exception(
            "COA dengan kode '{$kodeAkun}' tidak ditemukan untuk user ID {$user_id}. " .
            "Silakan buat COA ini terlebih dahulu di Master Data > Chart of Accounts sebelum melakukan produksi. " .
            "COA yang diperlukan untuk produksi: 1171 (WIP BBB), 1172 (WIP BTKL), 1173 (WIP BOP), " .
            "211 (Hutang Gaji), dan COA untuk setiap komponen BOP."
        );
    }

    /**
     * Get COA ID by kode for specific user - returns null if not found (no exception)
     */
    private function getCoaIdByKodeForUser($kodeAkun, $user_id): ?int
    {
        $coa = \App\Models\Coa::where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->orderByRaw('RPAD(kode_akun, 10, "0"), LENGTH(kode_akun)')
            ->first();

        return $coa ? $coa->id : null;
    }

    /**
     * Get detailed cost breakdown for production from saved details
*/
    private function getProductionCostBreakdown($produksi)
    {
        $breakdown = [
            'biaya_bahan' => [
                'bahan_baku' => [],
                'bahan_pendukung' => []
            ],
            'btkl' => [],
            'bop' => []
        ];

        // Get BBB from produksi_details
        $details = $produksi->details()->with('bahanBaku.satuan')->get();
        foreach ($details as $detail) {
            if ($detail->bahanBaku) {
                $breakdown['biaya_bahan']['bahan_baku'][] = [
                    'nama' => $detail->bahanBaku->nama_bahan,
                    'qty_resep' => $detail->qty_resep,
                    'satuan_resep' => $detail->satuan_resep,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                ];
            }
        }

        // Get BTKL details from HPP
        $user_id = $produksi->user_id;
        $hppBtkl = \App\Models\HargaPokokProduksiBtkl::where('user_id', $user_id)
            ->with('prosesProduksi')
            ->get();

        foreach ($hppBtkl as $btkl) {
            if ($btkl->prosesProduksi) {
                // Get tarif from proses_produksis table
                $tarifPerProduk = $btkl->prosesProduksi->tarif_per_produk ?? 0;
                $jumlahPegawai = $btkl->prosesProduksi->jumlah_pegawai ?? 1;
                $tarifTotal = $tarifPerProduk * $jumlahPegawai;
                $totalBiaya = $tarifTotal * $produksi->qty_produksi;

                $breakdown['btkl'][] = [
                    'nama' => $btkl->prosesProduksi->nama_proses,
                    'biaya_per_unit' => $tarifTotal,
                    'total_biaya' => $totalBiaya
                ];
            }
        }

        // ========================================
        // Get BOP details from HPP with NEW STRUCTURE
        // BOP now has komponen_bahan_pendukung and komponen_lainnya
        // ========================================
        $hppBop = \App\Models\HargaPokokProduksiBop::where('user_id', $user_id)
            ->with('bopProses')
            ->get();
        
        foreach ($hppBop as $bop) {
            if ($bop->bopProses) {
                $namaProses = $bop->bopProses->nama_bop_proses ?? 'BOP';
                
                // 1. Process Bahan Pendukung components
                $komponenBahanPendukung = $bop->bopProses->komponen_bahan_pendukung;
                if (is_string($komponenBahanPendukung)) {
                    $komponenBahanPendukung = json_decode($komponenBahanPendukung, true) ?? [];
                } elseif (!is_array($komponenBahanPendukung)) {
                    $komponenBahanPendukung = [];
                }
                
                foreach ($komponenBahanPendukung as $komponen) {
                    $namaKomponen = $komponen['nama'] ?? 'Bahan Pendukung';
                    $nilaiPerProduk = $komponen['total'] ?? 0;
                    $totalBiaya = $nilaiPerProduk * $produksi->qty_produksi;
                    
                    $breakdown['bop'][] = [
                        'nama_proses' => $namaProses,
                        'nama_komponen' => $namaKomponen,
                        'biaya_per_unit' => $nilaiPerProduk,
                        'total_biaya' => $totalBiaya
                    ];
                }
                
                // 2. Process Komponen Lainnya (overhead)
                $komponenLainnya = $bop->bopProses->komponen_lainnya;
                if (is_string($komponenLainnya)) {
                    $komponenLainnya = json_decode($komponenLainnya, true) ?? [];
                } elseif (!is_array($komponenLainnya)) {
                    $komponenLainnya = [];
                }
                
                foreach ($komponenLainnya as $komponen) {
                    $namaKomponen = $komponen['nama_komponen'] ?? 'Overhead';
                    $nilaiPerProduk = $komponen['nilai_per_produk'] ?? 0;
                    $totalBiaya = $nilaiPerProduk * $produksi->qty_produksi;
                    
                    $breakdown['bop'][] = [
                        'nama_proses' => $namaProses,
                        'nama_komponen' => $namaKomponen,
                        'biaya_per_unit' => $nilaiPerProduk,
                        'total_biaya' => $totalBiaya
                    ];
                }
            }
        }

        return $breakdown;
    }

    /**
     * Create material consumption journals only (not labor/overhead/finished goods)
     */
    private function createMaterialJournals($produksi, $journal, $produksiDetails)
    {
        $tanggal = $produksi->tanggal;
        $userId  = $produksi->user_id ?? auth()->id();

        // COA BDP-BBB spesifik (1171), fallback ke 117
        $coaBdpBbb = \App\Models\Coa::withoutGlobalScopes()
            ->where('user_id', $userId)->where('kode_akun', '1171')->first()
            ?? \App\Models\Coa::withoutGlobalScopes()
                ->where('user_id', $userId)->where('kode_akun', '117')->first();

        if (!$coaBdpBbb) return;

        $materialEntries = [];
        $totalMaterialCost = 0;

        foreach ($produksiDetails as $detail) {
            // Bahan Baku
            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                $bahan = $detail->bahanBaku;
                $coaPersediaan = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->where('kode_akun', $bahan->coa_persediaan_id ?? '114')->first();
                if ($coaPersediaan && $detail->subtotal > 0) {
                    $materialEntries[] = ['code' => $coaPersediaan->kode_akun, 'debit' => 0, 'credit' => $detail->subtotal, 'memo' => "Konsumsi {$bahan->nama_bahan}"];
                    $totalMaterialCost += $detail->subtotal;
                }
            }
            // Bahan Pendukung
            elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                $bahan = $detail->bahanPendukung;
                $coaPersediaan = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->where('kode_akun', $bahan->coa_persediaan_id ?? '115')->first();
                if ($coaPersediaan && $detail->subtotal > 0) {
                    $materialEntries[] = ['code' => $coaPersediaan->kode_akun, 'debit' => 0, 'credit' => $detail->subtotal, 'memo' => "Konsumsi {$bahan->nama_bahan}"];
                    $totalMaterialCost += $detail->subtotal;
                }
            }
        }

        if ($totalMaterialCost > 0) {
            array_unshift($materialEntries, [
                'code'  => $coaBdpBbb->kode_akun,
                'debit' => $totalMaterialCost,
                'credit' => 0,
                'memo'  => 'Transfer material ke BDP-BBB'
            ]);
            $journal->post($tanggal, 'production_material', (int)$produksi->id, 'Konsumsi Material untuk Produksi', $materialEntries);
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
                'user_id' => auth()->id(), // 🔒 SECURITY: Add user_id for multi-tenant
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
        $bomJobBTKLs = \App\Models\BomJobBTKL::where('user_id', auth()->id())->where('produk_id', $bomJobCosting->produk_id)->get();
        
        if ($bomJobBTKLs->count() == 0) {
            // If no BTKL processes, create a default process
            \App\Models\ProduksiProses::updateOrCreate([
                'produksi_id' => $produksi->id,
                'nama_proses' => 'Produksi ' . $produksi->produk->nama_produk,
                'user_id' => auth()->id(), // 🔒 SECURITY: Add user_id for multi-tenant
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
            $produksiProses = \App\Models\ProduksiProses::updateOrCreate([
                'produksi_id' => $produksi->id,
                'nama_proses' => $bomJobBTKL->nama_proses ?? 'Proses ' . $prosesOrder,
                'user_id' => auth()->id(), // 🔒 SECURITY: Add user_id for multi-tenant
            ], [
                'urutan' => $prosesOrder,
                'status' => 'pending', // Use 'pending' instead of 'belum_dimulai'
                'biaya_btkl' => $bomJobBTKL->subtotal ?? 0,
                'biaya_bop' => 0, // BOP will be calculated separately
                'total_biaya_proses' => $bomJobBTKL->subtotal ?? 0,
            ]);
            
            $prosesOrder++;
        }


        // Calculate BOP for each process
        $bomJobBOPs = \App\Models\BomJobBOP::where('user_id', auth()->id())->where('produk_id', $bomJobCosting->produk_id)->get();
        
        // Group BOP by process name and multiply by production quantity
$bopByProcess = [];
        $bopDetails = \App\Models\ProduksiBopDetail::where('produksi_id', $produksi->id)->get();
        foreach ($bopDetails as $bopDetail) {
            $namaProses = $bopDetail->nama_proses;
            if (!isset($bopByProcess[$namaProses])) {
                $bopByProcess[$namaProses] = 0;
            }
            $bopByProcess[$namaProses] += (float)$bopDetail->total;
        }

        // Fallback: jika ProduksiBopDetail belum ada, hitung dari BomJobBOP
        if (empty($bopByProcess)) {
            $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
            foreach ($bomJobBOPs as $bomJobBOP) {
                $namaBop  = $bomJobBOP->nama_bop ?? '';
                $dashPos  = strpos($namaBop, ' - ');
                $namaProses = $dashPos !== false ? trim(substr($namaBop, 0, $dashPos)) : 'Umum';
                if (!isset($bopByProcess[$namaProses])) {
                    $bopByProcess[$namaProses] = 0;
                }
                $bopByProcess[$namaProses] += ($bomJobBOP->subtotal ?? 0) * $produksi->qty_produksi;
            }
        }
        
        // Update BOP for each process
        foreach ($produksi->proses as $proses) {
            $bopAmount = 0;
            
            // Normalize process names by removing extra spaces for comparison
            $normalizedProsesName = preg_replace('/\s+/', ' ', trim($proses->nama_proses));
            
            // Exact match dengan nama yang dinormalisasi
            if (isset($bopByProcess[$normalizedProsesName])) {
                $bopAmount = $bopByProcess[$normalizedProsesName];
            } else {
                // Coba match dengan nama asli (fallback)
                if (isset($bopByProcess[$proses->nama_proses])) {
                    $bopAmount = $bopByProcess[$proses->nama_proses];
                } else {
                    // Partial match sebagai fallback dengan normalisasi
                    foreach ($bopByProcess as $prosesName => $bopValue) {
                        $normalizedBopName = preg_replace('/\s+/', ' ', trim($prosesName));
                        if ($prosesName !== 'Umum' && $normalizedBopName !== 'Umum' &&
                            (stripos($normalizedProsesName, $normalizedBopName) !== false ||
                             stripos($normalizedBopName, $normalizedProsesName) !== false)) {
                            $bopAmount = $bopValue;
                            break;
                        }
                    }
                    // Jika masih 0, cek apakah ada bucket 'Umum'
                    if ($bopAmount == 0 && isset($bopByProcess['Umum'])) {
                        $bopAmount = $bopByProcess['Umum'];
                    }
                }
            }
            
            // Also multiply BTKL by production quantity
            $btklAmount = $proses->biaya_btkl * $produksi->qty_produksi;
            
            $proses->update([
                'biaya_btkl' => $btklAmount,
                'biaya_bop' => $bopAmount,
                'total_biaya_proses' => $btklAmount + $bopAmount,
            ]);
        }

        // Update total proses count
        $produksi->update([
            'total_proses' => $prosesOrder - 1,
            'proses_selesai' => 0,
        ]);
    }

}