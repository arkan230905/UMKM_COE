<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Services\StockService;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::with(['produk','details']);
        
        // Filter by nomor transaksi
        if ($request->filled('nomor_transaksi')) {
            $query->where('nomor_penjualan', 'like', '%' . $request->nomor_transaksi . '%');
        }
        
        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        $penjualans = $query->with(['produk','details','returs'])->orderBy('tanggal','desc')->get();
        
        // Hitung ringkasan penjualan HARI INI saja
        $today = now()->format('Y-m-d');
        $penjualansHariIni = Penjualan::whereDate('tanggal', $today)->get();
        
        $totalPenjualan = 0;
        $totalProdukTerjual = 0;
        $totalProfit = 0;
        
        foreach ($penjualansHariIni as $penjualan) {
            $totalPenjualan += (float)($penjualan->total ?? 0);
            
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
        
        // Get return data for the return tab
        $salesReturns = \App\Models\ReturPenjualan::with(['penjualan', 'detailReturPenjualans.produk'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('transaksi.penjualan.index', compact('penjualans', 'totalPenjualan', 'jumlahTransaksiHariIni', 'totalProdukTerjual', 'totalProfit', 'salesReturns'));
    }

    public function create()
    {
        // Ambil produk dengan stok dari kolom stok di tabel produks
        $produks = Produk::all()->map(function($p) {
            // Gunakan stok dari tabel produks, bukan actual_stok dari StockLayer
            $p->stok_tersedia = (float)($p->stok ?? 0);
            return $p;
        });
        
        // Ambil akun kas/bank untuk dropdown
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.penjualan.create', compact('produks', 'kasbank'));
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
        
        return view('transaksi.penjualan.edit', compact('penjualan', 'produks', 'kasbank'));
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
        
        // Get bank accounts for transfer payment
        $bankAccounts = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        // Add label for sumber_dana
        $paymentData['sumber_dana_label'] = $bankAccounts
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
            
            // Create penjualan header
            $penjualan = Penjualan::create([
                'tanggal' => $tanggal,
                'payment_method' => $request->input('payment_method'),
                'jumlah' => collect($items)->sum('jumlah'),
                'harga_satuan' => null,
                'diskon_nominal' => 0,
                'total' => $paymentData['total'],
            ]);
            
            // Create detail items
            foreach ($items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $qty = (int) $item['jumlah'];
                
                \App\Models\PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $qty,
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon_persen' => $item['diskon_persen'] ?? 0,
                    'diskon_nominal' => 0,
                    'subtotal' => $item['subtotal'],
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
}
