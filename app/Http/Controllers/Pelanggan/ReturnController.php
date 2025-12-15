<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Retur;
use App\Models\ReturDetail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            $returnedMap = $this->mapReturnedQuantities($order);
            foreach ($order->items as $item) {
                $returned = (float) ($returnedMap[$item->id] ?? 0);
                $remaining = max($item->qty - $returned, 0);

                $item->setAttribute('qty_returned', $returned);
                $item->setAttribute('qty_remaining', $remaining);
            }
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

        $returnedMap = $this->mapReturnedQuantities($order);

        // Build map order items for quick lookup
        $orderItems = $order->items->keyBy('id');

        DB::beginTransaction();
        try {
            $kode = Retur::generateKodeRetur();
            $tipeKompensasi = $request->tipe_kompensasi === 'barang' ? 'barang' : 'uang';
            $today = now();

            $retur = Retur::create($this->filterColumns('returs', [
                'type' => 'sale',
                'tipe_retur' => 'penjualan',
                'ref_id' => $order->id,
                'referensi_id' => $order->id,
                'referensi_kode' => $order->nomor_order,
                'kode_retur' => $kode,
                'kompensasi' => $tipeKompensasi,
                'tipe_kompensasi' => $tipeKompensasi,
                'created_by' => auth()->id(),
                'alasan' => $request->alasan,
                'memo' => $kode,
                'tanggal' => $today,
                'status' => 'diproses',
                'jumlah' => 0,
                'total_nilai_retur' => 0,
                'nilai_kompensasi' => 0,
            ]));

            $total = 0;
            $detailsCreated = 0;
            foreach ($itemsInput as $row) {
                $oi = $orderItems->get((int)$row['order_item_id']);
                if (!$oi) { continue; }
                $qtyReq = (int)$row['qty'];
                $qtyMax = max((int)$oi->qty - (int)($returnedMap[$oi->id] ?? 0), 0);
                if ($qtyMax <= 0) {
                    continue;
                }

                $qty = max(1, min($qtyReq, $qtyMax));
                if ($qty <= 0) {
                    continue;
                }
                $harga = (float)$oi->harga;
                $subtotal = $qty * $harga;
                ReturDetail::create($this->filterColumns('retur_details', [
                    'retur_id' => $retur->id,
                    'produk_id' => $oi->produk_id,
                    'ref_detail_id' => $oi->id,
                    'item_type' => 'produk',
                    'item_id' => $oi->produk_id,
                    'item_nama' => optional($oi->produk)->nama_produk,
                    'qty' => $qty,
                    'qty_retur' => $qty,
                    'harga_satuan_asal' => $harga,
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                ]));
                $total += $subtotal;
                $detailsCreated++;
            }

            if ($detailsCreated === 0) {
                throw new \RuntimeException('Tidak ada item yang valid untuk diretur.');
            }

            $retur->update($this->filterColumns('returs', [
                'jumlah' => $total,
                'total_nilai_retur' => $total,
            ]));

            DB::commit();
            return redirect()->route('pelanggan.returns.index')->with('success', 'Pengajuan retur berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat retur: '.$e->getMessage());
        }
    }

    private function mapReturnedQuantities(Order $order): array
    {
        $returs = Retur::with('details')
            ->where('type', 'sale')
            ->where(function ($query) use ($order) {
                $query->where('ref_id', $order->id)
                      ->orWhere('referensi_id', $order->id);
            })
            ->get();

        $returned = [];
        foreach ($returs as $retur) {
            foreach ($retur->details as $detail) {
                $refDetailId = $detail->ref_detail_id;
                if (!$refDetailId) {
                    continue;
                }

                $qty = (float) ($detail->qty ?? $detail->qty_retur ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $returned[$refDetailId] = ($returned[$refDetailId] ?? 0) + $qty;
            }
        }

        return $returned;
    }

    private array $columnCache = [];

    private function filterColumns(string $table, array $attributes): array
    {
        if (!isset($this->columnCache[$table])) {
            $this->columnCache[$table] = Schema::getColumnListing($table);
        }

        $allowed = array_flip($this->columnCache[$table] ?? []);

        return collect($attributes)
            ->filter(fn ($value, $key) => array_key_exists($key, $allowed))
            ->all();
    }
}
