<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function notification(Request $request)
    {
        try {
            $serverKey = config('midtrans.server_key');
            $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

            if ($hashed !== $request->signature_key) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $order = Order::where('nomor_order', $request->order_id)->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $transactionStatus = $request->transaction_status;
            $fraudStatus = $request->fraud_status ?? 'accept';

            Log::info('Midtrans Notification', [
                'order_id' => $request->order_id,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $this->updateOrderStatus($order, 'paid', 'paid');
                }
            } elseif ($transactionStatus == 'settlement') {
                $this->updateOrderStatus($order, 'paid', 'paid');
            } elseif ($transactionStatus == 'pending') {
                $this->updateOrderStatus($order, 'pending', 'pending');
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel' || $transactionStatus == 'expire') {
                $this->updateOrderStatus($order, 'cancelled', 'failed');
                // Kembalikan stok
                foreach ($order->items as $item) {
                    $item->produk->increment('stok', $item->qty);
                }
            }

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    private function updateOrderStatus($order, $status, $paymentStatus)
    {
        $order->update([
            'status' => $status,
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        // Create notification
        if ($paymentStatus === 'paid') {
            Notification::createNotification(
                $order->user_id,
                'payment_success',
                'Pembayaran Berhasil',
                "Pembayaran untuk pesanan {$order->nomor_order} berhasil!",
                ['order_id' => $order->id]
            );
        } elseif ($paymentStatus === 'failed') {
            Notification::createNotification(
                $order->user_id,
                'payment_failed',
                'Pembayaran Gagal',
                "Pembayaran untuk pesanan {$order->nomor_order} gagal atau dibatalkan.",
                ['order_id' => $order->id]
            );
        }
    }
}
