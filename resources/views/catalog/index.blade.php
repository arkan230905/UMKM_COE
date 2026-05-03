@extends('layouts.catalog')

@section('title', 'E-Catalog ' . ($company->nama ?? 'UMKM'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Catalog</h1>
            <p>Welcome to our catalog!</p>
            
            @if($company)
                <h2>{{ $company->nama }}</h2>
                <p>{{ $company->catalog_description ?? 'Company description' }}</p>
            @endif
            
            <div class="row">
                @forelse($produks as $produk)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $produk->nama_produk }}</h5>
                                <p class="card-text">{{ $produk->deskripsi ?? 'No description' }}</p>
                                <p class="card-text">
                                    <strong>Price:</strong> Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}
                                </p>
                                <p class="card-text">
                                    <strong>Stock:</strong> {{ $produk->stok ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p>No products available.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
