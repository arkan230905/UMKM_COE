<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Services\StockService;
use App\Services\JournalService;

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
        
        $penjualans = $query->with(['produk','details','returs'])->orderBy('tanggal','desc')->orderBy('id','desc')->get();
        
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
        // Ambil produk dengan stok dari tabel produks
        $produks = Produk::all()->map(function($p) {
            $p->stok_tersedia = (float)($p->stok ?? 0);
            return $p;
        });
        
        // Ambil akun kas/bank untuk dropdown
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.penjualan.create', compact('produks', 'kasbank'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        
        // Multi-item path
        if (is_array($request->produk_id)) {
            // Get valid kas/bank codes from database
            $validKasBankCodes = \App\Helpers\AccountHelper::getKasBankAccounts()->pluck('kode_akun')->toArray();
            
            $request->validate([
                'tanggal' => 'required|date',
                'waktu' => 'required|date_format:H:i',
                'payment_method' => 'required|in:cash,transfer,credit',
                'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', $validKasBankCodes),
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => 'required|exists:produks,id',
                'jumlah' => 'required|array',
                'jumlah.*' => 'required|integer|min:1',
                'harga_satuan' => 'required|array',
                'harga_satuan.*' => 'required|string', // Ubah ke string karena format dari JS adalah "Rp 32.000"
                'diskon_persen' => 'nullable|array',
                'diskon_persen.*' => 'nullable|numeric|min:0|max:100',
            ]);

            $tanggal = $request->tanggal . ' ' . ($request->waktu ?? now()->format('H:i'));
            $produkIds = $request->produk_id;
            $jumlahArr = $request->jumlah;
            // Override harga jual dengan harga_jual dari master produk
            $hargaArr = [];
            foreach ($produkIds as $i => $pid) {
                $p = Produk::findOrFail($pid);
                $hargaArr[$i] = (float) ($p->harga_jual ?? 0);
            }
            $diskonPctArr = $request->diskon_persen ?? [];

            // Validasi stok cukup per item menggunakan StockService
            foreach ($produkIds as $i => $pid) {
                $p = Produk::findOrFail($pid);
                $qty = (int)($jumlahArr[$i] ?? 0); // Cast to integer
                
                // Get available stock from StockService untuk konsistensi
                $availableStock = (float) $p->actual_stok;
                
                if ($qty > $availableStock + 1e-9) {
                    return back()->withErrors([
                        'stok' => "Stok {$p->nama_produk} tidak cukup! Stok tersedia: " . number_format($availableStock, 0, ',', '.') . ", Anda input: " . number_format($qty, 0, ',', '.')
                    ])->withInput();
                }
            }

            // Hitung total header
            $grand = 0; $totalQtyHeader = 0; $totalDiscHeader = 0;
            foreach ($produkIds as $i => $pid) {
                $qty = (int)($jumlahArr[$i] ?? 0); // Cast to integer
                $price = (float)($hargaArr[$i] ?? 0);
                $pct = (float)($diskonPctArr[$i] ?? 0);
                $sub = $qty * $price;
                $discNom = max($sub * ($pct/100.0), 0);
                $line = max($sub - $discNom, 0);
                $grand += $line;
                $totalQtyHeader += $qty;
                $totalDiscHeader += $discNom;
            }

            // Get additional costs
            $biayaOngkir = (float) ($request->biaya_ongkir ?? 0);
            $biayaService = (float) ($request->biaya_service ?? 0);
            $ppnPersen = (float) ($request->ppn_persen ?? 0);
            
            // Calculate PPN
            $ppnBase = $grand + $biayaOngkir + $biayaService;
            $totalPPN = $ppnBase * ($ppnPersen / 100);
            
            // Calculate final total
            $finalTotal = $grand + $biayaOngkir + $biayaService + $totalPPN;

            $penjualan = Penjualan::create([
                'tanggal' => $tanggal,
                'payment_method' => $request->payment_method,
                'jumlah' => $totalQtyHeader,
                'harga_satuan' => null,
                'diskon_nominal' => $totalDiscHeader,
                'total' => $finalTotal,
            ]);

            // Simpan detail & konsumsi stok per item
            $cogsSum = 0.0;
            $errorsBelowCost = [];
            foreach ($produkIds as $i => $pid) {
                $prod = Produk::findOrFail($pid);
                $qty = (int)($jumlahArr[$i] ?? 0); // Cast to integer
                $price = (float)($hargaArr[$i] ?? 0);
                $pct = (float)($diskonPctArr[$i] ?? 0);
                $sub = $qty * $price;
                $discNom = max($sub * ($pct/100.0), 0);
                $line = max($sub - $discNom, 0);

                // Guard: jangan jual di bawah HPP FIFO (estimasi tanpa konsumsi)
                $estCogs = $stock->estimateCost('product', $prod->id, $qty);
                if ($estCogs <= 0) {
                    // fallback ke Harga BOM per unit
                    $sumBom = (float) \App\Models\Bom::where('produk_id', $prod->id)->sum('total_biaya');
                    $btkl = (float) ($prod->btkl_default ?? 0);
                    $bop  = (float) ($prod->bop_default ?? 0);
                    $estCogs = ($sumBom + $btkl + $bop) * $qty;
                }
                if ($line + 0.0001 < $estCogs) { // toleransi floating
                    $errorsBelowCost[] = "Harga jual di bawah HPP untuk {$prod->nama_produk}. HPP: Rp " . number_format($estCogs,0,',','.') . ", Subtotal (setelah diskon): Rp " . number_format($line,0,',','.');
                }

                \App\Models\PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $prod->id,
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'diskon_persen' => $pct,
                    'diskon_nominal' => $discNom,
                    'subtotal' => $line,
                ]);

                // FIFO OUT dan pengurangan stok
                $cogs = $stock->consume('product', $prod->id, $qty, 'pcs', 'sale', $penjualan->id, $tanggal);
                $cogsVal = (float) $cogs;
                if ($cogsVal <= 0) {
                    $sumBom = (float) \App\Models\Bom::where('produk_id', $prod->id)->sum('total_biaya');
                    $btkl = (float) ($prod->btkl_default ?? 0);
                    $bop  = (float) ($prod->bop_default ?? 0);
                    $cogsVal = ($sumBom + $btkl + $bop) * $qty;
                }
                $cogsSum += $cogsVal;
                $prod->stok = (float)($prod->stok ?? 0) - $qty;
                $prod->save();
            }

            if (!empty($errorsBelowCost)) {
                // Rollback by throwing validation via redirect back
                return redirect()->back()->withErrors($errorsBelowCost)->withInput();
            }

            return redirect()->route('transaksi.penjualan.index')
                             ->with('success', 'Data penjualan (multi item) berhasil ditambahkan.');
        }

        // Single-item fallback (tetap mendukung)
        // Get valid kas/bank codes from database
        $validKasBankCodes = \App\Helpers\AccountHelper::getKasBankAccounts()->pluck('kode_akun')->toArray();
        
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'tanggal' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,credit',
            'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', $validKasBankCodes),
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|string', // Ubah ke string karena format dari JS
            'diskon_nominal' => 'nullable|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $qty = (int)$request->jumlah; // Cast to integer
        // Override harga jual dengan harga_jual dari master produk
        $produk = Produk::findOrFail($request->produk_id);
        $price = (float) ($produk->harga_jual ?? 0);
        $disc = (float)($request->diskon_nominal ?? 0);
        if ($disc <= 0 && $request->filled('diskon_persen')) {
            $disc = max((($qty * $price) * ((float)$request->diskon_persen) / 100.0), 0);
        }
        $total = max(($qty * $price) - $disc, 0);

        // Validasi stok cukup
        if ((int)($produk->stok ?? 0) < $qty) {
            return back()->withErrors([
                'stok' => "Stok tidak cukup! Stok tersedia: " . number_format((float)($produk->stok ?? 0), 0, ',', '.') . ", Anda input: " . number_format($qty, 0, ',', '.')
            ])->withInput();
        }

        // Guard: jangan jual di bawah HPP FIFO (estimasi tanpa konsumsi)
        $estCogs = $stock->estimateCost('product', (int)$request->produk_id, $qty);
        if ($estCogs <= 0) {
            $sumBom = (float) \App\Models\Bom::where('produk_id', $produk->id)->sum('total_biaya');
            $btkl = (float) ($produk->btkl_default ?? 0);
            $bop  = (float) ($produk->bop_default ?? 0);
            $estCogs = ($sumBom + $btkl + $bop) * $qty;
        }
        if ($total + 0.0001 < $estCogs) {
            return back()->withErrors(["Harga jual di bawah HPP. HPP: Rp " . number_format($estCogs,0,',','.') . ", Total (setelah diskon): Rp " . number_format($total,0,',','.')])->withInput();
        }

        $penjualan = Penjualan::create([
            'produk_id' => $request->produk_id,
            'tanggal' => $request->tanggal,
            'payment_method' => $request->payment_method,
            'jumlah' => $qty,
            'harga_satuan' => $price,
            'diskon_nominal' => $disc,
            'total' => $total,
        ]);

        $tanggal = $request->tanggal;
        $qty     = (float)$request->jumlah;
        $cogs = $stock->consume('product', $produk->id, $qty, 'pcs', 'sale', $penjualan->id, $tanggal);
        if ((float)$cogs <= 0) {
            $sumBom = (float) \App\Models\Bom::where('produk_id', $produk->id)->sum('total_biaya');
            $btkl = (float) ($produk->btkl_default ?? 0);
            $bop  = (float) ($produk->bop_default ?? 0);
            $cogs = ($sumBom + $btkl + $bop) * $qty;
        }
        $produk->stok = (float)($produk->stok ?? 0) - $qty;
        $produk->save();

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan berhasil ditambahkan.');
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
        
        // Ambil produk dengan stok dari tabel produks
        $produks = Produk::all()->map(function($p) {
            $p->stok_tersedia = (float)($p->stok ?? 0);
            return $p;
        });
        
        // Ambil akun kas/bank untuk dropdown
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.penjualan.edit', compact('penjualan', 'produks', 'kasbank'));
    }

    public function update(Request $request, $id, StockService $stock, JournalService $journal)
    {
        $penjualan = Penjualan::with('details')->findOrFail($id);
        
        // Multi-item path
        if (is_array($request->produk_id)) {
            // Get valid kas/bank codes from database
            $validKasBankCodes = \App\Helpers\AccountHelper::getKasBankAccounts()->pluck('kode_akun')->toArray();
            
            $request->validate([
                'tanggal' => 'required|date',
                'payment_method' => 'required|in:cash,transfer,credit',
                'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', $validKasBankCodes),
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => 'required|exists:produks,id',
                'jumlah' => 'required|array',
                'jumlah.*' => 'required|integer|min:1',
                'harga_satuan' => 'required|array',
                'harga_satuan.*' => 'required|string',
                'diskon_persen' => 'nullable|array',
                'diskon_persen.*' => 'nullable|numeric|min:0|max:100',
            ]);

            $tanggal = $request->tanggal;
            $produkIds = $request->produk_id;
            $jumlahArr = $request->jumlah;
            
            // Override harga jual dengan harga_jual dari master produk
            $hargaArr = [];
            foreach ($produkIds as $i => $pid) {
                $p = Produk::findOrFail($pid);
                $hargaArr[$i] = (float) ($p->harga_jual ?? 0);
            }
            $diskonPctArr = $request->diskon_persen ?? [];

            // Kembalikan stok dari detail lama
            foreach ($penjualan->details as $oldDetail) {
                $oldProduk = Produk::find($oldDetail->produk_id);
                if ($oldProduk) {
                    $oldProduk->stok = (float)($oldProduk->stok ?? 0) + $oldDetail->jumlah;
                    $oldProduk->save();
                }
            }

            // Validasi stok cukup per item
            foreach ($produkIds as $i => $pid) {
                $p = Produk::findOrFail($pid);
                $qty = (int)($jumlahArr[$i] ?? 0);
                
                if ($qty > (float)($p->stok ?? 0)) {
                    return back()->withErrors([
                        'stok' => "Stok {$p->nama_produk} tidak cukup! Stok tersedia: " . number_format((float)($p->stok ?? 0), 0, ',', '.') . ", Anda input: " . number_format($qty, 0, ',', '.')
                    ])->withInput();
                }
            }

            // Hitung total header
            $grand = 0; $totalQtyHeader = 0; $totalDiscHeader = 0;
            foreach ($produkIds as $i => $pid) {
                $qty = (int)($jumlahArr[$i] ?? 0);
                $price = (float)($hargaArr[$i] ?? 0);
                $pct = (float)($diskonPctArr[$i] ?? 0);
                $sub = $qty * $price;
                $discNom = max($sub * ($pct/100.0), 0);
                $line = max($sub - $discNom, 0);
                $grand += $line;
                $totalQtyHeader += $qty;
                $totalDiscHeader += $discNom;
            }

            // Get additional costs
            $biayaOngkir = (float) ($request->biaya_ongkir ?? 0);
            $biayaService = (float) ($request->biaya_service ?? 0);
            $ppnPersen = (float) ($request->ppn_persen ?? 0);
            
            // Calculate PPN
            $ppnBase = $grand + $biayaOngkir + $biayaService;
            $totalPPN = $ppnBase * ($ppnPersen / 100);
            
            // Calculate final total
            $finalTotal = $grand + $biayaOngkir + $biayaService + $totalPPN;

            // Update penjualan header
            $penjualan->update([
                'tanggal' => $tanggal,
                'payment_method' => $request->payment_method,
                'jumlah' => $totalQtyHeader,
                'harga_satuan' => null,
                'diskon_nominal' => $totalDiscHeader,
                'total' => $finalTotal,
            ]);

            // Hapus detail lama
            $penjualan->details()->delete();

            // Simpan detail baru & konsumsi stok per item
            foreach ($produkIds as $i => $pid) {
                $prod = Produk::findOrFail($pid);
                $qty = (int)($jumlahArr[$i] ?? 0);
                $price = (float)($hargaArr[$i] ?? 0);
                $pct = (float)($diskonPctArr[$i] ?? 0);
                $sub = $qty * $price;
                $discNom = max($sub * ($pct/100.0), 0);
                $line = max($sub - $discNom, 0);

                \App\Models\PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $prod->id,
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'diskon_persen' => $pct,
                    'diskon_nominal' => $discNom,
                    'subtotal' => $line,
                ]);

                // Kurangi stok
                $prod->stok = (float)($prod->stok ?? 0) - $qty;
                $prod->save();
            }

            return redirect()->route('transaksi.penjualan.index')
                             ->with('success', 'Data penjualan berhasil diupdate.');
        }

        // Single-item fallback
        // Get valid kas/bank codes from database
        $validKasBankCodes = \App\Helpers\AccountHelper::getKasBankAccounts()->pluck('kode_akun')->toArray();
        
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'tanggal' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,credit',
            'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', $validKasBankCodes),
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|string',
            'diskon_nominal' => 'nullable|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        // Kembalikan stok lama
        if ($penjualan->produk_id) {
            $oldProduk = Produk::find($penjualan->produk_id);
            if ($oldProduk) {
                $oldProduk->stok = (float)($oldProduk->stok ?? 0) + $penjualan->jumlah;
                $oldProduk->save();
            }
        }

        $qty = (int)$request->jumlah;
        $produk = Produk::findOrFail($request->produk_id);
        $price = (float) ($produk->harga_jual ?? 0);
        $disc = (float)($request->diskon_nominal ?? 0);
        if ($disc <= 0 && $request->filled('diskon_persen')) {
            $disc = max((($qty * $price) * ((float)$request->diskon_persen) / 100.0), 0);
        }
        $total = max(($qty * $price) - $disc, 0);

        // Validasi stok cukup
        if ((int)($produk->stok ?? 0) < $qty) {
            return back()->withErrors([
                'stok' => "Stok tidak cukup! Stok tersedia: " . number_format((float)($produk->stok ?? 0), 0, ',', '.') . ", Anda input: " . number_format($qty, 0, ',', '.')
            ])->withInput();
        }

        // Update penjualan
        $penjualan->update([
            'produk_id' => $request->produk_id,
            'tanggal' => $request->tanggal,
            'payment_method' => $request->payment_method,
            'jumlah' => $qty,
            'harga_satuan' => $price,
            'diskon_nominal' => $disc,
            'total' => $total,
        ]);

        // Kurangi stok baru
        $produk->stok = (float)($produk->stok ?? 0) - $qty;
        $produk->save();

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan berhasil diupdate.');
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
        
        // Calculate available stock using stock movements for accuracy
        $stokMasuk = \DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $produk->id)
            ->where('direction', 'in')
            ->sum('qty');
        
        $stokKeluar = \DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $produk->id)
            ->where('direction', 'out')
            ->sum('qty');
        
        $stokTersedia = max(0, $stokMasuk - $stokKeluar);
        
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
            ->where('stok', '>', 0) // Only products with stock
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

}
