<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Pastikan order selesai atau sudah dibayar
        if (!($order->status === 'completed' || $order->payment_status === 'paid')) {
            return back()->with('error', 'Review hanya bisa diberikan untuk pesanan yang telah selesai atau dibayar.');
        }

        // Cek apakah sudah pernah review
        $existing = Review::where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah pernah memberikan review untuk pesanan ini.');
        }

        Review::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Review berhasil dikirim. Terima kasih!');
    }
}
