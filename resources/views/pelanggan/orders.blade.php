@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-dark">Pesanan Saya</h2>

    @if($orders->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="mt-3 text-dark">Belum Ada Pesanan</h4>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <small class="text-muted">Nomor Pesanan</small>
                            <h6 class="mb-0 text-dark">{{ $order->nomor_order }}</h6>
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
                            <div class="text-dark small">{{ $order->payment_method_label }}</div>
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
                            @if($order->status === 'completed' || $order->payment_status === 'paid')
                            <a href="#" class="btn btn-sm btn-outline-warning mt-1" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $order->id }}">
                                <i class="bi bi-star"></i> Review
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

@foreach($orders as $order)
@if($order->status === 'completed' || $order->payment_status === 'paid')
<!-- Modal Review Order -->
<div class="modal fade" id="reviewModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pelanggan.reviews.store') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Beri Review: {{ $order->nomor_order }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="btn-group" role="group">
                            @for($i = 1; $i <= 5; $i++)
                            <input type="radio" class="btn-check" name="rating" id="rating{{ $i }}_{{ $order->id }}" value="{{ $i }}" required>
                            <label class="btn btn-outline-warning" for="rating{{ $i }}_{{ $order->id }}">
                                <i class="bi bi-star-fill"></i> {{ $i }}
                            </label>
                            @endfor
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="review_{{ $order->id }}" class="form-label">Komentar (opsional)</label>
                        <textarea class="form-control" id="review_{{ $order->id }}" name="comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
