@extends('layouts.pelanggan')
@section('content')

<div style="background: white; padding: 1rem 0.8rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 1rem;">
            <h2 style="font-size: 0.9rem; font-weight: 800; color: #2d3748; margin: 0 0 0.15rem 0;">❤️ Produk Favorit Saya</h2>
            <p style="color: #999; margin: 0; font-size: 0.6rem;">Koleksi produk pilihan Anda</p>
        </div>
        
        @if($favoriteProduks->count() > 0)
            <div id="products-section" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.6rem;">
                @foreach($favoriteProduks as $produk)
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;">
                    <div style="position: relative; height: 100px; background: #f5f5f5; overflow: hidden;">
                        @if($produk->foto)
                        <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">📦</div>
                        @endif
                        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/favorites/toggle") }}" method="POST" style="position: absolute; top: 4px; right: 4px;" onsubmit="return false;">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <button type="button" onclick="toggleFavorite({{ $produk->id }}); return false;" style="width: 24px; height: 24px; background: white; border: none; border-radius: 50%; cursor: pointer; color: #ff4757; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">♥</button>
                        </form>
                    </div>
                    <div style="padding: 0.5rem;">
                        <h3 style="font-weight: 700; color: #2d3748; margin: 0 0 0.15rem 0; font-size: 0.65rem;">{{ $produk->nama_produk }}</h3>
                        <div style="font-size: 0.55rem; color: #999; margin-bottom: 0.2rem;">{{ $produk->kategori->nama ?? 'Lainnya' }}</div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #8b6f47; margin-bottom: 0.3rem;">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</div>
                        <div style="font-size: 0.55rem; color: #999; margin-bottom: 0.5rem;">Stok: <span style="color: #2d3748; font-weight: 700;">{{ (int)$produk->stok }}</span></div>
                        <div style="display: flex; gap: 0.2rem;">
                            @if($produk->stok > 0)
                            <div id="cart-btn-{{ $produk->id }}" style="flex: 1;">
                                <button type="button" onclick="addToCart({{ $produk->id }}); return false;" style="width: 100%; background: #8b6f47; color: white; border: none; border-radius: 8px; padding: 0.3rem; font-weight: 700; cursor: pointer; font-size: 0.55rem;">🛒 Keranjang</button>
                            </div>
                            @else
                            <button style="flex: 1; background: #e0e0e0; color: #999; border: none; border-radius: 8px; padding: 0.3rem; font-weight: 700; cursor: not-allowed; font-size: 0.55rem; opacity: 0.6;" disabled>Habis</button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($favoriteProduks->hasPages())
            <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
                {{ $favoriteProduks->links() }}
            </div>
            @endif
        @else
            <div style="text-align: center; padding: 2rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.8rem;">💔</div>
                <h4 style="color: #999; font-size: 0.8rem; margin-bottom: 0.5rem;">Belum ada produk favorit</h4>
                <p style="color: #bbb; font-size: 0.65rem; margin-bottom: 1rem;">Mulai tambahkan produk favorit Anda dengan mengklik tombol hati di halaman produk</p>
                <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}" style="display: inline-block; padding: 0.5rem 1.2rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.7rem;">Jelajahi Produk</a>
            </div>
        @endif
    </div>
</div>

<script>
let isLoggedIn = {{ auth('pelanggan')->check() ? 'true' : 'false' }};

function toggleFavorite(produkId) {
    if (!isLoggedIn) {
        window.location.href = "{{ url("/" . $perusahaan_slug . "/pelanggan/login") }}";
        return;
    }
    
    fetch("{{ url("/" . $perusahaan_slug . "/pelanggan/favorites/toggle") }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            produk_id: produkId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update favorites list
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function addToCart(produkId) {
    if (!isLoggedIn) {
        window.location.href = "{{ url("/" . $perusahaan_slug . "/pelanggan/login") }}";
        return;
    }
    
    fetch("{{ url("/" . $perusahaan_slug . "/pelanggan/cart/ajax/store") }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            produk_id: produkId,
            qty: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Silent success - no notification
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

@endsection
