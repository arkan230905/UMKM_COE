@extends('layouts.pelanggan')

@section('content')
<!-- HERO AESTHETIC SPECIAL FOR CART -->
<div class="hero-cart mb-5">

    <!-- Bubbles -->
    <div class="cart-bubble bubble-1"></div>
    <div class="cart-bubble bubble-2"></div>

    <div class="container py-4 text-center">
        
        <span class="cart-pill">Keranjang Kamu 🛒</span>

        <h1 class="cart-title fw-bold">
            Kelola & Review Pesananmu
        </h1>

        <p class="cart-subtext">
            Cek kembali produk pilihanmu, atur jumlahnya, lalu lanjut checkout 💜
        </p>

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

    @if($carts->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="mt-3 text-dark">Keranjang Kosong</h4>
            <p class="text-muted">Belum ada produk di keranjang Anda</p>
            <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-shop"></i> Mulai Belanja
            </a>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th width="150">Qty</th>
                            <th>Subtotal</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carts as $cart)
                        <tr>
                            <td>
                                <strong>{{ $cart->produk->nama_produk }}</strong>
                                <br>
                                <small class="text-secondary">Stok : {{ number_format($cart->produk->stok, 0, ',', '.') }}</small>
                            </td>
                            <td>Rp {{ number_format($cart->harga, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <form action="{{ route('pelanggan.cart.update', $cart) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="qty" value="{{ max(1, $cart->qty - 1) }}">
                                        <button type="submit" class="btn btn-sm btn-primary" {{ $cart->qty <= 1 ? 'disabled' : '' }}>
                                            <i class="bi bi-dash"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('pelanggan.cart.update', $cart) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="qty" value="{{ $cart->qty }}" min="1" max="{{ $cart->produk->stok }}" class="form-control" onchange="this.form.submit()">
                                        </div>
                                    </form>

                                    <form action="{{ route('pelanggan.cart.update', $cart) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="qty" value="{{ min($cart->produk->stok, $cart->qty + 1) }}">
                                        <button type="submit" class="btn btn-sm btn-primary" {{ $cart->qty >= $cart->produk->stok ? 'disabled' : '' }}>
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="fw-bold">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('pelanggan.cart.destroy', $cart) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item ini dari keranjang?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="3" class="text-end">Total:</th>
                            <th class="fs-5">Rp {{ number_format($total, 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('pelanggan.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Lanjut Belanja
        </a>
        <div>
            <form action="{{ route('pelanggan.cart.clear') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Kosongkan semua keranjang?')">
                    <i class="bi bi-trash"></i> Kosongkan Keranjang
                </button>
            </form>
            <a href="{{ route('pelanggan.checkout') }}" class="btn btn-primary ms-2">
                Checkout <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

<style>

/* ======================================================
   HERO CART AESTHETIC (MATCH TEMA DASHBOARD)
====================================================== */
.hero-cart {
    background: linear-gradient(135deg, #f6f8ff, #e8efff);
    border-radius: 0 0 18px 18px;
    padding: 28px 0 22px; /* pendek & profesional */
    text-align: center;
    color: #2f2f45; /* abu gelap elegan */
    border-bottom: 1px solid #d9e2ff; /* formal separator */
}

/* Tidak ada bubble */
.cart-bubble, .bubble-1, .bubble-2 {
    display: none !important;
}

/* Title */
.cart-title {
    font-size: 1.8rem;
    font-weight: 400;
    color: #2c3e50; /* corporate friendly */
}

.cart-pill {
    background: #e0e7ff;
    color: #3f4a6b;
    padding: 5px 14px;
    border-radius: 30px;
    font-size: .78rem;
    font-weight: 600;
}

.cart-subtext {
    font-size: .9rem;
    color: #5c6784;
    margin-top: 4px;
}

/* ======================================================
   TABLE CART STYLING (CLEAN & MODERN)
====================================================== */
.table-hover tbody tr:hover {
    background: #f6f1ff !important;
}

.table thead th {
    background: #f3ecff !important;
    color: #4b2e83;
}

.table-striped > tbody > tr:nth-child(odd) {
    background-color: #faf7ff !important;
}

/* Qty buttons */
.btn-primary {
    background: linear-gradient(135deg, #7d5cff, #9c6bff) !important;
    border: none !important;
}
.btn-primary:hover {
    box-shadow: 0 4px 15px rgba(125,92,255,0.4);
}

/* Remove Button */
.btn-danger {
    background: #ff5e7d !important;
    border: none !important;
}
.btn-danger:hover {
    background: #e44866 !important;
    transform: translateY(-2px);
}

/* Quantity input */
input[type="number"] {
    border: 1px solid #d3c7ff !important;
    border-radius: 5px !important;
}

/* Summary Section Buttons */
.btn-outline-danger {
    border-radius: 25px !important;
    border-color: #ff7d96 !important;
    color: #ff7d96 !important;
}
.btn-outline-danger:hover {
    background: #ff7d96 !important;
    color: white !important;
}

/* Checkout Button */
.btn-primary.ms-2 {
    font-weight: 600;
    border-radius: 25px;
}

/* Responsive */
@media (max-width: 768px) {
    .cart-title {
        font-size: 1.7rem;
    }
    .hero-cart {
        padding: 40px 0;
    }
}

</style>

