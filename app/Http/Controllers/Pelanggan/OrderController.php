<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Notification;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        // Get perusahaan_slug for URL generation
        $perusahaan = current_perusahaan();
        $perusahaan_slug = perusahaan_slug($perusahaan);

        return view('pelanggan.orders', compact('orders', 'perusahaan_slug'));
    }

    public function show($perusahaan_slug, Order $order)
    {
        // Cek ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items' => function ($query) {
            $query->with(['produk' => function ($q) {
                $q->withoutGlobalScopes();
            }]);
        }]);

        // Get perusahaan_slug for URL generation
        $perusahaan = current_perusahaan();
        $perusahaan_slug = perusahaan_slug($perusahaan);
        // Sync Midtrans Status Proactively if Pending
        if ($order->payment_gateway === 'midtrans' && $order->payment_status === 'pending' && $order->midtrans_order_id) {
            try {
                $midtransService = new MidtransService();
                $statusResponse = $midtransService->getTransactionStatus($order->midtrans_order_id);
                $transactionStatus = $statusResponse->transaction_status ?? null;
                $fraudStatus = $statusResponse->fraud_status ?? 'accept';

                Log::info('Proactive Midtrans Sync', [
                    'order_id' => $order->midtrans_order_id,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                ]);

                if ($transactionStatus == 'capture' && $fraudStatus == 'accept' || $transactionStatus == 'settlement') {
                    $order->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    \App\Models\Penjualan::where('order_id', $order->id)->update([
                        'payment_status' => 'paid',
                        'payment_confirmed_at' => now(),
                    ]);

                    Notification::createNotification(
                        $order->user_id,
                        'payment_success',
                        'Pembayaran Berhasil',
                        "Pembayaran untuk pesanan {$order->nomor_order} berhasil!",
                        ['order_id' => $order->id]
                    );
                } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                    $order->update([
                        'status' => 'cancelled',
                        'payment_status' => 'failed',
                    ]);
                    
                    \App\Models\Penjualan::where('order_id', $order->id)->update([
                        'payment_status' => 'failed',
                    ]);

                    foreach ($order->items as $item) {
                        $item->produk->increment('stok', $item->qty);
                    }
                    
                    Notification::createNotification(
                        $order->user_id,
                        'payment_failed',
                        'Pembayaran Gagal',
                        "Pembayaran untuk pesanan {$order->nomor_order} gagal atau dibatalkan.",
                        ['order_id' => $order->id]
                    );
                }
            } catch (\Exception $e) {
                Log::error('Proactive Midtrans Sync Failed: ' . $e->getMessage());
            }
        }

        return view('pelanggan.order-detail', compact('order', 'perusahaan_slug'));
    }

    public function uploadBukti(Request $request, $perusahaan_slug, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_pembayaran', $filename, 'public');

            $order->update([
                'bukti_pembayaran' => $path,
            ]);
            
            // Also update the related Penjualan records just in case owner checks there
            \App\Models\Penjualan::where('order_id', $order->id)->update([
                'keterangan' => \Illuminate\Support\Facades\DB::raw("CONCAT(COALESCE(keterangan, ''), ' | Bukti Bayar Diupload')")
            ]);

            return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload. Silakan tunggu konfirmasi.');
        }

        return redirect()->back()->with('error', 'Gagal upload bukti pembayaran.');
    }
}
