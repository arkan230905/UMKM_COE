<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Produk;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function create(Penjualan $penjualan)
    {
        $penjualan->load(['details.produk']);

        $existingReturns = SalesReturnItem::whereHas('salesReturn', function ($q) use ($penjualan) {
            $q->where('penjualan_id', $penjualan->id);
        })->get()->groupBy('penjualan_detail_id');

        return view('transaksi.penjualan.retur-create', [
            'penjualan' => $penjualan,
            'existingReturns' => $existingReturns,
        ]);
    }

    public function store(Request $request, Penjualan $penjualan)
    {
        $penjualan->load(['details']);

        $data = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.penjualan_detail_id' => 'required|integer|exists:penjualan_details,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
        ]);

        $items = collect($data['items'])
            ->filter(fn ($row) => ($row['quantity'] ?? 0) > 0)
            ->all();

        if (empty($items)) {
            return back()->withInput()->with('error', 'Minimal satu barang harus diretur.');
        }

        return DB::transaction(function () use ($penjualan, $data, $items) {
            $return = SalesReturn::create([
                'penjualan_id' => $penjualan->id,
                'return_date' => $data['return_date'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($items as $row) {
                /** @var PenjualanDetail $detail */
                $detail = $penjualan->details->firstWhere('id', (int) $row['penjualan_detail_id']);
                if (!$detail) {
                    continue;
                }

                $returnedQty = SalesReturnItem::whereHas('salesReturn', function ($q) use ($penjualan) {
                        $q->where('penjualan_id', $penjualan->id);
                    })
                    ->where('penjualan_detail_id', $detail->id)
                    ->sum('quantity');

                $requested = (float) $row['quantity'];
                $maxAllow = (float) $detail->jumlah - (float) $returnedQty;

                if ($requested <= 0) {
                    continue;
                }

                if ($requested - $maxAllow > 1e-9) {
                    throw new \RuntimeException('Qty retur untuk '.$detail->produk->nama_produk.' melebihi qty jual tersisa.');
                }

                $subtotal = $requested * (float) $detail->harga_satuan;
                $total += $subtotal;

                SalesReturnItem::create([
                    'sales_return_id' => $return->id,
                    'penjualan_detail_id' => $detail->id,
                    'produk_id' => $detail->produk_id,
                    'unit' => 'pcs',
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
                ->route('transaksi.sales-returns.show', $return->id)
                ->with('success', 'Retur penjualan berhasil dibuat, silakan review dan approve.');
        });
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['penjualan', 'items.produk']);

        return view('transaksi.penjualan.retur-show', [
            'retur' => $salesReturn,
        ]);
    }

    public function approve(SalesReturn $salesReturn, StockService $stock)
    {
        if ($salesReturn->status === 'completed') {
            return back()->with('info', 'Retur ini sudah di-approve sebelumnya.');
        }

        $salesReturn->load(['items.produk']);

        DB::transaction(function () use ($salesReturn, $stock) {
            foreach ($salesReturn->items as $item) {
                /** @var Produk $produk */
                $produk = $item->produk;
                $qty = (float) $item->quantity;

                if ($qty <= 0) {
                    continue;
                }

                // Tambah stok produk kembali (stock in)
                $stock->addLayer(
                    'product',
                    $produk->id,
                    $qty,
                    'pcs',
                    (float) $item->unit_price,
                    'sales_return',
                    $salesReturn->id,
                    $salesReturn->return_date->toDateString(),
                );

                $produk->stok = (float)($produk->stok ?? 0) + $qty;
                $produk->save();
            }

            $salesReturn->status = 'completed';
            $salesReturn->save();
        });

        return redirect()
            ->route('transaksi.sales-returns.show', $salesReturn->id)
            ->with('success', 'Retur penjualan berhasil di-approve dan stok sudah diperbarui.');
    }
}
