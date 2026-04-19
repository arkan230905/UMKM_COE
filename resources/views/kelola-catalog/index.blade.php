@extends('layouts.app')

@section('title', 'Kelola Catalog')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-store me-2"></i>Kelola Catalog
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('kelola-catalog.settings') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-cog me-1"></i>Pengaturan Catalog
                            </a>
                            <a href="{{ route('catalog') }}" target="_blank" class="btn btn-success btn-sm">
                                <i class="fas fa-eye me-1"></i>Lihat Catalog
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('kelola-catalog.index') }}">
                                        <div class="row g-3">
                                            <!-- Search -->
                                            <div class="col-md-4">
                                                <label class="form-label">Cari Produk</label>
                                                <input type="text" name="search" class="form-control" 
                                                       value="{{ request('search') }}" 
                                                       placeholder="Nama produk, deskripsi, barcode...">
                                            </div>
                                            
                                            <!-- Stock Filter -->
                                            <div class="col-md-3">
                                                <label class="form-label">Filter Stok</label>
                                                <select name="stock_filter" class="form-select">
                                                    <option value="all" {{ request('stock_filter') == 'all' ? 'selected' : '' }}>Semua Stok</option>
                                                    <option value="available" {{ request('stock_filter') == 'available' ? 'selected' : '' }}>Tersedia</option>
                                                    <option value="out_of_stock" {{ request('stock_filter') == 'out_of_stock' ? 'selected' : '' }}>Habis</option>
                                                    <option value="low_stock" {{ request('stock_filter') == 'low_stock' ? 'selected' : '' }}>Stok Rendah (<=10)</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Price Filter -->
                                            <div class="col-md-3">
                                                <label class="form-label">Filter Harga</label>
                                                <select name="price_filter" class="form-select">
                                                    <option value="all" {{ request('price_filter') == 'all' ? 'selected' : '' }}>Semua Harga</option>
                                                    <option value="under_10k" {{ request('price_filter') == 'under_10k' ? 'selected' : '' }}>< Rp 10.000</option>
                                                    <option value="10k_50k" {{ request('price_filter') == '10k_50k' ? 'selected' : '' }}>Rp 10.000 - 50.000</option>
                                                    <option value="50k_100k" {{ request('price_filter') == '50k_100k' ? 'selected' : '' }}>Rp 50.000 - 100.000</option>
                                                    <option value="over_100k" {{ request('price_filter') == 'over_100k' ? 'selected' : '' }}> > Rp 100.000</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Filter Buttons -->
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-filter"></i> Filter
                                                    </button>
                                                    <a href="{{ route('kelola-catalog.index') }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-refresh"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                    <label for="selectAll" class="form-check-label">Pilih Semua</label>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('show')" id="bulkShowBtn" disabled>
                                        <i class="fas fa-eye me-1"></i>Tampilkan di Catalog
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('hide')" id="bulkHideBtn" disabled>
                                        <i class="fas fa-eye-slash me-1"></i>Sembunyikan dari Catalog
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="row">
                        @forelse($produks as $produk)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 {{ $produk->show_in_catalog ? 'border-success' : 'border-secondary' }}">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $produk->nama_produk }}</h6>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input product-checkbox" 
                                               value="{{ $produk->id }}" id="product_{{ $produk->id }}">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Product Image -->
                                    <div class="text-center mb-3">
                                        @if($produk->foto)
                                            <img src="{{ asset('storage/'.$produk->foto) }}" 
                                                 alt="{{ $produk->nama_produk }}" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="height: 150px;">
                                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Product Info -->
                                    <div class="mb-2">
                                        <small class="text-muted">Barcode:</small>
                                        <span class="badge bg-light text-dark">{{ $produk->barcode ?? 'N/A' }}</span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Stok:</small>
                                        <span class="badge {{ $produk->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $produk->stok }} {{ $produk->satuan ?? 'pcs' }}
                                        </span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Harga Jual:</small>
                                        <span class="badge bg-primary">
                                            Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">HPP:</small>
                                        <span class="badge bg-info">
                                            Rp {{ number_format($produk->hpp_calculated, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Margin:</small>
                                        <span class="badge {{ $produk->margin_percentage > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $produk->margin_percentage }}%
                                        </span>
                                    </div>

                                    <!-- Catalog Description -->
                                    <div class="mb-3">
                                        <label class="form-label small">Deskripsi Catalog:</label>
                                        <textarea name="deskripsi_catalog" 
                                                  class="form-control form-control-sm" 
                                                  rows="2"
                                                  onchange="updateCatalogDescription({{ $produk->id }}, this.value)">{{ $produk->deskripsi_catalog ?? $produk->deskripsi ?? '' }}</textarea>
                                    </div>

                                    <!-- Catalog Visibility Toggle -->
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="catalog_{{ $produk->id }}"
                                               {{ $produk->show_in_catalog ? 'checked' : '' }}
                                               onchange="toggleCatalogVisibility({{ $produk->id }}, this.checked)">
                                        <label class="form-check-label" for="catalog_{{ $produk->id }}">
                                            <small>Tampilkan di Catalog</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <i class="fas fa-{{ $produk->show_in_catalog ? 'eye' : 'eye-slash' }}"></i>
                                            {{ $produk->show_in_catalog ? 'Ditampilkan' : 'Disembunyikan' }}
                                        </small>
                                        <a href="{{ route('master-data.produk.edit', $produk->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit Produk
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Belum ada produk tersedia. 
                                <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary btn-sm ms-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Produk
                                </a>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($produks->hasPages())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-center">
                                {{ $produks->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for bulk actions -->
<form id="bulkActionForm" method="POST" action="{{ route('kelola-catalog.bulk-visibility') }}">
    @csrf
    <input type="hidden" name="action" id="bulkAction">
    <input type="hidden" name="product_ids" id="bulkProductIds">
</form>

<!-- Hidden form for catalog description update -->
<form id="catalogDescForm" method="POST" action="">
    @csrf
    @method('PATCH')
    <input type="hidden" name="deskripsi_catalog" id="catalogDescValue">
</form>

<style>
.card.border-success {
    border-left: 5px solid #28a745 !important;
}

.card.border-secondary {
    border-left: 5px solid #6c757d !important;
    opacity: 0.8;
}

.product-checkbox {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.badge {
    font-size: 0.75em;
}
</style>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkButtons();
});

// Update bulk buttons state
function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const showBtn = document.getElementById('bulkShowBtn');
    const hideBtn = document.getElementById('bulkHideBtn');
    
    if (checkedBoxes.length > 0) {
        showBtn.disabled = false;
        hideBtn.disabled = false;
    } else {
        showBtn.disabled = true;
        hideBtn.disabled = true;
    }
}

// Listen to checkbox changes
document.querySelectorAll('.product-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkButtons);
});

// Bulk action
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const productIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (productIds.length === 0) {
        alert('Pilih minimal satu produk terlebih dahulu.');
        return;
    }
    
    document.getElementById('bulkAction').value = action;
    document.getElementById('bulkProductIds').value = JSON.stringify(productIds);
    document.getElementById('bulkActionForm').submit();
}

// Toggle catalog visibility
function toggleCatalogVisibility(productId, isVisible) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/kelola-catalog/${productId}/toggle-visibility`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ show_in_catalog: isVisible })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update card appearance
            const card = document.getElementById(`product_${productId}`).closest('.card');
            const statusText = card.querySelector('.card-footer small');
            
            if (isVisible) {
                card.classList.remove('border-secondary');
                card.classList.add('border-success');
                statusText.innerHTML = '<i class="fas fa-eye"></i> Ditampilkan';
            } else {
                card.classList.remove('border-success');
                card.classList.add('border-secondary');
                statusText.innerHTML = '<i class="fas fa-eye-slash"></i> Disembunyikan';
            }
            
            // Show success message
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan', 'error');
    });
}

// Update catalog description
function updateCatalogDescription(productId, description) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/kelola-catalog/${productId}/update-catalog-info`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ deskripsi_catalog: description })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Deskripsi catalog berhasil diperbarui', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan', 'error');
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
    
    setTimeout(() => {
        toastContainer.remove();
    }, 5000);
}
</script>
@endsection
