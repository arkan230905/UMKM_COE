@extends('layouts.pelanggan')

@section('content')
<div style="background-color: #faf7f2; min-height: 100vh; padding-bottom: 3rem; font-family: 'Inter', 'Segoe UI', sans-serif;">
    
    <!-- Secondary Header / Search Bar area -->
    <div style="background-color: white; padding: 1rem 0; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 2rem;">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="background: #8b5a2b; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    <i class="bi bi-bag-fill"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-weight: 700; color: #333; letter-spacing: 1px; font-size: 1.1rem;">SIMCOST</h4>
                    <small style="color: #888; font-size: 0.7rem;">Belanja Online</small>
                </div>
            </div>
            <div style="flex: 0 1 400px; position: relative;">
                <form action="{{ route('pelanggan.dashboard') }}" method="GET">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk favoritmu..." style="width: 100%; padding: 0.4rem 1rem; border-radius: 50px; border: 1px solid #eaeaea; background: #fbfbfb; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02); transition: all 0.3s; font-size: 0.8rem;">
                    <button type="submit" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #888; cursor: pointer;">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Hero Section -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: #faebd7; color: #8b5a2b; padding: 0.2rem 0.6rem; border-radius: 50px; font-weight: 600; font-size: 0.7rem; margin-bottom: 1rem;">
                    <i class="bi bi-hand-thumbs-up"></i> Selamat Datang!
                </div>
                <h1 style="font-size: 2.2rem; font-weight: 800; color: #333; line-height: 1.1; margin-bottom: 0.8rem;">
                    Selamat Datang di<br><span style="color: #8b5a2b;">UMKM COE</span>
                </h1>
                <p style="color: #666; font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.5rem; max-width: 90%;">
                    Temukan produk berkualitas terbaik dengan harga terjangkau. Belanja sekarang dan nikmati pengalaman berbelanja yang menyenangkan!
                </p>
                
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="#products" style="background: #8b5a2b; color: white; padding: 0.4rem 1.2rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.3s;">
                        <i class="bi bi-bag"></i> Mulai Belanja
                    </a>
                    <a href="{{ route('pelanggan.cart') }}" style="background: white; color: #8b5a2b; border: 1px solid #8b5a2b; padding: 0.4rem 1.2rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.3s;">
                        <i class="bi bi-cart3"></i> Keranjang Saya
                    </a>
                </div>

                <!-- Features -->
                <div class="d-flex flex-wrap gap-3 mt-3">
                    <div class="d-flex align-items-start gap-2">
                        <div style="color: #8b5a2b; font-size: 1rem;"><i class="bi bi-award"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.75rem; color: #333;">Produk Berkualitas</div>
                            <div style="font-size: 0.65rem; color: #888;">Kualitas terbaik pilihan</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <div style="color: #8b5a2b; font-size: 1rem;"><i class="bi bi-tags"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.75rem; color: #333;">Harga Terjangkau</div>
                            <div style="font-size: 0.65rem; color: #888;">Bersahabat di kantong</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <div style="color: #8b5a2b; font-size: 1rem;"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.75rem; color: #333;">Aman & Terpercaya</div>
                            <div style="font-size: 0.65rem; color: #888;">Belanja aman & nyaman</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <div style="color: #8b5a2b; font-size: 1rem;"><i class="bi bi-truck"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.75rem; color: #333;">Pengiriman Cepat</div>
                            <div style="font-size: 0.65rem; color: #888;">Sampai ke tanganmu</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 position-relative text-center">
                <div style="position: absolute; width: 100%; height: 100%; background: #f0e6d2; border-radius: 50% 50% 40% 60% / 60% 40% 50% 50%; z-index: 0; transform: scale(0.9); right: -5%;"></div>
                
                <!-- Floating UMKM Card -->
                <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: white; padding: 0.8rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); z-index: 2; width: 130px; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.3rem; color: #8b5a2b; font-size: 0.7rem; font-weight: 600;">
                        <i class="bi bi-star"></i> UMKM Pilihan
                    </div>
                    <div style="font-weight: 700; font-size: 0.9rem; color: #333; margin-bottom: 0.2rem;">100+ Produk</div>
                    <div style="font-size: 0.6rem; color: #888; margin-bottom: 0.6rem;">Siap Anda Jelajahi</div>
                    <a href="#products" style="display: inline-block; padding: 0.2rem 0.6rem; border: 1px solid #e0e0e0; border-radius: 50px; font-size: 0.7rem; color: #555; text-decoration: none; width: 100%; text-align: center; transition: all 0.2s;">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <img src="https://images.unsplash.com/photo-1605814562479-0db76ea3b482?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Produk UMKM" style="max-width: 75%; height: 280px; object-fit: cover; border-radius: 20px; position: relative; z-index: 1; box-shadow: 0 20px 40px rgba(139, 90, 43, 0.15);">
                
                <!-- Dots decoration -->
                <div style="position: absolute; top: 10%; right: 10%; width: 60px; height: 60px; background-image: radial-gradient(#d4a574 2px, transparent 2px); background-size: 10px 10px; z-index: 0;"></div>
            </div>
        </div>

        <!-- Section Title & Categories -->
        <div id="products" class="text-center mb-4 mt-5 pt-5">
            <div style="display: inline-flex; align-items: center; gap: 0.8rem; font-size: 1.5rem; font-weight: 800; color: #333;">
                <span style="color: #d4a574;">&mdash;</span> <i class="bi bi-bag-heart-fill text-brown" style="color: #8b5a2b;"></i> Semua Produk <span style="color: #d4a574;">&mdash;</span>
            </div>
            <p style="color: #666; margin-top: 0.5rem; font-size: 0.9rem;">Jelajahi berbagai produk menarik dari UMKM pilihan</p>
        </div>

        <!-- Category Pills -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
            <a href="{{ route('pelanggan.dashboard') }}" style="padding: 0.4rem 1.2rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.3s; {{ !$kategoriFilter ? 'background: #8b5a2b; color: white;' : 'background: white; color: #555; border: 1px solid #eaeaea;' }}">
                <i class="bi bi-grid"></i> Semua Produk
            </a>
            @if($kategoris && $kategoris->count() > 0)
                @foreach($kategoris as $kat)
                <a href="{{ route('pelanggan.dashboard', ['kategori' => $kat->id]) }}" style="padding: 0.4rem 1.2rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.4rem; {{ $kategoriFilter == $kat->id ? 'background: #8b5a2b; color: white;' : 'background: white; color: #555; border: 1px solid #eaeaea;' }}">
                    @php
                        $icons = ['MKN' => 'bi-cup-hot', 'MNM' => 'bi-cup-straw', 'SNK' => 'bi-cookie', 'FSH' => 'bi-handbag', 'KRJ' => 'bi-palette', 'KCT' => 'bi-stars', 'LNY' => 'bi-box-seam'];
                        $iconClass = $icons[$kat->kode_kategori] ?? 'bi-tag';
                    @endphp
                    <i class="bi {{ $iconClass }}"></i> {{ $kat->nama }}
                </a>
                @endforeach
            @endif
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" style="background-color: #d1e7dd; color: #0f5132;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Horizontal Product Cards -->
        <div class="row g-4">
            @if($produks && $produks->count() > 0)
                @foreach($produks as $produk)
                <div class="col-md-6 col-lg-4">
                    <div style="background: white; border-radius: 16px; padding: 12px; display: flex; gap: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: transform 0.3s ease, box-shadow 0.3s ease; border: 1px solid #f9f9f9; position: relative; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.03)';">
                        
                        <!-- Image Left -->
                        <div style="width: 130px; height: 130px; border-radius: 12px; overflow: hidden; flex-shrink: 0; background: #f5f5f5;">
                            @if($produk->foto)
                            <img src="{{ storage_url($produk->foto) }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #ccc;">
                                <i class="bi bi-image"></i>
                            </div>
                            @endif
                        </div>

                        <!-- Details Right -->
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; position: relative;">
                            
                            

                            <h3 style="font-size: 1rem; font-weight: 700; color: #2d3748; margin-bottom: 0.2rem; line-height: 1.3; padding-right: 20px;">
                                {{ $produk->nama_produk }}
                            </h3>
                            <div style="font-size: 0.8rem; color: #888; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                <span>{{ $produk->kategori->nama ?? 'Kategori' }}</span>
                                <span style="font-size: 0.4rem;">⚫</span>
                                <span style="color: {{ $produk->stok_tersedia > 0 ? '#28a745' : '#dc3545' }}">Stok: {{ number_format($produk->stok_tersedia, 0, ',', '.') }}</span>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: #8b5a2b; margin-bottom: 1rem;">
                                Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                            </div>
                            
                            <div class="d-flex align-items-center gap-2" style="margin-top: auto;">
                                <div id="cart-btn-{{ $produk->id }}" data-stok="{{ $produk->stok_tersedia }}" style="flex: 1;">
                                    <button onclick="addToCart({{ $produk->id }})" style="width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 0.3rem; padding: 0.35rem 0.6rem; border-radius: 50px; border: none; background: #8b5a2b; color: white; font-size: 0.75rem; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#a67546';" onmouseout="this.style.background='#8b5a2b';">
                                        <i class="bi bi-cart-plus"></i> Keranjang
                                    </button>
                                </div>
                                
                                <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                    <button type="submit" style="display: inline-flex; justify-content: center; align-items: center; width: 32px; height: 32px; padding: 0; border-radius: 50px; border: 1px solid {{ in_array($produk->id, $favoriteIds ?? []) ? '#ff4757' : '#eaeaea' }}; background: white; color: {{ in_array($produk->id, $favoriteIds ?? []) ? '#ff4757' : '#888' }}; font-size: 0.9rem; transition: all 0.2s;" onmouseover="this.style.borderColor='#ff4757'; this.style.color='#ff4757';" onmouseout="this.style.borderColor='{{ in_array($produk->id, $favoriteIds ?? []) ? '#ff4757' : '#eaeaea' }}'; this.style.color='{{ in_array($produk->id, $favoriteIds ?? []) ? '#ff4757' : '#888' }}';">
                                        <i class="{{ in_array($produk->id, $favoriteIds ?? []) ? 'bi bi-heart-fill' : 'bi bi-heart' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-12 text-center py-5">
                    <div style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"><i class="bi bi-box-seam"></i></div>
                    <h4 style="color: #666; font-weight: 600;">Belum ada produk tersedia</h4>
                    <p style="color: #999;">Coba sesuaikan kata kunci pencarian atau filter kategori.</p>
                </div>
            @endif
        </div>

        @if($produks && $produks->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $produks->links() }}
        </div>
        @endif
        
    </div>
