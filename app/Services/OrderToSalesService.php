<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk sinkronisasi Order dari pelanggan ke Penjualan owner
 * 
 * Flow:
 * 1. Order dibuat di checkout pelanggan
 * 2. Observer mendeteksi Order created event
 * 3. Service ini mengkonversi Order + OrderItem ke Penjualan + PenjualanDetail
 * 4. Penjualan otomatis muncul di dashboard owner
 * 5. Journal entries dibuat otomatis via Penjualan model observer
 */
class OrderToSalesService
{
    /**
     * Sinkronisasi Order ke Penjualan
     * 
     * Handles multi-owner orders by creating separate Penjualan for each owner
     * 
     * @param Order $order
     * @return Penjualan|array Single Penjualan or array of Penjualans if multi-owner
     * @throws \Exception
     */
    public function syncOrderToPenjualan(Order $order)
    {
        return DB::transaction(function () use ($order) {
            Log::info('OrderToSalesService: syncOrderToPenjualan called', [
                'order_id' => $order->id,
                'nomor_order' => $order->nomor_order,
            ]);
            
            // Load relasi yang diperlukan dengan withoutGlobalScopes untuk produk dari owner lain
            $order->load(['items' => function ($query) {
                $query->with(['produk' => function ($q) {
                    $q->withoutGlobalScopes();
                }]);
            }]);

            Log::info('OrderToSalesService: Order loaded with items', [
                'order_id' => $order->id,
                'items_count' => $order->items->count(),
            ]);

            if ($order->items->isEmpty()) {
                Log::error('OrderToSalesService: Order has no items', [
                    'order_id' => $order->id,
                    'nomor_order' => $order->nomor_order,
                ]);
                throw new \Exception("Order tidak memiliki items: {$order->nomor_order}");
            }

            Log::info('OrderToSalesService: Starting sync', [
                'order_id' => $order->id,
                'nomor_order' => $order->nomor_order,
                'items_count' => $order->items->count(),
            ]);

            // Group items by owner (produk.user_id)
            $itemsByOwner = $order->items->groupBy(function ($item) {
                if (!$item->produk) {
                    Log::error('OrderToSalesService: Produk is null for item', [
                        'item_id' => $item->id,
                        'produk_id' => $item->produk_id,
                    ]);
                    throw new \Exception("Produk tidak ditemukan untuk item: {$item->produk_id}");
                }
                return $item->produk->user_id;
            });

            Log::info('OrderToSalesService: Items grouped by owner', [
                'owner_count' => $itemsByOwner->count(),
                'owners' => $itemsByOwner->keys()->toArray(),
            ]);

            $penjualans = [];

            // Create Penjualan for each owner
            foreach ($itemsByOwner as $ownerId => $ownerItems) {
                $penjualan = $this->createPenjualanForOwner($order, $ownerId, $ownerItems);
                $penjualans[] = $penjualan;
            }

            // Return single Penjualan if only one owner, otherwise return array
            return count($penjualans) === 1 ? $penjualans[0] : $penjualans;
        });
    }

