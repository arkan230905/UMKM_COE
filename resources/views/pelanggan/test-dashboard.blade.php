@extends('layouts.pelanggan')
@section('content')
<div style="padding: 2rem; background: #f5f5f5; min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px;">
        <h1 style="color: #333; margin-bottom: 1rem;">Test Dashboard</h1>
        <p style="color: #666; margin-bottom: 1rem;">This is a test view to check if the layout is working.</p>
        
        <h2 style="color: #333; margin-top: 2rem; margin-bottom: 1rem;">Data Check:</h2>
        <ul style="color: #666;">
            <li>Kategoris count: {{ $kategoris->count() ?? 0 }}</li>
            <li>Produks count: {{ $produks->count() ?? 0 }}</li>
            <li>Cart count: {{ $cartCount ?? 0 }}</li>
        </ul>
        
        <h2 style="color: #333; margin-top: 2rem; margin-bottom: 1rem;">Categories:</h2>
        @if($kategoris && $kategoris->count() > 0)
            <ul style="color: #666;">
                @foreach($kategoris as $kat)
                    <li>{{ $kat->nama }} (ID: {{ $kat->id }})</li>
                @endforeach
            </ul>
        @else
            <p style="color: #999;">No categories found</p>
        @endif
        
        <h2 style="color: #333; margin-top: 2rem; margin-bottom: 1rem;">Products:</h2>
        @if($produks && $produks->count() > 0)
            <ul style="color: #666;">
                @foreach($produks as $produk)
                    <li>{{ $produk->nama_produk }} - Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</li>
                @endforeach
            </ul>
        @else
            <p style="color: #999;">No products found</p>
        @endif
    </div>
</div>
@endsection
