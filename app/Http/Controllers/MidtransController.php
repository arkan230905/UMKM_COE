<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Notification;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Services\MidtransService;
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
                    $this->updateOrderStatus($order, 'paid', 'paid', [
                        'midtrans_transaction_id' => $request->transaction_id,
                        'midtrans_order_id' => $request->order_id,
                    ]);
                    $this->createPenjualanFromOrder($order);
                }
            } elseif ($transactionStatus == 'settlement') {
                $this->updateOrderStatus($order, 'paid', 'paid', [
                    'midtrans_transaction_id' => $request->transaction_id,
                    'midtrans_order_id' => $request->order_id,
                ]);
                $this->createPenjualanFromOrder($order);
            } elseif ($transactionStatus == 'pending') {
                $this->updateOrderStatus($order, 'pending', 'pending', [
                    'midtrans_transaction_id' => $request->transaction_id,
                    'midtrans_order_id' => $request->order_id,
                ]);
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel' || $transactionStatus == 'expire') {
                $this->updateOrderStatus($order, 'cancelled', 'failed', [
                    'midtrans_transaction_id' => $request->transaction_id,
                    'midtrans_order_id' => $request->order_id,
                ]);
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

    public function clientComplete(Request $request, MidtransService $midtrans)
    {
        $data = $request->validate([
            'order_id' => 'required|string',
            'result' => 'nullable|array',
            'fallback_status' => 'nullable|string',
            'force_check' => 'nullable|boolean',
        ]);

        $order = Order::where('nomor_order', $data['order_id'])->first();

        if (!$order || $order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['redirect_status' => 'success', 'status' => $order->payment_status]);
        }

        try {
            $result = $data['result'] ?? [];
            $fallbackStatus = $data['fallback_status'] ?? 'pending';
            $forceCheck = (bool) ($data['force_check'] ?? false);

            $transactionStatus = $result['transaction_status'] ?? null;
            $fraudStatus = $result['fraud_status'] ?? 'accept';
            $statusCode = $result['status_code'] ?? null;
            $grossAmount = $result['gross_amount'] ?? null;
            $signatureKey = $result['signature_key'] ?? null;
            $transactionId = $result['transaction_id'] ?? null;
            $paymentType = $result['payment_type'] ?? null;

            if ($signatureKey && $statusCode && $grossAmount) {
                $serverKey = config('midtrans.server_key');
                $expectedSignature = hash('sha512', $data['order_id'] . $statusCode . $grossAmount . $serverKey);
                if (!hash_equals($expectedSignature, $signatureKey)) {
                    Log::warning('Midtrans Client Completion signature mismatch', [
                        'order_id' => $data['order_id'],
                        'payload' => $result,
                    ]);
                    return response()->json(['message' => 'Invalid signature'], 403);
                }
            }

            if (!$transactionStatus || $forceCheck) {
                $statusResponse = $midtrans->getTransactionStatus($order->nomor_order);
                $transactionStatus = $statusResponse->transaction_status ?? $transactionStatus;
                $fraudStatus = $statusResponse->fraud_status ?? $fraudStatus;
                $transactionId = $statusResponse->transaction_id ?? $transactionId;
                $paymentType = $statusResponse->payment_type ?? $paymentType;
            }

            Log::info('Midtrans Client Completion', [
                'order_id' => $order->nomor_order,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType,
            ]);

            $redirectStatus = $fallbackStatus;

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $this->updateOrderStatus($order, 'paid', 'paid', [
                        'midtrans_transaction_id' => $transactionId,
                        'midtrans_order_id' => $data['order_id'],
                    ]);
                    $this->createPenjualanFromOrder($order);
                    $redirectStatus = 'success';
                }
            } elseif ($transactionStatus === 'settlement') {
                $this->updateOrderStatus($order, 'paid', 'paid', [
                    'midtrans_transaction_id' => $transactionId,
                    'midtrans_order_id' => $data['order_id'],
                ]);
                $this->createPenjualanFromOrder($order);
                $redirectStatus = 'success';
            } elseif ($transactionStatus === 'pending') {
                $this->updateOrderStatus($order, 'pending', 'pending', [
                    'midtrans_transaction_id' => $transactionId,
                    'midtrans_order_id' => $data['order_id'],
                ]);
                $redirectStatus = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $this->updateOrderStatus($order, 'cancelled', 'failed', [
                    'midtrans_transaction_id' => $transactionId,
                    'midtrans_order_id' => $data['order_id'],
                ]);
                $order->loadMissing('items.produk');
                foreach ($order->items as $item) {
                    $item->produk->increment('stok', $item->qty);
                }
                $redirectStatus = 'error';
            }

            return response()->json([
                'redirect_status' => $redirectStatus,
                'transaction_status' => $transactionStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Client Completion Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to sync payment status'], 500);
        }
    }

    private function updateOrderStatus($order, $status, $paymentStatus, array $extra = [])
    {
        $payload = [
            'status' => $status,
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ];

        if (!empty($extra)) {
            $payload = array_merge($payload, array_filter($extra, fn ($value) => $value !== null));
        }

        $order->update($payload);

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

    /**
     * Create Penjualan record from Order for admin visibility
     */
    private function createPenjualanFromOrder($order)
    {
        try {
            // Skip if already has penjualan
            if ($order->penjualan_id) {
                return;
            }

            // Load order items
            $order->load('items.produk');

            // Calculate totals
            $totalAmount = $order->items->sum('subtotal');
            $totalQty = $order->items->sum('qty');

            // Map payment method from pelanggan to admin format
            $paymentMethodMap = [
                'cash' => 'cash',
                'va_bca' => 'transfer',
                'va_bni' => 'transfer',
                'va_bri' => 'transfer',
                'va_mandiri' => 'transfer',
            ];
            
            $adminPaymentMethod = $paymentMethodMap[$order->payment_method] ?? 'transfer';

            // Create Penjualan header
            $penjualan = Penjualan::create([
                'tanggal' => now(),
                'payment_method' => $adminPaymentMethod,
                'jumlah' => $totalQty,
                'harga_satuan' => null, // Multi-item, harga di detail
                'diskon_nominal' => 0,
                'total' => $totalAmount,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'catatan' => "Dari Order: {$order->nomor_order} - {$order->nama_penerima} (Metode: {$order->payment_method})",
            ]);

            // Create PenjualanDetail for each item
            foreach ($order->items as $item) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item->produk_id,
                    'jumlah' => $item->qty,
                    'harga_satuan' => $item->harga,
                    'diskon_persen' => 0,
                    'diskon_nominal' => 0,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Update Order to reference Penjualan
            $order->update(['penjualan_id' => $penjualan->id]);

            Log::info('Penjualan created from Order', [
                'order_id' => $order->id,
                'penjualan_id' => $penjualan->id,
                'nomor_order' => $order->nomor_order,
                'nomor_penjualan' => $penjualan->nomor_penjualan,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create Penjualan from Order: ' . $e->getMessage());
        }
    }
}
