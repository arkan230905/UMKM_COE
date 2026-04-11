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
        $pembelian->load(['details.bahanBaku', 'details.bahanPendukung']);

        $existingReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($pembelian) {
            $q->where('pembelian_id', $pembelian->id);
        })->get()->groupBy('pembelian_detail_id');

        return view('transaksi.retur-pembelian.create', [
            'pembelian' => $pembelian,
            'existingReturns' => $existingReturns,
        ]);
    }

    public function store(Request $request, Pembelian $pembelian)
    {
        $pembelian->load(['details']);

        $data = $request->validate([
            'return_date' => 'required|date',
            'alasan' => 'nullable|string|max:255',
            'jenis_retur' => 'required|string|in:tukar_barang,refund',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.pembelian_detail_id' => 'required|integer|exists:pembelian_details,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
        ], [
            'jenis_retur.required' => 'Jenis retur harus dipilih.',
            'jenis_retur.in' => 'Jenis retur yang dipilih tidak valid.',
            'alasan.max' => 'Alasan retur tidak boleh lebih dari 255 karakter.',
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
                'reason' => $data['alasan'] ?? null,
                'jenis_retur' => $data['jenis_retur'],
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
                    $materialName = '';
                    if ($detail->bahan_baku_id && $detail->bahanBaku) {
                        $materialName = $detail->bahanBaku->nama_bahan;
                    } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                        $materialName = $detail->bahanPendukung->nama_bahan;
                    } else {
                        $materialName = 'Item ID: ' . $detail->id;
                    }
                    throw new \RuntimeException('Qty retur untuk '.$materialName.' melebihi qty pembelian tersisa.');
                }

                $subtotal = $requested * (float) $detail->harga_satuan;
                $total += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'pembelian_detail_id' => $detail->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'bahan_pendukung_id' => $detail->bahan_pendukung_id,
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
                ->with('success', 'Retur pembelian berhasil dibuat dengan status pending.');
        });
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['pembelian.vendor', 'items.bahanBaku']);

        return view('transaksi.pembelian.retur-show', [
            'retur' => $purchaseReturn,
        ]);
    }

    public function approve(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status === 'completed') {
            return back()->with('info', 'Retur ini sudah di-approve sebelumnya.');
        }

        // Simply change status to completed - stock changes will be handled by proses method
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->status = 'completed';
            $purchaseReturn->save();
        });

        return redirect()
            ->route('transaksi.purchase-returns.show', $purchaseReturn->id)
            ->with('success', 'Retur pembelian berhasil di-approve. Stok akan diperbarui sesuai jenis retur.');
    }
}
