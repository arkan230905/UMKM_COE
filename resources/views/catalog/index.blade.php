@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary text-white rounded-3 p-4 text-center">
                <h1 class="mb-3">
                    <i class="fas fa-shopping-bag me-2"></i>
                    Katalog Produk
                </h1>
                <p class="lead mb-0">Temukan produk berkualitas terbaik untuk kebutuhan Anda</p>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('catalog') }}">
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-lg" 
                                   placeholder="Cari produk..." 
                                   value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        @forelse($produks as $produk)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm product-card">
                    <!-- Product Image -->
                    <div class="product-image-container">
                        @if($produk->foto)
                            <img src="{{ asset('storage/' . $produk->foto) }}" 
                                 class="card-img-top product-image" 
                                 alt="{{ $produk->nama_produk }}">
                        @else
                            <div class="card-img-top product-image-placeholder d-flex align-items-center justify-content-center">
                                <i class="fas fa-box fa-3x text-muted"></i>
                            </div>
                        @endif
                        
                        <!-- Stock Badge -->
                        <div class="stock-badge">
                            @if($produk->stok > 10)
                                <span class="badge bg-success">Tersedia</span>
                            @elseif($produk->stok > 0)
                                <span class="badge bg-warning">Terbatas</span>
                            @else
                                <span class="badge bg-danger">Habis</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <!-- Product Name -->
                        <h5 class="card-title text-truncate" title="{{ $produk->nama_produk }}">
                            {{ $produk->nama_produk }}
                        </h5>

                        <!-- Product Description -->
                        <p class="card-text text-muted small flex-grow-1">
                            {{ Str::limit($produk->deskripsi ?? 'Tidak ada deskripsi', 80) }}
                        </p>

                        <!-- Price -->
                        <div class="price-section mb-3">
                            <h4 class="text-primary mb-0">
                                Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                            </h4>
                            <small class="text-muted">Stok: {{ $produk->stok }} {{ $produk->satuan ?? 'unit' }}</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" 
                                    onclick="orderProduct({{ $produk->id }}, '{{ $produk->nama_produk }}')"
                                    @if($produk->stok <= 0) disabled @endif>
                                <i class="fas fa-shopping-cart me-1"></i>
                                @if($produk->stok > 0)
                                    Pesan Produk
                                @else
                                    Stok Habis
                                @endif
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" 
                                    onclick="viewDetails({{ $produk->id }})">
                                <i class="fas fa-info-circle me-1"></i>
                                Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Belum Ada Produk</h4>
                    <p class="text-muted">Maaf, belum ada produk yang tersedia saat ini.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Custom Styles -->
<style>
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.product-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    background-color: #f8f9fa;
}

.stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.price-section {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.btn-sm {
    font-size: 0.875rem;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
}

.card-text {
    line-height: 1.4;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-image-container {
        height: 150px;
    }
    
    .card-title {
        font-size: 0.9rem;
    }
}
</style>

<!-- JavaScript -->
<script>
function orderProduct(productId, productName) {
    // Redirect to customer login page with product info
    window.location.href = '/pelanggan/login?redirect=catalog&product=' + productId;
}

function viewDetails(productId) {
    // For now, just show an alert. Can be expanded to show modal
    alert('Detail produk akan segera tersedia!');
}

// Auto-focus search on load
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.focus();
    }
});
</script>
@endsection
