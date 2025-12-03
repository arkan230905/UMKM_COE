<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('pelanggan.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        // Cek ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.produk');

        return view('pelanggan.order-detail', compact('order'));
    }
}
