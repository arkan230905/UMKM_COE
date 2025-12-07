@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Favorit Saya</h2>
    </div>

    <div class="row">
        @forelse($favorites as $fav)
        @php($produk = $fav->produk)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
                @if($produk && $produk->foto)
                <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top" alt="{{ $produk->nama_produk }}" style="height: 200px; object-fit: cover;">
                @else
                <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title text-dark">{{ $produk->nama_produk }}</h5>
                    <p class="fw-bold text-primary fs-5">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">Belum ada produk favorit.</div>
        </div>
        @endforelse
    </div>
</div>
@endsection
