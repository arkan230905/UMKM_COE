@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-white">Pesanan Saya</h2>

    @if($orders->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="mt-3 text-white">Belum Ada Pesanan</h4>
            <p class="text-muted">Anda belum memiliki pesanan</p>
            <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-shop"></i> Mulai Belanja
            </a>
        </div>
    </div>
    @else
    <div class="row">
        @foreach($orders as $order)
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <small class="text-muted">Nomor Pesanan</small>
                            <h6 class="mb-0 text-white">{{ $order->nomor_order }}</h6>
                            <small class="text-muted">{{ $order->created_at->format('d M Y') }}</small>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Total</small>
                            <h6 class="mb-0 text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h6>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Status Pesanan</small>
                            <div>{!! $order->status_badge !!}</div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Pembayaran</small>
                            <div>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Metode</small>
                            <div class="text-white small">{{ $order->payment_method_label }}</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('pelanggan.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if($order->payment_status === 'pending')
                            <a href="{{ route('pelanggan.orders.show', $order) }}" class="btn btn-sm btn-success mt-1">
                                <i class="bi bi-credit-card"></i> Bayar
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
