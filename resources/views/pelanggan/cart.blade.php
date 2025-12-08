@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-dark">Keranjang Belanja</h2>

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
