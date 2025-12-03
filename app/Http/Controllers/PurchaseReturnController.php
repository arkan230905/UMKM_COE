<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function create(Pembelian $pembelian)
    {
        $pembelian->load(['details.bahanBaku']);

        $existingReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($pembelian) {
            $q->where('pembelian_id', $pembelian->id);
        })->get()->groupBy('pembelian_detail_id');

        return view('transaksi.pembelian.retur-create', [
            'pembelian' => $pembelian,
            'existingReturns' => $existingReturns,
        ]);
    }

    public function store(Request $request, Pembelian $pembelian)
    {
        $pembelian->load(['details']);

        $data = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.pembelian_detail_id' => 'required|integer|exists:pembelian_details,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
        ]);

        // Filter item dengan quantity > 0
        $items = collect($data['items'])
            ->filter(fn ($row) => ($row['quantity'] ?? 0) > 0)
            ->all();

        if (empty($items)) {
            return back()->withInput()->with('error', 'Minimal satu barang harus diretur.');
        }

        return DB::transaction(function () use ($pembelian, $data, $items) {
            $return = PurchaseReturn::create([
                'pembelian_id' => $pembelian->id,
                'return_date' => $data['return_date'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($items as $row) {
                /** @var PembelianDetail $detail */
                $detail = $pembelian->details->firstWhere('id', (int) $row['pembelian_detail_id']);
                if (!$detail) {
                    continue;
                }

                // Hitung qty retur sebelumnya untuk detail ini
                $returnedQty = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($pembelian) {
                        $q->where('pembelian_id', $pembelian->id);
                    })
                    ->where('pembelian_detail_id', $detail->id)
                    ->sum('quantity');

                $requested = (float) $row['quantity'];
                $maxAllow = (float) $detail->jumlah - (float) $returnedQty;

                if ($requested <= 0) {
                    continue;
                }

                if ($requested - $maxAllow > 1e-9) {
                    throw new \RuntimeException('Qty retur untuk '.$detail->bahanBaku->nama_bahan.' melebihi qty pembelian tersisa.');
                }

                $subtotal = $requested * (float) $detail->harga_satuan;
                $total += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'pembelian_detail_id' => $detail->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'unit' => $detail->satuan,
                    'quantity' => $requested,
                    'unit_price' => $detail->harga_satuan,
                    'subtotal' => $subtotal,
                ]);
            }

            if ($total <= 0) {
                throw new \RuntimeException('Tidak ada item retur yang valid.');
            }

            $return->total_return_amount = $total;
            $return->save();

            return redirect()
                ->route('transaksi.purchase-returns.show', $return->id)
                ->with('success', 'Retur pembelian berhasil dibuat, silakan review dan approve.');
        });
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['pembelian.vendor', 'items.bahanBaku']);

        return view('transaksi.pembelian.retur-show', [
            'retur' => $purchaseReturn,
        ]);
    }

    public function approve(PurchaseReturn $purchaseReturn, StockService $stock)
    {
        if ($purchaseReturn->status === 'completed') {
            return back()->with('info', 'Retur ini sudah di-approve sebelumnya.');
        }

        $purchaseReturn->load(['items.bahanBaku', 'pembelian']);

        DB::transaction(function () use ($purchaseReturn, $stock) {
            foreach ($purchaseReturn->items as $item) {
                // Kurangi stok bahan baku via StockService (stock out)
                $bahan = $item->bahanBaku;
                $qty = (float) $item->quantity;

                if ($qty <= 0) {
                    continue;
                }

                // Validasi stok cukup
                $available = $stock->getAvailableQty('material', $bahan->id);
                if ($qty - $available > 1e-9) {
                    throw new \RuntimeException('Stok bahan baku '.$bahan->nama_bahan.' tidak cukup untuk retur.');
                }

                $stock->consume(
                    'material',
                    $bahan->id,
                    $qty,
                    $item->unit ?? (string)($bahan->satuan->kode ?? $bahan->satuan->nama ?? 'unit'),
                    'purchase_return',
                    $purchaseReturn->id,
                    $purchaseReturn->return_date->toDateString(),
                );
            }

            $purchaseReturn->status = 'completed';
            $purchaseReturn->save();
        });

        return redirect()
            ->route('transaksi.purchase-returns.show', $purchaseReturn->id)
            ->with('success', 'Retur pembelian berhasil di-approve dan stok sudah diperbarui.');
    }
}
