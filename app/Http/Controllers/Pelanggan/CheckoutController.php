<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function index()
    {
        $carts = Cart::with('produk')
            ->where('user_id', auth()->id())
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('pelanggan.cart')
                ->with('error', 'Keranjang kosong!');
        }

        $total = $carts->sum('subtotal');

        return view('pelanggan.checkout', compact('carts', 'total'));
    }

    public function process(Request $request)
    {
        $allowedMethods = ['qris', 'va_bca', 'va_bni', 'va_bri', 'va_mandiri', 'cash'];
        
        // Debug: Log the received payment method
        \Log::info('Payment method received: ' . $request->payment_method);
        
        if (!in_array($request->payment_method, $allowedMethods)) {
            return back()->with('error', 'Metode pembayaran tidak valid! Received: ' . $request->payment_method);
        }
        
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'alamat_pengiriman' => 'required|string',
            'telepon_penerima' => 'required|string|max:20',
            'payment_method' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $carts = Cart::with('produk')
            ->where('user_id', auth()->id())
            ->get();

        if ($carts->isEmpty()) {
            return back()->with('error', 'Keranjang kosong!');
        }

        DB::beginTransaction();
        try {
            // Validasi stok
            foreach ($carts as $cart) {
                if ($cart->produk->stok < $cart->qty) {
                    throw new \Exception("Stok {$cart->produk->nama_produk} tidak mencukupi!");
                }
            }

            // Store payment method as-is
            $storedMethod = $request->payment_method;
            $catatanInput = $request->catatan;
            if ($request->payment_method === 'cash') {
                $prefixNote = 'Metode: Cash (Bayar di Kasir). ';
                $catatanInput = $prefixNote . (string) $catatanInput;
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'nomor_order' => Order::generateNomorOrder(),
                'total_amount' => $carts->sum('subtotal'),
                'status' => 'pending',
                'payment_method' => $storedMethod,
                'payment_status' => 'pending',
                'nama_penerima' => $request->nama_penerima,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'telepon_penerima' => $request->telepon_penerima,
                'catatan' => $catatanInput,
            ]);

            // Create order items & kurangi stok
            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'produk_id' => $cart->produk_id,
                    'qty' => $cart->qty,
                    'harga' => $cart->harga,
                    'subtotal' => $cart->subtotal,
                ]);

                // Kurangi stok
                $cart->produk->decrement('stok', $cart->qty);
            }

            // If payment method is cash, mark as paid and completed
            if ($request->payment_method === 'cash') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
            } else {
                // Get Midtrans Snap Token
                $snapToken = $this->midtrans->createTransaction($order, $order->items);
                $order->update(['snap_token' => $snapToken]);
            }

            // Create Penjualan record for admin visibility
            $this->createPenjualanFromOrder($order, $carts, $request);

            // Clear cart
            Cart::where('user_id', auth()->id())->delete();

            // Create notification
            Notification::createNotification(
                auth()->id(),
                'order_created',
                'Pesanan Dibuat',
                $request->payment_method === 'cash'
                    ? "Pesanan {$order->nomor_order} berhasil dibuat dan lunas (Cash)."
                    : "Pesanan {$order->nomor_order} berhasil dibuat. Silakan lakukan pembayaran.",
                ['order_id' => $order->id]
            );

            DB::commit();

            $msg = $request->payment_method === 'cash'
                ? 'Pesanan berhasil dibuat dan lunas (Cash)! Nomor Pesanan: ' . $order->nomor_order
                : 'Pesanan berhasil dibuat! Silakan lakukan pembayaran. Nomor Pesanan: ' . $order->nomor_order;

            return redirect()->route('pelanggan.orders.show', $order->id)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }

    /**
     * Create Penjualan record from Order for admin visibility
     */
    private function createPenjualanFromOrder($order, $carts, $request)
    {
        try {
            // Calculate totals
            $totalAmount = $carts->sum('subtotal');
            $totalQty = $carts->sum('qty');

            // Map payment method from pelanggan to admin format
            $paymentMethodMap = [
                'cash' => 'cash',
                'qris' => 'transfer',
                'va_bca' => 'transfer',
                'va_bni' => 'transfer',
                'va_bri' => 'transfer',
                'va_mandiri' => 'transfer',
            ];
            
            $adminPaymentMethod = $paymentMethodMap[$request->payment_method] ?? 'transfer';

            // Create Penjualan header
            $penjualan = Penjualan::create([
                'tanggal' => now(),
                'payment_method' => $adminPaymentMethod,
                'jumlah' => $totalQty,
                'harga_satuan' => null, // Multi-item, harga di detail
                'diskon_nominal' => 0,
                'total' => $totalAmount,
                'user_id' => auth()->id(),
                'order_id' => $order->id, // Reference to Order
                'catatan' => "Dari Order: {$order->nomor_order} - {$request->nama_penerima} (Metode: {$request->payment_method})",
            ]);

            // Create PenjualanDetail for each item
            foreach ($carts as $cart) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $cart->produk_id,
                    'jumlah' => $cart->qty,
                    'harga_satuan' => $cart->harga,
                    'diskon_persen' => 0,
                    'diskon_nominal' => 0,
                    'subtotal' => $cart->subtotal,
                ]);
            }

            // Update Order to reference Penjualan
            $order->update(['penjualan_id' => $penjualan->id]);

            return $penjualan;

        } catch (\Exception $e) {
            // Log error but don't fail the checkout
            \Log::error('Failed to create Penjualan from Order: ' . $e->getMessage());
            return null;
        }
    }
}
