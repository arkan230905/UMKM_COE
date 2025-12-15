@extends('layouts.pelanggan')

@section('content')
<div class="hero-checkout mb-5">
    <div class="container py-5 text-center">
        <div class="hero-copy mx-auto">
            <span class="checkout-pill">Checkout 💳</span>
            <h1 class="checkout-title fw-bold">Konfirmasi Pesananmu</h1>
            <p class="checkout-subtext">Selesaikan detail pengiriman, pilih metode pembayaran, dan pesananmu siap diproses.</p>
        </div>
        <div class="checkout-steps mt-4 justify-content-center">
            <div class="step completed">
                <span class="step-index"><i class="bi bi-cart3"></i></span>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step current">
                <span class="step-index"><i class="bi bi-truck"></i></span>
                <span class="step-label">Checkout</span>
            </div>
            <div class="step">
                <span class="step-index"><i class="bi bi-receipt"></i></span>
                <span class="step-label">Pembayaran</span>
            </div>
        </div>
    </div>
</div>

<div class="container">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('pelanggan.checkout.process') }}" method="POST" id="checkoutForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card checkout-card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="section-title mb-4">
                        <span class="section-icon"><i class="bi bi-basket"></i></span>
                        <div>
                            <h5 class="mb-1">Detail Produk</h5>
                            <p class="section-subtitle">Ringkasan item yang akan kamu checkout.</p>
                        </div>
                    </div>
                    @if(isset($carts) && $carts->count())
                        <div class="product-list">
                            @foreach($carts as $item)
                                <div class="checkout-product">
                                    <div class="product-thumb">
                                        <img src="{{ optional($item->produk)->foto ? asset('storage/'.$item->produk->foto) : 'https://via.placeholder.com/80x80?text=Produk' }}" alt="{{ optional($item->produk)->nama_produk ?? 'Produk' }}">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">{{ optional($item->produk)->nama_produk ?? 'Produk Tanpa Nama' }}</h6>
                                        <p class="product-price mb-1">Rp {{ number_format(optional($item->produk)->harga_jual ?? 0, 0, ',', '.') }}</p>
                                        <span class="product-qty badge rounded-pill">Qty: {{ $item->qty }}</span>
                                    </div>
                                    <div class="product-total text-end">
                                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <i class="bi bi-bag-dash display-6 d-block mb-3"></i>
                            <p class="mb-0 text-muted">Tidak ada produk di keranjang.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card checkout-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="section-title mb-4">
                        <span class="section-icon"><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <h5 class="mb-1">Informasi Pengiriman</h5>
                            <p class="section-subtitle">Pastikan alamat dan kontak penerima sudah benar.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" name="nama_penerima" class="form-control" placeholder="Contoh: Siti Rahma" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" name="telepon_penerima" class="form-control" placeholder="Contoh: 0812 3456 7890" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Pengiriman</label>
                            <textarea name="alamat_pengiriman" class="form-control" rows="3" placeholder="Tuliskan alamat lengkap pengiriman" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Instruksi tambahan untuk kurir?"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @php
                $paymentOptions = [
                    ['value' => 'va_bca', 'label' => 'VA BCA', 'description' => 'Transfer ke virtual account BCA.', 'icon' => 'bi-building'],
                    ['value' => 'va_bni', 'label' => 'VA BNI', 'description' => 'Transfer ke virtual account BNI.', 'icon' => 'bi-building'],
                    ['value' => 'va_bri', 'label' => 'VA BRI', 'description' => 'Transfer ke virtual account BRI.', 'icon' => 'bi-building'],
                    ['value' => 'va_mandiri', 'label' => 'VA Mandiri', 'description' => 'Transfer ke virtual account Mandiri.', 'icon' => 'bi-building'],
                    ['value' => 'cash', 'label' => 'Bayar di Kasir', 'description' => 'Bayar langsung saat ambil pesanan.', 'icon' => 'bi-cash-stack'],
                ];
            @endphp

            <div class="card checkout-card border-0 shadow-sm order-summary">
                <div class="card-body">
                    <div class="section-title mb-4">
                        <span class="section-icon"><i class="bi bi-receipt-cutoff"></i></span>
                        <div>
                            <h5 class="mb-1">Ringkasan Pesanan</h5>
                            <p class="section-subtitle">Cek kembali total pembayaranmu.</p>
                        </div>
                    </div>
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-line">
                        <span>Ongkos Kirim</span>
                        <span>Rp {{ number_format(0, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                    </div>

                    <div class="section-title mt-4 mb-3">
                        <span class="section-icon soft"><i class="bi bi-wallet2"></i></span>
                        <div>
                            <h6 class="mb-1">Metode Pembayaran</h6>
                            <p class="section-subtitle mb-0">Pilih cara pembayaran yang paling nyaman.</p>
                        </div>
                    </div>

                    <div class="payment-options">
                        @foreach($paymentOptions as $index => $option)
                            <div class="payment-option">
                                <input class="payment-input" type="radio" name="payment_method" value="{{ $option['value'] }}" id="pm-{{ $option['value'] }}" {{ $index === 0 ? 'checked' : '' }}>
                                <label class="payment-card" for="pm-{{ $option['value'] }}">
                                    <div class="card-icon">
                                        <i class="bi {{ $option['icon'] }}"></i>
                                    </div>
                                    <div class="card-meta">
                                        <span class="card-title">{{ $option['label'] }}</span>
                                        <span class="card-description">{{ $option['description'] }}</span>
                                    </div>
                                    <div class="card-check">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-gradient w-100 mt-4">
                        <i class="bi bi-lock-fill me-2"></i>Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
@endsection

<style>
.hero-checkout {
    background: linear-gradient(135deg, #f6f8ff, #e8efff);
    border-radius: 0 0 22px 22px;
    position: relative;
    border-bottom: 1px solid #d9e2ff;
}

.hero-copy {
    max-width: 520px;
    color: #2c3e50;
}

.checkout-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #e0e7ff;
    padding: 6px 18px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #3f4a6b;
}

.checkout-title {
    font-size: 2.2rem;
    font-weight: 800;
    margin: 16px 0 8px;
    color: #2c3e50;
}

.checkout-subtext {
    font-size: 0.95rem;
    color: #5c6784;
    margin-bottom: 0;
}

.checkout-steps {
    display: flex;
    align-items: center;
    gap: 14px;
}

.checkout-steps .step {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #475569;
    box-shadow: 0 8px 20px rgba(148, 163, 184, 0.12);
}

.checkout-steps .step.completed {
    border-color: #c7d2fe;
    color: #4b5563;
}

.checkout-steps .step.current {
    border-color: #7d5cff;
    color: #2c3e50;
    box-shadow: 0 12px 28px rgba(125, 92, 255, 0.18);
}

.step-index {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #eef2ff;
    color: #5b21b6;
}

.checkout-card {
    border-radius: 18px;
    border: 1px solid #eef2ff;
    background: white;
    box-shadow: 0 16px 40px rgba(31, 41, 55, 0.06);
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(125, 92, 255, 0.12);
    color: #7d5cff;
    font-size: 1.1rem;
}

.section-icon.soft {
    background: rgba(125, 92, 255, 0.08);
}

.section-subtitle {
    color: #64748b;
    margin-bottom: 0;
    font-size: 0.92rem;
}

.product-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.checkout-product {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px;
    border-radius: 14px;
    background: #f7f9ff;
    border: 1px solid #edf1ff;
}

.product-thumb img {
    width: 68px;
    height: 68px;
    border-radius: 12px;
    object-fit: cover;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 700;
    color: #1f2937;
}

.product-price {
    color: #475569;
    font-size: 0.9rem;
}

.product-qty {
    background: #e0e7ff !important;
    color: #4338ca !important;
    font-weight: 600;
    padding: 4px 10px;
}

.product-total span {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1rem;
}

.empty-state {
    border-radius: 14px;
    background: #eef2ff;
    color: #475569;
}

.order-summary {
    position: sticky;
    top: 90px;
}

.summary-line,
.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 0.92rem;
    color: #334155;
}

.summary-total {
    padding: 12px 14px;
    border-radius: 12px;
    background: #f1f5ff;
    color: #2c3e50;
    font-weight: 700;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-option {
    position: relative;
}

.payment-input {
    position: absolute;
    opacity: 0;
}

.payment-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-card:hover {
    border-color: #c7d2fe;
    box-shadow: 0 10px 26px rgba(125, 92, 255, 0.16);
}

.payment-input:checked + .payment-card {
    border-color: #7d5cff;
    box-shadow: 0 12px 30px rgba(125, 92, 255, 0.18);
    background: #f8f9ff;
}

.card-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #eef2ff;
    color: #7d5cff;
    font-size: 1.2rem;
}

.payment-input:checked + .payment-card .card-icon {
    background: #7d5cff;
    color: white;
}

.card-meta {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.card-title {
    font-weight: 700;
    color: #1f2937;
}

.card-description {
    font-size: 0.8rem;
    color: #64748b;
}

.payment-input:checked + .payment-card .card-title {
    color: #2c3e50;
}

.card-check {
    color: #cbd5f5;
    font-size: 1.2rem;
}

.payment-input:checked + .payment-card .card-check {
    color: #7d5cff;
}

.btn-gradient {
    background: linear-gradient(135deg, #7d5cff, #9c6bff);
    border: none;
    padding: 14px 20px;
    font-weight: 600;
    color: white;
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 12px 28px rgba(125, 92, 255, 0.25);
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 36px rgba(125, 92, 255, 0.28);
}

@media (max-width: 991.98px) {
    .order-summary {
        position: static;
        margin-top: 24px;
    }

    .checkout-steps {
        flex-wrap: wrap;
    }

    .checkout-steps .step {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 575.98px) {
    .checkout-title {
        font-size: 1.8rem;
    }

    .checkout-product {
        flex-direction: column;
        align-items: flex-start;
    }

    .product-total {
        width: 100%;
        text-align: left;
        margin-top: 6px;
    }
}
</style>