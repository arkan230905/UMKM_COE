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
            'coa_persediaan_barang_jadi_id' => 'nullable|exists:coas,id',
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
                'coa_persediaan_barang_jadi_id' => $request->coa_persediaan_barang_jadi_id,
                'tanggal' => $tanggal,
                'jumlah_produksi_bulanan' => $request->jumlah_produksi_bulanan,
                'hari_produksi_bulanan' => $request->hari_produksi_bulanan,
                'qty_produksi' => $qtyProd,
                'status' => 'draft', // Use draft status for production plan
            ]);

            // Calculate total costs from BOM data (without consuming materials)
            $bom = \App\Models\Bom::where('produk_id', $produk->id)->first();
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
            
            // Total BTKL dan BOP dari BOM Job Costing (per unit × qty)
            $totalBTKLPerUnit = 0;
            $totalBOPPerUnit = 0;
            
            if ($bomJobCosting) {
                $totalBTKLPerUnit = $bomJobCosting->total_btkl ?? 0;
                $totalBOPPerUnit  = $bomJobCosting->total_bop  ?? 0;
            }
            
            $totalBTKL = $totalBTKLPerUnit * $qtyProd;
            $totalBOP  = $totalBOPPerUnit  * $qtyProd;

            // total_bahan akan dihitung setelah detail disimpan (lihat di bawah)

            // Simpan detail BBB ke produksi_details
            if ($bomJobCosting) {
                $userId = auth()->id();

                // Detail Bahan Baku
                $bomJobBBBs = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBBBs as $bomJobBBB) {
                    $bahan = $bomJobBBB->bahanBaku;
                    if (!$bahan) continue;
                    $hargaPerUnit = (float)$bomJobBBB->subtotal;
                    $total = $hargaPerUnit * $qtyProd;
                    \App\Models\ProduksiDetail::create([
                        'produksi_id'   => $produksi->id,
                        'bahan_baku_id' => $bahan->id,
                        'qty_resep'     => $bomJobBBB->jumlah * $qtyProd,
                        'satuan_resep'  => $bomJobBBB->satuan ?: ($bahan->satuan->nama ?? 'unit'),
                        'qty_konversi'  => $bomJobBBB->jumlah * $qtyProd,
                        'harga_satuan'  => $hargaPerUnit,
                        'subtotal'      => $total,
                        'satuan'        => $bahan->satuan->nama ?? 'unit',
                        'user_id'       => $userId,
                    ]);
                }

                // Detail BTKL
                $allCoas = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)->orderBy('kode_akun')
                    ->get(['id','kode_akun','nama_akun'])->keyBy('kode_akun');

                $coaHutangGaji = $allCoas['211'] ?? null;
                $coaBdpBtkl    = $allCoas['1172'] ?? $allCoas['117'] ?? null;

                $bomJobBTKLs = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBTKLs as $bomJobBTKL) {
                    $hargaPerUnit = (float)$bomJobBTKL->subtotal;
                    $total = $hargaPerUnit * $qtyProd;
                    // Cari COA BTKL per proses
                    $coaBtkl = \App\Models\Coa::withoutGlobalScopes()
                        ->where('user_id', $userId)
                        ->where('kode_akun', 'LIKE', '52%')
                        ->where('nama_akun', 'LIKE', '%' . $bomJobBTKL->nama_proses . '%')
                        ->first() ?? $allCoas['52'] ?? null;

                    \App\Models\ProduksiBtklDetail::create([
                        'produksi_id'     => $produksi->id,
                        'nama_proses'     => $bomJobBTKL->nama_proses,
                        'harga_per_unit'  => $hargaPerUnit,
                        'total'           => $total,
                        'coa_debit_kode'  => $coaBdpBtkl->kode_akun ?? '1172',
                        'coa_debit_nama'  => $coaBdpBtkl->nama_akun ?? 'BDP - BTKL',
                        'coa_kredit_kode' => $coaHutangGaji->kode_akun ?? '211',
                        'coa_kredit_nama' => $coaHutangGaji->nama_akun ?? 'Hutang Gaji',
                    ]);
                }

                // Detail BOP — gunakan logika mapping yang sama seperti getBomDetails
                $coaBdpBop = $allCoas['1173'] ?? $allCoas['117'] ?? null;
                $bopCoaMap = $this->getBopCoaKeywordMap($allCoas, $userId);

                $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
                foreach ($bomJobBOPs as $bomJobBOP) {
                    $namaBop  = $bomJobBOP->nama_bop ?? '';
                    $subtotal = (float)($bomJobBOP->subtotal ?? 0);
                    $dashPos  = strpos($namaBop, ' - ');
                    $namaProses    = $dashPos !== false ? trim(substr($namaBop, 0, $dashPos)) : $namaBop;
                    $namaKomponen  = $dashPos !== false ? trim(substr($namaBop, $dashPos + 3)) : $namaBop;

                    [$kreditKode, $kreditNama] = $this->resolveBopKredit($namaKomponen, $bopCoaMap, $allCoas, $userId);

                    \App\Models\ProduksiBopDetail::create([
                        'produksi_id'     => $produksi->id,
                        'nama_proses'     => $namaProses,
                        'nama_komponen'   => $namaKomponen,
                        'rate_per_unit'   => $subtotal,
                        'total'           => $subtotal * $qtyProd,
                        'coa_debit_kode'  => $coaBdpBop->kode_akun ?? '1173',
                        'coa_debit_nama'  => $coaBdpBop->nama_akun ?? 'BDP - BOP',
                        'coa_kredit_kode' => $kreditKode,
                        'coa_kredit_nama' => $kreditNama,
                    ]);
                }
            }

            // Hitung total_bahan dari detail yang sudah disimpan (bukan dari BOM)
            $totalBahan = \App\Models\ProduksiDetail::where('produksi_id', $produksi->id)->sum('subtotal');
            $totalBiaya = $totalBahan + $totalBTKL + $totalBOP;

            $produksi->update([
                'total_bahan' => $totalBahan,
                'total_btkl'  => $totalBTKL,
                'total_bop'   => $totalBOP,
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
            $tanggal = $produksi->tanggal->format('Y-m-d');

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
                            'total_cost' => $subtotal, // Use subtotal which is correctly calculated as $hargaSatuan * $qtyResepTotal
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
                            'total_cost' => $subtotal, // Use subtotal which is correctly calculated as $hargaSatuan * $qtyResepTotal
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
        $produksi = Produksi::with([
            'produk',
            'details.bahanBaku.satuan',
            'details.bahanPendukung.satuan',
            'btklDetails',
            'bopDetails',
            'coaPersediaanBarangJadi',
        ])->findOrFail($id);

        return view('transaksi.produksi.show', compact('produksi'));
    }

    public function edit($id)
    {
        $produksi = Produksi::with(['produk','btklDetails','bopDetails','coaPersediaanBarangJadi'])->findOrFail($id);

        if ($produksi->status !== 'draft') {
            return redirect()->route('transaksi.produksi.show', $id)
                ->with('error', 'Produksi yang sudah diproses tidak dapat diedit.');
        }

        $produks = \App\Models\Produk::where('user_id', auth()->id())->get();
        return view('transaksi.produksi.edit', compact('produksi', 'produks'));
    }

    public function update(Request $request, $id)
    {
        $produksi = Produksi::findOrFail($id);

        if ($produksi->status !== 'draft') {
            return redirect()->route('transaksi.produksi.show', $id)
                ->with('error', 'Produksi yang sudah diproses tidak dapat diedit.');
        }

        $request->validate([
            'produk_id'                    => 'required|exists:produks,id',
            'coa_persediaan_barang_jadi_id' => 'nullable|exists:coas,id',
            'jumlah_produksi_bulanan'       => 'required|numeric|min:1',
            'hari_produksi_bulanan'         => 'required|integer|min:1|max:31',
            'qty_produksi'                  => 'required|numeric|min:1',
        ]);

        return DB::transaction(function () use ($request, $produksi) {
            $produk   = Produk::findOrFail($request->produk_id);
            $qtyProd  = (float)$request->qty_produksi;
            $userId   = auth()->id();

            // Hapus detail lama
            \App\Models\ProduksiDetail::where('produksi_id', $produksi->id)->delete();
            \App\Models\ProduksiBtklDetail::where('produksi_id', $produksi->id)->delete();
            \App\Models\ProduksiBopDetail::where('produksi_id', $produksi->id)->delete();

            // Hitung ulang biaya
            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

            $totalBTKL  = ($bomJobCosting->total_btkl ?? 0) * $qtyProd;
            $totalBOP   = ($bomJobCosting->total_bop  ?? 0) * $qtyProd;

            $produksi->update([
                'produk_id'                    => $produk->id,
                'coa_persediaan_barang_jadi_id' => $request->coa_persediaan_barang_jadi_id,
                'jumlah_produksi_bulanan'       => $request->jumlah_produksi_bulanan,
                'hari_produksi_bulanan'         => $request->hari_produksi_bulanan,
                'qty_produksi'                  => $qtyProd,
                // total_bahan dan total_biaya akan diupdate setelah detail disimpan
            ]);

            // Simpan ulang detail (sama seperti store)
            if ($bomJobCosting) {
                $allCoas = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)->orderBy('kode_akun')
                    ->get(['id','kode_akun','nama_akun'])->keyBy('kode_akun');

                // BBB
                foreach (\App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get() as $bomJobBBB) {
                    $bahan = $bomJobBBB->bahanBaku;
                    if (!$bahan) continue;
                    \App\Models\ProduksiDetail::create([
                        'produksi_id'   => $produksi->id,
                        'bahan_baku_id' => $bahan->id,
                        'qty_resep'     => $bomJobBBB->jumlah * $qtyProd,
                        'satuan_resep'  => $bomJobBBB->satuan ?: ($bahan->satuan->nama ?? 'unit'),
                        'qty_konversi'  => $bomJobBBB->jumlah * $qtyProd,
                        'harga_satuan'  => (float)$bomJobBBB->subtotal,
                        'subtotal'      => (float)$bomJobBBB->subtotal * $qtyProd,
                        'satuan'        => $bahan->satuan->nama ?? 'unit',
                        'user_id'       => $userId,
                    ]);
                }

                // BTKL
                $coaHutangGaji = $allCoas['211'] ?? null;
                $coaBdpBtkl    = $allCoas['1172'] ?? $allCoas['117'] ?? null;
                foreach (\App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get() as $bomJobBTKL) {
                    \App\Models\ProduksiBtklDetail::create([
                        'produksi_id'     => $produksi->id,
                        'nama_proses'     => $bomJobBTKL->nama_proses,
                        'harga_per_unit'  => (float)$bomJobBTKL->subtotal,
                        'total'           => (float)$bomJobBTKL->subtotal * $qtyProd,
                        'coa_debit_kode'  => $coaBdpBtkl->kode_akun ?? '1172',
                        'coa_debit_nama'  => $coaBdpBtkl->nama_akun ?? 'BDP - BTKL',
                        'coa_kredit_kode' => $coaHutangGaji->kode_akun ?? '211',
                        'coa_kredit_nama' => $coaHutangGaji->nama_akun ?? 'Hutang Gaji',
                    ]);
                }

                // BOP
                $coaBdpBop = $allCoas['1173'] ?? $allCoas['117'] ?? null;
                $bopCoaMap = $this->getBopCoaKeywordMap($allCoas, $userId);
                foreach (\App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get() as $bomJobBOP) {
                    $namaBop      = $bomJobBOP->nama_bop ?? '';
                    $subtotal     = (float)($bomJobBOP->subtotal ?? 0);
                    $dashPos      = strpos($namaBop, ' - ');
                    $namaProses   = $dashPos !== false ? trim(substr($namaBop, 0, $dashPos)) : $namaBop;
                    $namaKomponen = $dashPos !== false ? trim(substr($namaBop, $dashPos + 3)) : $namaBop;
                    [$kreditKode, $kreditNama] = $this->resolveBopKredit($namaKomponen, $bopCoaMap, $allCoas, $userId);
                    \App\Models\ProduksiBopDetail::create([
                        'produksi_id'     => $produksi->id,
                        'nama_proses'     => $namaProses,
                        'nama_komponen'   => $namaKomponen,
                        'rate_per_unit'   => $subtotal,
                        'total'           => $subtotal * $qtyProd,
                        'coa_debit_kode'  => $coaBdpBop->kode_akun ?? '1173',
                        'coa_debit_nama'  => $coaBdpBop->nama_akun ?? 'BDP - BOP',
                        'coa_kredit_kode' => $kreditKode,
                        'coa_kredit_nama' => $kreditNama,
                    ]);
                }
            }

            // Hitung total_bahan dari detail yang sudah disimpan (bukan dari BOM)
            $totalBahan = \App\Models\ProduksiDetail::where('produksi_id', $produksi->id)->sum('subtotal');
            $produksi->update([
                'total_bahan'  => $totalBahan,
                'total_btkl'   => $totalBTKL,
                'total_bop'    => $totalBOP,
                'total_biaya'  => $totalBahan + $totalBTKL + $totalBOP,
            ]);

            return redirect()->route('transaksi.produksi.show', $produksi->id)
                ->with('success', 'Rencana produksi berhasil diperbarui.');
        });
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
        $proses = \App\Models\ProduksiProses::findOrFail($prosesId);
        $produksi = $proses->produksi;

        // Selesaikan proses ini
        $proses->selesaikanProses();

        // Update produksi
        // Hitung ulang proses selesai berdasarkan data aktual
        $totalProsesSelesai = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
            ->where('status', 'selesai')
            ->count();
        
        $produksi->proses_selesai = $totalProsesSelesai;

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
        $tanggal = $produksi->tanggal->format('Y-m-d');

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
        $userId  = $produksi->user_id ?? auth()->id();

        // COA BDP spesifik
        $coaBdpBtkl = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1172')->first()
            ?? \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','117')->first();
        $coaBdpBop  = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1173')->first()
            ?? \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','117')->first();

        if (!$coaBdpBtkl || !$coaBdpBop) return;

        // ── Jurnal BTKL: debit BDP-BTKL, kredit Hutang Gaji per proses ──
        $btklDetails = \App\Models\ProduksiBtklDetail::where('produksi_id', $produksi->id)->get();
        if ($btklDetails->count() > 0) {
            $totalBtkl = $btklDetails->sum('total');
            $btklEntries = [[
                'code'   => $coaBdpBtkl->kode_akun,
                'debit'  => $totalBtkl,
                'credit' => 0,
                'memo'   => 'Transfer BTKL ke BDP-BTKL'
            ]];
            foreach ($btklDetails as $d) {
                // Resolve COA kredit dari kode yang tersimpan
                $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)->where('kode_akun', $d->coa_kredit_kode)->first();
                if ($coaKredit && $d->total > 0) {
                    $btklEntries[] = [
                        'code'   => $coaKredit->kode_akun,
                        'debit'  => 0,
                        'credit' => $d->total,
                        'memo'   => "Hutang Gaji — {$d->nama_proses}"
                    ];
                }
            }
            $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'Alokasi BTKL ke Produksi', $btklEntries);
        }

        // ── Jurnal BOP: debit BDP-BOP per proses, kredit per komponen ──
        $bopDetails = \App\Models\ProduksiBopDetail::where('produksi_id', $produksi->id)->get();
        if ($bopDetails->count() > 0) {
            // Group per proses
            $bopByProses = $bopDetails->groupBy('nama_proses');
            foreach ($bopByProses as $namaProses => $items) {
                $totalProses = $items->sum('total');
                $bopEntries = [[
                    'code'   => $coaBdpBop->kode_akun,
                    'debit'  => $totalProses,
                    'credit' => 0,
                    'memo'   => "Transfer BOP {$namaProses} ke BDP-BOP"
                ]];
                foreach ($items as $d) {
                    $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                        ->where('user_id', $userId)->where('kode_akun', $d->coa_kredit_kode)->first();
                    if ($coaKredit && $d->total > 0) {
                        $bopEntries[] = [
                            'code'   => $coaKredit->kode_akun,
                            'debit'  => 0,
                            'credit' => $d->total,
                            'memo'   => "{$namaProses} — {$d->nama_komponen}"
                        ];
                    }
                }
                $journal->post($tanggal, 'production_bop', (int)$produksi->id, "Alokasi BOP {$namaProses}", $bopEntries);
            }
        }
    }
    
    /**
     * Transfer WIP ke Barang Jadi saat produksi selesai
     */
    private function transferWipToFinishedGoods($produksi)
    {
        $journal    = app(\App\Services\JournalService::class);
        $totalBiaya = (float)$produksi->total_biaya;
        $userId     = $produksi->user_id ?? auth()->id();

        if ($totalBiaya <= 0) return;

        // COA Persediaan Barang Jadi
        $coaBarangJadi = $produksi->coaPersediaanBarangJadi
            ?? \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','116')->first();

        if (!$coaBarangJadi) {
            throw new \RuntimeException('COA Persediaan Barang Jadi tidak ditemukan.');
        }

        // COA BDP spesifik
        $coaBdpBbb  = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1171')->first();
        $coaBdpBtkl = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1172')->first();
        $coaBdpBop  = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1173')->first();

        // Fallback ke 117 jika sub-akun belum ada
        $coaWip = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','117')->first();
        $coaBdpBbb  = $coaBdpBbb  ?? $coaWip;
        $coaBdpBtkl = $coaBdpBtkl ?? $coaWip;
        $coaBdpBop  = $coaBdpBop  ?? $coaWip;

        if (!$coaBdpBbb) throw new \RuntimeException('COA BDP tidak ditemukan.');

        $lines = [
            ['code' => $coaBarangJadi->kode_akun, 'debit' => $totalBiaya, 'credit' => 0, 'memo' => "Selesai produksi → {$coaBarangJadi->nama_akun}"],
        ];

        if ($produksi->total_bahan > 0) {
            $lines[] = ['code' => $coaBdpBbb->kode_akun,  'debit' => 0, 'credit' => (float)$produksi->total_bahan, 'memo' => 'BDP - BBB'];
        }
        if ($produksi->total_btkl > 0) {
            $lines[] = ['code' => $coaBdpBtkl->kode_akun, 'debit' => 0, 'credit' => (float)$produksi->total_btkl, 'memo' => 'BDP - BTKL'];
        }
        if ($produksi->total_bop > 0) {
            $lines[] = ['code' => $coaBdpBop->kode_akun,  'debit' => 0, 'credit' => (float)$produksi->total_bop,  'memo' => 'BDP - BOP'];
        }

        $journal->post(
            $produksi->tanggal,
            'production_finish',
            (int)$produksi->id,
            "Transfer BDP ke Barang Jadi ({$coaBarangJadi->kode_akun} - {$coaBarangJadi->nama_akun})",
            $lines
        );
    }

    /**
     * Start production again for completed products using last production data
     */
    public function mulaiLagi(Request $request, StockService $stock, JournalService $journal, KonversiProduksiService $konversiService)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
        ]);

        // Check if product has completed production before
        $lastProduction = Produksi::where('produk_id', $request->produk_id)
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->first();
            
        if (!$lastProduction) {
            return back()->withErrors([
                'produk_id' => 'Produk ini belum pernah menyelesaikan produksi sebelumnya.',
            ])->withInput();
        }

        // Guard: pastikan produk sudah memiliki BOM dan detail
        $bom = \App\Models\Bom::where('produk_id', $request->produk_id)
            ->withCount('details')
            ->first();
        if (!$bom || (int)($bom->details_count ?? 0) === 0) {
            return back()->withErrors([
                'bom' => 'Produk belum melewati perhitungan Bill Of Material. Silakan lakukan perhitungan Bill Of Material untuk produk tersebut.',
            ])->withInput();
        }

        return DB::transaction(function () use ($request, $lastProduction, $stock, $journal, $konversiService) {
            $produk = Produk::findOrFail($request->produk_id);
            $tanggal = now()->toDateString(); // Use current date

            // Use data from last production
            $qtyProd = $lastProduction->qty_produksi;
            $jumlahProduksiBulanan = $lastProduction->jumlah_produksi_bulanan;
            $hariProduksiBulanan = $lastProduction->hari_produksi_bulanan;

            // Create production plan with same data as last production
            $produksi = Produksi::create([
                'produk_id' => $produk->id,
                'tanggal' => $tanggal,
                'jumlah_produksi_bulanan' => $jumlahProduksiBulanan,
                'hari_produksi_bulanan' => $hariProduksiBulanan,
                'qty_produksi' => $qtyProd,
                'status' => 'draft',
            ]);

            // Calculate total costs from BOM data (same as original create method)
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
            
            // Total BTKL dan BOP dari BOM Job Costing
            $totalBTKLPerUnit = 0;
            $totalBOPPerUnit = 0;
            
            if ($bomJobCosting) {
                $totalBTKLPerUnit = $bomJobCosting->total_btkl ?? 0;
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

            return redirect()->route('transaksi.produksi.index')
                ->with('success', 'Produksi baru untuk ' . $produk->nama_produk . ' berhasil dibuat dengan data yang sama (' . number_format($qtyProd, 2) . ' pcs). Klik "Mulai Produksi" untuk memulai proses.');
        });
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

                // Get BOP from BomJobBOP - setiap baris adalah 1 komponen
                // nama_bop format: "NamaProses - NamaKomponen"
                $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();

                $bopByProses = [];
                foreach ($bomJobBOPs as $bomJobBOP) {
                    $namaBop = $bomJobBOP->nama_bop ?? '';
                    $subtotal = floatval($bomJobBOP->subtotal ?? 0);

                    // Parse "NamaProses - NamaKomponen" → pisah di " - " pertama
                    $dashPos = strpos($namaBop, ' - ');
                    if ($dashPos !== false) {
                        $namaProses   = trim(substr($namaBop, 0, $dashPos));
                        $namaKomponen = trim(substr($namaBop, $dashPos + 3));
                    } else {
                        $namaProses   = $namaBop ?: 'Umum';
                        $namaKomponen = $namaBop ?: 'Umum';
                    }

                    // Normalisasi nama proses (hapus suffix nama produk jika ada)
                    // Contoh: "Perbumbuan Ayam Crispy Macdi" → "Perbumbuan"
                    $prosesNormalized = $namaProses;
                    foreach (['Perbumbuan', 'Penggorengan', 'Pengemasan', 'Menggoreng', 'Packing'] as $keyword) {
                        if (stripos($namaProses, $keyword) !== false) {
                            $prosesNormalized = $namaProses; // Simpan nama asli lengkap
                            break;
                        }
                    }

                    if (!isset($bopByProses[$namaProses])) {
                        $bopByProses[$namaProses] = [
                            'nama'           => $namaProses,
                            'harga_per_unit' => 0,
                            'komponen'       => [],
                        ];
                    }

                    $bopByProses[$namaProses]['harga_per_unit'] += $subtotal;
                    $bopByProses[$namaProses]['komponen'][] = [
                        'nama'          => $namaKomponen,
                        'rate_per_hour' => $subtotal,
                        'description'   => '',
                    ];
                }

                $breakdown['bop'] = array_values($bopByProses);

                // COA mapping untuk preview jurnal — ambil COA milik user yang login
                $userId = auth()->id();
                $allCoas = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->orderBy('kode_akun')
                    ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun'])
                    ->keyBy('kode_akun');

                // Bahan baku: tambahkan coa_persediaan_kode & coa_hpp_kode
                foreach ($breakdown['biaya_bahan']['bahan_baku'] as &$bb) {
                    $bahan = \App\Models\BahanBaku::where('user_id', $userId)
                        ->where('nama_bahan', $bb['nama'])->first();
                    $bb['coa_persediaan_kode'] = $bahan->coa_persediaan_id ?? null;
                    $bb['coa_persediaan_nama'] = $bahan && $bahan->coa_persediaan_id
                        ? ($allCoas[$bahan->coa_persediaan_id]->nama_akun ?? $bahan->coa_persediaan_id)
                        : null;
                }
                unset($bb);

                // Bahan pendukung: tambahkan coa_persediaan_kode & coa_persediaan_nama
                foreach ($breakdown['biaya_bahan']['bahan_pendukung'] as &$bp) {
                    $bahan = \App\Models\BahanPendukung::where('user_id', $userId)
                        ->where('nama_bahan', $bp['nama'])->first();
                    $bp['coa_persediaan_kode'] = $bahan->coa_persediaan_id ?? null;
                    $bp['coa_persediaan_nama'] = $bahan && $bahan->coa_persediaan_id
                        ? ($allCoas[$bahan->coa_persediaan_id]->nama_akun ?? $bahan->coa_persediaan_id)
                        : null;
                }
                unset($bp);

                // BTKL: tambahkan coa_kode per proses (520, 521, 522 dst)
                // BTKL: kredit ke Hutang Gaji, debit ke akun BTKL per proses
                $coaHutangGaji = \App\Models\Coa::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->where('kode_akun', '211')
                    ->first();

                foreach ($breakdown['btkl'] as &$bt) {
                    // Debit: akun BTKL per proses (520, 521, 522)
                    $coaBtkl = \App\Models\Coa::withoutGlobalScopes()
                        ->where('user_id', $userId)
                        ->where('kode_akun', 'LIKE', '52%')
                        ->where('nama_akun', 'LIKE', '%' . $bt['nama'] . '%')
                        ->first()
                        ?? \App\Models\Coa::withoutGlobalScopes()
                            ->where('user_id', $userId)->where('kode_akun', '52')->first();

                    $bt['coa_kode']        = $coaBtkl->kode_akun ?? '52';
                    $bt['coa_nama']        = $coaBtkl->nama_akun ?? 'BTKL';
                    // Kredit: Hutang Gaji
                    $bt['coa_kredit_kode'] = $coaHutangGaji->kode_akun ?? '211';
                    $bt['coa_kredit_nama'] = $coaHutangGaji->nama_akun ?? 'Hutang Gaji';
                }
                unset($bt);

                // BOP: mapping keyword → kode COA yang presisi
                // Urutan: keyword spesifik dulu, fallback ke parent
                $bopCoaKeywordMap = [
                    // ── Bahan penolong → kredit ke Persediaan Bahan Pendukung (115x) ──
                    'Air Mineral'          => ['kode' => '531', 'nama' => 'BOP-Air Mineral Galon',        'kredit_prefix' => '115'],
                    'Air Galon'            => ['kode' => '531', 'nama' => 'BOP-Air Mineral Galon',        'kredit_prefix' => '115'],
                    'Minyak Goreng'        => ['kode' => '532', 'nama' => 'BOP-Minyak Goreng',            'kredit_prefix' => '115'],
                    'Tepung Terigu'        => ['kode' => '533', 'nama' => 'BOP-Tepung Terigu',            'kredit_prefix' => '115'],
                    'Tepung Maizena'       => ['kode' => '534', 'nama' => 'BOP-Tepung Maizena',           'kredit_prefix' => '115'],
                    'Lada'                 => ['kode' => '535', 'nama' => 'BOP- Lada',                    'kredit_prefix' => '115'],
                    'Bubuk Kaldu'          => ['kode' => '536', 'nama' => 'BOP- Bubuk Kaldu',             'kredit_kode' => '1155'],
                    'Bubuk Bawang'         => ['kode' => '537', 'nama' => 'BOP- Bubuk Bawang Putih',      'kredit_prefix' => '115'],
                    'Bawang Putih'         => ['kode' => '537', 'nama' => 'BOP- Bubuk Bawang Putih',      'kredit_prefix' => '115'],
                    'Kemasan'              => ['kode' => '538', 'nama' => 'BOP-Kemasan',                  'kredit_prefix' => '115'],
                    // ── BTKTL → kredit ke Hutang Gaji (211) ──
                    'BTKTL'                => ['kode' => '54',  'nama' => 'BOP BTKTL',                    'kredit_kode' => '211'],
                    'Pegawai Pemasaran'    => ['kode' => '540', 'nama' => 'BOP BTKTL - Biaya Pegawai Pemasaran', 'kredit_kode' => '211'],
                    'Pegawai Kemasan'      => ['kode' => '541', 'nama' => 'BOP BTKTL - Biaya Pegawai Kemasan',   'kredit_kode' => '211'],
                    'Satpam'               => ['kode' => '542', 'nama' => 'BOP BTKTL - Biaya Satpam',     'kredit_kode' => '211'],
                    'Cleaning'             => ['kode' => '543', 'nama' => 'BOP BTKTL - Cleaning Service', 'kredit_kode' => '211'],
                    'Mandor'               => ['kode' => '544', 'nama' => 'BOP BTKTL - Biaya Mandor',     'kredit_kode' => '211'],
                    'Pegawai Keuangan'     => ['kode' => '545', 'nama' => 'BOP BTKTL - Pegawai Keuangan', 'kredit_kode' => '211'],
                    // ── Overhead non-bahan → kredit ke Hutang Usaha (210) ──
                    'Listrik'              => ['kode' => '550', 'nama' => 'BOP TL - Biaya Listrik',       'kredit_kode' => '210'],
                    'Sewa'                 => ['kode' => '551', 'nama' => 'BOP TL - Sewa Tempat',         'kredit_kode' => '210'],
                    'Penyusutan Gedung'    => ['kode' => '552', 'nama' => 'BOP TL - Penyusutan Gedung',   'kredit_kode' => '120'],
                    'Penyusutan Peralatan' => ['kode' => '553', 'nama' => 'BOP TL - Penyusutan Peralatan','kredit_kode' => '120'],
                    'Penyusutan Alat'      => ['kode' => '553', 'nama' => 'BOP TL - Penyusutan Peralatan','kredit_kode' => '120'],
                    'Penyusutan Kendaraan' => ['kode' => '554', 'nama' => 'BOP TL - Penyusutan Kendaraan','kredit_kode' => '124'],
                    'Penyusutan Mesin'     => ['kode' => '555', 'nama' => 'BOP TL - Penyusutan Mesin',    'kredit_kode' => '126'],
                    'Biaya Air'            => ['kode' => '556', 'nama' => 'BOP TL - Biaya Air',           'kredit_kode' => '210'],
                    'Air &'                => ['kode' => '556', 'nama' => 'BOP TL - Biaya Air',           'kredit_kode' => '210'],
                    'Gas'                  => ['kode' => '557', 'nama' => 'BOP TL - Lainnya',             'kredit_kode' => '210'],
                    'BBM'                  => ['kode' => '557', 'nama' => 'BOP TL - Lainnya',             'kredit_kode' => '210'],
                    'Maintenance'          => ['kode' => '557', 'nama' => 'BOP TL - Lainnya',             'kredit_kode' => '210'],
                    'Maintenace'           => ['kode' => '557', 'nama' => 'BOP TL - Lainnya',             'kredit_kode' => '210'],
                    'Kebersihan'           => ['kode' => '557', 'nama' => 'BOP TL - Lainnya',             'kredit_kode' => '210'],
                ];

                // Preload akun akumulasi penyusutan & hutang gaji
                $coaAkumPeralatan  = $allCoas['120'] ?? null;
                $coaAkumKendaraan  = $allCoas['124'] ?? null;
                $coaAkumMesin      = $allCoas['126'] ?? null;
                $coaHutangGajiBop  = $allCoas['211'] ?? null;

                foreach ($breakdown['bop'] as &$bop) {
                    foreach ($bop['komponen'] as &$k) {
                        $namaKomponen = $k['nama'];
                        $matched = null;

                        foreach ($bopCoaKeywordMap as $keyword => $cfg) {
                            if (stripos($namaKomponen, $keyword) !== false) {
                                $matched = $cfg;
                                break;
                            }
                        }

                        if ($matched) {
                            $coaBop = $allCoas[$matched['kode']] ?? null;
                            $k['coa_kode'] = $matched['kode'];
                            $k['coa_nama'] = $coaBop->nama_akun ?? $matched['nama'];

                            // Tentukan akun kredit
                            if (isset($matched['kredit_prefix'])) {
                                // Bahan penolong → cari COA persediaan bahan pendukung yang namanya cocok
                                $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                                    ->where('user_id', $userId)
                                    ->where('kode_akun', 'LIKE', $matched['kredit_prefix'] . '%')
                                    ->where('nama_akun', 'LIKE', '%' . $namaKomponen . '%')
                                    ->first();
                                // Fallback ke parent 115
                                if (!$coaKredit) {
                                    $coaKredit = $allCoas[$matched['kredit_prefix']] ?? null;
                                }
                                $k['kredit_kode'] = $coaKredit->kode_akun ?? $matched['kredit_prefix'];
                                $k['kredit_nama'] = $coaKredit->nama_akun ?? 'Pers. Bahan Pendukung';
                            } else {
                                $kreditKode = $matched['kredit_kode'];
                                $coaKredit  = $allCoas[$kreditKode] ?? null;
                                $k['kredit_kode'] = $coaKredit->kode_akun ?? $kreditKode;
                                $k['kredit_nama'] = $coaKredit->nama_akun ?? 'Hutang Gaji';
                            }
                        } else {
                            // Fallback
                            $k['coa_kode']    = '53';
                            $k['coa_nama']    = $allCoas['53']->nama_akun ?? 'BOP';
                            $k['kredit_kode'] = '210';
                            $k['kredit_nama'] = $allCoas['210']->nama_akun ?? 'Hutang Usaha';
                        }
                    }
                    unset($k);
                }
                unset($bop);

                // COA WIP (117) dan sub-akun BDP
                $coaWip   = $allCoas['117']  ?? null;
                $coaBdpBbb  = $allCoas['1171'] ?? $allCoas['117'] ?? null;
                $coaBdpBtkl = $allCoas['1172'] ?? $allCoas['117'] ?? null;
                $coaBdpBop  = $allCoas['1173'] ?? $allCoas['117'] ?? null;

                $breakdown['coa_wip']      = ['kode' => $coaWip->kode_akun ?? '117',   'nama' => $coaWip->nama_akun ?? 'Pers. Barang dalam Proses'];
                $breakdown['coa_bdp_bbb']  = ['kode' => $coaBdpBbb->kode_akun ?? '1171',  'nama' => $coaBdpBbb->nama_akun ?? 'BDP - BBB'];
                $breakdown['coa_bdp_btkl'] = ['kode' => $coaBdpBtkl->kode_akun ?? '1172', 'nama' => $coaBdpBtkl->nama_akun ?? 'BDP - BTKL'];
                $breakdown['coa_bdp_bop']  = ['kode' => $coaBdpBop->kode_akun ?? '1173',  'nama' => $coaBdpBop->nama_akun ?? 'BDP - BOP'];

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
        if ($satuanResep === $satuanBahan) return "Tidak perlu konversi (satuan sama)";
        $konversi = $bahan->convertToSubUnit(1, $satuanBahan, $satuanResep);
        if ($konversi != 1) return "1 {$satuanBahan} = {$konversi} {$satuanResep}";
        return "Konversi standar";
    }

    /**
     * Keyword map untuk resolusi COA kredit BOP
     */
    private function getBopCoaKeywordMap($allCoas, $userId): array
    {
        return [
            'Air Mineral'          => ['kredit_prefix' => '115'],
            'Air Galon'            => ['kredit_prefix' => '115'],
            'Minyak Goreng'        => ['kredit_prefix' => '115'],
            'Tepung Terigu'        => ['kredit_prefix' => '115'],
            'Tepung Maizena'       => ['kredit_prefix' => '115'],
            'Lada'                 => ['kredit_prefix' => '115'],
            'Bubuk Kaldu'          => ['kredit_prefix' => '115'],
            'Bubuk Bawang'         => ['kredit_prefix' => '115'],
            'Bawang Putih'         => ['kredit_prefix' => '115'],
            'Kemasan'              => ['kredit_prefix' => '115'],
            'Susu'                 => ['kredit_prefix' => '115'],
            'Keju'                 => ['kredit_prefix' => '115'],
            'Cup'                  => ['kredit_prefix' => '115'],
            'BTKTL'                => ['kredit_kode' => '211'],
            'Pegawai'              => ['kredit_kode' => '211'],
            'Satpam'               => ['kredit_kode' => '211'],
            'Cleaning'             => ['kredit_kode' => '211'],
            'Mandor'               => ['kredit_kode' => '211'],
            'Listrik'              => ['kredit_kode' => '210'],
            'Sewa'                 => ['kredit_kode' => '210'],
            'Penyusutan Gedung'    => ['kredit_kode' => '120'],
            'Penyusutan Peralatan' => ['kredit_kode' => '120'],
            'Penyusutan Alat'      => ['kredit_kode' => '120'],
            'Penyusutan Kendaraan' => ['kredit_kode' => '124'],
            'Penyusutan Mesin'     => ['kredit_kode' => '126'],
            'Biaya Air'            => ['kredit_kode' => '210'],
            'Air &'                => ['kredit_kode' => '210'],
            'Gas'                  => ['kredit_kode' => '210'],
            'BBM'                  => ['kredit_kode' => '210'],
            'Maintenance'          => ['kredit_kode' => '210'],
            'Maintenace'           => ['kredit_kode' => '210'],
            'Kebersihan'           => ['kredit_kode' => '210'],
        ];
    }

    /**
     * Resolve COA kredit untuk komponen BOP
     */
    private function resolveBopKredit(string $namaKomponen, array $bopCoaMap, $allCoas, int $userId): array
    {
        foreach ($bopCoaMap as $keyword => $cfg) {
            if (stripos($namaKomponen, $keyword) !== false) {
                if (isset($cfg['kredit_prefix'])) {
                    // Coba match nama_akun dengan kata-kata dari namaKomponen (partial, tiap kata)
                    $words = array_filter(explode(' ', $namaKomponen), fn($w) => strlen($w) > 3);
                    $coaKredit = null;

                    // 1. Coba exact match nama komponen
                    $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                        ->where('user_id', $userId)
                        ->where('kode_akun', 'LIKE', $cfg['kredit_prefix'] . '%')
                        ->where('nama_akun', 'LIKE', '%' . $namaKomponen . '%')
                        ->first();

                    // 2. Coba match tiap kata penting dari namaKomponen
                    if (!$coaKredit) {
                        foreach ($words as $word) {
                            $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                                ->where('user_id', $userId)
                                ->where('kode_akun', 'LIKE', $cfg['kredit_prefix'] . '%')
                                ->where('kode_akun', '!=', $cfg['kredit_prefix']) // hindari parent account
                                ->where('nama_akun', 'LIKE', '%' . $word . '%')
                                ->first();
                            if ($coaKredit) break;
                        }
                    }

                    // 3. Fallback ke keyword itu sendiri
                    if (!$coaKredit) {
                        $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                            ->where('user_id', $userId)
                            ->where('kode_akun', 'LIKE', $cfg['kredit_prefix'] . '%')
                            ->where('kode_akun', '!=', $cfg['kredit_prefix'])
                            ->where('nama_akun', 'LIKE', '%' . $keyword . '%')
                            ->first();
                    }

                    // 4. Fallback ke parent account
                    if (!$coaKredit) {
                        $coaKredit = $allCoas[$cfg['kredit_prefix']] ?? null;
                    }

                    return [$coaKredit->kode_akun ?? $cfg['kredit_prefix'], $coaKredit->nama_akun ?? 'Pers. Bahan Pendukung'];
                } else {
                    $coaKredit = $allCoas[$cfg['kredit_kode']] ?? null;
                    return [$coaKredit->kode_akun ?? $cfg['kredit_kode'], $coaKredit->nama_akun ?? 'Hutang Usaha'];
                }
            }
        }
        $fallback = $allCoas['210'] ?? null;
        return [$fallback->kode_akun ?? '210', $fallback->nama_akun ?? 'Hutang Usaha'];
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

        // Get BOP from BomJobBOP - Use nama_bop_proses from BopProses master data
        $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
            ->with('bopProses')
            ->get();
        
        $bopByProcess = [];
        foreach ($bomJobBOPs as $bomJobBOP) {
            $namaProses = 'Umum';
            
            // Use nama_bop_proses from BopProses if available
            if ($bomJobBOP->bopProses && $bomJobBOP->bopProses->nama_bop_proses) {
                $namaProses = $bomJobBOP->bopProses->nama_bop_proses;
            } else {
                // Fallback to string matching
                $namaBiaya = strtolower($bomJobBOP->nama_bop ?? '');
                if (stripos($namaBiaya, 'penggorengan') !== false) {
                    $namaProses = 'Penggorengan';
                } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                    $namaProses = 'Perbumbuan';
                } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                    $namaProses = 'Pengemasan';
                }
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
            $produksiProses = \App\Models\ProduksiProses::updateOrCreate([
                'produksi_id' => $produksi->id,
                'nama_proses' => $bomJobBTKL->nama_proses ?? 'Proses ' . $prosesOrder,
            ], [
                'urutan' => $prosesOrder,
                'status' => 'pending', // Use 'pending' instead of 'belum_dimulai'
                'biaya_btkl' => $bomJobBTKL->subtotal ?? 0,
                'biaya_bop' => 0, // BOP will be calculated separately
                'total_biaya_proses' => $bomJobBTKL->subtotal ?? 0,
            ]);
            
            $prosesOrder++;
        }

        // Calculate BOP for each process — group by nama_proses dari ProduksiBopDetail
        // (lebih akurat daripada parsing nama_bop dari BomJobBOP)
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
            
            // Exact match dulu
            if (isset($bopByProcess[$proses->nama_proses])) {
                $bopAmount = $bopByProcess[$proses->nama_proses];
            } else {
                // Partial match sebagai fallback
                foreach ($bopByProcess as $prosesName => $bopValue) {
                    if ($prosesName !== 'Umum' &&
                        (stripos($proses->nama_proses, $prosesName) !== false ||
                         stripos($prosesName, $proses->nama_proses) !== false)) {
                        $bopAmount = $bopValue;
                        break;
                    }
                }
                // Jika masih 0, cek apakah ada bucket 'Umum'
                if ($bopAmount == 0 && isset($bopByProcess['Umum'])) {
                    $bopAmount = $bopByProcess['Umum'];
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
            $coaWIP = \App\Models\Coa::where('kode_akun', '117')->first(); // Barang Dalam Proses
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
            $coaWIP = \App\Models\Coa::where('kode_akun', '117')->first(); // Barang Dalam Proses
            $coaBTKL = \App\Models\Coa::where('kode_akun', '52')->first(); // Biaya Tenaga Kerja Langsung
            $coaBOP = \App\Models\Coa::where('kode_akun', '53')->first(); // Biaya Overhead Pabrik
            
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
            $coaWIP = \App\Models\Coa::where('kode_akun', '117')->first(); // Barang Dalam Proses
            $coaFinishedGoods = \App\Models\Coa::where('kode_akun', '116')->first(); // Persediaan Barang Jadi
            
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