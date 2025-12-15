@extends('layouts.pelanggan')

@section('content')
<!-- HERO WRAPPER -->
<div class="orders-hero mb-5">
    <div class="container text-center">
        <span class="orders-pill">Pesanan Kamu 📦</span>
        <h1 class="orders-title fw-bold">Kelola & Pantau Status Pesananmu</h1>
        <p class="orders-subtext">Semua pembelianmu tersusun rapi dengan status terbaru secara real-time</p>
    </div>
</div>

<div class="container orders-wrapper">

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
    <div class="row g-4">
        @foreach($orders as $order)
        <div class="col-lg-6">
            <div class="order-card h-100">
                <div class="order-card__header">
                    <div>
                        <span class="order-number">{{ $order->nomor_order }}</span>
                        <span class="order-date">{{ $order->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="order-total text-end">
                        <small class="text-muted d-block">Total</small>
                        <span class="order-total__amount">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="order-card__body">
                    <div class="order-meta">
                        <div class="order-meta__item">
                            <span class="order-meta__label"><i class="bi bi-clipboard-check"></i> Status Pesanan</span>
                            <span class="order-meta__value">{!! $order->status_badge !!}</span>
                        </div>
                        <div class="order-meta__item">
                            <span class="order-meta__label"><i class="bi bi-credit-card"></i> Status Pembayaran</span>
                            <span class="order-meta__value">
                                <span class="badge rounded-pill bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </span>
                        </div>
                        <div class="order-meta__item">
                            <span class="order-meta__label"><i class="bi bi-wallet2"></i> Metode Pembayaran</span>
                            <span class="order-meta__value text-dark">{{ $order->payment_method_label }}</span>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="{{ route('pelanggan.orders.show', $order) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        @if($order->payment_status === 'pending')
                        <a href="{{ route('pelanggan.orders.show', $order) }}" class="btn btn-success">
                            <i class="bi bi-credit-card"></i> Lanjut Bayar
                        </a>
                        @endif
                        @if($order->status === 'completed' || $order->payment_status === 'paid')
                        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $order->id }}">
                            <i class="bi bi-star"></i> Tulis Review
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($orders instanceof \Illuminate\Contracts\Pagination\Paginator && $orders->hasPages())
    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
    @endif
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

<style>
.orders-hero {
    background: linear-gradient(135deg, #f6f8ff, #e8efff);
    border-radius: 0 0 22px 22px;
    padding: 36px 0 28px;
    border-bottom: 1px solid #d9e2ff;
    position: relative;
    overflow: hidden;
}

.orders-hero::after {
    content: "";
    position: absolute;
    bottom: -40px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle at center, rgba(118,75,162,0.18), rgba(118,75,162,0));
    transform: rotate(12deg);
}

.orders-pill {
    background: #e0e7ff;
    color: #3f4a6b;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.orders-title {
    font-size: 2rem;
    color: #2c3e50;
    margin-top: 12px;
}

.orders-subtext {
    color: #5c6784;
    margin-top: 6px;
    font-size: 0.95rem;
}

.orders-wrapper {
    margin-top: -30px;
    position: relative;
    z-index: 1;
}

.order-card {
    background: white;
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(31, 41, 55, 0.08);
    border: 1px solid #eef2ff;
    transition: all 0.3s ease;
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    position: relative;
    overflow: hidden;
}

.order-card::before {
    content: "";
    position: absolute;
    top: 0;
    right: -30px;
    width: 160px;
    height: 100%;
    background: linear-gradient(135deg, rgba(125,92,255,0.18), rgba(125,92,255,0));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.order-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 24px 45px rgba(31, 41, 55, 0.12);
}

.order-card:hover::before {
    opacity: 1;
}

.order-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.order-number {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2c3e50;
    letter-spacing: 0.3px;
}

.order-date {
    display: inline-block;
    margin-top: 4px;
    font-size: 0.82rem;
    color: #7b88a1;
}

.order-total__amount {
    font-size: 1.3rem;
    font-weight: 700;
    color: #7d5cff;
}

.order-card__body {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.order-meta {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.order-meta__item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: #f7f9ff;
    border-radius: 14px;
    border: 1px solid #edf1ff;
}

.order-meta__label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    color: #4b5563;
    font-weight: 600;
}

.order-meta__label i {
    color: #7d5cff;
}

.order-meta__value .badge {
    padding: 6px 14px;
    font-size: 0.8rem;
}

.order-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
}

.order-actions .btn {
    border-radius: 999px;
    padding: 8px 18px;
    font-weight: 600;
    font-size: 0.88rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
}

.order-actions .btn-primary {
    background: linear-gradient(135deg, #7d5cff, #9c6bff);
    box-shadow: 0 6px 20px rgba(125, 92, 255, 0.25);
}

.order-actions .btn-success {
    background: linear-gradient(135deg, #34d399, #10b981);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.2);
}

.order-actions .btn-outline-warning {
    border: 1px solid #f59e0b;
    color: #d97706;
    background: linear-gradient(135deg, rgba(255, 237, 213, 0.8), rgba(255, 246, 235, 0.9));
    box-shadow: 0 6px 18px rgba(251, 191, 36, 0.18);
}

.order-actions .btn-outline-warning:hover {
    background: #f59e0b;
    color: #fff;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 20px 45px rgba(31, 41, 55, 0.08);
    border: 1px solid #eef2ff;
}

.empty-state i {
    font-size: 4rem;
    color: #7d5cff;
    margin-bottom: 20px;
}

.empty-state h4 {
    color: #2c3e50;
}

.empty-state p {
    color: #6b7280;
}

.empty-state .btn-primary {
    border-radius: 999px;
    padding: 10px 24px;
    font-weight: 600;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.25);
}

@media (max-width: 991px) {
    .order-card__header {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-total {
        width: 100%;
        text-align: left !important;
    }

    .orders-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 576px) {
    .orders-hero {
        padding: 40px 0 36px;
    }

    .orders-wrapper {
        margin-top: -20px;
    }

    .order-card {
        padding: 20px;
    }

    .order-meta__item {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-actions {
        justify-content: flex-start;
    }

    .order-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
