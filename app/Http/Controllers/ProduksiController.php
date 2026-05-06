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
            'details.bahanPendukung.satuan',
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
        
        $produksis = $query->orderBy('tanggal','desc')->paginate(10);
        
        // Get products for dropdown - CRITICAL: Filter by user_id
        $produks = Produk::where('user_id', auth()->id())
            ->whereHas('boms', function($query) {
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

            // Create production record
            $produksi = Produksi::create([
                'produk_id' => $produk->id,
                'coa_persediaan_barang_jadi_id' => $request->coa_persediaan_barang_jadi_id,
                'tanggal' => $tanggal,
                'jumlah_produksi_bulanan' => $request->jumlah_produksi_bulanan,
                'hari_produksi_bulanan' => $request->hari_produksi_bulanan,
                'qty_produksi' => $qtyProd,
<<<<<<< HEAD
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

                // Detail BOP — ambil COA dari BopProses komponen
                $coaBdpBop = $allCoas['1173'] ?? $allCoas['117'] ?? null;

                $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
                    ->with('bopProses')
                    ->get();
                    
                foreach ($bomJobBOPs as $bomJobBOP) {
                    $namaBop  = $bomJobBOP->nama_bop ?? '';
                    $subtotal = (float)($bomJobBOP->subtotal ?? 0);
                    $dashPos  = strpos($namaBop, ' - ');
                    $namaProses    = $dashPos !== false ? trim(substr($namaBop, 0, $dashPos)) : $namaBop;
                    $namaKomponen  = $dashPos !== false ? trim(substr($namaBop, $dashPos + 3)) : $namaBop;

                    // PRIORITAS 1: Ambil COA dari BopProses komponen_bop
                    $kreditKode = '210'; // Default
                    $kreditNama = 'Hutang Usaha';
                    $debitKode = '1173'; // Default
                    $debitNama = 'BDP - BOP';
                    
                    if ($bomJobBOP->bopProses && $bomJobBOP->bopProses->komponen_bop) {
                        $komponenBop = is_array($bomJobBOP->bopProses->komponen_bop) 
                            ? $bomJobBOP->bopProses->komponen_bop 
                            : json_decode($bomJobBOP->bopProses->komponen_bop, true);
                        
                        if (is_array($komponenBop)) {
                            // Cari komponen yang sesuai dengan namaKomponen
                            foreach ($komponenBop as $komponen) {
                                $componentName = $komponen['component'] ?? '';
                                if (stripos($componentName, $namaKomponen) !== false || stripos($namaKomponen, $componentName) !== false) {
                                    // Gunakan COA dari komponen
                                    if (!empty($komponen['coa_debit'])) {
                                        $coaDebit = \App\Models\Coa::withoutGlobalScopes()
                                            ->where('user_id', $userId)
                                            ->where('kode_akun', $komponen['coa_debit'])
                                            ->first();
                                        if ($coaDebit) {
                                            $debitKode = $coaDebit->kode_akun;
                                            $debitNama = $coaDebit->nama_akun;
                                        }
                                    }
                                    
                                    if (!empty($komponen['coa_kredit'])) {
                                        $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                                            ->where('user_id', $userId)
                                            ->where('kode_akun', $komponen['coa_kredit'])
                                            ->first();
                                        if ($coaKredit) {
                                            $kreditKode = $coaKredit->kode_akun;
                                            $kreditNama = $coaKredit->nama_akun;
                                        }
                                    }
                                    break; // Sudah ketemu, stop
                                }
                            }
                        }
                    }
                    
                    // PRIORITAS 2: Fallback ke resolveBopKredit jika tidak ada di komponen
                    if ($kreditKode === '210' && $kreditNama === 'Hutang Usaha') {
                        $bopCoaMap = $this->getBopCoaKeywordMap($allCoas, $userId);
                        [$kreditKode, $kreditNama] = $this->resolveBopKredit($namaKomponen, $bopCoaMap, $allCoas, $userId);
                    }

                    \App\Models\ProduksiBopDetail::create([
                        'produksi_id'     => $produksi->id,
                        'nama_proses'     => $namaProses,
                        'nama_komponen'   => $namaKomponen,
                        'rate_per_unit'   => $subtotal,
                        'total'           => $subtotal * $qtyProd,
                        'coa_debit_kode'  => $debitKode,
                        'coa_debit_nama'  => $debitNama,
                        'coa_kredit_kode' => $kreditKode,
                        'coa_kredit_nama' => $kreditNama,
                    ]);

                    // Stock movement pengurangan bahan pendukung (kredit ke akun 115x)
                    if (str_starts_with((string)$kreditKode, '115')) {
                        $bpList = \App\Models\BahanPendukung::where('coa_persediaan_id', $kreditKode)->get();
                        $bp = null;
                        foreach ($bpList as $candidate) {
                            if (stripos($namaKomponen, $candidate->nama_bahan) !== false
                                || stripos($candidate->nama_bahan, $namaKomponen) !== false) {
                                $bp = $candidate; break;
                            }
                        }
                        if (!$bp) $bp = $bpList->first();
                        if ($bp) {
                            $totalBopItem = $subtotal * $qtyProd;
                            // Qty keluar dalam satuan utama = total rupiah / harga satuan master
                            // Contoh: Rp 120.000 / Rp 25.000 per Bungkus = 4.8 Bungkus
                            $hargaSatuanMaster = (float) $bp->harga_satuan;
                            $qtyKeluar = $hargaSatuanMaster > 0
                                ? round($totalBopItem / $hargaSatuanMaster, 4)
                                : 0;
                            \App\Models\StockMovement::create([
                                'item_type'  => 'support',
                                'item_id'    => $bp->id,
                                'tanggal'    => $tanggal,
                                'direction'  => 'out',
                                'qty'        => $qtyKeluar,
                                'satuan'     => optional($bp->satuanRelation)->nama_satuan,
                                'unit_cost'  => $hargaSatuanMaster,
                                'total_cost' => $totalBopItem,
                                'ref_type'   => 'production',
                                'ref_id'     => $produksi->id,
                            ]);
                        }
                    }
                }
            }

            // Hitung total_bahan dari detail yang sudah disimpan (bukan dari BOM)
            $totalBahan = \App\Models\ProduksiDetail::where('produksi_id', $produksi->id)->sum('subtotal');
            $totalBiaya = $totalBahan + $totalBTKL + $totalBOP;

            $produksi->update([
                'total_bahan' => $totalBahan,
                'total_btkl'  => $totalBTKL,
                'total_bop'   => $totalBOP,
=======
                'total_bahan' => $totalBahan,
                'total_btkl' => $totalBTKLTotal,
                'total_bop' => $totalBOPTotal,
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                'total_biaya' => $totalBiaya,
                'status' => 'draft',
                'user_id' => $user_id,
            ]);

            // Save production details (BBB, BTKL, BOP)
            $this->saveProductionDetails($produksi, $hppData, $qtyProd);

            // Update total_proses after creating proses records
            $produksi->total_proses = $produksi->proses()->count();
            $produksi->save();

            // Create journal entries
            $this->createProductionJournals($produksi, $hppData, $qtyProd, $tanggal, $journal);

            return redirect()->route('transaksi.produksi.index')
                ->with('success', 'Produksi berhasil disimpan dengan lengkap. Data detail dan jurnal telah tercatat.');
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

            // Validasi stok cukup untuk setiap bahan baku dari produksi_details
            $shortages = [];
            $shortNames = [];
            
            // Periksa bahan baku dari produksi_details
            foreach ($produksi->details as $detail) {
                if ($detail->bahanBaku) {
                    $bahan = $detail->bahanBaku;
                    $qtyResepTotal = $detail->qty_resep;
                    $satuanResep = $detail->satuan_resep;
                    $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                    
                    // Convert to base unit if needed
                    if ($satuanResep !== $satuanBahan) {
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                    } else {
                        $qtyBase = $qtyResepTotal;
                    }
                    
                    $available = (float)($bahan->stok ?? 0);
                    
                    if ($available + 1e-9 < $qtyBase) {
                        $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh " . number_format($qtyBase, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2) . " {$satuanBahan}";
                        $shortNames[] = $bahan->nama_bahan;
                    }
                }
            }
            
            if (!empty($shortages)) {
                return redirect()->back()->with('error', 'Tidak dapat memulai produksi. Bahan yang kurang: ' . implode(', ', $shortNames) . '. Detail: ' . implode(' | ', $shortages));
            }

            // Jika stok cukup, mulai produksi - kurangi stok bahan
            foreach ($produksi->details as $detail) {
                if ($detail->bahanBaku) {
                    $bahan = $detail->bahanBaku;
                    $qtyResepTotal = $detail->qty_resep;
                    $satuanResep = $detail->satuan_resep;
                    $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                    
                    // Convert to base unit if needed
                    if ($satuanResep !== $satuanBahan) {
                        $qtyBase = $bahan->konversiBerdasarkanProduksi($qtyResepTotal, $satuanResep, $satuanBahan);
                    } else {
                        $qtyBase = $qtyResepTotal;
                    }
                    
                    // Update stok bahan baku
                    $bahan->stok = (float)$bahan->stok - $qtyBase;
                    $bahan->save();
                    
                    // Record stock movement
                    \App\Models\StockMovement::create([
                        'item_type' => 'material',
                        'item_id' => $bahan->id,
                        'tanggal' => now()->format('Y-m-d'),
                        'direction' => 'out',
                        'qty' => $qtyBase,
                        'satuan' => $satuanBahan,
                        'unit_cost' => $detail->harga_satuan,
                        'total_cost' => $detail->subtotal,
                        'keterangan' => "Produksi {$produk->nama_produk} - {$produksi->id}",
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

    public function show($id)
    {
<<<<<<< HEAD
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

                // BOP - ambil COA dari BopProses komponen
                $coaBdpBop = $allCoas['1173'] ?? $allCoas['117'] ?? null;
                
                foreach (\App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->with('bopProses')->get() as $bomJobBOP) {
                    $namaBop      = $bomJobBOP->nama_bop ?? '';
                    $subtotal     = (float)($bomJobBOP->subtotal ?? 0);
                    $dashPos      = strpos($namaBop, ' - ');
                    $namaProses   = $dashPos !== false ? trim(substr($namaBop, 0, $dashPos)) : $namaBop;
                    $namaKomponen = $dashPos !== false ? trim(substr($namaBop, $dashPos + 3)) : $namaBop;
=======
        // 🔒 SECURITY: Add user_id filtering to prevent cross-tenant data access
        $produksi = Produksi::with(['produk','details.bahanBaku.satuan','details.bahanPendukung.satuan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // If production is still in draft status, fetch breakdown data
        if ($produksi->status === 'draft') {
            // Get breakdown similar to create page
            $breakdown = $this->getProductionCostBreakdown($produksi);
            $produksi->detail_breakdown = $breakdown;
        } else {
            // Calculate proper conversions for display from existing details
            foreach ($produksi->details as $detail) {
                if ($detail->bahan_baku_id && $detail->bahanBaku) {
                    $bahan = $detail->bahanBaku;
                    $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan ?? 'unit';
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                    
                    // PRIORITAS 1: Ambil COA dari BopProses komponen_bop
                    $kreditKode = '210'; // Default
                    $kreditNama = 'Hutang Usaha';
                    $debitKode = '1173'; // Default
                    $debitNama = 'BDP - BOP';
                    
                    if ($bomJobBOP->bopProses && $bomJobBOP->bopProses->komponen_bop) {
                        $komponenBop = is_array($bomJobBOP->bopProses->komponen_bop) 
                            ? $bomJobBOP->bopProses->komponen_bop 
                            : json_decode($bomJobBOP->bopProses->komponen_bop, true);
                        
                        if (is_array($komponenBop)) {
                            // Cari komponen yang sesuai dengan namaKomponen
                            foreach ($komponenBop as $komponen) {
                                $componentName = $komponen['component'] ?? '';
                                if (stripos($componentName, $namaKomponen) !== false || stripos($namaKomponen, $componentName) !== false) {
                                    // Gunakan COA dari komponen
                                    if (!empty($komponen['coa_debit'])) {
                                        $coaDebit = \App\Models\Coa::withoutGlobalScopes()
                                            ->where('user_id', $userId)
                                            ->where('kode_akun', $komponen['coa_debit'])
                                            ->first();
                                        if ($coaDebit) {
                                            $debitKode = $coaDebit->kode_akun;
                                            $debitNama = $coaDebit->nama_akun;
                                        }
                                    }
                                    
                                    if (!empty($komponen['coa_kredit'])) {
                                        $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                                            ->where('user_id', $userId)
                                            ->where('kode_akun', $komponen['coa_kredit'])
                                            ->first();
                                        if ($coaKredit) {
                                            $kreditKode = $coaKredit->kode_akun;
                                            $kreditNama = $coaKredit->nama_akun;
                                        }
                                    }
                                    break; // Sudah ketemu, stop
                                }
                            }
                        }
                    }
                    
                    // PRIORITAS 2: Fallback ke resolveBopKredit jika tidak ada di komponen
                    if ($kreditKode === '210' && $kreditNama === 'Hutang Usaha') {
                        $bopCoaMap = $this->getBopCoaKeywordMap($allCoas, $userId);
                        [$kreditKode, $kreditNama] = $this->resolveBopKredit($namaKomponen, $bopCoaMap, $allCoas, $userId);
                    }
                    
                    \App\Models\ProduksiBopDetail::create([
                        'produksi_id'     => $produksi->id,
                        'nama_proses'     => $namaProses,
                        'nama_komponen'   => $namaKomponen,
                        'rate_per_unit'   => $subtotal,
                        'total'           => $subtotal * $qtyProd,
                        'coa_debit_kode'  => $debitKode,
                        'coa_debit_nama'  => $debitNama,
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

        // Only complete if total_proses is set and all processes are done
        if ($produksi->total_proses > 0 && $totalProsesSelesai >= $produksi->total_proses) {
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

        // Note: Journal entries are already created in store() method
        // No need to create duplicate journals here

        // Update status produksi ke selesai
        $produksi->update([
            'status' => 'selesai',
            'waktu_selesai_produksi' => now(),
        ]);
    }

    /**
<<<<<<< HEAD
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
=======
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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
                $bahanPendukungDetails = \App\Models\BomJobBahanPendukung::where('user_id', auth()->id())->where('produk_id', $bomJobCosting->produk_id)->get();
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

<<<<<<< HEAD
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
                    'Susu'                 => ['kode' => '539', 'nama' => 'BOP-Susu',                    'kredit_prefix' => '115'],
                    'Keju'                 => ['kode' => '540', 'nama' => 'BOP-Keju',                    'kredit_prefix' => '115'],
                    'Cup'                  => ['kode' => '541', 'nama' => 'BOP-Cup/Kemasan',             'kredit_prefix' => '115'],
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
=======
            if (!$hasHppData) {
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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
                    $tarif = $proses->tarif_btkl ?? 0;
                    $kapasitas = $proses->kapasitas_per_jam ?? 1;
                    $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;

                    $breakdown['btkl'][] = [
                        'nama' => $proses->nama_proses ?? 'Proses Produksi',
                        'harga_per_unit' => $biayaPerProduk,
                        'kapasitas_per_jam' => $kapasitas,
                        'tarif_per_jam' => $tarif
                    ];
                }
            }

            // Get BOP from new HPP system
            $selectedBop = \App\Models\HargaPokokProduksiBop::where('user_id', $user_id)
                ->with('bopProses.prosesProduksi')
                ->get();

            foreach ($selectedBop as $hpp) {
                $bopProses = $hpp->bopProses;
                if ($bopProses) {
                    $namaProses = $bopProses->prosesProduksi->nama_proses ?? 'BOP';
                    $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;

                    // Parse komponen_bop JSON for detailed display and jurnal entries
                    if ($bopProses->komponen_bop && $totalBopPerProduk > 0) {
                        $komponenBop = is_string($bopProses->komponen_bop) 
                            ? json_decode($bopProses->komponen_bop, true) 
                            : $bopProses->komponen_bop;

                        if (is_array($komponenBop) && count($komponenBop) > 0) {
                            // Calculate total rate per hour from all components
                            $totalRatePerHour = 0;
                            foreach ($komponenBop as $komponen) {
                                $totalRatePerHour += $komponen['rate_per_hour'] ?? 0;
                            }

                            // Distribute total_bop_per_produk proportionally to each component
                            foreach ($komponenBop as $komponen) {
                                $namaKomponen = $komponen['component'] ?? $komponen['nama'] ?? 'BOP';
                                $ratePerHour = $komponen['rate_per_hour'] ?? 0;
                                
                                // Calculate proportional rate per produk
                                $ratePerProduk = $totalRatePerHour > 0 
                                    ? ($ratePerHour / $totalRatePerHour) * $totalBopPerProduk 
                                    : 0;

                                // Determine COA based on component name keywords
                                $coaInfo = $this->determineBopCoaByKeyword($namaKomponen);

                                // Add to BOP display array
                                $breakdown['bop'][] = [
                                    'nama_proses' => $namaProses,
                                    'nama_komponen' => $namaKomponen,
                                    'harga_per_unit' => $ratePerProduk,
                                    'rate_per_hour' => $ratePerHour,
                                    'coa_kode' => $coaInfo['kode'],
                                    'coa_nama' => $coaInfo['nama']
                                ];

                                // Add to BOP komponen for jurnal
                                $breakdown['bop_komponen'][] = [
                                    'nama_bop' => $namaProses . ' - ' . $namaKomponen,
                                    'nama_komponen' => $namaKomponen,
                                    'keterangan' => '',
                                    'subtotal' => $ratePerProduk,
                                    'coa_kode' => $coaInfo['kode'],
                                    'coa_nama' => $coaInfo['nama'],
                                ];
                            }
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
<<<<<<< HEAD
     * Keyword map untuk resolusi COA kredit BOP
     */
    private function getBopCoaKeywordMap($allCoas, $userId): array
    {
        return [
            // Bahan Pendukung - akan dicari dari master data bahan_pendukungs
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
            'Susu'                 => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Keju'                 => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Cup'                  => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Toples'               => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Botol'                => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Plastik'              => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Kardus'               => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            'Label'                => ['kredit_prefix' => '113'], // Pers. Bahan Pendukung
            // BTKL dan BOP lainnya - langsung ke hutang/biaya
            'BTKTL'                => ['kredit_kode' => '211'],
            'Pegawai'              => ['kredit_kode' => '211'],
            'Satpam'               => ['kredit_kode' => '211'],
            'Cleaning'             => ['kredit_kode' => '211'],
            'Mandor'               => ['kredit_kode' => '211'],
            'Listrik'              => ['kredit_kode' => '210'], // Hutang Usaha (bukan persediaan)
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
=======
     * Determine COA for BOP component based on keyword matching
     */
    private function determineBopCoaByKeyword($namaKomponen)
    {
        $namaLower = strtolower($namaKomponen);
        
        // Mapping kata kunci ke COA (urutan penting - yang lebih spesifik di atas)
        $mappings = [
            // Listrik
            ['keywords' => ['listrik', 'electricity', 'power', 'electric'], 'kode' => '531', 'nama' => 'Biaya Listrik'],
            
            // Gas/BBM
            ['keywords' => ['gas', 'bbm', 'bahan bakar', 'fuel', 'lpg', 'bensin', 'solar'], 'kode' => '532', 'nama' => 'Biaya Gas/BBM'],
            
            // Penyusutan Mesin
            ['keywords' => ['penyusutan', 'depresiasi', 'depreciation', 'mesin', 'machine', 'equipment'], 'kode' => '533', 'nama' => 'Biaya Penyusutan Mesin'],
            
            // Maintenance/Pemeliharaan
            ['keywords' => ['maintenance', 'pemeliharaan', 'perawatan', 'repair', 'service'], 'kode' => '534', 'nama' => 'Biaya Maintenance'],
            
            // Gaji Mandor/Supervisor
            ['keywords' => ['mandor', 'supervisor', 'gaji', 'salary', 'upah'], 'kode' => '535', 'nama' => 'Gaji Mandor/Supervisor'],
            
            // Air & Kebersihan
            ['keywords' => ['air', 'water', 'pdam', 'kebersihan', 'cleaning', 'sanitasi'], 'kode' => '536', 'nama' => 'Biaya Air & Kebersihan'],
            
            // Sewa
            ['keywords' => ['sewa', 'rent', 'rental', 'lease'], 'kode' => '537', 'nama' => 'Biaya Sewa'],
            
            // Asuransi
            ['keywords' => ['asuransi', 'insurance'], 'kode' => '538', 'nama' => 'Biaya Asuransi'],
            
            // Bahan Pendukung Produksi (Susu, Keju, Cup, dll)
            ['keywords' => ['susu', 'milk', 'keju', 'cheese', 'cup', 'gelas', 'topping', 'coklat', 'chocolate', 'meses', 'sprinkle'], 'kode' => '1151', 'nama' => 'Persediaan Bahan Pendukung'],
            
            // Packaging/Kemasan
            ['keywords' => ['packaging', 'kemasan', 'packing', 'bungkus', 'plastik', 'kardus', 'box'], 'kode' => '539', 'nama' => 'Biaya Packaging'],
            
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
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        ];
    }

    /**
<<<<<<< HEAD
     * Resolve COA kredit untuk komponen BOP
     */
    private function resolveBopKredit(string $namaKomponen, array $bopCoaMap, $allCoas, int $userId): array
    {
        foreach ($bopCoaMap as $keyword => $cfg) {
            if (stripos($namaKomponen, $keyword) !== false) {
                if (isset($cfg['kredit_prefix'])) {
                    // PRIORITAS 1: Cari dari master data bahan pendukung berdasarkan nama
                    $bahanPendukung = \App\Models\BahanPendukung::withoutGlobalScopes()
                        ->where('user_id', $userId)
                        ->where('nama_bahan', 'LIKE', '%' . $namaKomponen . '%')
                        ->first();
                    
                    // Jika tidak ditemukan dengan nama lengkap, coba dengan keyword
                    if (!$bahanPendukung) {
                        $bahanPendukung = \App\Models\BahanPendukung::withoutGlobalScopes()
                            ->where('user_id', $userId)
                            ->where('nama_bahan', 'LIKE', '%' . $keyword . '%')
                            ->first();
                    }
                    
                    // Jika ditemukan bahan pendukung dan memiliki COA persediaan, gunakan itu
                    if ($bahanPendukung && $bahanPendukung->coa_persediaan_id) {
                        $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                            ->where('user_id', $userId)
                            ->where('kode_akun', $bahanPendukung->coa_persediaan_id)
                            ->first();
                        
                        if ($coaKredit) {
                            return [$coaKredit->kode_akun, $coaKredit->nama_akun];
                        }
                    }
                    
                    // PRIORITAS 2: Coba match nama_akun dengan kata-kata dari namaKomponen (partial, tiap kata)
                    $words = array_filter(explode(' ', $namaKomponen), fn($w) => strlen($w) > 3);
                    $coaKredit = null;

                    // 2.1. Coba exact match nama komponen
                    $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                        ->where('user_id', $userId)
                        ->where('kode_akun', 'LIKE', $cfg['kredit_prefix'] . '%')
                        ->where('nama_akun', 'LIKE', '%' . $namaKomponen . '%')
                        ->first();

                    // 2.2. Coba match tiap kata penting dari namaKomponen
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

                    // 2.3. Fallback ke keyword itu sendiri
                    if (!$coaKredit) {
                        $coaKredit = \App\Models\Coa::withoutGlobalScopes()
                            ->where('user_id', $userId)
                            ->where('kode_akun', 'LIKE', $cfg['kredit_prefix'] . '%')
                            ->where('kode_akun', '!=', $cfg['kredit_prefix'])
                            ->where('nama_akun', 'LIKE', '%' . $keyword . '%')
                            ->first();
                    }

                    // 2.4. Fallback ke parent account
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
=======
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
                $tarif = $proses->tarif_btkl ?? 0;
                $kapasitas = $proses->kapasitas_per_jam ?? 1;
                $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;

                $breakdown['btkl'][] = [
                    'proses_produksi_id' => $proses->id,
                    'nama_proses' => $proses->nama_proses,
                    'tarif_per_jam' => $tarif,
                    'kapasitas_per_jam' => $kapasitas,
                    'subtotal' => $biayaPerProduk,
                ];
            }
        }

        // Get BOP with components
        $selectedBop = \App\Models\HargaPokokProduksiBop::where('user_id', $user_id)
            ->with('bopProses.prosesProduksi')
            ->get();

        foreach ($selectedBop as $hpp) {
            $bopProses = $hpp->bopProses;
            if ($bopProses) {
                $namaProses = $bopProses->prosesProduksi->nama_proses ?? 'BOP';
                $prosesId = $bopProses->proses_produksi_id ?? null;
                $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;

                if ($bopProses->komponen_bop && $totalBopPerProduk > 0) {
                    $komponenBop = is_string($bopProses->komponen_bop) 
                        ? json_decode($bopProses->komponen_bop, true) 
                        : $bopProses->komponen_bop;

                    if (is_array($komponenBop) && count($komponenBop) > 0) {
                        $totalRatePerHour = 0;
                        foreach ($komponenBop as $komponen) {
                            $totalRatePerHour += $komponen['rate_per_hour'] ?? 0;
                        }

                        foreach ($komponenBop as $komponen) {
                            $namaKomponen = $komponen['component'] ?? $komponen['nama'] ?? 'BOP';
                            $ratePerHour = $komponen['rate_per_hour'] ?? 0;
                            
                            $ratePerProduk = $totalRatePerHour > 0 
                                ? ($ratePerHour / $totalRatePerHour) * $totalBopPerProduk 
                                : 0;

                            $coaInfo = $this->determineBopCoaByKeyword($namaKomponen);

                            $breakdown['bop'][] = [
                                'bop_proses_id' => $bopProses->id,
                                'proses_id' => $prosesId,
                                'nama_proses' => $namaProses,
                                'nama_komponen' => $namaKomponen,
                                'subtotal' => $ratePerProduk,
                            ];

                            $breakdown['bop_komponen'][] = [
                                'nama_komponen' => $namaKomponen,
                                'subtotal' => $ratePerProduk,
                                'coa_kode' => $coaInfo['kode'],
                                'coa_nama' => $coaInfo['nama'],
                            ];
                        }
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
            \App\Models\ProduksiDetail::create([
                'produksi_id' => $produksi->id,
                'bahan_baku_id' => $bbb['bahan_baku_id'],
                'qty_resep' => $bbb['jumlah'] * $qtyProd,
                'satuan_resep' => $bbb['satuan'],
                'harga_satuan' => $bbb['harga_satuan'],
                'subtotal' => $bbb['subtotal'] * $qtyProd,
                'user_id' => $produksi->user_id,
            ]);
        }

        // Save BTKL as produksi_proses records
        $urutan = 1;
        foreach ($hppData['btkl'] as $btkl) {
            $biayaBtkl = $btkl['subtotal'] * $qtyProd;
            
            \App\Models\ProduksiProses::create([
                'produksi_id' => $produksi->id,
                'proses_produksi_id' => $btkl['proses_produksi_id'],
                'nama_proses' => $btkl['nama_proses'],
                'urutan' => $urutan++,
                'biaya_btkl' => $biayaBtkl,
                'biaya_bop' => 0, // Will be calculated from BOP data
                'total_biaya_proses' => $biayaBtkl, // Will be updated after BOP
                'status' => 'pending',
                'user_id' => $produksi->user_id,
            ]);
        }

        // Group BOP by proses_id and sum the subtotals
        $bopByProses = [];
        foreach ($hppData['bop'] as $bop) {
            $prosesId = $bop['proses_id'];
            if (!isset($bopByProses[$prosesId])) {
                $bopByProses[$prosesId] = 0;
            }
            $bopByProses[$prosesId] += $bop['subtotal'];
        }

        // Update BOP costs in produksi_proses
        foreach ($bopByProses as $prosesId => $totalBop) {
            $proses = \App\Models\ProduksiProses::where('produksi_id', $produksi->id)
                ->where('proses_produksi_id', $prosesId)
                ->first();
            
            if ($proses) {
                $biayaBop = $totalBop * $qtyProd;
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
        
        // VALIDATE: Ensure all required COAs exist before creating journals
        // This prevents incorrect journal entries with fallback COAs
        \App\Helpers\ProductionCoaValidator::validateOrThrow($hppData, $user_id);
        
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
                'referensi' => $produksi->id,
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
                        'referensi' => $produksi->id,
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
                'referensi' => $produksi->id,
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
                'referensi' => $produksi->id,
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
                'referensi' => $produksi->id,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
            ]);

            // KREDIT: Per komponen BOP
            foreach ($hppData['bop_komponen'] as $komponen) {
                $totalKomponen = $komponen['subtotal'] * $qtyProd;
                if ($totalKomponen > 0) {
                    $coaId = $this->getCoaIdByKode($komponen['coa_kode']);

                    \App\Models\JurnalUmum::create([
                        'user_id' => $user_id,
                        'coa_id' => $coaId,
                        'tanggal' => $tanggal,
                        'keterangan' => 'BOP - ' . $komponen['nama_komponen'],
                        'debit' => 0,
                        'kredit' => $totalKomponen,
                        'referensi' => $produksi->id,
                        'tipe_referensi' => 'produksi_bop',
                        'created_by' => $user_id,
                    ]);
                }
            }
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
                'referensi' => $produksi->id,
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
                    'referensi' => $produksi->id,
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
                    'referensi' => $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }

            if ($totalBOP > 0) {
                \App\Models\JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1173'),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BOP ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBOP,
                    'referensi' => $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }
        }
    }

    /**
     * Get COA ID by kode_akun - STRICT MODE (no fallback)
     * Throws exception if COA not found to prevent incorrect journal entries
     */
    private function getCoaIdByKode($kodeAkun)
    {
        $user_id = auth()->id();
        
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
     * Get detailed cost breakdown for production from saved details
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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
                $tarifPerJam = $btkl->prosesProduksi->tarif_btkl ?? 0;
                $kapasitasPerJam = $btkl->prosesProduksi->kapasitas_per_jam ?? 1;
                
                // Calculate biaya per unit
                $biayaPerUnit = $kapasitasPerJam > 0 ? $tarifPerJam / $kapasitasPerJam : 0;
                $totalBiaya = $biayaPerUnit * $produksi->qty_produksi;
                
                $breakdown['btkl'][] = [
                    'nama' => $btkl->prosesProduksi->nama_proses,
                    'biaya_per_unit' => $biayaPerUnit,
                    'total_biaya' => $totalBiaya
                ];
            }
        }

        // Get BOP details from HPP with components
        $hppBop = \App\Models\HargaPokokProduksiBop::where('user_id', $user_id)
            ->with('bopProses.prosesProduksi')
            ->get();
        
        foreach ($hppBop as $bop) {
            if ($bop->bopProses && $bop->bopProses->prosesProduksi) {
                // Check if komponen_bop is already an array or needs decoding
                $komponenBop = $bop->bopProses->komponen_bop;
                if (is_string($komponenBop)) {
                    $komponenBop = json_decode($komponenBop, true) ?? [];
                } elseif (!is_array($komponenBop)) {
                    $komponenBop = [];
                }
                
                $totalBopPerProduk = $bop->bopProses->total_bop_per_produk ?? 0;
                
                // Calculate total rate to get proportions
                $totalRate = 0;
                foreach ($komponenBop as $komp) {
                    $totalRate += $komp['rate_per_hour'] ?? 0;
                }
                
                // Add each component
                foreach ($komponenBop as $komp) {
                    $namaKomponen = $komp['component'] ?? 'BOP';
                    $ratePerHour = $komp['rate_per_hour'] ?? 0;
                    
                    // Calculate proportional BOP for this component
                    $bopPerUnit = $totalRate > 0 ? ($ratePerHour / $totalRate) * $totalBopPerProduk : 0;
                    $totalBiaya = $bopPerUnit * $produksi->qty_produksi;
                    
                    $breakdown['bop'][] = [
                        'nama_proses' => $bop->bopProses->prosesProduksi->nama_proses,
                        'nama_komponen' => $namaKomponen,
                        'biaya_per_unit' => $bopPerUnit,
                        'total_biaya' => $totalBiaya
                    ];
                }
            }
        }
<<<<<<< HEAD

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
=======
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        
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

<<<<<<< HEAD
        // Calculate BOP for each process — group by nama_proses dari ProduksiBopDetail
        // (lebih akurat daripada parsing nama_bop dari BomJobBOP)
=======
        // Calculate BOP for each process
        $bomJobBOPs = \App\Models\BomJobBOP::where('user_id', auth()->id())->where('produk_id', $bomJobCosting->produk_id)->get();
        
        // Group BOP by process name and multiply by production quantity
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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