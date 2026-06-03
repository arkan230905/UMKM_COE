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

        return view('pelanggan.order-detail', compact('order', 'perusahaan_slug'));
    }
}
