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

        // SSL CA bundle handling for local Windows/XAMPP environments
        $caPath = env('MIDTRANS_CACERT_PATH');
        if ($caPath && file_exists($caPath)) {
            Config::$curlOptions[CURLOPT_CAINFO] = $caPath;
            Config::$curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
        } elseif (app()->environment('local')) {
            // TEMP: avoid local SSL errors; DO NOT use in production
            Config::$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
            Config::$curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        // Ensure curlOptions is initialized and HTTP headers key exists (avoid "Undefined array key 10023")
        if (!is_array(Config::$curlOptions ?? null)) {
            Config::$curlOptions = [];
        }
        if (!isset(Config::$curlOptions[CURLOPT_HTTPHEADER]) || !is_array(Config::$curlOptions[CURLOPT_HTTPHEADER])) {
            Config::$curlOptions[CURLOPT_HTTPHEADER] = [];
        }

        // Enforce JSON headers expected by Midtrans SDK
        $defaultHeaders = ['Content-Type: application/json', 'Accept: application/json'];
        foreach ($defaultHeaders as $h) {
            if (!in_array($h, Config::$curlOptions[CURLOPT_HTTPHEADER], true)) {
                Config::$curlOptions[CURLOPT_HTTPHEADER][] = $h;
            }
        }

        // Force TLS 1.2 where necessary
        if (!isset(Config::$curlOptions[CURLOPT_SSLVERSION])) {
            Config::$curlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
        }
    }

    public function createTransaction($order, $items)
    {
        // Sanitize phone to digits only (Midtrans expects numeric phone)
        $customerPhone = preg_replace('/\D+/', '', (string) $order->telepon_penerima);

        $params = [
            'transaction_details' => [
                'order_id' => $order->nomor_order,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->nama_penerima,
                'email' => $order->user->email,
                'phone' => $customerPhone,
                'shipping_address' => [
                    'address' => $order->alamat_pengiriman,
                    'phone' => $customerPhone,
                ],
            ],
            'item_details' => $items->map(function ($item) {
                // Midtrans limits name length; trim to safe length
                $name = $item->produk->nama_produk ?? 'Produk';
                $name = mb_strimwidth((string) $name, 0, 50, '');

                return [
                    'id' => (string) $item->produk_id,
                    'price' => (int) $item->harga,
                    'quantity' => (int) $item->qty,
                    'name' => $name,
                ];
            })->values()->toArray(),

            'enabled_payments' => $this->getEnabledPayments($order->payment_method),

            'callbacks' => [
                'finish' => route('pelanggan.orders.show', $order->id),
                'error' => route('pelanggan.orders.show', $order->id),
                'pending' => route('pelanggan.orders.show', $order->id),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage(), [
                'params' => $params,
                'payment_method' => $order->payment_method,
                'items_count' => $items->count(),
                'items_data' => $items->toArray(),
                'exception' => $e
            ]);
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    private function getEnabledPayments($method)
    {
        $paymentMap = [
            'qris' => ['qris'],
            'va_bca' => ['bca_va'],
            'va_bni' => ['bni_va'],
            'va_bri' => ['bri_va'],
            'va_mandiri' => ['echannel'],
            'cash' => ['cstore', 'alfamart', 'indomaret'],
        ];
        
        return $paymentMap[$method] ?? ['qris', 'bca_va', 'bni_va', 'bri_va', 'echannel'];
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
