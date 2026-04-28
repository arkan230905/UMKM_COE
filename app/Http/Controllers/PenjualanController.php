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
        // ── 1. Main list query (single eager-load, no duplicate) ──────────────
        $query = Penjualan::with(['details.produk', 'returs']);

        if ($request->filled('nomor_transaksi')) {
            $query->where('nomor_penjualan', 'like', '%' . $request->nomor_transaksi . '%');
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $penjualans = $query->orderBy('tanggal', 'desc')->get();

        // ── 2. Summary stats – use DB aggregates, NO HPP loop ────────────────
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Aggregate totals directly in SQL (no PHP loops, no N+1)
        $statsToday = DB::table('penjualans')
            ->whereDate('tanggal', $today)
            ->selectRaw('
                COUNT(*)                         AS jumlah_transaksi,
                COALESCE(SUM(total), 0)          AS total_penjualan,
                COALESCE(SUM(biaya_ongkir), 0)   AS total_ongkir,
                COALESCE(SUM(diskon_nominal), 0) AS total_diskon
            ')
            ->first();

        $statsYesterday = DB::table('penjualans')
            ->whereDate('tanggal', $yesterday)
            ->selectRaw('
                COUNT(*)                         AS jumlah_transaksi,
                COALESCE(SUM(total), 0)          AS total_penjualan,
                COALESCE(SUM(biaya_ongkir), 0)   AS total_ongkir,
                COALESCE(SUM(diskon_nominal), 0) AS total_diskon
            ')
            ->first();

        // Qty terjual hari ini & kemarin via details join
        $qtyToday = DB::table('penjualan_details')
            ->join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->whereDate('penjualans.tanggal', $today)
            ->sum('penjualan_details.jumlah');

        $qtyYesterday = DB::table('penjualan_details')
            ->join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->whereDate('penjualans.tanggal', $yesterday)
            ->sum('penjualan_details.jumlah');

        // Profit: harga_satuan - harga_pokok (stored on produk) × jumlah
        // Use produk.harga_pokok as HPP proxy – avoids expensive getActualHPP() loop
        $profitToday = DB::table('penjualan_details')
            ->join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('produks', 'produks.id', '=', 'penjualan_details.produk_id')
            ->whereDate('penjualans.tanggal', $today)
            ->selectRaw('SUM((penjualan_details.harga_satuan - COALESCE(produks.harga_pokok, 0)) * penjualan_details.jumlah) AS profit')
            ->value('profit') ?? 0;

        $profitYesterday = DB::table('penjualan_details')
            ->join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('produks', 'produks.id', '=', 'penjualan_details.produk_id')
            ->whereDate('penjualans.tanggal', $yesterday)
            ->selectRaw('SUM((penjualan_details.harga_satuan - COALESCE(produks.harga_pokok, 0)) * penjualan_details.jumlah) AS profit')
            ->value('profit') ?? 0;

        // Assign to named variables expected by the view
        $totalPenjualan        = (float) $statsToday->total_penjualan;
        $totalOngkir           = (float) $statsToday->total_ongkir;
        $totalDiskon           = (float) $statsToday->total_diskon;
        $jumlahTransaksiHariIni = (int)  $statsToday->jumlah_transaksi;
        $totalProdukTerjual    = (float) $qtyToday;
        $totalProfit           = (float) $profitToday;

        $totalPenjualanKemarin        = (float) $statsYesterday->total_penjualan;
        $totalOngkirKemarin           = (float) $statsYesterday->total_ongkir;
        $totalDiskonKemarin           = (float) $statsYesterday->total_diskon;
        $jumlahTransaksiKemarin       = (int)   $statsYesterday->jumlah_transaksi;
        $totalProdukTerjualKemarin    = (float) $qtyYesterday;
        $totalProfitKemarin           = (float) $profitYesterday;

        // ── 3. Percentage changes ─────────────────────────────────────────────
        $pct = fn($now, $prev) => $prev > 0 ? (($now - $prev) / $prev) * 100 : ($now > 0 ? 100 : 0);

        $penjualanChange = $pct($totalPenjualan, $totalPenjualanKemarin);
        $transaksiChange = $pct($jumlahTransaksiHariIni, $jumlahTransaksiKemarin);
        $produkChange    = $pct($totalProdukTerjual, $totalProdukTerjualKemarin);
        $ongkirChange    = $pct($totalOngkir, $totalOngkirKemarin);
        $diskonChange    = $pct($totalDiskon, $totalDiskonKemarin);
        $profitChange    = $pct($totalProfit, $totalProfitKemarin);

        // ── 4. Returns ────────────────────────────────────────────────────────
        $salesReturns = \App\Models\ReturPenjualan::with(['penjualan', 'detailReturPenjualans.produk'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaksi.penjualan.index', compact(
            'penjualans',
            'totalPenjualan',
            'jumlahTransaksiHariIni',
            'totalProdukTerjual',
            'totalProfit',
            'totalOngkir',
            'totalDiskon',
            'salesReturns',
            'penjualanChange',
            'transaksiChange',
            'produkChange',
            'ongkirChange',
            'diskonChange',
            'profitChange'
        ));
    }

    public function create()
    {
        // Ambil produk dengan stok dari kolom stok di tabel produks
        $produks = Produk::all()->map(function($p) {
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
        $penjualan = Penjualan::with('details.produk', 'produk', 'returPenjualans.detailReturPenjualans.produk')->findOrFail($id);
        
        return view('transaksi.penjualan.show', compact('penjualan'));
    }

    public function struk($id)
    {
        $penjualan = Penjualan::with('details.produk', 'produk')->findOrFail($id);
        
        // Ambil data perusahaan
        $dataPerusahaan = \App\Models\Perusahaan::first();
        
        return view('transaksi.penjualan.struk', compact('penjualan', 'dataPerusahaan'));
    }

    public function edit($id)
    {
        $penjualan = Penjualan::with('details.produk')->findOrFail($id);
        
        // Ambil produk dengan stok dari kolom stok di tabel produks
        $produks = Produk::all()->map(function($p) {
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
        // Update logic here
        return redirect()->route('transaksi.penjualan.index');
    }

    public function destroy($id, JournalService $journal)
    {
        $penjualan = Penjualan::findOrFail($id);
        // Hapus jurnal terkait penjualan
        $journal->deleteByRef('sale', (int)$penjualan->id);
        $journal->deleteByRef('sale_cogs', (int)$penjualan->id);
        // Hapus data penjualan
        $penjualan->delete();

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan dan jurnal terkait berhasil dihapus.');
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

        $produk = Produk::where('barcode', $barcode)->first();
        
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

        $products = Produk::where(function($query) use ($search) {
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
        
        // Add label for sumber_dana
        $paymentData['sumber_dana_label'] = \App\Helpers\AccountHelper::getKasBankAccounts()
            ->where('kode_akun', $paymentData['sumber_dana'])
            ->first()
            ?->nama_akun ?? 'Tidak diketahui';
        
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
                'bukti_pembayaran' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            ]);
        }
        
        // Create penjualan record
        return DB::transaction(function() use ($request, $paymentData, $stock, $journal) {
            $tanggal = $paymentData['tanggal'] . ' ' . $paymentData['waktu'];
            $items = $paymentData['items'];
            
            // Validate stock for all items
            foreach ($items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $qty = (int) $item['jumlah'];
                
                if ((float)($produk->stok ?? 0) < $qty) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak cukup");
                }
            }
            
            // Resolve coa_id dari sumber_dana (kode akun yang dipilih user)
            $sumberDanaKode = $paymentData['sumber_dana'] ?? null;
            $coaId = null;
            if ($sumberDanaKode) {
                $coaRecord = \App\Models\Coa::where('kode_akun', $sumberDanaKode)->first();
                $coaId = $coaRecord?->id;
            }

            // Create penjualan header
            $penjualan = Penjualan::create([
                'tanggal'        => $tanggal,
                'payment_method' => $request->input('payment_method'),
                'coa_id'         => $coaId,
                'user_id'        => auth()->id(),
                'jumlah'         => collect($items)->sum('jumlah'),
                'harga_satuan'   => null,
                'diskon_nominal' => 0,
                'total'          => $paymentData['total'],
                'biaya_ongkir'   => $paymentData['biaya_ongkir'] ?? 0,
                'biaya_ppn'      => $paymentData['total_ppn'] ?? 0,
                'grand_total'    => $paymentData['total'] ?? 0,
            ]);
            
            // Create detail items
            foreach ($items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $qty = (int) $item['jumlah'];
                
                \App\Models\PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $qty,
                    'harga_satuan' => (float) $item['harga_satuan'],
                    'diskon_persen' => (float) ($item['diskon_persen'] ?? 0),
                    'diskon_nominal' => 0,
                    'subtotal' => (float) $item['subtotal'],
                ]);
                
                // Consume stock
                $stock->consume('product', $item['produk_id'], $qty, 'pcs', 'sale', $penjualan->id, $tanggal);
                
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
            
            // Create journal entries
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);
            
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

            $penjualan = Penjualan::findOrFail($id);
            
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
            $bukti = \App\Models\BuktiPembayaran::where('penjualan_id', $penjualanId)
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
}
