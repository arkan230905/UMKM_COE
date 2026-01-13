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
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $penjualans = $query->orderBy('tanggal','desc')->get();
        
        return view('transaksi.penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        // Ambil produk dengan stok tersedia
        $produks = Produk::all()->map(function($p) {
            // Hitung stok dari stock_movements
            $stokMasuk = \DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('direction', 'in')
                ->sum('qty');
            
            $stokKeluar = \DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('direction', 'out')
                ->sum('qty');
            
            $p->stok_tersedia = $stokMasuk - $stokKeluar;
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
            $request->validate([
                'tanggal' => 'required|date',
                'payment_method' => 'required|in:cash,transfer,credit',
                'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', \App\Helpers\AccountHelper::KAS_BANK_CODES),
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => 'required|exists:produks,id',
                'jumlah' => 'required|array',
                'jumlah.*' => 'required|numeric|min:0.0001',
                'harga_satuan' => 'required|array',
                'harga_satuan.*' => 'required|numeric|min:0',
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

            // Validasi stok cukup per item
            foreach ($produkIds as $i => $pid) {
                $p = Produk::findOrFail($pid);
                $qty = (float)($jumlahArr[$i] ?? 0);
                
                // Hitung stok tersedia
                $stokMasuk = \DB::table('stock_movements')
                    ->where('item_type', 'product')
                    ->where('item_id', $pid)
                    ->where('direction', 'in')
                    ->sum('qty');
                
                $stokKeluar = \DB::table('stock_movements')
                    ->where('item_type', 'product')
                    ->where('item_id', $pid)
                    ->where('direction', 'out')
                    ->sum('qty');
                
                $stokTersedia = $stokMasuk - $stokKeluar;
                
                if ($qty > $stokTersedia) {
                    return back()->withErrors([
                        'stok' => "Stok {$p->nama_produk} tidak cukup! Stok tersedia: " . number_format($stokTersedia, 0, ',', '.') . ", Anda input: " . number_format($qty, 0, ',', '.')
                    ])->withInput();
                }
            }
            $errors = [];
            foreach ($produkIds as $i => $pid) {
                $prod = Produk::findOrFail($pid);
                $qty = (float)($jumlahArr[$i] ?? 0);
                if ((float)($prod->stok ?? 0) < $qty) {
                    $errors[] = "Stok produk {$prod->nama_produk} tidak cukup. Butuh $qty, tersedia " . (float)($prod->stok ?? 0);
                }
            }
            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }

            // Hitung total header
            $grand = 0; $totalQtyHeader = 0; $totalDiscHeader = 0;
            foreach ($produkIds as $i => $pid) {
                $qty = (float)($jumlahArr[$i] ?? 0);
                $price = (float)($hargaArr[$i] ?? 0);
                $pct = (float)($diskonPctArr[$i] ?? 0);
                $sub = $qty * $price;
                $discNom = max($sub * ($pct/100.0), 0);
                $line = max($sub - $discNom, 0);
                $grand += $line;
                $totalQtyHeader += $qty;
                $totalDiscHeader += $discNom;
            }

            $penjualan = Penjualan::create([
                'tanggal' => $tanggal,
                'payment_method' => $request->payment_method,
                'jumlah' => $totalQtyHeader,
                'harga_satuan' => null,
                'diskon_nominal' => $totalDiscHeader,
                'total' => $grand,
            ]);

            // Simpan detail & konsumsi stok per item
            $cogsSum = 0.0;
            $errorsBelowCost = [];
            foreach ($produkIds as $i => $pid) {
                $prod = Produk::findOrFail($pid);
                $qty = (float)($jumlahArr[$i] ?? 0);
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

            // Jurnal penjualan: Dr Kas/Bank/Piutang ; Cr Penjualan (4101)
            // HPP: Dr HPP (5001) ; Cr Persediaan Barang Jadi (1107)
            // Tentukan akun berdasarkan metode pembayaran
            if ($request->payment_method === 'cash' || $request->payment_method === 'transfer') {
                // Gunakan sumber dana yang dipilih user
                $accountCode = $request->sumber_dana;
            } else {
                $accountCode = '1103';  // Piutang Usaha (kredit)
            }
            
            $journal->post($tanggal, 'sale', (int)$penjualan->id, 'Penjualan Produk', [
                ['code' => $accountCode, 'debit' => (float)$penjualan->total, 'credit' => 0],
                ['code' => '4101', 'debit' => 0, 'credit' => (float)$penjualan->total],  // Penjualan Produk
            ]);
            if (($cogsSum ?? 0) > 0) {
                $journal->post($tanggal, 'sale_cogs', (int)$penjualan->id, 'HPP Penjualan', [
                    ['code' => '5001', 'debit' => (float)$cogsSum, 'credit' => 0],  // HPP
                    ['code' => '1107', 'debit' => 0, 'credit' => (float)$cogsSum],  // Persediaan Barang Jadi
                ]);
            }

            return redirect()->route('transaksi.penjualan.index')
                             ->with('success', 'Data penjualan (multi item) berhasil ditambahkan.');
        }

        // Single-item fallback (tetap mendukung)
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'tanggal' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,credit',
            'jumlah' => 'required|numeric|min:0.0001',
            'harga_satuan' => 'required|numeric|min:0',
            'diskon_nominal' => 'nullable|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $qty = (float)$request->jumlah;
        // Override harga jual dengan harga_jual dari master produk
        $produk = Produk::findOrFail($request->produk_id);
        $price = (float) ($produk->harga_jual ?? 0);
        $disc = (float)($request->diskon_nominal ?? 0);
        if ($disc <= 0 && $request->filled('diskon_persen')) {
            $disc = max((($qty * $price) * ((float)$request->diskon_persen) / 100.0), 0);
        }
        $total = max(($qty * $price) - $disc, 0);

        // Validasi stok cukup
        if ((float)($produk->stok ?? 0) < $qty) {
            return back()->withErrors(["Stok produk {$produk->nama_produk} tidak cukup. Butuh $qty, tersedia " . (float)($produk->stok ?? 0)])->withInput();
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

        // Jurnal penjualan & HPP
        // Tentukan akun berdasarkan metode pembayaran
        if ($request->payment_method === 'cash' || $request->payment_method === 'transfer') {
            // Gunakan sumber dana yang dipilih user
            $accountCode = $request->sumber_dana ?? '1101';
        } else {
            $accountCode = '1103';  // Piutang Usaha (kredit)
        }
        
        $journal->post($tanggal, 'sale', (int)$penjualan->id, 'Penjualan Produk', [
            ['code' => $accountCode, 'debit' => (float)$penjualan->total, 'credit' => 0],
            ['code' => '4101', 'debit' => 0, 'credit' => (float)$penjualan->total],  // Penjualan Produk
        ]);
        if (($cogs ?? 0) > 0) {
            $journal->post($tanggal, 'sale_cogs', (int)$penjualan->id, 'HPP Penjualan', [
                ['code' => '5001', 'debit' => (float)$cogs, 'credit' => 0],  // HPP
                ['code' => '1107', 'debit' => 0, 'credit' => (float)$cogs],  // Persediaan Barang Jadi
            ]);
        }

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        
        // Ambil produk dengan stok tersedia
        $produks = Produk::all()->map(function($p) {
            // Hitung stok dari stock_movements
            $stokMasuk = \DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('direction', 'in')
                ->sum('qty');
            
            $stokKeluar = \DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $p->id)
                ->where('direction', 'out')
                ->sum('qty');
            
            $p->stok_tersedia = $stokMasuk - $stokKeluar;
            return $p;
        });
        
        return view('transaksi.penjualan.edit', compact('penjualan', 'produks'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'jumlah' => 'required|numeric|min:1',
            'total' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        $penjualan = Penjualan::findOrFail($id);
        $penjualan->update($request->all());

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
    public function findByBarcode($barcode)
    {
        $produk = Produk::where('barcode', $barcode)->first();
        
        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        
        // Calculate available stock
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
        
        $stokTersedia = $stokMasuk - $stokKeluar;
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $produk->id,
                'barcode' => $produk->barcode,
                'nama_produk' => $produk->nama_produk,
                'harga_jual' => $produk->harga_jual ?? 0,
                'stok_tersedia' => $stokTersedia,
                'foto' => $produk->foto ? asset('storage/' . $produk->foto) : null,
            ]
        ]);
    }
}