    /**
     * Create Penjualan for a specific owner
     * 
     * @param Order $order
     * @param int $ownerId
     * @param \Illuminate\Support\Collection $ownerItems
     * @return Penjualan
     */
    private function createPenjualanForOwner(Order $order, int $ownerId, $ownerItems): Penjualan
    {
        // Hitung total dan komponen untuk items milik owner ini
        $subtotal = 0;
        $totalDiskon = 0;

        foreach ($ownerItems as $item) {
            $subtotal += $item->subtotal;
        }

        // Hitung PPN (11%)
        $biayaPPN = $subtotal * 0.11;

        // Hitung ongkir dari order
        // NOTE: Ongkir dibagi rata ke semua owner jika multi-owner
        $biayaOngkir = 0;
        if ($order->ongkir_amount > 0) {
            $ownerCount = $order->items->groupBy(function ($item) {
                return $item->produk->user_id;
            })->count();
            $biayaOngkir = round($order->ongkir_amount / $ownerCount);
        }

        $grandTotal = $subtotal + $biayaPPN + $biayaOngkir;

        // Tentukan payment method
        $paymentMethod = $this->mapPaymentMethod($order->payment_method);

        // Cari atau buat data pelanggan untuk owner ini
        $pelangganId = null;
        if ($order->user) {
            $customerUser = $order->user;
            
            // Cari data pelanggan yang sudah ada di master owner ini
            $pelanggan = \App\Models\Pelanggan::withoutGlobalScopes()
                ->where('user_id', $ownerId)
                ->where(function($q) use ($customerUser) {
                    $q->where('email', $customerUser->email)
                      ->orWhere('telepon', $customerUser->phone ?? '');
                })->first();

            if (!$pelanggan) {
                // Buat master pelanggan baru untuk owner ini
                $pelanggan = \App\Models\Pelanggan::create([
                    'user_id' => $ownerId,
                    'nama_pelanggan' => $customerUser->name ?? $order->nama_penerima,
                    'email' => $customerUser->email,
                    'telepon' => $customerUser->phone ?? $order->telepon_penerima,
                    'alamat' => $customerUser->address ?? $order->alamat_pengiriman,
                ]);
            }
            $pelangganId = $pelanggan->id;
        }

        // Buat Penjualan header
        $penjualan = Penjualan::create([
            'order_id' => $order->id,  // Link ke order
            'user_id' => $ownerId,  // Owner ID
            'pelanggan_id' => $pelangganId,
            'tanggal' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',  // Initially pending, will be updated when payment confirmed
            'approval_status' => 'pending', // Online order needs owner approval
            'harga_satuan' => null, // Akan diisi dari detail
            'jumlah' => $ownerItems->sum('qty'),
            'diskon_nominal' => $totalDiskon,
            'total' => $subtotal,
            'biaya_ongkir' => $biayaOngkir,
            'biaya_ppn' => $biayaPPN,
            'grand_total' => $grandTotal,
            'catatan_pembayaran' => "Order #{$order->nomor_order} - {$order->nama_penerima} ({$order->alamat_pengiriman})",
            'catatan' => $order->catatan,
            'bukti_pembayaran' => $order->bukti_pembayaran,
        ]);

        Log::info('OrderToSalesService: Penjualan header created', [
            'penjualan_id' => $penjualan->id,
            'nomor_penjualan' => $penjualan->nomor_penjualan,
            'owner_id' => $ownerId,
            'grand_total' => $grandTotal,
            'items_count' => $ownerItems->count(),
        ]);

        // Buat PenjualanDetail untuk setiap item
        foreach ($ownerItems as $item) {
            PenjualanDetail::create([
                'penjualan_id' => $penjualan->id,
                'produk_id' => $item->produk_id,
                'jumlah' => $item->qty,
                'harga_satuan' => $item->harga,
                'diskon_persen' => 0,
                'diskon_nominal' => 0,
                'subtotal' => $item->subtotal,
            ]);

            Log::info('OrderToSalesService: PenjualanDetail created', [
                'penjualan_id' => $penjualan->id,
                'produk_id' => $item->produk_id,
                'qty' => $item->qty,
                'harga' => $item->harga,
            ]);
        }

        // Set payment_status to match the order's payment_status
        $penjualan->update(['payment_status' => $order->payment_status ?? 'pending']);

        // CRITICAL: Explicitly create journal after all details saved
        try {
            // NOTE: Journal tidak lagi dibuat otomatis di sini karena harus menunggu approval owner
            // $penjualan->load(['details.produk', 'produk']);
            // \App\Services\JournalService::createJournalFromPenjualan($penjualan, $ownerId);
            Log::info('OrderToSalesService: Journal creation skipped pending owner approval', [
                'penjualan_id' => $penjualan->id,
                'nomor_penjualan' => $penjualan->nomor_penjualan
            ]);
        } catch (\Exception $e) {
            Log::error('OrderToSalesService: Failed to create journal', [
                'penjualan_id' => $penjualan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - allow transaction to complete
        }

        Log::info('OrderToSalesService: Penjualan created successfully', [
            'penjualan_id' => $penjualan->id,
            'nomor_penjualan' => $penjualan->nomor_penjualan,
            'owner_id' => $ownerId,
            'details_count' => $penjualan->details->count(),
        ]);

        return $penjualan;
    }

    /**
     * Map payment method dari Order ke Penjualan
     * 
     * @param string|null $orderPaymentMethod
     * @return string
     */
    private function mapPaymentMethod(?string $orderPaymentMethod): string
    {
        if ($orderPaymentMethod === null) {
            return 'transfer'; // Default to transfer if null (e.g., from midtrans)
        }
        
        return match ($orderPaymentMethod) {
            'qris' => 'qris',
            'va_bca' => 'transfer',
            'va_bni' => 'transfer',
            'va_bri' => 'transfer',
            'va_mandiri' => 'transfer',
            'transfer' => 'transfer',
            'cod' => 'cash', // COD dianggap cash untuk jurnal
            'kasir' => 'cash', // Kasir dianggap cash untuk jurnal
            default => 'cash',
        };
    }
}