</div>

<script>
// Cart state management
let cartItems = {};

// Load cart items on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
});

function loadCartItems() {
    fetch('{{ route("pelanggan.cart.ajax.items") }}')
        .then(response => response.json())
        .then(data => {
            cartItems = data;
            updateCartButtons();
        })
        .catch(error => console.error('Error loading cart:', error));
}

function updateCartButtons() {
    let totalQty = 0;
    Object.keys(cartItems).forEach(produkId => {
        const cartBtn = document.getElementById('cart-btn-' + produkId);
        if (cartItems[produkId]) {
            const qty = parseInt(cartItems[produkId].qty) || 0;
            totalQty += qty;
            
            if (cartBtn) {
                const maxStok = cartBtn.getAttribute('data-stok') || 999;
                cartBtn.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.3rem;">
                        <button onclick="updateCartQty(${produkId}, -1)" style="width: 28px; height: 28px; border-radius: 50%; border: none; background: #8b5a2b; color: white; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; cursor: pointer;">-</button>
                        <input type="number" id="qty-input-${produkId}" value="${qty}" onchange="setCartQty(${produkId}, this.value)" style="width: 40px; text-align: center; border: 1px solid #ddd; border-radius: 6px; font-weight: 600; font-size: 0.85rem; padding: 0.2rem 0; appearance: textfield;" min="1" max="${maxStok}">
                        <button onclick="updateCartQty(${produkId}, 1)" style="width: 28px; height: 28px; border-radius: 50%; border: none; background: #8b5a2b; color: white; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; cursor: pointer;">+</button>
                    </div>
                `;
            }
        }
    });

    // Update navbar badge
    const badge = document.getElementById('cart-badge-header');
    if (badge) {
        if (totalQty > 0) {
            badge.style.display = 'block';
            badge.innerText = totalQty;
        } else {
            badge.style.display = 'none';
        }
    }
}

function addToCart(produkId) {
    const formData = new FormData();
    formData.append('produk_id', produkId);
    formData.append('qty', 1);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('{{ route("pelanggan.cart.ajax.store") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cartItems[produkId] = { qty: data.qty, id: data.cart_id };
            updateCartButtons();
        } else {
            alert(data.error || 'Gagal menambahkan ke keranjang');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan ke keranjang');
    });
}

function updateCartQty(produkId, change) {
    const currentQty = cartItems[produkId]?.qty || 0;
    const newQty = currentQty + change;
    setCartQty(produkId, newQty);
}

function setCartQty(produkId, newQty) {
    newQty = parseInt(newQty);
    if (isNaN(newQty)) return;
    
    if (newQty <= 0) {
        removeCartItem(produkId);
        return;
    }
    
    const cartBtn = document.getElementById('cart-btn-' + produkId);
    const maxStok = cartBtn ? parseInt(cartBtn.getAttribute('data-stok') || 999) : 999;
    
    if (newQty > maxStok) {
        alert('Stok tidak mencukupi! Maksimal: ' + maxStok);
        updateCartButtons(); // revert visually
        return;
    }

    // Optimistic UI update
    const previousQty = cartItems[produkId].qty;
    cartItems[produkId].qty = newQty;
    updateCartButtons();

    const formData = new FormData();
    formData.append('qty', newQty);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'PUT');

    fetch(`{{ route("pelanggan.cart.ajax.update", ":id") }}`.replace(':id', cartItems[produkId].id), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.error || 'Gagal mengupdate keranjang');
            // Revert on fail
            cartItems[produkId].qty = previousQty;
            updateCartButtons();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert on fail
        cartItems[produkId].qty = previousQty;
        updateCartButtons();
    });
}

function removeCartItem(produkId) {
    if (!confirm('Hapus item ini dari keranjang?')) return;

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'DELETE');

    fetch(`{{ route("pelanggan.cart.ajax.destroy", ":id") }}`.replace(':id', cartItems[produkId].id), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            delete cartItems[produkId];
            updateCartButtons();
            
            // Reset button to "Keranjang"
            const cartBtn = document.getElementById('cart-btn-' + produkId);
            if (cartBtn) {
                cartBtn.innerHTML = `
                    <button onclick="addToCart(${produkId})" style="width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 0.3rem; padding: 0.35rem 0.6rem; border-radius: 50px; border: none; background: #8b5a2b; color: white; font-size: 0.75rem; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#a67546';" onmouseout="this.style.background='#8b5a2b';">
                        <i class="bi bi-cart-plus"></i> Keranjang
                    </button>
                `;
            }
        } else {
            alert('Gagal menghapus item dari keranjang');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus item');
    });
}
</script>
@endsection
