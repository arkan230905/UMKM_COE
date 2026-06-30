@extends('layouts.pelanggan')

@section('content')
<div style="background: white; padding: 1.5rem 1rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: #2d3748; margin: 0;">📦 Detail Pesanan #{{ $order->nomor_order }}</h2>
            <p style="color: #999; margin: 0.3rem 0 0 0; font-size: 0.7rem;">Lihat detail lengkap pesanan Anda</p>
        </div>

        @if(session('success'))
        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 0.8rem; margin-bottom: 1rem; color: #155724; font-size: 0.7rem;">
            ✓ {{ session('success') }}
        </div>
        @endif

        <!-- Order Information Card -->
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; margin-bottom: 1rem;">
            <div style="padding: 1rem; border-bottom: 1px solid #f0f0f0;">
                <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">ℹ️ Informasi Pesanan</h6>
            </div>
            <div style="padding: 1rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Nomor Pesanan</div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #2d3748;">{{ $order->nomor_order }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Tanggal Pesanan</div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #2d3748;">{{ $order->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Status Pesanan</div>
                        <div style="font-size: 0.65rem;">{!! $order->status_badge !!}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Status Pembayaran</div>
                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.6rem; font-weight: 700; background: {{ $order->payment_status === 'paid' ? '#d4edda' : ($order->payment_status === 'failed' ? '#f8d7da' : '#fff3cd') }}; color: {{ $order->payment_status === 'paid' ? '#155724' : ($order->payment_status === 'failed' ? '#721c24' : '#856404') }};">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Metode Pembayaran</div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #2d3748;">{{ $order->payment_method_label }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Total Pembayaran</div>
                        <div style="font-size: 0.85rem; font-weight: 800; color: #8b6f47;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                    </div>
                    @if($order->paid_at)
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Dibayar Pada</div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #2d3748;">{{ $order->paid_at->format('d M Y H:i') }}</div>
                    </div>
                    @endif
                </div>

                @if($order->payment_status === 'pending')
                    @if($order->payment_gateway === 'midtrans' && $order->snap_token)
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 0.8rem; margin-top: 1rem; color: #856404; font-size: 0.7rem;">
                        ⚠️ Pesanan Anda menunggu pembayaran
                    </div>
                    <button id="pay-button" style="width: 100%; background: #10b981; color: white; border: none; border-radius: 8px; padding: 0.6rem; font-weight: 700; cursor: pointer; font-size: 0.7rem; margin-top: 0.8rem;">
                        💳 Bayar dengan Midtrans
                    </button>
                    @elseif($order->payment_gateway === 'manual_transfer')
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 0.8rem; margin-top: 1rem; color: #856404; font-size: 0.7rem;">
                        ⚠️ Pesanan Anda menunggu pembayaran via Transfer Bank Manual.
                        @if(!$order->bukti_pembayaran)
                        <form action="{{ route('orders.upload-bukti', $order->id) }}" method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
                            @csrf
                            <input type="file" name="bukti_pembayaran" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required style="font-size: 0.7rem; margin-bottom: 5px; width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                            @error('bukti_pembayaran')
                                <small style="color: #dc3545; display: block; margin-bottom: 5px;">{{ $message }}</small>
                            @enderror
                            <button type="submit" style="width: 100%; background: #2196f3; color: white; border: none; border-radius: 8px; padding: 0.6rem; font-weight: 700; cursor: pointer; font-size: 0.7rem;">
                                📤 Upload Bukti Transfer
                            </button>
                        </form>
                        @else
                        <div style="margin-top: 10px; padding: 8px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 6px; color: #2e7d32;">
                            ✓ Bukti transfer telah diupload dan sedang diverifikasi admin.
                        </div>
                        @endif
                    </div>
                    @endif
                @endif

                @if($order->payment_status === 'paid')
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 0.8rem; margin-top: 1rem; color: #155724; font-size: 0.7rem;">
                    ✓ Pembayaran berhasil! Pesanan Anda sedang diproses.
                </div>
                @endif
            </div>
        </div>

        <!-- Items Card -->
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; margin-bottom: 1rem;">
            <div style="padding: 1rem; border-bottom: 1px solid #f0f0f0;">
                <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">📦 Item Pesanan</h6>
            </div>
            <div style="padding: 1rem; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.65rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f0f0f0;">
                            <th style="text-align: left; padding: 0.4rem; font-weight: 700; color: #2d3748;">Produk</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Harga</th>
                            <th style="text-align: center; padding: 0.4rem; font-weight: 700; color: #2d3748;">Qty</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        @if($item->produk)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 0.4rem; color: #2d3748;">{{ $item->produk->nama_produk }}</td>
                            <td style="padding: 0.4rem; text-align: right; color: #8b6f47; font-weight: 600;">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td style="padding: 0.4rem; text-align: center; color: #2d3748;">{{ $item->qty }}</td>
                            <td style="padding: 0.4rem; text-align: right; color: #2d3748; font-weight: 600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @else
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 0.4rem; color: #999; font-style: italic;">Produk tidak ditemukan</td>
                            <td style="padding: 0.4rem; text-align: right; color: #8b6f47; font-weight: 600;">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td style="padding: 0.4rem; text-align: center; color: #2d3748;">{{ $item->qty }}</td>
                            <td style="padding: 0.4rem; text-align: right; color: #2d3748; font-weight: 600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #f0f0f0;">
                            <th colspan="3" style="text-align: right; padding: 0.4rem; font-weight: 600; color: #666;">Subtotal:</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 600; color: #2d3748;">Rp {{ number_format($order->subtotal_amount, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right; padding: 0.4rem; font-weight: 600; color: #666;">Ongkos Kirim:</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 600; color: #2d3748;">Rp {{ number_format($order->ongkir_amount, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right; padding: 0.4rem; font-weight: 600; color: #666;">PPN:</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 600; color: #2d3748;">Rp {{ number_format($order->ppn_amount, 0, ',', '.') }}</th>
                        </tr>
                        <tr style="background: #f9f9f9;">
                            <th colspan="3" style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Total Pembayaran:</th>
                            <th style="text-align: right; padding: 0.4rem; font-weight: 800; color: #8b6f47;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Right Column: Shipping & Timeline -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <!-- Shipping Info Card -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;">
                <div style="padding: 1rem; border-bottom: 1px solid #f0f0f0;">
                    <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">🚚 Data Pengiriman</h6>
                </div>
                <div style="padding: 1rem;">
                    <div style="margin-bottom: 0.8rem;">
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Nama Penerima</div>
                        <div style="font-size: 0.75rem; color: #2d3748; font-weight: 600;">{{ $order->nama_penerima }}</div>
                    </div>
                    <div style="margin-bottom: 0.8rem;">
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Alamat</div>
                        <div style="font-size: 0.75rem; color: #2d3748;">{{ $order->alamat_pengiriman }}</div>
                    </div>
                    <div style="margin-bottom: 0.8rem;">
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Telepon</div>
                        <div style="font-size: 0.75rem; color: #2d3748; font-weight: 600;">{{ $order->telepon_penerima }}</div>
                    </div>
                    @if($order->catatan)
                    <div>
                        <div style="font-size: 0.6rem; color: #999; margin-bottom: 0.2rem; font-weight: 600;">Catatan</div>
                        <div style="font-size: 0.75rem; color: #2d3748;">{{ $order->catatan }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline Card -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;">
                <div style="padding: 1rem; border-bottom: 1px solid #f0f0f0;">
                    <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">⏱️ Timeline</h6>
                </div>
                <div style="padding: 1rem;">
                    <div style="padding-left: 1.5rem; position: relative; margin-bottom: 0.8rem;">
                        <div style="position: absolute; left: 0; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #2d3748;">Pesanan Dibuat</div>
                        <small style="font-size: 0.6rem; color: #999;">{{ $order->created_at->format('d M Y H:i') }}</small>
                    </div>
                    @if($order->paid_at)
                    <div style="padding-left: 1.5rem; position: relative;">
                        <div style="position: absolute; left: 0; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #2d3748;">Pembayaran Berhasil</div>
                        <small style="font-size: 0.6rem; color: #999;">{{ $order->paid_at->format('d M Y H:i') }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.5rem; justify-content: center;">
            <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/returns/create?order_id=" . $order->id) }}" style="padding: 0.5rem 1.2rem; background: #f59e0b; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                🔄 Ajukan Retur
            </a>
            <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/orders") }}" style="padding: 0.5rem 1.2rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                ← Kembali ke Pesanan
            </a>
        </div>
    </div>
</div>

@if($order->payment_status === 'pending' && $order->snap_token)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function () {
    snap.pay('{{ $order->snap_token }}', {
        onSuccess: function(result){
            alert('Pembayaran berhasil!');
            window.location.reload();
        },
        onPending: function(result){
            alert('Menunggu pembayaran Anda');
            window.location.reload();
        },
        onError: function(result){
            alert('Pembayaran gagal! Silakan coba lagi.');
        },
        onClose: function(){
            alert('Anda menutup popup pembayaran');
        }
    });
});
</script>
@endif

@endsection
