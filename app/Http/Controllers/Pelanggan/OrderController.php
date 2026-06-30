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

    public function uploadBukti(Request $request, $perusahaan_slug, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_pembayaran', $filename, 'public');

            $order->update([
                'bukti_pembayaran' => $path,
            ]);
            
            // Also update the related Penjualan records just in case owner checks there
            \App\Models\Penjualan::where('order_id', $order->id)->update([
                'keterangan' => \Illuminate\Support\Facades\DB::raw("CONCAT(COALESCE(keterangan, ''), ' | Bukti Bayar Diupload')")
            ]);

            return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload. Silakan tunggu konfirmasi.');
        }

        return redirect()->back()->with('error', 'Gagal upload bukti pembayaran.');
    }
}
