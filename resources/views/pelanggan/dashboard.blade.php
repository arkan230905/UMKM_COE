@extends('layouts.pelanggan')
@section('content')
<!-- Cache Buster: {{ time() }} -->
<div style="background: linear-gradient(135deg, #f5e6d3 0%, #e8d4c0 100%); padding: 1rem 0.8rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: center;">
            <div>
                <div style="color: #8b6f47; font-weight: 600; font-size: 0.55rem; margin-bottom: 0.2rem;">👋 Selamat Datang!</div>
                <h1 style="font-size: 1rem; font-weight: 800; color: #2d3748; margin-bottom: 0.2rem;">Selamat Datang di<br><span style="color: #8b6f47;">{{ $perusahaan->nama }}</span></h1>
                <p style="font-size: 0.6rem; color: #666; margin-bottom: 0.6rem;">Temukan produk berkualitas terbaik dengan harga terjangkau. Belanja sekarang dan nikmati pengalaman berbelanja yang menyenangkan!</p>
                <div style="margin-bottom: 0.6rem;">
                    <div style="display: flex; align-items: center; background: white; border-radius: 50px; padding: 0.25rem 0.6rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <input type="text" placeholder="Cari produk favoritmu..." style="flex: 1; border: none; background: transparent; outline: none; font-size: 0.6rem; color: #666;">
                        <span style="color: #8b6f47; cursor: pointer; font-size: 0.8rem;">🔍</span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.4rem; margin-bottom: 0.6rem;">
                    <a href="#products-section" style="padding: 0.4rem 1rem; background: #2d3748; color: white; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem;">🛍️ Mulai Belanja</a>
                    <a href="/{{ $perusahaan_slug }}/pelanggan/cart" style="padding: 0.4rem 1rem; background: white; color: #8b6f47; border: 2px solid #8b6f47; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem;">🛒 Keranjang Saya</a>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                    <div style="display: flex; gap: 0.3rem;">
                        <div style="font-size: 0.9rem;">📦</div>
                        <div><div style="font-weight: 700; color: #2d3748; font-size: 0.55rem;">Produk Berkualitas</div><div style="font-size: 0.5rem; color: #999;">Kualitas terbaik pilihan</div></div>
                    </div>
                    <div style="display: flex; gap: 0.3rem;">
                        <div style="font-size: 0.9rem;">💰</div>
                        <div><div style="font-weight: 700; color: #2d3748; font-size: 0.55rem;">Harga Terjangkau</div><div style="font-size: 0.5rem; color: #999;">Harga bersaing & terjangkau</div></div>
                    </div>
                    <div style="display: flex; gap: 0.3rem;">
                        <div style="font-size: 0.9rem;">✅</div>
                        <div><div style="font-weight: 700; color: #2d3748; font-size: 0.55rem;">Aman & Terpercaya</div><div style="font-size: 0.5rem; color: #999;">Belanja aman & nyaman</div></div>
                    </div>
                    <div style="display: flex; gap: 0.3rem;">
                        <div style="font-size: 0.9rem;">🚚</div>
                        <div><div style="font-weight: 700; color: #2d3748; font-size: 0.55rem;">Pengiriman Cepat</div><div style="font-size: 0.5rem; color: #999;">Sampai ke tangan Anda</div></div>
                    </div>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="width: 100%; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); border-radius: 20px; padding: 0.8rem; display: flex; align-items: center; justify-content: center; min-height: 220px;">
                    {{-- DEBUG: bestSellers count = {{ $bestSellers ? $bestSellers->count() : 'null' }} --}}
                    @if($bestSellers && $bestSellers->count() > 0)
                        <!-- Best Sellers Carousel -->
                        <div style="width: 100%; max-width: 280px;">
                            <div style="position: relative; overflow: hidden;">
                                <div id="bestSellersCarousel" style="display: flex; transition: transform 0.5s ease-in-out;">
                                    @foreach($bestSellers as $product)
                                    <div style="min-width: 100%; padding: 0 0.5rem;">
                                        <div style="background: white; border-radius: 16px; padding: 0.6rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                                            <!-- Best Seller Badge -->
                                            <div style="position: absolute; top: 0.5rem; left: 0.5rem; background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%); color: white; padding: 0.2rem 0.5rem; border-radius: 20px; font-weight: 700; font-size: 0.55rem;">
                                                ⭐ Best Seller
                                            </div>
                                            
                                            <!-- Product Image -->
                                            <div style="width: 100%; height: 120px; background: #f5f5f5; border-radius: 12px; overflow: hidden; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                @if($product->foto)
                                                    <img src="{{ storage_url($product->foto) }}" alt="{{ $product->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div style="font-size: 1.8rem;">📦</div>
                                                @endif
                                            </div>
                                            
                                            <!-- Product Info -->
                                            <h3 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0.5rem 0 0.15rem 0;">{{ $product->nama_produk }}</h3>
                                            
                                            <!-- Price -->
                                            <div style="font-size: 0.8rem; font-weight: 800; color: #ff6b9d; margin-bottom: 0.15rem;">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</div>
                                            
                                            <!-- Rating & Sales -->
                                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-size: 0.6rem; color: #666;">
                                                <span>⭐ 5.0 • {{ $product->total_terjual ?? 0 }} terjual</span>
                                            </div>
                                            
                                            <!-- Add to Cart Button -->
                                            @if($product->stok > 0)
                                            <button onclick="addToCart({{ $product->id }})" style="width: 100%; background: linear-gradient(135deg, #8b6f47 0%, #6b5a3a 100%); color: white; border: none; padding: 0.4rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.6rem; transition: all 0.3s;">
                                                🛒 Tambah ke Keranjang
                                            </button>
                                            @else
                                            <button style="width: 100%; background: #e0e0e0; color: #999; border: none; padding: 0.4rem; border-radius: 8px; font-weight: 700; cursor: not-allowed; opacity: 0.6; font-size: 0.6rem;">
                                                Stok Habis
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Navigation Buttons -->
                                @if($bestSellers->count() > 1)
                                <button onclick="prevSlide()" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                    ‹
                                </button>
                                <button onclick="nextSlide()" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                    ›
                                </button>
                                @endif
                            </div>
                            
                            <!-- Dots Indicator -->
                            @if($bestSellers->count() > 1)
                            <div style="display: flex; justify-content: center; gap: 0.25rem; margin-top: 0.6rem;">
                                @for($i = 0; $i < $bestSellers->count(); $i++)
                                <div class="carousel-dot" data-index="{{ $i }}" onclick="goToSlide({{ $i }})" style="width: 5px; height: 5px; border-radius: 50%; background: {{ $i === 0 ? '#8b6f47' : '#ddd' }}; cursor: pointer; transition: all 0.3s;"></div>
                                @endfor
                            </div>
                            @endif
                        </div>
                    @else
                        <!-- Fallback: Tampilkan carousel dengan produk random -->
                        <div style="width: 100%; max-width: 400px;">
                            <div style="position: relative; overflow: hidden;">
                                <div id="bestSellersCarousel" style="display: flex; transition: transform 0.5s ease-in-out;">
                                    @foreach($bestSellers as $product)
                                    <div style="min-width: 100%; padding: 0 0.8rem;">
                                        <div style="background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                                            <!-- Produk Pilihan Badge -->
                                            <div style="position: absolute; top: 0.8rem; left: 0.8rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 0.4rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.75rem;">
                                                ✨ Produk Pilihan
                                            </div>
                                            
                                            <!-- Product Image -->
                                            <div style="width: 100%; height: 180px; background: #f5f5f5; border-radius: 12px; overflow: hidden; margin-bottom: 0.8rem; display: flex; align-items: center; justify-content: center;">
                                                @if($product->foto)
                                                    <img src="{{ storage_url($product->foto) }}" alt="{{ $product->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div style="font-size: 2.5rem;">📦</div>
                                                @endif
                                            </div>
                                            
                                            <!-- Product Info -->
                                            <h3 style="font-size: 1rem; font-weight: 800; color: #2d3748; margin: 0.8rem 0 0.3rem 0;">{{ $product->nama_produk }}</h3>
                                            
                                            <!-- Price -->
                                            <div style="font-size: 1.2rem; font-weight: 800; color: #3b82f6; margin-bottom: 0.3rem;">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</div>
                                            
                                            <!-- Rating & Sales -->
                                            <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.8rem; font-size: 0.8rem; color: #666;">
                                                <span>⭐ 5.0 • Stok: {{ $product->stok }}</span>
                                            </div>
                                            
                                            <!-- Add to Cart Button -->
                                            @if($product->stok > 0)
                                            <button onclick="addToCart({{ $product->id }})" style="width: 100%; background: linear-gradient(135deg, #8b6f47 0%, #6b5a3a 100%); color: white; border: none; padding: 0.6rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.8rem; transition: all 0.3s;">
                                                🛒 Tambah ke Keranjang
                                            </button>
                                            @else
                                            <button style="width: 100%; background: #e0e0e0; color: #999; border: none; padding: 0.6rem; border-radius: 8px; font-weight: 700; cursor: not-allowed; opacity: 0.6; font-size: 0.8rem;">
                                                Stok Habis
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Navigation Buttons -->
                                @if($bestSellers->count() > 1)
                                <button onclick="prevSlide()" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                    ‹
                                </button>
                                <button onclick="nextSlide()" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                    ›
                                </button>
                                @endif
                            </div>
                            
                            <!-- Dots Indicator -->
                            @if($bestSellers->count() > 1)
                            <div style="display: flex; justify-content: center; gap: 0.4rem; margin-top: 1rem;">
                                @for($i = 0; $i < $bestSellers->count(); $i++)
                                <div class="carousel-dot" data-index="{{ $i }}" onclick="goToSlide({{ $i }})" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $i === 0 ? '#8b6f47' : '#ddd' }}; cursor: pointer; transition: all 0.3s;"></div>
                                @endfor
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div style="background: white; padding: 1rem 0.8rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 1rem;">
            <h2 style="font-size: 0.9rem; font-weight: 800; color: #2d3748; margin: 0 0 0.15rem 0;">🏷️ Semua Produk</h2>
            <p style="color: #999; margin: 0; font-size: 0.6rem;">Jelajahi berbagai produk menarik dari UMKM pilihan</p>
        </div>
        
        <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
            <button onclick="filterKategori(null)" style="padding: 0.3rem 0.8rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; font-size: 0.6rem;">📦 Semua Produk</button>
            @if($kategoris && $kategoris->count() > 0)
                @foreach($kategoris as $kat)
                <button onclick="filterKategori({{ $kat->id }})" style="padding: 0.3rem 0.8rem; background: white; color: #8b6f47; border: 2px solid #8b6f47; border-radius: 50px; font-weight: 700; cursor: pointer; font-size: 0.6rem;">{{ $kat->nama }}</button>
                @endforeach
            @endif
        </div>
        
        <div id="products-section" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.6rem;">
            @if($produks && $produks->count() > 0)
                @foreach($produks as $produk)
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;">
                    <div style="position: relative; height: 100px; background: #f5f5f5; overflow: hidden;">
                        @if($produk->foto)
                        <img src="{{ storage_url($produk->foto) }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">📦</div>
                        @endif
                        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/favorites/toggle") }}" method="POST" style="position: absolute; top: 4px; right: 4px;" onsubmit="return false;">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <button type="button" onclick="toggleFavorite({{ $produk->id }}); return false;" style="width: 24px; height: 24px; background: white; border: none; border-radius: 50%; cursor: pointer; color: {{ in_array($produk->id, $favoriteIds) ? '#ff4757' : '#8b6f47' }}; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">{{ in_array($produk->id, $favoriteIds) ? '♥' : '♡' }}</button>
                        </form>
                    </div>
                    <div style="padding: 0.5rem;">
                        <h3 style="font-weight: 700; color: #2d3748; margin: 0 0 0.15rem 0; font-size: 0.65rem;">{{ $produk->nama_produk }}</h3>
                        <div style="font-size: 0.55rem; color: #999; margin-bottom: 0.2rem;">{{ $produk->kategori->nama ?? 'Lainnya' }}</div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #8b6f47; margin-bottom: 0.3rem;">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</div>
                        <div style="font-size: 0.55rem; color: #999; margin-bottom: 0.5rem;">Stok: <span style="color: #2d3748; font-weight: 700;">{{ (int)$produk->stok }}</span></div>
                        <div style="display: flex; gap: 0.2rem;">
                            @if($produk->stok_tersedia > 0)
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
            @else
            <div style="grid-column: 1/-1; text-align: center; padding: 1rem 0.8rem;">
                <div style="font-size: 1.8rem; margin-bottom: 0.5rem;">📭</div>
                <h4 style="color: #999; font-size: 0.7rem;">Belum ada produk tersedia</h4>
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
    
    // Update immediately - no server call
    cartItems[produkId].qty = newQty;
    
    // Update input value
    const input = document.getElementById(`qty-${produkId}`);
    if (input) {
        input.value = newQty;
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
}

function removeFromCart(produkId) {
    const cartItem = cartItems[produkId];
    if (!cartItem) return;
    
    // Update immediately - no server call
    delete cartItems[produkId];
    
    // Update DOM directly
    const btn = document.getElementById(`cart-btn-${produkId}`);
    if (btn) {
        btn.innerHTML = `<button type="button" onclick="addToCart(${produkId}); return false;" style="width: 100%; background: #8b6f47; color: white; border: none; border-radius: 8px; padding: 0.6rem; font-weight: 700; cursor: pointer; font-size: 0.85rem;">🛒 Keranjang</button>`;
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
