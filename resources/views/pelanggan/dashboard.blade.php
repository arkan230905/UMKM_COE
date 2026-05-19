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
                <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard" . ( ? "?" .  : "") . "") }}" method="GET" id="searchForm">
                    <input type="text" id="searchInput" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Cari produk favoritmu..." style="width: 100%; padding: 0.4rem 1rem; border-radius: 50px; border: 1px solid #eaeaea; background: #fbfbfb; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02); transition: all 0.3s; font-size: 0.8rem;">
                    <button type="submit" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #888; cursor: pointer;">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <!-- Autocomplete Dropdown -->
                <div id="searchResults" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-top: 0.5rem; z-index: 1000; max-height: 350px; overflow-y: auto; border: 1px solid #eaeaea;">
                </div>
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
                    <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/cart" . ( ? "?" .  : "") . "") }}" style="background: white; color: #8b5a2b; border: 1px solid #8b5a2b; padding: 0.4rem 1.2rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.3s;">
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
                
                <!-- Best Seller Card -->
                <div style="background: white; border-radius: 20px; padding: 12px; display: inline-flex; gap: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.06); border: 2px solid #f8f9fa; position: relative; z-index: 1; width: 100%; max-width: 400px; margin: 0 auto; margin-top: 2rem;">
                    
                    <!-- Badge Best Seller -->
                    <div style="position: absolute; top: -14px; left: 16px; background: #ffe4e6; color: #e11d48; padding: 0.25rem 0.8rem; border-radius: 8px; font-weight: 700; font-size: 0.75rem; border: 1px solid #fecdd3; box-shadow: 0 4px 6px rgba(225, 29, 72, 0.1);">
                        Best Seller
                    </div>

                    <!-- Image -->
                    <div style="width: 140px; height: 140px; border-radius: 12px; overflow: hidden; flex-shrink: 0;">
                        @php $bestSeller = isset($bestSellers) && $bestSellers->count() > 0 ? $bestSellers->first() : $produks->first(); @endphp
                        @if($bestSeller && $bestSeller->foto)
                            <img src="{{ Storage::url($bestSeller->foto) }}" alt="Best Seller" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Best Seller" style="width: 100%; height: 100%; object-fit: cover;">
                        @endif
                    </div>

                    <!-- Details -->
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; text-align: left; padding-right: 0.5rem;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 0.4rem; line-height: 1.3;">
                            {{ $bestSeller ? $bestSeller->nama_produk : 'Nasi Ayam Ketumbar' }}
                        </h3>
                        <div style="font-size: 1.2rem; font-weight: 800; color: #fb7185; margin-bottom: 0.6rem;">
                            Rp {{ $bestSeller ? number_format($bestSeller->harga_jual, 0, ',', '.') : '18.606' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-star-fill" style="color: #fbbf24; font-size: 0.85rem;"></i> {{ $bestSeller->rating ?? '5.0' }} &bull; {{ $bestSeller ? ($bestSeller->total_terjual ?? 0) : 0 }} terjual
                        </div>
                    </div>
                </div>
                
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
            <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard" . ( ? "?" .  : "") . "") }}" style="padding: 0.4rem 1.2rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.3s; {{ !$kategoriFilter ? 'background: #8b5a2b; color: white;' : 'background: white; color: #555; border: 1px solid #eaeaea;' }}">
                <i class="bi bi-grid"></i> Semua Produk
            </a>
            @if($kategoris && $kategoris->count() > 0)
                @foreach($kategoris as $kat)
                <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard" . ('kategori' => $kat->id ? "?" . 'kategori' => $kat->id : "") . "") }}" style="padding: 0.4rem 1.2rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.4rem; {{ $kategoriFilter == $kat->id ? 'background: #8b5a2b; color: white;' : 'background: white; color: #555; border: 1px solid #eaeaea;' }}">
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
                            <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
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
                            <div style="font-size: 0.8rem; color: #888; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.4rem;">
                                <span>{{ $produk->kategori->nama ?? 'Kategori' }}</span>
                                <span style="font-size: 0.4rem;">⚫</span>
                                <span style="color: {{ $produk->stok_tersedia > 0 ? '#28a745' : '#dc3545' }}">Stok: {{ number_format($produk->stok_tersedia, 0, ',', '.') }}</span>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.3rem;">
                                <i class="bi bi-star-fill" style="color: #fbbf24; font-size: 0.85rem;"></i> {{ $produk->rating ?? '0.0' }} 
                                @if($produk->reviews && $produk->reviews->count() > 0)
                                <span class="text-muted">({{ $produk->reviews->count() }} ulasan)</span>
                                @endif
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
                                
                                <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/favorites.toggle" . ( ? "?" .  : "") . "") }}" method="POST" style="margin: 0;">
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
// Autocomplete Search logic
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length === 0) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard" . ( ? "?" .  : "") . "") }}?q=${encodeURIComponent(query)}&autocomplete=1`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let html = '<div style="display: flex; flex-direction: column;">';
                        data.forEach(item => {
                            const price = new Intl.NumberFormat('id-ID').format(item.harga_jual);
                            const imgUrl = item.foto ? `/storage/${item.foto}` : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80';
                            html += `
                                <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard" . ( ? "?" .  : "") . "") }}?q=${encodeURIComponent(item.nama_produk)}" style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem 1rem; text-decoration: none; border-bottom: 1px solid #f0f0f0; transition: background 0.2s; color: inherit;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='transparent'">
                                    <div style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: #eee;">
                                        <img src="${imgUrl}" alt="${item.nama_produk}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #333; margin-bottom: 0.1rem;">${item.nama_produk}</div>
                                        <div style="font-size: 0.75rem; color: #8b5a2b; font-weight: 700;">Rp ${price}</div>
                                    </div>
                                </a>
                            `;
                        });
                        html += '</div>';
                        searchResults.innerHTML = html;
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: #888; font-size: 0.85rem;">Produk tidak ditemukan</div>';
                        searchResults.style.display = 'block';
                    }
                })
                .catch(error => console.error('Search error:', error));
            }, 300);
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        // Show dropdown when clicking input if it has value
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0 && searchResults.innerHTML.trim() !== '') {
                searchResults.style.display = 'block';
            }
        });
    }
});
</script>
@endsection








