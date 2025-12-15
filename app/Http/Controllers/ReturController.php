<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retur;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\ReturDetail;
use App\Services\StockService;
use App\Services\JournalService;
use App\Models\StockLayer;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index()
    {
        $returs = Retur::with(['details.produk'])->orderBy('id','desc')->get();
        return view('transaksi.retur.index', compact('returs'));
    }

    public function indexPenjualan()
    {
        $returs = Retur::where('type', 'sale')
            ->with(['details.produk'])
            ->orderBy('id', 'desc')
            ->get();

        return view('transaksi.retur.index', compact('returs'));
    }

    public function create()
    {
        $produks = Produk::all();
        $bahanBakus = \App\Models\BahanBaku::all();
        $pembelians = Pembelian::all();
        $penjualans = Penjualan::all();
        return view('transaksi.retur.create', compact('produks', 'bahanBakus', 'pembelians','penjualans'));
    }

    public function createPenjualan(Request $request)
    {
        $penjualanId = $request->query('penjualan_id');

        return redirect()->route('transaksi.retur.create', array_filter([
            'type' => 'sale',
            'ref_id' => $penjualanId,
        ], fn ($v) => !is_null($v) && $v !== ''));
    }

    public function storePenjualan(Request $request)
    {
        $request->merge(['type' => 'sale']);
        return $this->store($request);
    }

    public function showPenjualan($id)
    {
        return redirect()->route('transaksi.retur.index');
    }

    public function destroyPenjualan($id)
    {
        $retur = Retur::findOrFail($id);
        $retur->delete();
        return redirect()->route('transaksi.retur.index')->with('success', 'Data retur berhasil dihapus.');
    }

    public function createPembelian(Request $request)
    {
        $pembelianId = $request->query('pembelian_id');
        
        if (!$pembelianId) {
            return redirect()->route('transaksi.pembelian.index')
                ->with('error', 'Silakan pilih pembelian yang ingin diretur dari daftar pembelian.');
        }
        
        $pembelian = Pembelian::with(['details.bahanBaku', 'vendor'])->findOrFail($pembelianId);
        
        return view('transaksi.retur-pembelian.create', compact('pembelian'));
    }

    public function indexPembelian()
    {
        $returs = Retur::where('type', 'purchase')
            ->with(['details'])
            ->orderBy('id', 'desc')
            ->get();
        
        return view('transaksi.retur-pembelian.index', compact('returs'));
    }

    public function storePembelian(Request $request, StockService $stock, JournalService $journal)
    {
        $request->validate([
            'pembelian_id' => 'required|exists:pembelians,id',
            'alasan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        // Tanggal retur otomatis hari ini
        $tanggalRetur = date('Y-m-d');

        $pembelian = Pembelian::with('details')->findOrFail($request->pembelian_id);
        
        // Filter items dengan qty > 0
        $items = collect($request->items)->filter(function($item) {
            return isset($item['qty']) && (float)$item['qty'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        DB::transaction(function() use ($request, $pembelian, $items, $stock, $journal, $tanggalRetur) {
            // Buat retur
            $retur = Retur::create([
                'type' => 'purchase',
                'ref_id' => $pembelian->id,
                'tanggal' => $tanggalRetur,
                'kompensasi' => 'refund', // default refund
                'status' => 'approved',
                'alasan' => $request->alasan,
                'memo' => $request->memo,
            ]);

            $totalNominal = 0;

            foreach ($items as $itemData) {
                $detail = $pembelian->details()->where('bahan_baku_id', $itemData['bahan_baku_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $hargaSatuan = (float)$detail->harga_satuan;
                $subtotal = $qty * $hargaSatuan;

                // Simpan detail retur
                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $detail->bahan_baku_id, // Untuk retur pembelian, ini bahan_baku_id
                    'ref_detail_id' => $detail->id,
                    'qty' => $qty,
                    'harga_satuan_asal' => $hargaSatuan,
                ]);

                // Kurangi stok bahan baku
                $bahanBaku = \App\Models\BahanBaku::find($detail->bahan_baku_id);
                if ($bahanBaku) {
                    $bahanBaku->stok -= $qty;
                    $bahanBaku->save();
                }

                $totalNominal += $subtotal;
            }

            // Posting jurnal
            if ($totalNominal > 0) {
                $journal->post($tanggalRetur, 'purchase_return', $retur->id, 'Retur Pembelian', [
                    ['code' => '1101', 'debit' => $totalNominal, 'credit' => 0],  // Kas (refund dari vendor)
                    ['code' => '1104', 'debit' => 0, 'credit' => $totalNominal],  // Persediaan Bahan Baku (berkurang)
                ]);
            }

            $retur->status = 'posted';
            $retur->save();
        });

        return redirect()->route('transaksi.retur.index')->with('success', 'Retur pembelian berhasil dibuat dan diposting.');
    }

    public function store(Request $request)
    {
        // Validasi dasar
        $request->validate([
            'type' => 'required|in:sale,purchase',
            'tanggal' => 'required|date',
            'kompensasi' => 'required|in:refund,credit',
            'details' => 'required|array|min:1',
            'details.*.produk_id' => 'required|integer',
            'details.*.qty' => 'required|numeric|min:0.0001',
        ]);

        // Validasi tambahan berdasarkan tipe
        if ($request->type === 'sale') {
            // Retur penjualan: validasi produk_id ada di tabel produks
            foreach ($request->details as $detail) {
                if (!Produk::find($detail['produk_id'])) {
                    return back()->withErrors(['details' => 'Produk tidak ditemukan'])->withInput();
                }
            }
        } else {
            // Retur pembelian: validasi produk_id (sebenarnya bahan_baku_id) ada di tabel bahan_bakus
            foreach ($request->details as $detail) {
                if (!\App\Models\BahanBaku::find($detail['produk_id'])) {
                    return back()->withErrors(['details' => 'Bahan baku tidak ditemukan'])->withInput();
                }
            }
        }

        $retur = Retur::create([
            'type' => $request->type,
            'ref_id' => $request->ref_id ?? 0,
            'tanggal' => $request->tanggal,
            'kompensasi' => $request->kompensasi,
            'status' => 'approved',
            'alasan' => $request->input('alasan'),
            'memo' => $request->input('memo'),
        ]);

        foreach ($request->details as $d) {
            ReturDetail::create([
                'retur_id' => $retur->id,
                'produk_id' => (int)$d['produk_id'], // Untuk retur pembelian, ini sebenarnya bahan_baku_id
                'ref_detail_id' => $d['ref_detail_id'] ?? null,
                'qty' => (float)$d['qty'],
                'harga_satuan_asal' => $d['harga_satuan_asal'] ?? null,
            ]);
        }

        return redirect()->route('transaksi.retur.index')->with('success', 'Retur dibuat (Approved). Lakukan Posting untuk menjurnal & stok.');
    }

    public function edit(Retur $retur)
    {
        $produks = Produk::all();
        $pembelians = Pembelian::all();
        return view('transaksi.retur.edit', compact('retur', 'produks', 'pembelians'));
    }

    public function update(Request $request, Retur $retur)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|in:draft,approved,posted',
        ]);
        $retur->update($request->only('tanggal','status','alasan','memo'));
        return redirect()->route('transaksi.retur.index')->with('success', 'Retur diperbarui.');
    }

    public function destroy(Retur $retur)
    {
        $retur->delete();
        return redirect()->route('transaksi.retur.index')->with('success', 'Data retur berhasil dihapus.');
    }

    public function approve($id)
    {
        $retur = Retur::findOrFail($id);
        if ($retur->status !== 'draft') {
            return back()->with('success', 'Retur sudah di-approve.');
        }
        $retur->status = 'approved';
        $retur->save();
        return back()->with('success', 'Retur di-approve.');
    }

    public function post($id, StockService $stock, JournalService $journal)
    {
        $retur = Retur::with(['details.produk'])->findOrFail($id);
        if ($retur->status === 'posted') {
            return back()->with('success', 'Retur sudah posted.');
        }

        DB::transaction(function () use ($retur, $stock, $journal) {
            $tanggal = $retur->tanggal;
            $totalNominal = 0.0; $totalHpp = 0.0;

            foreach ($retur->details as $d) {
                $prod = $d->produk;
                $qty = (float)$d->qty;
                // Ambil average cost saat ini (fallback harga produk jika belum ada layer)
                $avg = (float) StockLayer::where('item_type','product')
                    ->where('item_id', $prod->id)
                    ->selectRaw('CASE WHEN SUM(remaining_qty) > 0 THEN SUM(remaining_qty*unit_cost)/SUM(remaining_qty) ELSE 0 END as avg')
                    ->value('avg');
                if ($avg <= 0) $avg = (float)($d->harga_satuan_asal ?? $prod->harga ?? 0);

                if ($retur->type === 'sale') {
                    // Retur penjualan: stok IN barang jadi, nominal retur gunakan harga jual asal jika ada, hpp dari average
                    $stock->addLayer('product', (int)$prod->id, $qty, 'pcs', (float)$avg, 'sale_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($d->harga_satuan_asal ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                    $totalNominal += $lineNominal;
                    $totalHpp += $lineHpp;
                } else {
                    // Retur pembelian: stok OUT (keluar) dengan biaya average
                    $lineHpp = $stock->consume('product', (int)$prod->id, $qty, 'pcs', 'purchase_return', (int)$retur->id, $tanggal);
                    $totalHpp += (float)$lineHpp;
                    $lineNominal = $avg * $qty; // sebagai nilai retur ke vendor
                    $totalNominal += $lineNominal;
                }
            }

            if ($retur->type === 'sale') {
                // Pembalikan penjualan
                $cashOrReceivable = $retur->kompensasi === 'credit' ? '1102' : '1101';  // Bank atau Kas
                if ($totalNominal > 0) {
                    $journal->post($tanggal, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                        ['code' => '4101', 'debit' => (float)$totalNominal, 'credit' => 0],  // Penjualan (pembalik)
                        ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],  // Kas/Bank
                    ]);
                }
                if ($totalHpp > 0) {
                    $journal->post($tanggal, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                        ['code' => '1107', 'debit' => (float)$totalHpp, 'credit' => 0],  // Persediaan Barang Jadi
                        ['code' => '5001', 'debit' => 0, 'credit' => (float)$totalHpp],  // HPP (pembalik)
                    ]);
                }
            } else {
                // Pembalikan pembelian
                if ($retur->kompensasi === 'refund') {
                    // Vendor mengembalikan uang ke kita: kas bertambah, hutang berkurang (atau langsung kas)
                    $journal->post($tanggal, 'purchase_return', (int)$retur->id, 'Retur Pembelian (Refund)', [
                        ['code' => '2101', 'debit' => (float)$totalNominal, 'credit' => 0],  // Hutang Usaha (berkurang)
                        ['code' => '1101', 'debit' => 0, 'credit' => (float)$totalNominal],  // Kas (berkurang karena refund)
                    ]);
                } else {
                    // Credit note supplier: kurangi hutang usaha
                    $journal->post($tanggal, 'purchase_return', (int)$retur->id, 'Retur Pembelian (Credit)', [
                        ['code' => '2101', 'debit' => (float)$totalNominal, 'credit' => 0],  // Hutang Usaha (berkurang)
                        ['code' => '2101', 'debit' => 0, 'credit' => (float)$totalNominal],  // Credit Note (netting)
                    ]);
                }
                if ($totalHpp > 0) {
                    // Persediaan keluar (sisi persediaan sudah dicatat lewat consume di stock movements); jurnal balancing persediaan
                    $journal->post($tanggal, 'purchase_return_inv', (int)$retur->id, 'Retur Pembelian - Persediaan', [
                        ['code' => '1104', 'debit' => 0, 'credit' => (float)$totalHpp],  // Persediaan Bahan Baku (berkurang)
                        ['code' => '2101', 'debit' => (float)$totalHpp, 'credit' => 0],  // Hutang Usaha (berkurang)
                    ]);
                }
            }

            $retur->status = 'posted';
            $retur->save();
        });

        return back()->with('success', 'Retur berhasil diposting.');
    }
}
