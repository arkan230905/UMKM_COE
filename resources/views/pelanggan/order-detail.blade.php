@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Detail Pesanan #{{ $order->nomor_order }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('pelanggan.returns.create', ['order_id' => $order->id]) }}" class="btn btn-outline-warning">
                <i class="bi bi-arrow-counterclockwise"></i> Ajukan Retur
            </a>
            <a href="{{ route('pelanggan.orders') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-info-circle"></i> Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>Nomor Pesanan:</strong></td>
                            <td>{{ $order->nomor_order }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Pesanan:</strong></td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Pesanan:</strong></td>
                            <td>{!! $order->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Pembayaran:</strong></td>
                            <td>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Metode Pembayaran:</strong></td>
                            <td>{{ $order->payment_method_label }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Pembayaran:</strong></td>
                            <td class="fw-bold fs-5 text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->paid_at)
                        <tr>
                            <td><strong>Dibayar Pada:</strong></td>
                            <td>{{ $order->paid_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($order->payment_status === 'pending' && $order->snap_token)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Pesanan Anda menunggu pembayaran
                    </div>
                    <button id="pay-button" class="btn btn-success w-100 py-3">
                        <i class="bi bi-credit-card"></i> Bayar Sekarang
                    </button>
                    @endif

                    @if($order->payment_status === 'paid')
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Pembayaran berhasil! Pesanan Anda sedang diproses.
                    </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-box-seam"></i> Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->produk->nama_produk }}</td>
                                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-geo-alt"></i> Data Pengiriman</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong class="text-dark">Nama Penerima:</strong><br>
                        <span class="text-dark">{{ $order->nama_penerima }}</span>
                    </p>
                    <p class="mb-2">
                        <strong class="text-dark">Alamat:</strong><br>
                        <span class="text-dark">{{ $order->alamat_pengiriman }}</span>
                    </p>
                    <p class="mb-2">
                        <strong class="text-dark">Telepon:</strong><br>
                        <span class="text-dark">{{ $order->telepon_penerima }}</span>
                    </p>
                    @if($order->catatan)
                    <p class="mb-0">
                        <strong class="text-dark">Catatan:</strong><br>
                        <span class="text-dark">{{ $order->catatan }}</span>
                    </p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-dark"><i class="bi bi-clock-history"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="text-dark">Pesanan Dibuat</span>
                            <small class="text-muted d-block">{{ $order->created_at->format('d M Y H:i') }}</small>
                        </div>
                        @if($order->paid_at)
                        <div class="timeline-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="text-dark">Pembayaran Berhasil</span>
                            <small class="text-muted d-block">{{ $order->paid_at->format('d M Y H:i') }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
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

<style>
.timeline-item {
    padding-left: 30px;
    position: relative;
    padding-bottom: 15px;
}
.timeline-item i {
    position: absolute;
    left: 0;
    top: 0;
}
</style>
@endsection
