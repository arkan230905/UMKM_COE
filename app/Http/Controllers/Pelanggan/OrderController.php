<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Retur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('pelanggan.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        // Cek ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.produk');

        $orderReturns = Retur::with('details')
            ->where('type', 'sale')
            ->where(function ($query) use ($order) {
                $query->where('ref_id', $order->id)
                      ->orWhere('referensi_id', $order->id);
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $returnedMap = [];
        foreach ($orderReturns as $retur) {
            foreach ($retur->details as $detail) {
                $refDetailId = $detail->ref_detail_id;
                if (!$refDetailId) {
                    continue;
                }

                $qty = (float) ($detail->qty ?? $detail->qty_retur ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $returnedMap[$refDetailId] = ($returnedMap[$refDetailId] ?? 0) + $qty;
            }
        }

        foreach ($order->items as $item) {
            $returned = (float) ($returnedMap[$item->id] ?? 0);
            $remaining = max($item->qty - $returned, 0);

            $item->setAttribute('qty_returned', $returned);
            $item->setAttribute('qty_remaining', $remaining);
        }

        $redirectStatus = request('redirect_status');
        if ($redirectStatus) {
            $messages = [
                'success' => [
                    'type' => 'success',
                    'message' => '<i class="bi bi-check-circle"></i> Pembayaran berhasil! Pesanan Anda sedang diproses.'
                ],
                'pending' => [
                    'type' => 'warning',
                    'message' => '<i class="bi bi-exclamation-triangle"></i> Pembayaran Anda masih menunggu penyelesaian. Silakan cek kembali statusnya nanti.'
                ],
                'error' => [
                    'type' => 'danger',
                    'message' => '<i class="bi bi-x-circle"></i> Pembayaran gagal atau dibatalkan. Silakan coba lagi atau pilih metode lain.'
                ],
            ];

            if (isset($messages[$redirectStatus])) {
                session()->flash('midtrans_status', $messages[$redirectStatus]);
            }
        }

        return view('pelanggan.order-detail', [
            'order' => $order,
            'orderReturns' => $orderReturns,
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending' || !in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        try {
            DB::transaction(function () use ($order) {
                $order->loadMissing('items.produk');

                foreach ($order->items as $item) {
                    if ($item->produk) {
                        $item->produk->increment('stok', $item->qty);
                    }
                }

                if ($order->penjualan) {
                    $order->penjualan->details()->delete();
                    $order->penjualan->delete();
                    $order->update(['penjualan_id' => null]);
                }

                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                    'snap_token' => null,
                ]);
            });

            Notification::createNotification(
                $order->user_id,
                'order_cancelled',
                'Pesanan Dibatalkan',
                "Pesanan {$order->nomor_order} berhasil dibatalkan sesuai permintaan Anda.",
                ['order_id' => $order->id]
            );

            return redirect()->route('pelanggan.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibatalkan. Stok telah dikembalikan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }
}
