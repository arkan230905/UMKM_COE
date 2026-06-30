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

        // Get perusahaan_slug for URL generation
        $perusahaan = current_perusahaan();
        $perusahaan_slug = request()->route('perusahaan_slug') ?? perusahaan_slug($perusahaan);

        return view('pelanggan.returns.index', compact('returs', 'perusahaan_slug'));
    }

    public function create(Request $request)
    {
        // Run migration if columns are missing
        if (!Schema::hasColumn('returs', 'metode_refund') || !Schema::hasColumn('returs', 'bukti_foto')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('Auto migration failed: ' . $e->getMessage());
            }
        }

        $orderId = $request->query('order_id');
        $orders = Order::where('user_id', auth()->id())
            ->latest()->get(['id','nomor_order','total_amount','status']);

        // Get perusahaan_slug for URL generation
        $perusahaan = current_perusahaan();
        $perusahaan_slug = request()->route('perusahaan_slug') ?? perusahaan_slug($perusahaan);

        $order = null;
        if ($orderId) {
            $order = Order::with('items.produk')->where('user_id', auth()->id())->findOrFail($orderId);

            // 5-Hour Return Logic Restriction
            $paymentStatusLower = strtolower($order->payment_status);
            if ($paymentStatusLower !== 'paid' && $paymentStatusLower !== 'lunas') {
                return redirect()->route('pelanggan.returns.index', $perusahaan_slug ?? 'default')
                    ->with('error', 'Retur hanya dapat diajukan setelah pembayaran berhasil.');
            }
            if (!$order->paid_at) {
                return redirect()->route('pelanggan.returns.index', $perusahaan_slug ?? 'default')
                    ->with('error', 'Retur tidak dapat diajukan karena data waktu pembayaran tidak ditemukan.');
            }
            if (now()->greaterThan($order->paid_at->copy()->addHours(5))) {
                return redirect()->route('pelanggan.returns.index', $perusahaan_slug ?? 'default')
                    ->with('error', 'Batas waktu pengajuan retur telah berakhir. Retur hanya dapat diajukan maksimal 5 jam setelah pembayaran berhasil.');
            }
        }

        return view('pelanggan.returns.create', compact('orders','order', 'perusahaan_slug'));
    }

    public function store(Request $request)
    {
        // Run migration if columns are missing
        if (!Schema::hasColumn('returs', 'metode_refund') || !Schema::hasColumn('returs', 'bukti_foto')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                \Log::error('Auto migration failed: ' . $e->getMessage());
            }
        }

        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'tipe_kompensasi' => 'required|in:barang,uang',
                'alasan' => 'nullable|string',
                'metode_refund' => 'nullable|required_if:tipe_kompensasi,uang|in:tunai,transfer',
                'nama_bank' => 'nullable|required_if:metode_refund,transfer|string',
                'rekening_nomor' => 'nullable|required_if:metode_refund,transfer|string',
                'rekening_nama' => 'nullable|required_if:metode_refund,transfer|string',
                'bukti_foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120', // max 5MB
                'items' => 'required|array|min:1',
                'items.*.order_item_id' => 'required|exists:order_items,id',
                'items.*.qty' => 'required|integer|min:0',
            ]);

            // Pastikan order milik user
            $order = Order::with('items')->where('user_id', auth()->id())->findOrFail($request->order_id);

            // 5-Hour Return Logic Restriction
            if (strtolower($order->payment_status) !== 'paid' && strtolower($order->payment_status) !== 'lunas') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Retur hanya dapat diajukan setelah pembayaran berhasil.');
            }

            if (!$order->paid_at) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Retur tidak dapat diajukan karena data waktu pembayaran tidak ditemukan.');
            }

            if (now()->greaterThan($order->paid_at->copy()->addHours(5))) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Batas waktu pengajuan retur telah berakhir. Retur hanya dapat diajukan maksimal 5 jam setelah pembayaran berhasil.');
            }

            // Filter item milik order
            $itemsInput = collect($request->items)
                ->filter(fn($i) => (int)($i['qty'] ?? 0) > 0)
                ->values();
            if ($itemsInput->isEmpty()) {
                return back()->withInput()->with('error', 'Isi minimal satu item retur.');
            }

            // Build map order items for quick lookup
            $orderItems = $order->items->keyBy('id');

            // Handle file upload
            $buktiFotoPath = null;
            if ($request->hasFile('bukti_foto')) {
                $buktiFotoPath = $request->file('bukti_foto')->store('returns', 'public');
            }

            DB::beginTransaction();
            try {
                $kode = Retur::generateKodeRetur();

                $metodeRefundValue = null;
                if ($request->tipe_kompensasi === 'uang') {
                    if ($request->metode_refund === 'transfer') {
                        $metodeRefundValue = 'Transfer Bank: ' . $request->nama_bank . ' - ' . $request->rekening_nomor . ' a/n ' . $request->rekening_nama;
                    } else {
                        $metodeRefundValue = 'Tunai / Kas';
                    }
                }

                $retur = Retur::create([
                    'type' => 'sale',
                    'ref_id' => $order->id,
                    'tanggal' => now(), // Tambahkan tanggal
                    'kompensasi' => $request->tipe_kompensasi === 'barang' ? 'barang' : 'uang',
                    'metode_refund' => $metodeRefundValue,
                    'bukti_foto' => $buktiFotoPath,
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
                
                // Get perusahaan_slug for redirect
                $perusahaan_slug = request()->route('perusahaan_slug');
                
                return redirect()->route('pelanggan.returns.index', ['perusahaan_slug' => $perusahaan_slug])
                                ->with('success', 'Pengajuan retur berhasil dibuat.');
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error('Retur store error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withInput()->with('error', 'Gagal membuat retur: '.$e->getMessage());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Retur validation error', [
                'errors' => $e->errors()
            ]);
            return back()->withInput()->withErrors($e->errors());
        }
    }
}
