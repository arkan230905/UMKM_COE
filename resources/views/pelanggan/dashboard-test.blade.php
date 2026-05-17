@extends('layouts.pelanggan')
@section('content')
<div style="padding: 2rem;">
    <h1>Test Best Sellers Carousel</h1>
    
    <p>bestSellers count: {{ $bestSellers ? $bestSellers->count() : 'null' }}</p>
    
    @if($bestSellers && $bestSellers->count() > 0)
        <div style="background: #e0f2fe; padding: 2rem; border-radius: 10px;">
            <h2>Best Sellers Found: {{ $bestSellers->count() }}</h2>
            
            @foreach($bestSellers as $product)
            <div style="background: white; padding: 1rem; margin: 1rem 0; border-radius: 8px;">
                <h3>{{ $product->nama_produk }}</h3>
                <p>Harga: Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</p>
                <p>Stok: {{ $product->stok }}</p>
                <p>Terjual: {{ $product->total_terjual ?? 0 }}</p>
                @if($product->foto)
                    <img src="{{ storage_url($product->foto) }}" alt="{{ $product->nama_produk }}" style="max-width: 200px; height: auto;">
                @endif
            </div>
            @endforeach
        </div>
    @else
        <p style="color: red;">No best sellers found!</p>
    @endif
</div>
@endsection
