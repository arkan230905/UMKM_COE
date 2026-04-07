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
        $bahanBakus = \App\Models\BahanBaku::all();
        $pembelians = Pembelian::all();
        $penjualans = Penjualan::all();
        return view('transaksi.retur.create', compact('produks', 'bahanBakus', 'pembelians','penjualans'));
    }

    public function indexPembelian()
    {
        $returs = Retur::where('type', 'purchase')
            ->with(['details'])
            ->orderBy('id', 'desc')
            ->get();
        
        return view('transaksi.retur-pembelian.index', compact('returs'));
    }

    public function indexPenjualan()
    {
        $returs = Retur::where('type', 'sale')
            ->with(['details.produk', 'penjualan'])
            ->orderBy('id', 'desc')
            ->get();
        
        return view('transaksi.retur-penjualan.index', compact('returs'));
    }

    public function createPenjualan(Request $request)
    {
        $penjualanId = $request->query('penjualan_id');
        
        if (!$penjualanId) {
            return redirect()->route('transaksi.penjualan.index')
                ->with('error', 'Silakan pilih penjualan yang ingin diretur dari daftar penjualan.');
        }
        
        $penjualan = Penjualan::with(['details.produk'])->findOrFail($penjualanId);
        
        return view('transaksi.retur-penjualan.create', compact('penjualan'));
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

    public function storePenjualan(Request $request, StockService $stock, JournalService $journal)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tanggal' => 'required|date',
            'alasan' => 'required|string',
            'kompensasi' => 'required|in:refund,credit,replace',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.produk_id' => 'required|exists:produks,id',
        ]);

        $items = collect($request->items)->filter(function($item) {
            return isset($item['qty']) && isset($item['selected']) && (float)$item['qty'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        $tanggalRetur = $request->tanggal;

        $penjualan = Penjualan::with(['details'])->findOrFail($request->penjualan_id);
        
        DB::transaction(function() use ($request, $penjualan, $items, $stock, $journal, $tanggalRetur) {
            $retur = Retur::create([
                'type' => 'sale',
                'ref_id' => $penjualan->id,
                'tanggal' => $tanggalRetur,
                'kompensasi' => $request->kompensasi,
                'status' => 'approved',
                'alasan' => $request->alasan,
                'memo' => $request->catatan,
            ]);

            $totalNominal = 0;
            $totalHpp = 0;

            foreach ($items as $itemData) {
                $detail = $penjualan->details()->where('produk_id', $itemData['produk_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $actualHPP = $detail->produk->getHPPForSaleDate($penjualan->tanggal);
                $margin = ($detail->harga_satuan - $actualHPP) * $qty;
                $subtotal = $qty * $detail->harga_satuan;

                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $itemData['produk_id'],
                    'qty' => $qty,
                    'harga_satuan_asal' => $detail->harga_satuan,
                    'hpp_asal' => $actualHPP,
                    'margin' => $margin,
                    'subtotal' => $subtotal,
                ]);

                $stock->addLayer('product', (int)$itemData['produk_id'], $qty, 'pcs', $actualHPP, 'sale_return', (int)$retur->id, $tanggalRetur);

                $totalNominal += $subtotal;
                $totalHpp += $actualHPP * $qty;
            }

            $cashOrReceivable = $request->kompensasi === 'credit' ? '1102' : '1101';
            if ($totalNominal > 0) {
                $journal->post($tanggalRetur, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                    ['code' => '41', 'debit' => (float)$totalNominal, 'credit' => 0],
                    ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],
                ]);
            }
            if ($totalHpp > 0) {
                $journal->post($tanggalRetur, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                    ['code' => '1107', 'debit' => (float)$totalHpp, 'credit' => 0],
                    ['code' => '5001', 'debit' => 0, 'credit' => (float)$totalHpp],
                ]);
            }
        });

        return redirect()->route('transaksi.retur-penjualan.index')->with('success', 'Retur penjualan berhasil dibuat dan diposting.');
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

        $tanggalRetur = date('Y-m-d');

        $pembelian = Pembelian::with('details')->findOrFail($request->pembelian_id);
        
        $items = collect($request->items)->filter(function($item) {
            return isset($item['qty']) && (float)$item['qty'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0.']);
        }

        DB::transaction(function() use ($request, $pembelian, $items, $stock, $journal, $tanggalRetur) {
            $retur = Retur::create([
                'type' => 'purchase',
                'ref_id' => $pembelian->id,
                'tanggal' => $tanggalRetur,
                'kompensasi' => 'refund',
                'status' => 'approved',
                'alasan' => $request->alasan,
                'memo' => $request->memo,
            ]);

            $totalNominal = 0;

            foreach ($items as $itemData) {
                $detail = $pembelian->details()->where('bahan_baku_id', $itemData['bahan_baku_id'])->first();
                
                if (!$detail) continue;

                $qty = (float)$itemData['qty'];
                $avg = $detail->bahanBaku->averagePurchasePrice($pembelian->tanggal);
                if ($avg <= 0) $avg = (float)($d->harga_satuan_asal ?? $prod->harga ?? 0);

                if ($retur->type === 'sale') {
                    $stock->addLayer('product', (int)$prod->id, $qty, 'pcs', (float)$avg, 'sale_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($d->harga_satuan_asal ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                } else {
                    $stock->remove('bahan_baku', (int)$itemData['bahan_baku_id'], $qty, 'kg', (float)$avg, 'purchase_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($detail->harga_satuan ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                }

                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $itemData['produk_id'] ?? null,
                    'bahan_baku_id' => $itemData['bahan_baku_id'] ?? null,
                    'qty' => $qty,
                    'harga_satuan_asal' => $detail->harga_satuan ?? $detail->harga_satuan_asal ?? 0,
                    'hpp_asal' => $avg,
                    'margin' => $lineNominal - $lineHpp,
                    'subtotal' => $lineNominal,
                ]);

                $totalNominal += $lineNominal;
            }

            if ($retur->type === 'sale') {
                $cashOrReceivable = $retur->kompensasi === 'credit' ? '1102' : '1101';
                if ($totalNominal > 0) {
                    $journal->post($tanggal, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                        ['code' => '41', 'debit' => (float)$totalNominal, 'credit' => 0],
                        ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],
                    ]);
                }
                if ($totalHpp > 0) {
                    $journal->post($tanggal, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                        ['code' => '1107', 'debit' => (float)$totalHpp, 'credit' => 0],
                        ['code' => '5001', 'debit' => 0, 'credit' => (float)$totalHpp],
                    ]);
                }
            }
        });

        return redirect()->route('transaksi.retur.index')->with('success', 'Retur pembelian berhasil dibuat dan diposting.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'type' => 'required|in:sale,purchase',
            'alasan' => 'required|string',
            'kompensasi' => 'required|in:refund,credit,replace',
        ]);

        if ($request->type === 'sale') {
            foreach ($request->details as $detail) {
                if (!Produk::find($detail['produk_id'])) {
                    return back()->withErrors(['details' => 'Produk tidak ditemukan'])->withInput();
                }
            }
        } elseif ($request->type === 'purchase') {
            foreach ($request->details as $detail) {
                if (!\App\Models\BahanBaku::find($detail['bahan_baku_id'])) {
                    return back()->withErrors(['details' => 'Bahan baku tidak ditemukan'])->withInput();
                }
            }
        }

        $tanggalRetur = $request->tanggal;

        DB::transaction(function() use ($request, $tanggalRetur) {
            $retur = Retur::create([
                'type' => $request->type,
                'ref_id' => $request->ref_id,
                'tanggal' => $tanggalRetur,
                'kompensasi' => $request->kompensasi,
                'status' => 'draft',
                'alasan' => $request->alasan,
                'memo' => $request->memo,
                'details' => $request->details,
            ]);

            foreach ($request->details as $detail) {
                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $detail['produk_id'] ?? null,
                    'bahan_baku_id' => $detail['bahan_baku_id'] ?? null,
                    'qty' => $detail['qty'],
                    'harga_satuan_asal' => $detail['harga_satuan_asal'],
                    'hpp_asal' => $detail['hpp_asal'],
                    'margin' => $detail['margin'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('transaksi.retur.index')->with('success', 'Retur dibuat (Approved). Lakukan Posting untuk menjurnal dan stok.');
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
            'alasan' => 'required|string',
            'kompensasi' => 'required|in:refund,credit,replace',
            'memo' => 'nullable|string',
        ]);

        $retur->update([
            'tanggal' => $request->tanggal,
            'alasan' => $request->alasan,
            'kompensasi' => $request->kompensasi,
            'memo' => $request->memo,
        ]);

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
            return back()->with('error', 'Hanya retur dengan status draft yang bisa di-approve.');
        }

        $retur->update(['status' => 'approved']);
        return back()->with('success', 'Retur di-approve.');
    }

    public function post($id, StockService $stock, JournalService $journal)
    {
        $retur = Retur::with(['details.produk'])->findOrFail($id);
        if ($retur->status === 'posted') {
            return back()->with('error', 'Retur sudah diposting.');
        }

        $tanggal = $retur->tanggal;
        $totalNominal = 0;
        $totalHpp = 0;

        DB::transaction(function() use ($retur, $stock, $journal, $tanggal, &$totalNominal, &$totalHpp) {
            foreach ($retur->details as $d) {
                $qty = $d->qty;
                $avg = $d->hpp_asal;
                $prod = $d->produk;

                if ($retur->type === 'sale') {
                    $stock->addLayer('product', (int)$prod->id, $qty, 'pcs', (float)$avg, 'sale_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($d->harga_satuan_asal ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                } else {
                    $stock->remove('bahan_baku', (int)$d->bahan_baku_id, $qty, 'kg', (float)$avg, 'purchase_return', (int)$retur->id, $tanggal);
                    $lineNominal = (float)($detail->harga_satuan ?? 0) * $qty;
                    $lineHpp = $avg * $qty;
                }

                $totalNominal += $lineNominal;
                $totalHpp += $lineHpp;
            }

            if ($retur->type === 'sale') {
                $cashOrReceivable = $retur->kompensasi === 'credit' ? '1102' : '1101';
                if ($totalNominal > 0) {
                    $journal->post($tanggal, 'sale_return', (int)$retur->id, 'Retur Penjualan', [
                        ['code' => '41', 'debit' => (float)$totalNominal, 'credit' => 0],
                        ['code' => $cashOrReceivable, 'debit' => 0, 'credit' => (float)$totalNominal],
                    ]);
                }
                if ($totalHpp > 0) {
                    $journal->post($tanggal, 'sale_return_cogs', (int)$retur->id, 'Retur Penjualan - Pembalik HPP', [
                        ['code' => '1107', 'debit' => (float)$totalHpp, 'credit' => 0],
                        ['code' => '5001', 'debit' => 0, 'credit' => (float)$totalHpp],
                    ]);
                }
            }
        });

        $retur->update(['status' => 'posted']);
        return back()->with('success', 'Retur berhasil diposting.');
    }

    public function showPembelian($id)
    {
        $retur = Retur::with(['details.bahanBaku'])->findOrFail($id);
        return view('transaksi.retur-pembelian.show', compact('retur'));
    }

    public function showPenjualan($id)
    {
        $retur = Retur::with(['details.produk', 'penjualan'])->findOrFail($id);
        return view('transaksi.retur-penjualan.show', compact('retur'));
    }

    public function destroyPembelian($id)
    {
        $retur = Retur::findOrFail($id);
        if ($retur->type !== 'purchase') {
            return back()->with('error', 'Ini bukan retur pembelian.');
        }
        $retur->delete();
        return redirect()->route('transaksi.retur-pembelian.index')->with('success', 'Data retur pembelian berhasil dihapus.');
    }

    public function destroyPenjualan($id)
    {
        $retur = Retur::findOrFail($id);
        if ($retur->type !== 'sale') {
            return back()->with('error', 'Ini bukan retur penjualan.');
        }
        $retur->delete();
        return redirect()->route('transaksi.retur-penjualan.index')->with('success', 'Data retur penjualan berhasil dihapus.');
    }
}
