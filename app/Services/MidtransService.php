<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction($order, $items)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->nomor_order,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->nama_penerima,
                'email' => $order->user->email,
                'phone' => $order->telepon_penerima,
                'shipping_address' => [
                    'address' => $order->alamat_pengiriman,
                    'phone' => $order->telepon_penerima,
                ],
            ],
            'item_details' => $items->map(function ($item) {
                return [
                    'id' => $item->produk_id,
                    'price' => (int) $item->harga,
                    'quantity' => $item->qty,
                    'name' => $item->produk->nama_produk,
                ];
            })->toArray(),
            'enabled_payments' => $this->getEnabledPayments($order->payment_method),
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    private function getEnabledPayments($method)
    {
        return match($method) {
            'qris' => ['qris'],
            'va_bca' => ['bca_va'],
            'va_bni' => ['bni_va'],
            'va_bri' => ['bri_va'],
            'va_mandiri' => ['echannel'],
            default => ['qris', 'bca_va', 'bni_va', 'bri_va', 'echannel'],
        };
    }

    public function getTransactionStatus($orderId)
    {
        try {
            return Transaction::status($orderId);
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }
}
