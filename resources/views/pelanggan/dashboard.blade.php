@extends('layouts.pelanggan')
@section('content')
<!-- Cache Buster: {{ time() }} -->
<style>
    .hero-container {
        background: linear-gradient(135deg, #fdfbf7 0%, #f4eee6 100%);
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 10px 30px rgba(139, 111, 71, 0.05);
        margin: 2rem auto;
        position: relative;
        overflow: hidden;
    }
    
    /* Decorative elements */
    .hero-container::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(212,165,116,0.1) 0%, rgba(212,165,116,0) 70%);
        border-radius: 50%;
    }
    .hero-container::after {
        content: '';
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(184,147,95,0.1) 0%, rgba(184,147,95,0) 70%);
        border-radius: 50%;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    @media (min-width: 992px) {
        .hero-grid {
            grid-template-columns: 1.2fr 0.8fr;
            gap: 4rem;
        }
    }

    .hero-badge {
        display: inline-block;
        background: #ffffff;
        color: #8b6f47;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        margin-bottom: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2d3748;
        line-height: 1.2;
        margin-bottom: 1rem;
    }

    .hero-title span {
        color: #8b6f47;
    }

    .hero-desc {
        font-size: 1.1rem;
        color: #64748b;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .hero-search {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 50px;
        padding: 0.5rem 0.5rem 0.5rem 1.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        max-width: 500px;
    }

    .hero-search input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        font-size: 1rem;
        color: #4a5568;
    }

    .hero-search button {
        background: #8b6f47;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .hero-search button:hover {
        background: #6b5a3a;
    }

    .hero-buttons {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .btn-hero-primary {
        padding: 0.8rem 1.8rem;
        background: #8b6f47;
        color: white;
        border: none;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-hero-primary:hover {
        background: #6b5a3a;
        color: white;
        transform: translateY(-2px);
    }

    .btn-hero-outline {
        padding: 0.8rem 1.8rem;
        background: white;
        color: #8b6f47;
        border: 2px solid #8b6f47;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-hero-outline:hover {
        background: #fdfbf7;
        color: #6b5a3a;
    }

    .benefit-cards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    @media (min-width: 576px) {
        .benefit-cards {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .benefit-card {
        background: white;
        padding: 0.8rem;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .benefit-card .icon {
        font-size: 1.5rem;
    }

    .benefit-card .text {
        font-weight: 700;
        color: #2d3748;
        font-size: 0.8rem;
        line-height: 1.2;
    }
    .benefit-card .subtext {
        font-size: 0.65rem;
        color: #94a3b8;
    }
</style>

<div class="container">
    <div class="hero-container">
        <div class="hero-grid">
            <!-- Left Content -->
            <div>
                <div class="hero-badge">👋 Selamat Datang!</div>
                <h1 class="hero-title">Selamat Datang di<br><span>{{ $perusahaan->nama }}</span></h1>
                <p class="hero-desc">Temukan produk berkualitas terbaik dengan harga terjangkau.<br>Belanja sekarang dan nikmati pengalaman berbelanja yang menyenangkan!</p>
                
                <form action="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('dashboard') }}" method="GET" class="hero-search">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk favoritmu...">
                    <button type="submit"><i class="bi bi-search"></i></button>
                </form>
                
                <div class="hero-buttons">
                    <a href="#products-section" class="btn-hero-primary"><i class="bi bi-bag"></i> Mulai Belanja</a>
                    <a href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('cart') }}" class="btn-hero-outline"><i class="bi bi-cart3"></i> Keranjang Saya</a>
                </div>
                
                <div class="benefit-cards">
                    <div class="benefit-card">
                        <div class="icon">📦</div>
                        <div><div class="text">Produk Berkualitas</div><div class="subtext">Kualitas terbaik pilihan</div></div>
                    </div>
                    <div class="benefit-card">
                        <div class="icon">✅</div>
                        <div><div class="text">Aman & Terpercaya</div><div class="subtext">Belanja aman & nyaman</div></div>
                    </div>
                    <div class="benefit-card">
                        <div class="icon">💰</div>
                        <div><div class="text">Harga Terjangkau</div><div class="subtext">Harga bersaing & terjangkau</div></div>
                    </div>
                    <div class="benefit-card">
                        <div class="icon">🚚</div>
                        <div><div class="text">Pengiriman Cepat</div><div class="subtext">Sampai ke tangan Anda</div></div>
                    </div>
                </div>
            </div>

            <!-- Right Content: Best Seller -->
            <div style="display: flex; justify-content: center; align-items: center;">
                @if($bestSellers && $bestSellers->count() > 0)
                    <div style="width: 100%; max-width: 320px; position: relative;">
                        <div id="bestSellersCarousel" style="display: flex; transition: transform 0.5s ease-in-out; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.08);">
                            @foreach($bestSellers as $product)
                            <div style="min-width: 100%; background: white;">
                                <div style="position: relative;">
                                    <div style="position: absolute; top: 1rem; left: 1rem; background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%); color: white; padding: 0.3rem 0.8rem; border-radius: 50px; font-weight: 700; font-size: 0.75rem; z-index: 2;">
                                        ⭐ Best Seller
                                    </div>
                                    <div style="width: 100%; height: 220px; background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                        @if($product->foto)
                                            <img src="{{ storage_url($product->foto) }}" alt="{{ $product->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="font-size: 3rem;">📦</div>
                                        @endif
                                    </div>
                                </div>
                                <div style="padding: 1.5rem;">
                                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 0.3rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $product->nama_produk }}</h3>
                                    <div style="font-size: 1.25rem; font-weight: 800; color: #dc2626; margin-bottom: 0.5rem;">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
                                        <span>⭐ 5.0 • {{ $product->total_terjual ?? 0 }} terjual</span>
                                    </div>
                                    @if($product->stok > 0)
                                    <button onclick="addToCart({{ $product->id }})" class="btn-hero-primary" style="width: 100%; border-radius: 12px; display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 0.8rem;">
                                        🛒 Tambah ke Keranjang
                                    </button>
                                    @else
                                    <button disabled style="width: 100%; background: #e2e8f0; color: #94a3b8; border: none; padding: 0.8rem; border-radius: 12px; font-weight: 700; cursor: not-allowed; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                                        Habis
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        @if($bestSellers->count() > 1)
                        <div style="display: flex; justify-content: center; gap: 0.4rem; margin-top: 1rem;">
                            @for($i = 0; $i < $bestSellers->count(); $i++)
                            <div class="carousel-dot" data-index="{{ $i }}" onclick="goToSlide({{ $i }})" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $i === 0 ? '#8b6f47' : '#e2e8f0' }}; cursor: pointer; transition: all 0.3s;"></div>
                            @endfor
                        </div>
                        <button onclick="prevSlide()" style="position: absolute; left: -15px; top: 40%; transform: translateY(-50%); background: white; border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; z-index: 10; color: #475569;">‹</button>
                        <button onclick="nextSlide()" style="position: absolute; right: -15px; top: 40%; transform: translateY(-50%); background: white; border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; z-index: 10; color: #475569;">›</button>
                        @endif
                    </div>
                @else
                    <!-- Fallback if no best sellers -->
                    <div style="width: 100%; max-width: 320px; background: white; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); padding: 2rem; text-align: center;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🛍️</div>
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">Mulai Belanja</h3>
                        <p style="color: #64748b; font-size: 0.9rem;">Temukan produk terbaik pilihan kami di katalog di bawah ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div style="background: white; padding: 1rem 0.8rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin: 0 0 0.5rem 0;">Semua Produk</h2>
            <p style="color: #64748b; margin: 0; font-size: 1rem;">Jelajahi berbagai produk menarik dari UMKM pilihan</p>
        </div>
        
        <div style="display: flex; justify-content: center; gap: 0.8rem; flex-wrap: wrap; margin-bottom: 2rem;">
            <button onclick="filterKategori(null)" class="btn-hero-primary" style="padding: 0.5rem 1.2rem; font-size: 0.9rem;">📦 Semua Produk</button>
            @if($kategoris && $kategoris->count() > 0)
                @foreach($kategoris as $kat)
                <button onclick="filterKategori({{ $kat->id }})" class="btn-hero-outline" style="padding: 0.5rem 1.2rem; font-size: 0.9rem;">{{ $kat->nama }}</button>
                @endforeach
            @endif
        </div>
        
        <div id="products-section" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
            @if($produks && $produks->count() > 0)
                @foreach($produks as $produk)
                <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04); border: 1px solid #f8fafc; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.04)'">
                    <div style="position: relative; height: 180px; background: #f1f5f9; overflow: hidden;">
                        @if($produk->foto)
                        <img src="{{ storage_url($produk->foto) }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        @else
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem;">📦</div>
                        @endif
                        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/favorites/toggle") }}" method="POST" style="position: absolute; top: 10px; right: 10px;" onsubmit="return false;">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <button type="button" onclick="toggleFavorite({{ $produk->id }}); return false;" style="width: 32px; height: 32px; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; cursor: pointer; color: {{ in_array($produk->id, $favoriteIds) ? '#ff4757' : '#94a3b8' }}; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transition: all 0.2s;">{{ in_array($produk->id, $favoriteIds) ? '♥' : '♡' }}</button>
                        </form>
                    </div>
                    <div style="padding: 1.2rem;">
                        <h3 style="font-weight: 800; color: #1e293b; margin: 0 0 0.3rem 0; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $produk->nama_produk }}</h3>
                        <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem;">{{ $produk->kategori->nama ?? 'Lainnya' }}</div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #8b6f47; margin-bottom: 0.5rem;">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>📦 Stok:</span> <span style="color: #1e293b; font-weight: 700;">{{ (int)$produk->stok }}</span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            @if($produk->stok_tersedia > 0)
                            <div id="cart-btn-{{ $produk->id }}" style="flex: 1;">
                                <button type="button" onclick="addToCart({{ $produk->id }}); return false;" style="width: 100%; background: #8b6f47; color: white; border: none; border-radius: 10px; padding: 0.6rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; transition: background 0.3s;" onmouseover="this.style.background='#6b5a3a'" onmouseout="this.style.background='#8b6f47'">🛒 Keranjang</button>
                            </div>
                            @else
                            <button style="flex: 1; background: #e2e8f0; color: #94a3b8; border: none; border-radius: 10px; padding: 0.6rem; font-weight: 700; cursor: not-allowed; font-size: 0.85rem;" disabled>Habis</button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                <h4 style="color: #64748b; font-size: 1.1rem; font-weight: 600;">Belum ada produk tersedia</h4>
            </div>
            @endif
        </div>

        @if($produks && $produks->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 2rem;">
            {{ $produks->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Kenapa Belanja Section -->
<div style="background: linear-gradient(135deg, #fdfbf7 0%, #f4eee6 100%); padding: 4rem 1rem; margin-top: 2rem;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 2rem;">Mengapa Belanja di {{ $perusahaan->nama }}?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🌟</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem;">Kualitas Terjamin</h3>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6;">Semua produk melalui proses seleksi ketat untuk memastikan kualitas terbaik sampai ke tangan Anda.</p>
            </div>
            <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem;">Transaksi Aman</h3>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6;">Sistem pembayaran yang aman dan nyaman untuk melindungi privasi pelanggan saat berbelanja.</p>
            </div>
            <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem;">Dukungan UMKM Lokal</h3>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6;">Dengan berbelanja di sini, Anda turut membantu dan memajukan ekonomi Usaha Mikro, Kecil, dan Menengah.</p>
            </div>
        </div>
    </div>
</div>

<script>
console.log('Dashboard script loaded');
let cartItems = {};
let isLoggedIn = {{ auth('pelanggan')->check() ? 'true' : 'false' }};
let currentSlide = 0;
let totalSlides = {{ $bestSellers ? $bestSellers->count() : 0 }};

console.log('isLoggedIn:', isLoggedIn);


// Load cart items on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    if (isLoggedIn) {
        // Don't load from server - just initialize empty
        console.log('Cart initialized');
    }
});

