@extends('layouts.pelanggan')

@section('content')
<div class="hero-cart mb-5">
    <div class="cart-bubble bubble-1"></div>
    <div class="cart-bubble bubble-2"></div>
    <div class="container py-4 text-center">
        <span class="cart-pill">Checkout 💳</span>
        <h1 class="cart-title fw-bold">Konfirmasi Pesananmu</h1>
        <p class="cart-subtext">Periksa detail pesanan dan selesaikan pembayaran 💜</p>
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Detail Produk</h5>
                    @if(isset($carts))
                        @foreach($carts as $item)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <img src="{{ asset('storage/'.$item->produk->foto ?? 'placeholder.jpg') }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-1">{{ $item->produk->nama_produk }}</h6>
                                <p class="text-muted mb-1">Rp {{ number_format($item->produk->harga_jual, 0, ',', '.') }} x {{ $item->qty }}</p>
                                <p class="mb-0 fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">Tidak ada produk di keranjang</p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Informasi Pengiriman</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" name="nama_penerima" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" name="telepon_penerima" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Pengiriman</label>
                        <textarea name="alamat_pengiriman" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Ringkasan Pesanan</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkos Kirim</span>
                        <span>Rp {{ number_format(0, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong class="text-primary">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</strong>
                    </div>

                    <h6 class="mb-3">Metode Pembayaran</h6>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="qris" id="qris" checked>
                            <label class="form-check-label" for="qris">QRIS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="va_bca" id="va_bca">
                            <label class="form-check-label" for="va_bca">Virtual Account BCA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="va_bni" id="va_bni">
                            <label class="form-check-label" for="va_bni">Virtual Account BNI</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="va_bri" id="va_bri">
                            <label class="form-check-label" for="va_bri">Virtual Account BRI</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="va_mandiri" id="va_mandiri">
                            <label class="form-check-label" for="va_mandiri">Virtual Account Mandiri</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="cash" id="cash">
                            <label class="form-check-label" for="cash">Cash on Delivery / Bayar di Kasir</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-lock"></i> Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
@endsection

<style>
/* Hero Cart Styles */
.hero-cart {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 25px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
}

.cart-bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 6s ease-in-out infinite;
}

.bubble-1 {
    width: 80px;
    height: 80px;
    top: 20px;
    left: 10%;
    animation-delay: 0s;
}

.bubble-2 {
    width: 60px;
    height: 60px;
    bottom: 20px;
    right: 15%;
    animation-delay: 3s;
}

.cart-pill {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 20px;
    border-radius: 20px;
    color: white;
    font-size: 0.9rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.cart-title {
    color: white;
    font-size: 2.5rem;
    font-weight: 800;
    margin: 20px 0 10px 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.cart-subtext {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    margin-bottom: 0;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}
</style>