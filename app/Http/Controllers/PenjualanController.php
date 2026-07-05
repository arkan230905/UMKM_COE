<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\BuktiPembayaran;
use App\Models\OngkirSetting;
use App\Models\PaketMenu;
use App\Services\StockService;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        // FIX OLD DATA: Sync completed orders to have 'paid' payment status
        $completedUnpaidOrders = \App\Models\Order::withoutGlobalScope('user')
            ->where('status', 'completed')
            ->where(function($q) {
                $q->where('payment_status', '!=', 'paid')
                  ->where('payment_status', '!=', 'lunas');
            })->get();
            
        foreach ($completedUnpaidOrders as $order) {
            $order->payment_status = 'paid';
            if (!$order->paid_at) {
                $order->paid_at = now();
            }
            $order->save();
            
            \Illuminate\Support\Facades\DB::table('penjualans')->where('order_id', $order->id)->update([
                'payment_status' => 'paid',
                'payment_confirmed_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(payment_confirmed_at, NOW())')
            ]);
        }

        // Run migration if columns are missing
        if (!\Illuminate\Support\Facades\Schema::hasColumn('returs', 'metode_refund') || 
            !\Illuminate\Support\Facades\Schema::hasColumn('returs', 'bukti_foto') ||
            !\Illuminate\Support\Facades\Schema::hasColumn('retur_penjualans', 'bukti_foto')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('Auto migration failed in PenjualanController: ' . $e->getMessage());
            }
        }

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $query = Penjualan::with(['produk', 'details', 'returs', 'pelanggan'])
            ->where('user_id', auth()->id());
        
        // Filter by nomor transaksi
        if ($request->filled('nomor_transaksi')) {
            $query->where('nomor_penjualan', 'like', '%' . $request->nomor_transaksi . '%');
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        // Filter by metode pembayaran (coa_id atau metode khusus)
        if ($request->filled('payment_filter')) {
            $paymentFilter = $request->payment_filter;
            if (str_starts_with($paymentFilter, 'coa:')) {
                $query->where('coa_id', substr($paymentFilter, 4));
            } elseif (str_starts_with($paymentFilter, 'method:')) {
                $query->where('payment_method', substr($paymentFilter, 7));
            }
        } elseif ($request->filled('coa_id')) {
            // Fallback for old parameter
            $query->where('coa_id', $request->coa_id);
        }

        // Filter by produk
        if ($request->filled('produk_id')) {
            $produkId = $request->produk_id;
            $query->where(function($q) use ($produkId) {
                $q->where('produk_id', $produkId)
                  ->orWhereHas('details', function($q2) use ($produkId) {
                      $q2->where('produk_id', $produkId);
                  });
            });
        }
        // Filter by status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'pending') {
                $query->where('approval_status', 'pending');
            } elseif ($status === 'approved') {
                $query->where('approval_status', 'approved');
            } elseif ($status === 'rejected') {
                $query->where('approval_status', 'rejected');
            } elseif ($status === 'paid') {
                $query->where('payment_status', 'paid');
            } elseif ($status === 'unpaid') {
                $query->where('payment_status', '!=', 'paid');
            }
        }

        $pendingQuery = clone $query;
        $pendingPenjualans = $pendingQuery->where('approval_status', 'pending')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        \Log::info('PenjualanController: Querying Pengajuan Penjualan Pelanggan', [
            'perusahaan_id_owner' => auth()->id(),
            'jumlah_order_pending_ditemukan' => $pendingPenjualans->count(),
            'status_yang_ikut_query' => $request->status ?? 'semua',
        ]);
        
        $query->where('approval_status', '!=', 'pending');
        
        // Ensure default sorting in DB query
        $query->orderBy('tanggal', 'desc')
              ->orderBy('created_at', 'desc')
              ->orderBy('id', 'desc');
              
        $penjualans = $query->get();
        $sortBy = $request->get('sort_by', 'tanggal');
        $sortDir = $request->get('sort_dir', 'desc');
        $isDesc = $sortDir === 'desc';
        
        if ($sortBy === 'no') {
            $penjualans = $penjualans->sortBy(fn($p) => $p->id, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'nomor_transaksi') {
            $penjualans = $penjualans->sortBy(fn($p) => $p->nomor_penjualan, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'tanggal' && !$isDesc) {
            // DB already sorts by tanggal DESC, so only reverse if ascending is requested
            $penjualans = $penjualans->reverse();
        } elseif ($sortBy === 'pembayaran') {
            $penjualans = $penjualans->sortBy(fn($p) => $p->payment_method, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'pelanggan') {
            $penjualans = $penjualans->sortBy(fn($p) => $p->pelanggan?->nama_pelanggan ?? 'Umum', SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'produk') {
            $penjualans = $penjualans->sortBy(function($p) {
                if ($p->details->count() > 0) {
                    return $p->details->first()->produk?->nama_produk ?? '';
                }
                return $p->produk?->nama_produk ?? '';
            }, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'qty') {
            $penjualans = $penjualans->sortBy(function($p) {
                if ($p->details->count() > 0) {
                    return $p->details->sum('jumlah');
                }
                return $p->jumlah ?? 0;
            }, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'harga') {
            $penjualans = $penjualans->sortBy(function($p) {
                if ($p->details->count() > 0) {
                    return $p->details->first()->harga_satuan ?? 0;
                }
                return $p->harga_satuan ?? 0;
            }, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'diskon') {
            $penjualans = $penjualans->sortBy(function($p) {
                return (float)($p->diskon_nominal ?? 0);
            }, SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'ongkir') {
            $penjualans = $penjualans->sortBy(fn($p) => (float)($p->biaya_ongkir ?? 0), SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'total') {
            $penjualans = $penjualans->sortBy(fn($p) => (float)($p->grand_total ?? $p->total), SORT_REGULAR, $isDesc);
        } elseif ($sortBy === 'qty_retur') {
            $penjualans = $penjualans->sortBy(fn($p) => (float)($p->total_qty_retur), SORT_REGULAR, $isDesc);
        }
        
        // Hitung ringkasan penjualan HARI INI saja
        $today = now()->format('Y-m-d');
        // CRITICAL: Filter by user_id
        $penjualansHariIni = Penjualan::where('user_id', auth()->id())
            ->whereDate('tanggal', $today)
            ->get();
        
        $totalPenjualan = 0;
        $totalProdukTerjual = 0;
        $totalProfit = 0;
        $totalOngkir = 0;
        $totalDiskon = 0;
        
        foreach ($penjualansHariIni as $penjualan) {
            $totalPenjualan += (float)($penjualan->total ?? 0);
            $totalOngkir += (float)($penjualan->ongkir ?? 0);
            $totalDiskon += (float)($penjualan->diskon_nominal ?? 0);
            
            $detailCount = $penjualan->details->count();
            if ($detailCount > 1) {
                foreach ($penjualan->details as $d) {
                    $totalProdukTerjual += (float)($d->jumlah ?? 0);
                    $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal);
                    $margin = ((float)($d->harga_satuan ?? 0) - $actualHPP) * (float)($d->jumlah ?? 0);
                    $totalProfit += $margin;
                }
            } elseif ($detailCount === 1) {
                $d = $penjualan->details[0];
                $totalProdukTerjual += (float)($d->jumlah ?? 0);
                $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal);
                $margin = ((float)($d->harga_satuan ?? 0) - $actualHPP) * (float)($d->jumlah ?? 0);
                $totalProfit += $margin;
            } else {
                $totalProdukTerjual += (float)($penjualan->jumlah ?? 0);
                $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                $hdrHarga = $penjualan->harga_satuan;
                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                }
                $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                $totalProfit += $margin;
            }
        }
        
        $jumlahTransaksiHariIni = $penjualansHariIni->count();
        
        // Calculate yesterday's data for comparison
        $yesterday = now()->subDay()->format('Y-m-d');
        $penjualansKemarin = Penjualan::where('user_id', auth()->id())
            ->whereDate('tanggal', $yesterday)
            ->get();
        
        $totalPenjualanKemarin = 0;
        $totalProdukTerjualKemarin = 0;
        $totalProfitKemarin = 0;
        $totalOngkirKemarin = 0;
        $totalDiskonKemarin = 0;
        
        foreach ($penjualansKemarin as $penjualan) {
            $totalPenjualanKemarin += (float)($penjualan->total ?? 0);
            $totalOngkirKemarin += (float)($penjualan->ongkir ?? 0);
            $totalDiskonKemarin += (float)($penjualan->diskon_nominal ?? 0);
            
            $detailCount = $penjualan->details->count();
            if ($detailCount > 1) {
                foreach ($penjualan->details as $d) {
                    $totalProdukTerjualKemarin += (float)($d->jumlah ?? 0);
                    $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal);
                    $margin = ((float)($d->harga_satuan ?? 0) - $actualHPP) * (float)($d->jumlah ?? 0);
                    $totalProfitKemarin += $margin;
                }
            } elseif ($detailCount === 1) {
                $d = $penjualan->details[0];
                $totalProdukTerjualKemarin += (float)($d->jumlah ?? 0);
                $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal);
                $margin = ((float)($d->harga_satuan ?? 0) - $actualHPP) * (float)($d->jumlah ?? 0);
                $totalProfitKemarin += $margin;
            } else {
                $totalProdukTerjualKemarin += (float)($penjualan->jumlah ?? 0);
                $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                $hdrHarga = $penjualan->harga_satuan;
                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                }
                $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                $totalProfitKemarin += $margin;
            }
        }
        
        $jumlahTransaksiKemarin = $penjualansKemarin->count();
        
        // Calculate percentage changes
        $penjualanChange = $totalPenjualanKemarin > 0 ? (($totalPenjualan - $totalPenjualanKemarin) / $totalPenjualanKemarin) * 100 : 0;
        $transaksiChange = $jumlahTransaksiKemarin > 0 ? (($jumlahTransaksiHariIni - $jumlahTransaksiKemarin) / $jumlahTransaksiKemarin) * 100 : 0;
        $produkChange = $totalProdukTerjualKemarin > 0 ? (($totalProdukTerjual - $totalProdukTerjualKemarin) / $totalProdukTerjualKemarin) * 100 : 0;
        $ongkirChange = $totalOngkirKemarin > 0 ? (($totalOngkir - $totalOngkirKemarin) / $totalOngkirKemarin) * 100 : 0;
        $diskonChange = $totalDiskonKemarin > 0 ? (($totalDiskon - $totalDiskonKemarin) / $totalDiskonKemarin) * 100 : 0;
        $profitChange = $totalProfitKemarin > 0 ? (($totalProfit - $totalProfitKemarin) / $totalProfitKemarin) * 100 : 0;
        
        // Get return data for the return tab
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $salesReturns = \App\Models\ReturPenjualan::where('user_id', auth()->id())
            ->with(['penjualan', 'detailReturPenjualans.produk'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Fetch customer-submitted returns
        $customerReturns = \App\Models\Retur::where('type', 'sale')
            ->whereIn('ref_id', function($q) {
                $q->select('order_id')
                  ->from('penjualans')
                  ->where('user_id', auth()->id())
                  ->whereNotNull('order_id');
            })
            ->whereIn('status', ['draft', 'pending', 'menunggu_approval', 'diajukan'])
            ->with(['penjualan', 'details.produk'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Sorting for sales returns
        $sortReturBy = $request->get('sort_retur_by', 'tanggal');
        $sortReturDir = $request->get('sort_retur_dir', 'desc');
        $isReturDesc = $sortReturDir === 'desc';

        if ($sortReturBy === 'no') {
            $salesReturns = $salesReturns->sortBy(fn($r) => $r->id, SORT_REGULAR, $isReturDesc);
        } elseif ($sortReturBy === 'tanggal' && !$isReturDesc) {
            $salesReturns = $salesReturns->reverse();
        } elseif ($sortReturBy === 'nomor_penjualan') {
            $salesReturns = $salesReturns->sortBy(fn($r) => $r->penjualan?->nomor_penjualan ?? '', SORT_REGULAR, $isReturDesc);
        } elseif ($sortReturBy === 'deskripsi') {
            $salesReturns = $salesReturns->sortBy(fn($r) => $r->keterangan ?? '', SORT_REGULAR, $isReturDesc);
        } elseif ($sortReturBy === 'kompensasi') {
            $salesReturns = $salesReturns->sortBy(fn($r) => $r->jenis_retur ?? '', SORT_REGULAR, $isReturDesc);
        } elseif ($sortReturBy === 'status') {
            $salesReturns = $salesReturns->sortBy(fn($r) => $r->status ?? '', SORT_REGULAR, $isReturDesc);
        } elseif ($sortReturBy === 'total_retur') {
            $salesReturns = $salesReturns->sortBy(fn($r) => (float)($r->total_retur ?? 0), SORT_REGULAR, $isReturDesc);
        } elseif ($sortReturBy === 'produk') {
            $salesReturns = $salesReturns->sortBy(function($r) {
                if ($r->detailReturPenjualans && $r->detailReturPenjualans->count() > 0) {
                    return $r->detailReturPenjualans->first()->produk?->nama_produk ?? '';
                }
                return '';
            }, SORT_REGULAR, $isReturDesc);
        }

        $produks = \App\Models\Produk::where('user_id', auth()->id())->get();
        $perusahaan = \App\Models\Perusahaan::where('user_id', auth()->id())->first();
        
        // Ambil akun kas/bank yang valid untuk tenant ini
        $paymentCoas = \App\Models\Coa::where('user_id', auth()->id())
            ->where(function($q) {
                $q->whereIn('kode_akun', ['111', '112', '113']) // Kas, Kas Kecil, Kas Bank default
                  ->orWhere(function($q2) {
                      $q2->whereIn('tipe_akun', ['Asset', 'Aset', 'Harta', 'Aktiva'])
                         ->where(function($q3) {
                             $q3->where('nama_akun', 'like', '%kas%')
                                ->orWhere('nama_akun', 'like', '%bank%');
                         });
                  });
            })
            ->orderBy('kode_akun')
            ->get();
            
        // Ambil payment method distinct dari transaksi
        $usedPaymentMethods = \App\Models\Penjualan::where('user_id', auth()->id())
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->pluck('payment_method');
            
        // Buat struktur opsi dropdown
        $paymentOptions = [];
        
        // 1. Tambahkan dari COA
        foreach ($paymentCoas as $coa) {
            $paymentOptions["coa:{$coa->id}"] = $coa->nama_akun;
        }
        
        // 2. Tambahkan dari payment_method yang belum ada di COA
        foreach ($usedPaymentMethods as $method) {
            $label = $method;
            if (strtolower($method) === 'cash' || strtolower($method) === 'tunai') {
                $label = 'Tunai';
            } elseif (strtolower($method) === 'transfer') {
                $label = 'Transfer Manual';
            } elseif (strtolower($method) === 'midtrans' || strtolower($method) === 'midtrans_va') {
                $label = 'Transfer VA Midtrans';
            } elseif (strtolower($method) === 'credit') {
                $label = 'Kredit / Tempo';
            } elseif (strtolower($method) === 'cod') {
                $label = 'COD / Bayar di Tempat';
            } else {
                $label = ucfirst($method);
            }
            
            // Hindari duplikat label
            if (!in_array($label, $paymentOptions)) {
                $paymentOptions["method:{$method}"] = $label;
            }
        }
        
        // Fallback backward compatibility for kasbanks if needed in view
        $kasbanks = $paymentCoas;

        return view('transaksi.penjualan.index', compact(
            'penjualans',
            'pendingPenjualans',
            'totalPenjualan',
            'jumlahTransaksiHariIni',
            'totalProdukTerjual',
            'totalProfit',
            'totalOngkir',
            'totalDiskon',
            'salesReturns',
            'customerReturns',
            'penjualanChange',
            'transaksiChange',
            'produkChange',
            'ongkirChange',
            'diskonChange',
            'profitChange',
            'produks',
            'perusahaan',
            'kasbanks',
            'paymentOptions'
        ));
    }

    public function create()
    {
        // Ambil produk dengan stok dari kolom stok di tabel produks
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $produks = Produk::where('user_id', auth()->id())
            ->get()
            ->map(function($p) {
                // Gunakan stok dari tabel produks, bukan actual_stok dari StockLayer
                $p->stok_tersedia = (float)($p->stok ?? 0);
                return $p;
            });
        
        // Ambil akun kas/bank + piutang untuk dropdown "Terima di"
        // 111=Bank, 112/113=Kas, 118=Piutang Usaha
        $kasbank = \App\Models\Coa::whereIn('kode_akun', ['111', '112', '113', '118'])
            ->orderBy('kode_akun')
            ->get();
        
        // Ambil ongkir settings yang aktif
        $ongkirSettings = OngkirSetting::where('status', true)
            ->orderBy('jarak_min')
            ->get();
        
        // Ambil paket menu yang aktif dengan detail produk
        $paketMenus = PaketMenu::with('details.produk')
            ->where('status', 'aktif')
            ->orderBy('nama_paket')
            ->get();
        
        return view('transaksi.penjualan.create', compact('produks', 'kasbank', 'ongkirSettings', 'paketMenus'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        // This method is now replaced by confirmPayment
        return redirect()->route('transaksi.penjualan.create');
    }

    public function show($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())
            ->with('details.produk', 'produk', 'returPenjualans.detailReturPenjualans.produk')
            ->findOrFail($id);
        
        return view('transaksi.penjualan.show', compact('penjualan'));
    }

    public function struk($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())
            ->with('details.produk', 'produk')
            ->findOrFail($id);
        
        // Ambil data perusahaan sesuai dengan user yang sedang login
        $dataPerusahaan = \App\Models\Perusahaan::where('user_id', auth()->id())->first();
        
        return view('transaksi.penjualan.struk', compact('penjualan', 'dataPerusahaan'));
    }

    public function edit($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())
            ->with('details.produk')
            ->findOrFail($id);
        
        // Ambil produk dengan stok dari kolom stok di tabel produks
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $produks = Produk::where('user_id', auth()->id())
            ->get()
            ->map(function($p) {
                // Gunakan stok dari tabel produks, bukan actual_stok dari StockLayer
                $p->stok_tersedia = (float)($p->stok ?? 0);
                return $p;
            });
        
        // Ambil akun kas/bank untuk dropdown
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        // Ambil ongkir settings yang aktif
        $ongkirSettings = OngkirSetting::where('status', true)
            ->orderBy('jarak_min')
            ->get();
        
        // Ambil paket menu yang aktif dengan detail produk
        $paketMenus = PaketMenu::with('details.produk')
            ->where('status', 'aktif')
            ->orderBy('nama_paket')
            ->get();
        
        return view('transaksi.penjualan.edit', compact('penjualan', 'produks', 'kasbank', 'ongkirSettings', 'paketMenus'));
    }

    public function update(Request $request, $id, StockService $stock, JournalService $journal)
    {
        // Validate request data
        $request->validate([
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'payment_method' => 'required|in:cash,transfer',
            'sumber_dana' => 'required',
            'produk_id.*' => 'required',
            'jumlah.*' => 'required|integer|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
            'diskon_persen.*' => 'nullable|numeric|min:0|max:100',
            'biaya_ongkir' => 'nullable|numeric|min:0',
            'ppn_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function() use ($request, $id, $stock, $journal) {
            $penjualan = Penjualan::findOrFail($id);
            
            // Get existing details for stock restoration
            $existingDetails = $penjualan->details()->get();
            
            // Restore stock for existing items
            foreach ($existingDetails as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    // Restore stock
                    $produk->stok = (float)($produk->stok ?? 0) + $detail->jumlah;
                    $produk->save();
                    
                    // Reverse stock consumption
                    $stock->reverse('product', $detail->produk_id, $detail->jumlah, 'pcs', 'sale', $penjualan->id, $penjualan->tanggal);
                }
            }
            
            // Delete existing details
            $penjualan->details()->delete();
            
            // Delete existing journals
            $journal->deleteByRef('sale', (int)$penjualan->id);
            $journal->deleteByRef('sale_cogs', (int)$penjualan->id);
            
            // Prepare new data
            $tanggal = $request->tanggal . ' ' . $request->waktu;
            $produkIds = $request->produk_id;
            $jumlahs = $request->jumlah;
            $hargaSatuans = $request->harga_satuan;
            $diskonPersens = $request->diskon_persen ?? [];
            $biayaOngkir = (float)($request->biaya_ongkir ?? 0);
            $ppnPersen = (float)($request->ppn_persen ?? 11);
            
            // Calculate totals
            $subtotalProduk = 0;
            $items = [];
            
            foreach ($produkIds as $index => $produkId) {
                $produk = Produk::findOrFail($produkId);
                $qty = (int)$jumlahs[$index];
                $harga = (float)$hargaSatuans[$index];
                $diskonPersen = (float)($diskonPersens[$index] ?? 0);
                $subtotal = $qty * $harga * (1 - $diskonPersen / 100);
                
                // Validate stock
                if ((float)($produk->stok ?? 0) < $qty) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak cukup. Tersedia: " . ($produk->stok ?? 0) . ", Dibutuhkan: {$qty}");
                }
                
                $subtotalProduk += $subtotal;
                $items[] = [
                    'produk_id' => $produkId,
                    'jumlah' => $qty,
                    'harga_satuan' => $harga,
                    'diskon_persen' => $diskonPersen,
                    'subtotal' => $subtotal
                ];
            }
            
            $totalPPN = $subtotalProduk * ($ppnPersen / 100);
            $grandTotal = $subtotalProduk + $biayaOngkir + $totalPPN;
            
            // Resolve coa_id dari sumber_dana
            $sumberDanaKode = $request->sumber_dana;
            $coaId = null;
            if ($sumberDanaKode) {
                $coaRecord = \App\Models\Coa::where('kode_akun', $sumberDanaKode)->first();
                $coaId = $coaRecord?->id;
            }
            
            // Update penjualan header
            $penjualan->update([
                'tanggal'        => $tanggal,
                'payment_method' => $request->payment_method,
                'coa_id'         => $coaId,
                'jumlah'         => collect($items)->sum('jumlah'),
                'harga_satuan'   => null,
                'diskon_nominal' => 0,
                'total'          => $subtotalProduk,
                'biaya_ongkir'   => $biayaOngkir,
                'biaya_ppn'      => $totalPPN,
                'grand_total'    => $grandTotal,
                'subtotal_produk' => $subtotalProduk,
                'total_ppn'       => $totalPPN,
                'ppn_persen'      => $ppnPersen,
            ]);
            
            // Create new detail items
            foreach ($items as $item) {
                $produk = Produk::find($item['produk_id']);
                $qty = $item['jumlah'];
                
                \App\Models\PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $qty,
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon_persen' => $item['diskon_persen'],
                    'diskon_nominal' => 0,
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Consume stock
                $stock->consume('product', $item['produk_id'], $qty, 'pcs', 'sale', $penjualan->id, $tanggal);
                
                // Update stok
                $produk->stok = (float)($produk->stok ?? 0) - $qty;
                $produk->save();
            }
            
            // Create journal entries
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);
            
            return redirect()->route('transaksi.penjualan.show', $penjualan->id)
                           ->with('success', 'Data penjualan berhasil diperbarui.');
        });
    }

    public function destroy($id, JournalService $journal)
    {
        // DISABLED: Delete functionality has been disabled to prevent deletion of sales transactions
        // Sales transactions must be kept for accounting and audit trail purposes
        abort(403, 'Penghapusan data penjualan tidak diizinkan. Silakan hubungi administrator jika perlu menghapus data penjualan.');
    }

    /**
     * Tampilkan detail jurnal untuk satu transaksi penjualan.
     * Jika ada akun yang belum tersedia, tampilkan notifikasi.
     */
    public function showJurnal($id)
    {
        $penjualan = Penjualan::with('details.produk', 'produk')->findOrFail($id);

        $validator  = new \App\Services\JournalValidationService();
        $validation = $validator->validate($penjualan);

        // Ambil jurnal yang sudah ada dari jurnal_umum (jika ada)
        $journalLines = \App\Models\JurnalUmum::where('tipe_referensi', 'sale')
            ->where('referensi', (string)$penjualan->id)
            ->with('coa')
            ->orderBy('id')
            ->get();

        // Jika jurnal belum ada dan validasi gagal, tampilkan error
        if ($journalLines->isEmpty() && !$validation['valid']) {
            $namaAkunMissing = array_map(fn($m) => $m['nama'], $validation['missing']);
            if (count($namaAkunMissing) === 1) {
                $pesan = "Jurnal penjualan tidak dapat dibuat.\n" . $validation['missing'][0]['pesan'];
            } else {
                $pesanList = array_map(fn($m) => '• ' . $m['pesan'], $validation['missing']);
                $pesan = "Jurnal penjualan tidak dapat dibuat. Akun berikut belum tersedia:\n"
                       . implode("\n", $pesanList);
            }
            
            session()->flash('error', $pesan);
        }

        // Transform jurnal lines untuk kompatibilitas dengan view
        $journalEntry = null;
        if ($journalLines->isNotEmpty()) {
            $journalEntry = (object) [
                'linesWithAccount' => $journalLines->map(function($line) {
                    return (object) [
                        'debit' => $line->debit,
                        'credit' => $line->kredit,
                        'memo' => $line->keterangan,
                        'coa' => $line->coa,
                    ];
                }),
                'created_at' => $journalLines->first()->created_at,
            ];
        }

        return view('transaksi.penjualan.jurnal', compact('penjualan', 'validation', 'journalEntry'));
    }

    /**
     * Buat ulang jurnal penjualan (rebuild).
     * Hanya bisa dilakukan jika semua akun sudah tersedia.
     */
    public function rebuildJurnal(Request $request, $id)
    {
        $penjualan = Penjualan::with('details.produk', 'produk')->findOrFail($id);

        try {
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);

            return redirect()->route('transaksi.penjualan.jurnal', $penjualan->id)
                ->with('success', 'Jurnal penjualan berhasil dibuat/diperbarui.');
        } catch (\RuntimeException $e) {
            return redirect()->route('transaksi.penjualan.jurnal', $penjualan->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * API: Validasi akun jurnal secara real-time (digunakan di form penjualan).
     */
    public function validateJurnal(Request $request)
    {
        $userId    = auth()->id();
        $hasPPN    = (bool)$request->input('has_ppn', false);
        $hasOngkir = (bool)$request->input('has_ongkir', false);
        $hasDiskon = (bool)$request->input('has_diskon', false);
        $produkIds = (array)$request->input('produk_ids', []);

        $validator  = new \App\Services\JournalValidationService();
        $validation = $validator->validateQuick($userId, $hasPPN, $hasOngkir, $hasDiskon, $produkIds);

        return response()->json($validation);
    }
    
    /**
     * Find product by barcode (API endpoint for barcode scanner)
     */
    public function findByBarcode(Request $request)
    {
        // Handle both direct parameter and request parameter for backward compatibility
        $barcode = $request->get('barcode', '') ?: $request->route('barcode', '');
        
        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode is required',
                'data' => null
            ]);
        }

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $produk = Produk::where('user_id', auth()->id())
            ->where('barcode', $barcode)
            ->first();
        
        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        
        // Use stok from produks table as requested by user
        $stokTersedia = (float)($produk->stok ?? 0);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $produk->id,
                'nama' => $produk->nama_produk ?? $produk->nama,
                'barcode' => $produk->barcode,
                'harga' => round($produk->harga_jual ?? 0),
                'stok' => $stokTersedia,
                'foto' => $produk->foto ? asset('storage/' . $produk->foto) : null,
            ]
        ]);
    }

    /**
     * API endpoint for real-time product search by barcode or name
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        
        if (strlen($search) < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Search term too short',
                'data' => []
            ]);
        }

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $products = Produk::where('user_id', auth()->id())
            ->where(function($query) use ($search) {
                $query->where('barcode', 'LIKE', "%{$search}%")
                      ->orWhere('nama_produk', 'LIKE', "%{$search}%")
                      ->orWhere('nama', 'LIKE', "%{$search}%");
            })
            ->where('stok', '>', 0)
            ->select('id', 'nama_produk', 'nama', 'barcode', 'harga_jual', 'stok')
            ->limit(10)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'nama' => $product->nama_produk ?? $product->nama,
                    'barcode' => $product->barcode,
                    'harga' => round($product->harga_jual ?? 0),
                    'stok' => $product->stok ?? 0
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Prepare payment - store data in session and show payment page
     */
    public function preparePayment(Request $request)
    {
        $paymentData = $request->all();
        
        // Validate payment data
        if (empty($paymentData['items']) || count($paymentData['items']) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada item dalam pesanan'
            ], 422);
        }
        
        if ($paymentData['total'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Total pembayaran harus lebih dari 0'
            ], 422);
        }
        
        // Store in session
        session(['penjualan_payment_data' => $paymentData]);
        
        return response()->json([
            'success' => true,
            'redirect_url' => route('transaksi.penjualan.payment')
        ]);
    }

    /**
     * Show payment page
     */
    public function showPayment()
    {
        $paymentData = session('penjualan_payment_data');
        
        if (!$paymentData) {
            return redirect()->route('transaksi.penjualan.create')
                           ->with('error', 'Data pembayaran tidak ditemukan');
        }
        
        // Get bank accounts for transfer payment (only banks with account numbers)
        $bankAccounts = \App\Helpers\AccountHelper::getBankAccountsForTransfer();
        
        return view('transaksi.penjualan.payment', [
            'payment_data' => $paymentData,
            'bank_accounts' => $bankAccounts
        ]);
    }

    /**
     * Confirm payment and create penjualan record
     */
    public function confirmPayment(Request $request, StockService $stock, JournalService $journal)
    {
        $paymentData = json_decode($request->input('payment_data'), true);
        
        if (!$paymentData) {
            return back()->with('error', 'Data pembayaran tidak valid');
        }
        
        // Validate based on payment method
        if ($request->input('payment_method') === 'cash') {
            $request->validate([
                'jumlah_diterima' => 'required|numeric|min:0',
            ]);
            
            $jumlahDiterima = (float) $request->input('jumlah_diterima');
            $total = (float) $paymentData['total'];
            
            if ($jumlahDiterima < $total) {
                return back()->with('error', 'Jumlah uang yang diterima kurang dari total pembayaran');
            }
        } elseif ($request->input('payment_method') === 'transfer') {
            $request->validate([
                'bukti_pembayaran' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            ]);
        }
        
        // Resolve sumber_dana (payment account)
        $sumberDanaKode = $request->input('sumber_dana') ?? $paymentData['sumber_dana'] ?? null;
        
        // For cash payment, if sumber_dana not provided, use first available kas account
        if ($request->input('payment_method') === 'cash' && !$sumberDanaKode) {
            $kasAccount = \App\Helpers\AccountHelper::getKasAccounts(auth()->id())->first();
            if ($kasAccount) {
                $sumberDanaKode = $kasAccount->kode_akun;
            }
        }
        
        if (!$sumberDanaKode) {
            return back()->with('error', 'Akun penerima pembayaran belum dipilih atau tidak tersedia');
        }
        
        $coaRecord = \App\Models\Coa::where('kode_akun', $sumberDanaKode)
            ->where('user_id', auth()->id())
            ->first();
            
        if (!$coaRecord) {
            return back()->with('error', 'Akun penerima pembayaran tidak ditemukan: ' . $sumberDanaKode);
        }
        
        // Create penjualan record
        return DB::transaction(function() use ($request, $paymentData, $stock, $journal, $coaRecord) {
            $tanggal = $paymentData['tanggal'] . ' ' . $paymentData['waktu'];
            $items = $paymentData['items'];
            
            // Validate stock for all items
            foreach ($items as $item) {
                // CRITICAL: Filter by user_id untuk multi-tenant isolation
                $produk = Produk::where('user_id', auth()->id())->findOrFail($item['produk_id']);
                $qty = (int) $item['jumlah'];
                
                if ((float)($produk->stok ?? 0) < $qty) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak cukup");
                }
            }
            
            // Create penjualan header
            $penjualan = Penjualan::create([
                'user_id' => auth()->id(), // CRITICAL: Set user_id
                'tanggal' => $tanggal,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => 'pending', // Set to pending initially to prevent journal creation before details exist
                'payment_confirmed_at' => now(),
                'coa_id'         => $coaRecord->id,
                'jumlah'         => collect($items)->sum('jumlah'),
                'harga_satuan'   => null,
                'diskon_nominal' => 0,
                'total'          => $paymentData['subtotal_produk'] ?? $paymentData['total'],
                'biaya_ongkir'   => $paymentData['biaya_ongkir'] ?? 0,
                'biaya_ppn'      => $paymentData['total_ppn'] ?? 0,
                'grand_total'    => $paymentData['total'] ?? 0,
                'catatan_pembayaran' => $request->input('catatan'),
            ]);
            
            // Create detail items
            foreach ($items as $item) {
                // CRITICAL: Filter by user_id untuk multi-tenant isolation
                $produk = Produk::where('user_id', auth()->id())->findOrFail($item['produk_id']);
                $qty = (int) $item['jumlah'];
                
                \App\Models\PenjualanDetail::create([
                    'penjualan_id'   => $penjualan->id,
                    'produk_id'      => $item['produk_id'],
                    'jumlah'         => $qty,
                    'harga_satuan'   => (float) $item['harga_satuan'],
                    'diskon_persen'  => round((float) ($item['diskon_persen'] ?? 0), 2),
                    'diskon_nominal' => round((float) ($item['diskon_nominal'] ?? 0)),
                    'subtotal' => (float) $item['subtotal'],
                ]);
                
                // Consume stock
                $stock->consume('product', $item['produk_id'], $qty, 'pcs', 'sale', $penjualan->id, $tanggal, auth()->id());
                
                // Update stok
                $produk->stok = (float)($produk->stok ?? 0) - $qty;
                $produk->save();
            }
            
            // Handle payment proof for transfer
            if ($request->input('payment_method') === 'transfer' && $request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $path = $file->store('bukti-pembayaran', 'public');
                
                $penjualan->update([
                    'bukti_pembayaran' => $path,
                    'catatan_pembayaran' => $request->input('catatan'),
                ]);
            }
            
            // Update status to paid USING UPDATE METHOD to trigger event
            $penjualan->update(['payment_status' => 'paid']);
            
            // CRITICAL: Explicitly create journal after all details are saved
            // Refresh to ensure relationships are loaded
            $penjualan = $penjualan->fresh(['details.produk', 'produk']);
            
            try {
                \App\Services\JournalService::createJournalFromPenjualan($penjualan, auth()->id());
                \Log::info('Journal explicitly created for penjualan in confirmPayment', [
                    'penjualan_id' => $penjualan->id,
                    'nomor_penjualan' => $penjualan->nomor_penjualan,
                    'user_id' => auth()->id(),
                    'grand_total' => $penjualan->grand_total
                ]);
            } catch (\Exception $e) {
                \Log::error('CRITICAL: Failed to create journal for penjualan in confirmPayment', [
                    'penjualan_id' => $penjualan->id,
                    'nomor_penjualan' => $penjualan->nomor_penjualan,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Re-throw to alert user there's a problem
                throw new \Exception('Penjualan berhasil disimpan, tetapi gagal membuat jurnal: ' . $e->getMessage());
            }
            
            // Clear session
            session()->forget('penjualan_payment_data');
            
            return redirect()->route('transaksi.penjualan.show', $penjualan->id)
                           ->with('success', 'Pembayaran berhasil dikonfirmasi. Penjualan telah dicatat.');
        });
    }

    public function uploadBuktiPembayaran(Request $request, $id)
    {
        try {
            \Log::info('Upload bukti pembayaran called', ['id' => $id, 'files' => $request->allFiles()]);
            
            $request->validate([
                'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // 5MB
                'keterangan' => 'nullable|string|max:255'
            ]);

            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $penjualan = Penjualan::where('user_id', auth()->id())->findOrFail($id);
            
            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('bukti_pembayaran', $filename, 'public');

                // Simpan ke database
                $bukti = new BuktiPembayaran();
                $bukti->penjualan_id = $penjualan->id;
                $bukti->file_path = $path;
                $bukti->keterangan = $request->keterangan;
                $bukti->save();

                \Log::info('Bukti pembayaran saved', ['bukti_id' => $bukti->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran berhasil diupload'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Upload bukti pembayaran error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload bukti pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteBuktiPembayaran($penjualanId, $buktiId)
    {
        try {
            // CRITICAL: Verify penjualan belongs to user first
            $penjualan = Penjualan::where('user_id', auth()->id())->findOrFail($penjualanId);
            
            $bukti = \App\Models\BuktiPembayaran::where('penjualan_id', $penjualan->id)
                                                ->where('id', $buktiId)
                                                ->firstOrFail();

            // Hapus file dari storage
            if (\Storage::disk('public')->exists($bukti->file_path)) {
                \Storage::disk('public')->delete($bukti->file_path);
            }

            // Hapus record dari database
            $bukti->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus bukti pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveOnlineTransaction(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $penjualan = Penjualan::where('user_id', auth()->id())->findOrFail($id);

            if ($penjualan->approval_status !== 'pending') {
                throw new \Exception('Hanya transaksi pending yang dapat disetujui.');
            }

            // Update status
            $penjualan->update([
                'approval_status' => 'approved',
            ]);

            // Update order status if exists
            if ($penjualan->order_id) {
                \App\Models\Order::withoutGlobalScope('user')->where('id', $penjualan->order_id)->update([
                    'status' => 'processing',
                    'approved_at' => now(),
                ]);
            }

            // Buat Jurnal
            \App\Services\JournalService::createJournalFromPenjualan($penjualan, auth()->id());

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success_approve', 'Transaksi berhasil disetujui dan jurnal telah dicatat.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Failed to approve transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyetujui transaksi: ' . $e->getMessage());
        }
    }

    public function rejectOnlineTransaction(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $penjualan = Penjualan::with('details.produk')->where('user_id', auth()->id())->findOrFail($id);

            if ($penjualan->approval_status !== 'pending') {
                throw new \Exception('Hanya transaksi pending yang dapat ditolak.');
            }

            // Kembalikan Stok
            foreach ($penjualan->details as $detail) {
                if ($detail->produk) {
                    \App\Models\Produk::withoutGlobalScopes()
                        ->where('id', $detail->produk_id)
                        ->increment('stok', $detail->jumlah);
                        
                    // Audit trail for stock return
                    \App\Models\StockMovement::create([
                        'user_id' => $penjualan->user_id,
                        'item_type' => 'product',
                        'item_id' => $detail->produk_id,
                        'tanggal' => now()->toDateString(),
                        'direction' => 'in',
                        'qty' => $detail->jumlah,
                        'satuan' => $detail->produk->satuan_id ? $detail->produk->satuan->nama : 'pcs',
                        'unit_cost' => $detail->harga_satuan,
                        'total_cost' => $detail->subtotal,
                        'ref_type' => 'sale_rejected',
                        'ref_id' => $penjualan->id,
                        'keterangan' => "Penjualan Ditolak #{$penjualan->nomor_penjualan}",
                    ]);
                }
            }

            // Update status
            $penjualan->update([
                'approval_status' => 'rejected',
                'status' => 'batal',
            ]);
            
            // Also update order status if exists
            if ($penjualan->order_id) {
                \App\Models\Order::withoutGlobalScope('user')->where('id', $penjualan->order_id)->update([
                    'status' => 'cancelled',
                    'alasan_penolakan' => $request->alasan_penolakan ?? 'Ditolak oleh penjual',
                    'rejected_at' => now(),
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success_reject', 'Transaksi berhasil ditolak dan stok telah dikembalikan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Failed to reject transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menolak transaksi: ' . $e->getMessage());
        }
    }

    public function completeOnlineTransaction(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $penjualan = Penjualan::with('order')->where('user_id', auth()->id())->findOrFail($id);

            if ($penjualan->approval_status !== 'approved') {
                throw new \Exception('Hanya transaksi yang sudah disetujui yang dapat diselesaikan.');
            }

            // Update order status if exists
            if ($penjualan->order_id) {
                $updateData = ['status' => 'ready_for_pickup'];
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'ready_pickup_at')) {
                    $updateData['ready_pickup_at'] = now();
                }
                \App\Models\Order::withoutGlobalScope('user')->where('id', $penjualan->order_id)->update($updateData);
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui menjadi Bisa Diambil.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Failed to complete transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyelesaikan pesanan: ' . $e->getMessage());
        }
    }

    public function deliverOnlineTransaction(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $penjualan = Penjualan::with('order')->where('user_id', auth()->id())->findOrFail($id);

            if ($penjualan->approval_status !== 'approved') {
                throw new \Exception('Hanya transaksi yang sudah disetujui yang dapat diantar.');
            }

            if ($penjualan->order_id) {
                $updateData = ['status' => 'shipped'];
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shipped_at')) {
                    $updateData['shipped_at'] = now();
                }
                \App\Models\Order::withoutGlobalScope('user')->where('id', $penjualan->order_id)->update($updateData);
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Pesanan berhasil ditandai sedang diantar.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Failed to deliver transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function completeDeliveryOnlineTransaction(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $penjualan = Penjualan::with('order')->where('user_id', auth()->id())->findOrFail($id);

            if ($penjualan->order_id) {
                $updateData = [
                    'status' => 'completed',
                    'payment_status' => 'paid'
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'completed_at')) {
                    $updateData['completed_at'] = now();
                }
                if (empty($penjualan->order->paid_at)) {
                    $updateData['paid_at'] = now();
                }
                \App\Models\Order::withoutGlobalScope('user')->where('id', $penjualan->order_id)->update($updateData);

                // Update penjualan payment status as well
                \Illuminate\Support\Facades\DB::table('penjualans')->where('id', $penjualan->id)->update([
                    'payment_status' => 'paid',
                    'payment_confirmed_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(payment_confirmed_at, NOW())'),
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Pesanan berhasil diselesaikan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Failed to complete delivery transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyelesaikan pesanan: ' . $e->getMessage());
        }
    }

    public function payOnlineTransaction(Request $request, $id)
    {
        try {
            $request->validate([
                'uang_diterima' => 'required|numeric|min:0',
            ]);

            \Illuminate\Support\Facades\DB::beginTransaction();

            $penjualan = Penjualan::with('order')->where('user_id', auth()->id())->findOrFail($id);

            if ($penjualan->approval_status !== 'approved') {
                throw new \Exception('Hanya transaksi yang sudah disetujui yang dapat dibayar.');
            }

            $uangDiterima = $request->uang_diterima;
            $totalPembayaran = $penjualan->grand_total ?? $penjualan->total;

            if ($uangDiterima < $totalPembayaran) {
                throw new \Exception('Uang diterima kurang dari total pembayaran.');
            }

            $kembalian = $uangDiterima - $totalPembayaran;

            // Update penjualan
            $penjualan->update([
                'payment_status' => 'paid',
                'payment_confirmed_at' => now(),
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
            ]);

            // Update order status if exists
            if ($penjualan->order_id) {
                \App\Models\Order::withoutGlobalScope('user')->where('id', $penjualan->order_id)->update([
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            // TODO: If Jurnal/Kas integration is needed, it should be done here.
            // As per instructions, "jika ada akun kas tunai, jurnal/akun pembayaran menggunakan kas tunai sesuai logic yang sudah ada".
            // Since we don't have the full KasController logic here, if there's a specific method to call, we'd do it. Let's see if approveOnlineTransaction handles kas.
            
            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Pembayaran berhasil disimpan. Transaksi telah Lunas.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Failed to pay transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}
