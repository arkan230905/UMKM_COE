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

    public function create()
    {
        $produks = Produk::all();
        $pembelians = Pembelian::all();
        $penjualans = Penjualan::all();
        return view('transaksi.retur.create', compact('produks','pembelians','penjualans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sale,purchase',
            'ref_id' => 'required|integer',
            'tanggal' => 'required|date',
            'kompensasi' => 'required|in:refund,credit',
            'details' => 'required|array|min:1',
            'details.*.produk_id' => 'required|exists:produks,id',
            'details.*.qty' => 'required|numeric|min:0.0001',
        ]);

        $retur = Retur::create([
            'type' => $request->type,
            'ref_id' => $request->ref_id,
            'tanggal' => $request->tanggal,
            'kompensasi' => $request->kompensasi,
            'status' => 'approved',
            'alasan' => $request->input('alasan'),
            'memo' => $request->input('memo'),
        ]);

        foreach ($request->details as $d) {
            ReturDetail::create([
                'retur_id' => $retur->id,
                'produk_id' => (int)$d['produk_id'],
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
                $cashOrReceivable = $retur->kompensasi === 'credit' ? '102' : '101';
                if ($totalNominal > 0) {
                    $journal->post($tanggal, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                        ['code' => '401', 'debit' => (float)$totalNominal, 'credit' => 0],
                        ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],
                    ]);
                }
                if ($totalHpp > 0) {
                    $journal->post($tanggal, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                        ['code' => '123', 'debit' => (float)$totalHpp, 'credit' => 0],
                        ['code' => '501', 'debit' => 0, 'credit' => (float)$totalHpp],
                    ]);
                }
            } else {
                // Pembalikan pembelian
                if ($retur->kompensasi === 'refund') {
                    // Vendor mengembalikan uang ke kita: kas bertambah, hutang berkurang (atau langsung kas)
                    $journal->post($tanggal, 'purchase_return', (int)$retur->id, 'Retur Pembelian (Refund)', [
                        ['code' => '201', 'debit' => (float)$totalNominal, 'credit' => 0],
                        ['code' => '101', 'debit' => 0, 'credit' => (float)$totalNominal],
                    ]);
                } else {
                    // Credit note supplier: kurangi hutang usaha
                    $journal->post($tanggal, 'purchase_return', (int)$retur->id, 'Retur Pembelian (Credit)', [
                        ['code' => '201', 'debit' => (float)$totalNominal, 'credit' => 0],
                        ['code' => '201', 'debit' => 0, 'credit' => (float)$totalNominal],
                    ]);
                }
                if ($totalHpp > 0) {
                    // Persediaan keluar (sisi persediaan sudah dicatat lewat consume di stock movements); jurnal balancing persediaan
                    $journal->post($tanggal, 'purchase_return_inv', (int)$retur->id, 'Retur Pembelian - Persediaan', [
                        ['code' => '121', 'debit' => 0, 'credit' => (float)$totalHpp],
                        ['code' => '201', 'debit' => (float)$totalHpp, 'credit' => 0],
                    ]);
                }
            }

            $retur->status = 'posted';
            $retur->save();
        });

        return back()->with('success', 'Retur berhasil diposting.');
    }
}
