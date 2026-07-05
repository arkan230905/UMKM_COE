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

    private function checkOrderEligibility($order, &$baseTime = null, &$errorMessage = null) {
        $status = strtolower($order->status);
        $paymentStatus = strtolower($order->payment_status);
        
        $isPaymentLunas = in_array($paymentStatus, ['paid', 'lunas']);
        
        // Khusus Ambil di Toko, jika lunas maka anggap selesai walaupun statusnya masih bisa diambil
        if ($isPaymentLunas && in_array($status, ['ready_for_pickup', 'bisa_diambil', 'bisa diambil'])) {
            $status = 'completed';
        }
        
        $isStatusSelesai = in_array($status, ['completed', 'selesai']);
        
        $invalidStatuses = ['pending', 'processing', 'diproses', 'ready_for_pickup', 'bisa_diambil', 'bisa diambil', 'cancelled', 'dibatalkan', 'rejected', 'ditolak', 'expired'];
        
        // Cek status tidak valid
        if (in_array($status, $invalidStatuses)) {
            $errorMessage = "Pesanan berstatus " . ucfirst($status) . " tidak dapat diretur.";
            return false;
        }
        
        // Harus selesai atau lunas
        if (!$isStatusSelesai && !$isPaymentLunas) {
            $errorMessage = "Pesanan belum selesai atau belum lunas.";
            return false;
        }
        
        // Prioritas field waktu
        $rawTime = $order->completed_at ?? $order->selesai_at ?? $order->order_completed_at ?? ($isPaymentLunas ? $order->paid_at : null) ?? $order->updated_at;
        
        if (!$rawTime) {
            $errorMessage = "Data waktu penyelesaian pesanan tidak ditemukan.";
            return false;
        }
        
        $baseTime = \Carbon\Carbon::parse($rawTime);
        
        if (now()->greaterThan($baseTime->copy()->addHours(5))) {
            $errorMessage = "Masa pengajuan retur untuk pesanan ini sudah berakhir. Retur hanya dapat diajukan maksimal 5 jam setelah pesanan selesai.";
            return false;
        }
        
        return true;
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
        
        // Get perusahaan_slug for URL generation
        $perusahaan = current_perusahaan();
        $perusahaan_slug = request()->route('perusahaan_slug') ?? perusahaan_slug($perusahaan);

        $allOrders = Order::where('user_id', auth()->id())
            ->where('perusahaan_id', $perusahaan->user_id)
            ->with(['items'])
            ->latest()
            ->get();
            
        \Log::info('Mengecek pesanan untuk retur', [
            'pelanggan_id' => auth()->id(),
            'perusahaan_id' => $perusahaan->user_id,
            'jumlah_total_order' => $allOrders->count()
        ]);
            
        $orders = $allOrders->filter(function($order) {
            $baseTime = null;
            $errorMsg = null;
            $isEligible = $this->checkOrderEligibility($order, $baseTime, $errorMsg);
            
            // Check if there are items left to return
            $hasRemaining = false;
            if ($isEligible) {
                foreach($order->items as $item) {
                    $returnedQty = \App\Models\ReturDetail::where('ref_detail_id', $item->id)->sum('qty');
                    if ($item->qty > $returnedQty) {
                        $hasRemaining = true;
                        break;
                    }
                }
                if (!$hasRemaining) {
                    $isEligible = false;
                    $errorMsg = "Semua item pesanan sudah diretur.";
                }
            }
            
            \Log::info('Log Evaluasi Pesanan Retur', [
                'nomor_order' => $order->nomor_order,
                'status_pesanan' => $order->status,
                'status_pembayaran' => $order->payment_status,
                'completed_at' => $order->completed_at ?? $order->selesai_at ?? $order->order_completed_at,
                'paid_at' => $order->paid_at,
                'updated_at' => $order->updated_at,
                'batas_retur' => $baseTime ? \Carbon\Carbon::parse($baseTime)->copy()->addHours(5)->toDateTimeString() : null,
                'sekarang' => now()->toDateTimeString(),
                'eligible_retur' => $isEligible,
                'alasan' => $errorMsg
            ]);
            
            if ($isEligible) {
                $order->calculated_base_time = $baseTime;
                return true;
            }
            
            return false;
        })->values();

        $order = null;
        if ($orderId) {
            $order = Order::with('items.produk')->where('user_id', auth()->id())->where('perusahaan_id', $perusahaan->user_id)->findOrFail($orderId);

            $baseTime = null;
            $errorMsg = null;
            if (!$this->checkOrderEligibility($order, $baseTime, $errorMsg)) {
                return redirect()->route('pelanggan.returns.create', $perusahaan_slug ?? 'default')
                    ->with('error', $errorMsg);
            }
            
            $order->calculated_base_time = $baseTime;
            
            // Filter order items that are fully returned
            $order->items = $order->items->filter(function($item) {
                $returnedQty = \App\Models\ReturDetail::where('ref_detail_id', $item->id)->sum('qty');
                $item->remaining_qty = $item->qty - $returnedQty;
                return $item->remaining_qty > 0;
            })->values();
            
            if ($order->items->isEmpty()) {
                return redirect()->route('pelanggan.returns.create', $perusahaan_slug ?? 'default')
                    ->with('error', 'Semua item dalam pesanan ini sudah diretur.');
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
                // Delivery fields
                'metode_pengambilan_retur' => 'nullable|required_if:tipe_kompensasi,barang|in:ambil_di_toko,delivery',
                'alamat_retur' => 'nullable|required_if:metode_pengambilan_retur,delivery|string',
                'detail_alamat_retur' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kota' => 'nullable|string',
                'provinsi' => 'nullable|string',
                'kode_pos' => 'nullable|string',
                'latitude_pengiriman' => 'nullable|required_if:metode_pengambilan_retur,delivery|string',
                'longitude_pengiriman' => 'nullable|required_if:metode_pengambilan_retur,delivery|string',
                'biaya_ongkir' => 'nullable|required_if:metode_pengambilan_retur,delivery|numeric|min:0',
            ]);

            // Pastikan order milik user dan dari perusahaan yang benar
            $perusahaan = current_perusahaan();
            $order = Order::with('items')->where('user_id', auth()->id())->where('perusahaan_id', $perusahaan->user_id)->findOrFail($request->order_id);

            // 5-Hour Return Logic Restriction
            $baseTime = null;
            $errorMsg = null;
            if (!$this->checkOrderEligibility($order, $baseTime, $errorMsg)) {
                \Log::info('Order tidak eligible saat submit retur', [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'alasan' => $errorMsg
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMsg);
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

                $returData = [
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
                ];

                if ($request->tipe_kompensasi === 'barang') {
                    $returData['metode_pengambilan_retur'] = $request->metode_pengambilan_retur;
                    if ($request->metode_pengambilan_retur === 'delivery') {
                        $returData['alamat_retur'] = $request->alamat_retur;
                        $returData['detail_alamat_retur'] = $request->detail_alamat_retur;
                        $returData['kecamatan'] = $request->kecamatan;
                        $returData['kota'] = $request->kota;
                        $returData['provinsi'] = $request->provinsi;
                        $returData['kode_pos'] = $request->kode_pos;
                        $returData['latitude'] = $request->latitude_pengiriman;
                        $returData['longitude'] = $request->longitude_pengiriman;
                        $returData['ongkir_retur'] = $request->biaya_ongkir;
                    } else {
                        $returData['ongkir_retur'] = 0;
                    }
                }

                $retur = Retur::create($returData);

                $total = 0;
                foreach ($itemsInput as $row) {
                    $oi = $orderItems->get((int)$row['order_item_id']);
                    if (!$oi) { continue; }
                    $qtyReq = (int)$row['qty'];
                    $returnedQty = \App\Models\ReturDetail::where('ref_detail_id', $oi->id)->sum('qty');
                    $qtyMax = (int)$oi->qty - $returnedQty;
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
