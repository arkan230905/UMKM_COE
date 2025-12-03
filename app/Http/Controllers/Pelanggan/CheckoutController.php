<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
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
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'alamat_pengiriman' => 'required|string',
            'telepon_penerima' => 'required|string|max:20',
            'payment_method' => 'required|in:qris,va_bca,va_bni,va_bri,va_mandiri',
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

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'nomor_order' => Order::generateNomorOrder(),
                'total_amount' => $carts->sum('subtotal'),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'nama_penerima' => $request->nama_penerima,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'telepon_penerima' => $request->telepon_penerima,
                'catatan' => $request->catatan,
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

            // Get Midtrans Snap Token
            $snapToken = $this->midtrans->createTransaction($order, $order->items);
            $order->update(['snap_token' => $snapToken]);

            // Clear cart
            Cart::where('user_id', auth()->id())->delete();

            // Create notification
            Notification::createNotification(
                auth()->id(),
                'order_created',
                'Pesanan Dibuat',
                "Pesanan {$order->nomor_order} berhasil dibuat. Silakan lakukan pembayaran.",
                ['order_id' => $order->id]
            );

            DB::commit();

            return redirect()->route('pelanggan.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }
}
