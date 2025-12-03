@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Katalog Produk</h2>
        <a href="{{ route('pelanggan.cart') }}" class="btn btn-primary">
            <i class="bi bi-cart"></i> Keranjang 
            @if($cartCount > 0)
            <span class="badge bg-danger">{{ $cartCount }}</span>
            @endif
        </a>
    </div>

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

    <div class="row">
        @forelse($produks as $produk)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
                @if($produk->foto)
                <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top" alt="{{ $produk->nama_produk }}" style="height: 200px; object-fit: cover;">
                @else
                <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title text-white">{{ $produk->nama_produk }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($produk->deskripsi ?? 'Produk berkualitas', 80) }}</p>
                    <p class="fw-bold text-primary fs-5">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                    <p class="small text-white">
                        Stok: 
                        @if($produk->stok > 10)
                        <span class="badge bg-success">{{ $produk->stok }}</span>
                        @elseif($produk->stok > 0)
                        <span class="badge bg-warning">{{ $produk->stok }}</span>
                        @else
                        <span class="badge bg-danger">Habis</span>
                        @endif
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    @if($produk->stok > 0)
                    <form action="{{ route('pelanggan.cart.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                    @else
                    <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada produk tersedia saat ini.
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $produks->links() }}
    </div>
</div>
@endsection
