<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Retur;
use App\Models\ReturDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReturnController extends Controller
{
    public function index()
    {
        $returs = Retur::where('type', 'sale')
            ->whereIn('ref_id', function($query) {
                $query->select('id')
                      ->from('orders')
                      ->where('user_id', auth()->id());
            })
            ->with('details')
            ->latest()
            ->paginate(10);

        return view('pelanggan.returns.index', compact('returs'));
    }

    public function create(Request $request)
    {
        $orderId = $request->query('order_id');
        $orders = Order::where('user_id', auth()->id())
            ->latest()->get(['id','nomor_order','total_amount','status']);

        $order = null;
        if ($orderId) {
            $order = Order::with('items.produk')->where('user_id', auth()->id())->findOrFail($orderId);
        }

        return view('pelanggan.returns.create', compact('orders','order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'tipe_kompensasi' => 'required|in:barang,uang',
            'alasan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        // Pastikan order milik user
        $order = Order::with('items')->where('user_id', auth()->id())->findOrFail($request->order_id);

        // Filter item milik order
        $itemsInput = collect($request->items)
            ->filter(fn($i) => (int)($i['qty'] ?? 0) > 0)
            ->values();
        if ($itemsInput->isEmpty()) {
            return back()->withInput()->with('error', 'Isi minimal satu item retur.');
        }

        // Build map order items for quick lookup
        $orderItems = $order->items->keyBy('id');

        DB::beginTransaction();
        try {
            $kode = Retur::generateKodeRetur();
            $retur = Retur::create([
                'type' => 'sale',
                'ref_id' => $order->id,
                'kompensasi' => $request->tipe_kompensasi === 'barang' ? 'barang' : 'uang',
                'created_by' => auth()->id(),
                'alasan' => $request->alasan,
                'memo' => $kode,
                'jumlah' => 0,
            ]);

            $total = 0;
            foreach ($itemsInput as $row) {
                $oi = $orderItems->get((int)$row['order_item_id']);
                if (!$oi) { continue; }
                $qtyReq = (int)$row['qty'];
                $qtyMax = (int)$oi->qty; // simple cap to ordered qty
                $qty = max(1, min($qtyReq, $qtyMax));
                $harga = (float)$oi->harga;
                $subtotal = $qty * $harga;
                ReturDetail::create([
                    'retur_id' => $retur->id,
                    'produk_id' => $oi->produk_id,
                    'ref_detail_id' => $oi->id,
                    'qty' => $qty,
                    'harga_satuan_asal' => $harga,
                ]);
                $total += $subtotal;
            }

            $retur->update([
                'jumlah' => $total,
            ]);

            DB::commit();
            return redirect()->route('pelanggan.returns.index')->with('success', 'Pengajuan retur berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat retur: '.$e->getMessage());
        }
    }
}