// Carousel functions
function nextSlide() {
    if (totalSlides <= 1) return;
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
}

function prevSlide() {
    if (totalSlides <= 1) return;
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateCarousel();
}

function goToSlide(index) {
    currentSlide = index;
    updateCarousel();
}

function updateCarousel() {
    const carousel = document.getElementById('bestSellersCarousel');
    if (carousel) {
        carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
    }
    
    // Update dots
    document.querySelectorAll('.carousel-dot').forEach((dot, index) => {
        if (index === currentSlide) {
            dot.style.background = '#8b6f47';
        } else {
            dot.style.background = '#ddd';
        }
    });
}

// Auto-rotate carousel every 5 seconds
setInterval(() => {
    if (totalSlides > 1) {
        nextSlide();
    }
}, 5000);

function addToCart(produkId) {
    console.log('addToCart called with produkId:', produkId);
    if (!isLoggedIn) {
        console.log('User not logged in, redirecting to login');
        window.location.href = "{{ url("/" . $perusahaan_slug . "/pelanggan/login") }}";
        return;
    }
    
    console.log('Adding to cart:', produkId);
    
    // Add to cart immediately without waiting for server
    cartItems[produkId] = { qty: 1, id: produkId };
    
    // Update DOM directly
    const btn = document.getElementById(`cart-btn-${produkId}`);
    if (btn) {
        btn.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.3rem;">
                <button type="button" onclick="updateQty(${produkId}, -1); return false;" style="background: #8b6f47; color: white; border: none; border-radius: 4px; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 0.9rem; padding: 0; display: flex; align-items: center; justify-content: center;">−</button>
                <input type="number" id="qty-${produkId}" value="1" min="1" max="999" onchange="updateQtyInput(${produkId})" style="width: 40px; text-align: center; font-weight: 700; color: #2d3748; font-size: 0.9rem; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">
                <button type="button" onclick="updateQty(${produkId}, 1); return false;" style="background: #8b6f47; color: white; border: none; border-radius: 4px; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 0.9rem; padding: 0; display: flex; align-items: center; justify-content: center;">+</button>
            </div>
        `;
    }
    
    // Send to server in background (fire and forget)
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
            cartItems[produkId].id = data.cart_id;
            updateCartBadge(data.cart_total_qty);
        }
    })
    .catch(error => {
        console.error('Background sync error:', error);
    });
}

function updateQty(produkId, change) {
    const cartItem = cartItems[produkId];
    if (!cartItem) return;
    
    const currentQty = cartItem.qty || 0;
    const newQty = currentQty + change;
    
    if (newQty < 1) {
        removeFromCart(produkId);
        return;
    }
    
    // Update immediately
    cartItems[produkId].qty = newQty;
    
    // Update input value
    const input = document.getElementById(`qty-${produkId}`);
    if (input) {
        input.value = newQty;
    }

    // Sync to server
    if (cartItem.id) {
        fetch("{{ url("/" . $perusahaan_slug . "/pelanggan/cart/ajax") }}/" + cartItem.id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ qty: newQty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) updateCartBadge(data.cart_total_qty);
        })
        .catch(err => console.error(err));
    }
}

function updateQtyInput(produkId) {
    const input = document.getElementById(`qty-${produkId}`);
    if (!input) return;
    
    let newQty = parseInt(input.value) || 1;
    
    if (newQty < 1) {
        removeFromCart(produkId);
        return;
    }
    
    // Update immediately
    cartItems[produkId].qty = newQty;

    // Sync to server
    const cartItem = cartItems[produkId];
    if (cartItem && cartItem.id) {
        fetch("{{ url("/" . $perusahaan_slug . "/pelanggan/cart/ajax") }}/" + cartItem.id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ qty: newQty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) updateCartBadge(data.cart_total_qty);
        })
        .catch(err => console.error(err));
    }
}

function removeFromCart(produkId) {
    const cartItem = cartItems[produkId];
    if (!cartItem) return;
    
    // Update immediately
    const cartId = cartItem.id;
    delete cartItems[produkId];
    
    // Update DOM directly
    const btn = document.getElementById(`cart-btn-${produkId}`);
    if (btn) {
        btn.innerHTML = `<button type="button" onclick="addToCart(${produkId}); return false;" style="width: 100%; background: #8b6f47; color: white; border: none; border-radius: 8px; padding: 0.6rem; font-weight: 700; cursor: pointer; font-size: 0.85rem;">🛒 Keranjang</button>`;
    }

    // Sync to server
    if (cartId) {
        fetch("{{ url("/" . $perusahaan_slug . "/pelanggan/cart/ajax") }}/" + cartId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) updateCartBadge(data.cart_total_qty);
        })
        .catch(err => console.error(err));
    }
}

function filterKategori(kategoriId) {
    let url = "{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}";
    if (kategoriId) {
        url += "?kategori=" + kategoriId;
    }
    window.location.href = url;
}

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
            // Update the heart icon silently
            const buttons = document.querySelectorAll(`button[onclick*="toggleFavorite(${produkId})"]`);
            buttons.forEach(btn => {
                if (data.is_favorite) {
                    btn.textContent = '♥';
                    btn.style.color = '#ff4757';
                } else {
                    btn.textContent = '♡';
                    btn.style.color = '#8b6f47';
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

@endsection
