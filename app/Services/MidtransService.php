<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\CoreApi;

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
        
        // FIX: Prevent 'Undefined array key 10023' (CURLOPT_HTTPHEADER) error in Midtrans SDK
        if (!isset(Config::$curlOptions[CURLOPT_HTTPHEADER])) {
            Config::$curlOptions[CURLOPT_HTTPHEADER] = [];
        }
    }

    public function createTransaction($order, $items, $bankVa = null)
    {
        $enabledPayments = $this->getEnabledPayments($order->payment_method);
        if ($bankVa) {
            $codeMap = [
                'bca' => 'bca_va',
                'bni' => 'bni_va',
                'bri' => 'bri_va',
                'echannel' => 'echannel',
                'permata' => 'permata_va',
                'cimb' => 'cimb_va',
            ];
            if (isset($codeMap[$bankVa])) {
                $enabledPayments = [$codeMap[$bankVa]];
            }
        }

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
            'item_details' => $this->buildItemDetails($order, $items),
            'enabled_payments' => $enabledPayments,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }



    private function buildItemDetails($order, $items)
    {
        $itemDetails = $items->map(function ($item) {
            return [
                'id' => $item->produk_id,
                'price' => (int) $item->harga,
                'quantity' => $item->qty,
                'name' => substr($item->produk->nama_produk ?? 'Produk', 0, 50),
            ];
        })->toArray();

        if ($order->ppn_amount > 0) {
            $itemDetails[] = [
                'id' => 'TAX-PPN',
                'price' => (int) $order->ppn_amount,
                'quantity' => 1,
                'name' => 'PPN 11%',
            ];
        }

        if ($order->ongkir_amount > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->ongkir_amount,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        return $itemDetails;
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
