<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Penjualan;
use App\Services\OrderToSalesService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * NOTE: Do NOT sync here because OrderItems are not created yet!
     * Sync is called from CheckoutController after OrderItems are created.
     */
    public function created(Order $order): void
    {
        // Do nothing - sync is handled in CheckoutController
        Log::info('OrderObserver::created() - skipping sync (handled in controller)', [
            'order_id' => $order->id,
            'nomor_order' => $order->nomor_order,
        ]);
    }

    /**
     * Handle the Order "updated" event.
     * Sync payment status changes to Penjualan
     */
    public function updated(Order $order): void
    {
        try {
            // Check if payment_status changed
            if ($order->wasChanged('payment_status')) {
                Log::info('OrderObserver: Payment status changed', [
                    'order_id' => $order->id,
                    'old_status' => $order->getOriginal('payment_status'),
                    'new_status' => $order->payment_status,
                ]);

                // Update all related Penjualan records
                $penjualans = Penjualan::where('order_id', $order->id)->get();
                
                foreach ($penjualans as $penjualan) {
                    $penjualan->update([
                        'payment_status' => $order->payment_status,
                        'payment_confirmed_at' => $order->payment_status === 'paid' ? now() : null,
                    ]);
                    
                    Log::info('OrderObserver: Penjualan payment status updated', [
                        'penjualan_id' => $penjualan->id,
                        'payment_status' => $order->payment_status,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('OrderObserver: Failed to update penjualan payment status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Tidak menghapus Penjualan saat Order dihapus
        // Penjualan adalah record independen untuk owner
    }
}
