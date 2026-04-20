@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-sitemap me-2"></i>Harga Pokok Produksi Per Produk
        </h2>
        <a href="{{ route('master-data.harga-pokok-produksi.create') }}" class="btn btn-primary">
            <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Produk
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.harga-pokok-produksi.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="{{ request('nama_produk') }}" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- BOM Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Harga Pokok Produksi Per Produk
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Produk</th>
                            <th>Biaya Bahan</th>
                            <th>Biaya BTKL</th>
                            <th>Biaya BOP</th>
                            <th>Total Biaya Harga Pokok Produksi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                            @php
                                $bomJobCosting = $produk->bomJobCosting;
                                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                                $totalBTKL = $bomJobCosting->total_btkl ?? 0;
                                $totalBOP = $bomJobCosting->total_bop ?? 0;
                                $totalHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $produk->nama_produk }}</div>
                                            <small class="text-muted">ID: {{ $produk->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        Rp {{ number_format($totalBTKL, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        Rp {{ number_format($totalBOP, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">
                                        Rp {{ number_format($totalHPP, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Lengkap
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('master-data.harga-pokok-produksi.show', $produk->id) }}" class="btn btn-sm btn-outline-info me-1" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('master-data.harga-pokok-produksi.edit', $produk->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border-0 p-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 400px;">
                                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                        <p class="text-muted fs-5 mb-0">Belum ada Harga Pokok Produksi yang dibuat</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $produks->firstItem() }} - {{ $produks->lastItem() }} dari {{ $produks->total() }} data
                </div>
                {{ $produks->links() }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-radius: 4px;
        padding: 2px 4px;
    }
    
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table th {
        border-top: 2px solid #dee2e6 !important;
        border-bottom: 2px solid #dee2e6 !important;
        font-weight: 600;
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .table td {
        border-bottom: 1px solid #dee2e6;
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .table td:first-child {
        text-align: left !important;
    }
    
    .table td:nth-child(5),
    .table td:nth-child(6),
    .table td:nth-child(7) {
        text-align: center !important;
    }
</style>
@endpush
@endsection

@section('scripts')
<script>
// Auto-refresh BOP data - DINONAKTIFKAN UNTUK PRESENTASI
/*
function refreshBOPData() {
    @foreach($produks as $produk)
        const productId{{ $produk->id }} = {{ $produk->id }};
        const storedHPP{{ $produk->id }} = localStorage.getItem(`hpp_produk_${productId{{ $produk->id }}}`);
        
        if (storedHPP{{ $produk->id }}) {
            console.log(`Found HPP for product ${productId{{ $produk->id }}}:`, storedHPP{{ $produk->id }});
            
            // Update the display if different
            const hppElement{{ $produk->id }} = document.querySelector(`#hpp-display-${productId{{ $produk->id }}}`);
            if (hppElement{{ $produk->id }}) {
                hppElement{{ $produk->id }}.textContent = `Rp ${parseInt(storedHPP{{ $produk->id }}).toLocaleString('id-ID')}`;
            }
        }
    @endforeach
}

// Listen for storage events from detail page
window.addEventListener('storage', function(e) {
    if (e.key && e.key.startsWith('hpp_produk_')) {
        console.log('Storage event detected:', e.key, e.newValue);
        refreshBOPData();
        // Refresh page after 1 second to show updated data
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
});

// Auto-refresh every 10 seconds
setInterval(function() {
    refreshBOPData();
}, 10000);

// Initial refresh on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshBOPData();
});

// Force refresh button
function forceRefreshBOP() {
    console.log('Force refreshing BOP data...');
    localStorage.clear();
    location.reload();
}
*/
</script>
@endsection
