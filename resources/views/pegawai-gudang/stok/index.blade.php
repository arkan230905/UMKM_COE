@extends('layouts.app')

@section('title', 'Manajemen Stok - Pegawai Gudang')

@push('styles')
<style>
/* Pegawai Gudang Stok Page - Special Design */
.table tbody tr:hover {
    background-color: #f8f9fa !important;
}

.table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: 600 !important;
    border: none !important;
}

.table td {
    vertical-align: middle !important;
}

/* Special styling for stock levels */
.stock-low {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107 !important;
}

.stock-critical {
    background-color: #f8d7da !important;
    border-left: 4px solid #dc3545 !important;
}

.stock-good {
    background-color: #d1e7dd !important;
    border-left: 4px solid #198754 !important;
}

/* Card styling */
.card {
    border: none !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    border-radius: 10px !important;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    border-radius: 10px 10px 0 0 !important;
    border: none !important;
}

/* Badge styling */
.badge {
    font-size: 0.75rem !important;
    padding: 0.25rem 0.5rem !important;
}

/* Button styling */
.btn {
    border-radius: 6px !important;
    font-weight: 500 !important;
}

/* Icon styling */
.icon-box {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Price display */
.price-display {
    font-size: 0.875rem;
    color: #6c757d;
    font-style: italic;
}

/* Stock quantity */
.stock-quantity {
    font-weight: 600;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .icon-box {
        width: 30px;
        height: 30px;
        font-size: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-warehouse me-2"></i>Manajemen Stok
            </h2>
            <p class="text-muted mb-0">Pantau dan kelola stok bahan baku dan bahan pendukung</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pegawai-gudang.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body">
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tipe == 'material' ? 'active' : '' }}" 
                            onclick="window.location.href='{{ route('pegawai-gudang.stok.index', ['tipe' => 'material']) }}'">
                        <i class="fas fa-boxes me-2"></i>Bahan Baku
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tipe == 'bahan_pendukung' ? 'active' : '' }}" 
                            onclick="window.location.href='{{ route('pegawai-gudang.stok.index', ['tipe' => 'bahan_pendukung']) }}'">
                        <i class="fas fa-flask me-2"></i>Bahan Pendukung
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tipe == 'product' ? 'active' : '' }}" 
                            onclick="window.location.href='{{ route('pegawai-gudang.stok.index', ['tipe' => 'product']) }}'">
                        <i class="fas fa-cube me-2"></i>Produk
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                @if($tipe == 'material')
                    <i class="fas fa-boxes me-2"></i>Daftar Stok Bahan Baku
                @elseif($tipe == 'bahan_pendukung')
                    <i class="fas fa-flask me-2"></i>Daftar Stok Bahan Pendukung
                @else
                    <i class="fas fa-cube me-2"></i>Daftar Stok Produk
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nama Item</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th class="text-center">Status Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $key => $item)
                            <tr class="{{ $this->getStockRowClass($item->stok ?? 0) }}">
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            @if($tipe == 'material')
                                                <i class="fas fa-box text-primary"></i>
                                            @elseif($tipe == 'bahan_pendukung')
                                                <i class="fas fa-flask text-warning"></i>
                                            @else
                                                <i class="fas fa-cube text-info"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->nama_bahan ?? $item->nama_produk }}</div>
                                            <small class="text-muted">ID: {{ $item->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($tipe == 'material')
                                        {{ is_string($item->satuan) ? $item->satuan : (optional($item->satuan)->nama ?? '-') }}
                                    @elseif($tipe == 'bahan_pendukung')
                                        {{ is_string($item->satuan) ? $item->satuan : (optional($item->satuanRelation)->nama ?? '-') }}
                                    @else
                                        {{ optional($item->satuan)->nama ?? 'PCS' }}
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp {{ number_format($item->harga_satuan_display, 0, ',', '.') }}
                                    @if(isset($item->harga_satuan_display) && $item->harga_satuan_display != $item->harga_satuan)
                                        <small class="text-muted d-block">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Rata-rata dari pembelian
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($tipe == 'product')
                                        <span class="stock-quantity">{{ number_format($item->stok_tersedia ?? 0, 2, ',', '.') }}</span>
                                        @if($item->stok_tersedia != $item->stok_database)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-sync me-1"></i>
                                                DB: {{ number_format($item->stok_database, 2, ',', '.') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="stock-quantity">{{ number_format($item->stok ?? 0, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($tipe == 'product')
                                        {{ $this->getStockStatusBadge($item->stok_tersedia ?? 0) }}
                                    @else
                                        {{ $this->getStockStatusBadge($item->stok ?? 0) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-{{ $tipe == 'material' ? 'boxes' : ($tipe == 'bahan_pendukung' ? 'flask' : 'cube') }} fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">
                                        @if($tipe == 'material')
                                            Tidak ada data bahan baku
                                        @elseif($tipe == 'bahan_pendukung')
                                            Tidak ada data bahan pendukung
                                        @else
                                            Tidak ada data produk
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">Total Item</h5>
                    <h2 class="mb-0">{{ $items->count() }}</h2>
                    <small class="text-muted">{{ $tipe == 'material' ? 'Bahan Baku' : ($tipe == 'bahan_pendukung' ? 'Bahan Pendukung' : 'Produk') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">Stok Aman</h5>
                    <h2 class="mb-0">{{ $items->where('stok', '>', 10)->count() }}</h2>
                    <small class="text-muted">Item dengan stok > 10</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning">Stok Rendah</h5>
                    <h2 class="mb-0">{{ $items->where('stok', '<=', 10)->count() }}</h2>
                    <small class="text-muted">Item dengan stok ≤ 10</small>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    // Helper methods untuk view
    function getStockRowClass($stok) {
        if ($stok <= 0) return 'stock-critical';
        if ($stok <= 10) return 'stock-low';
        return 'stock-good';
    }
    
    function getStockStatusBadge($stok) {
        if ($stok <= 0) return '<span class="badge bg-danger">Habis</span>';
        if ($stok <= 10) return '<span class="badge bg-warning">Rendah</span>';
        return '<span class="badge bg-success">Aman</span>';
    }
    
    function getIconColor($tipe) {
        if ($tipe == 'material') return 'bg-primary bg-opacity-10 text-primary';
        if ($tipe == 'bahan_pendukung') return 'bg-warning bg-opacity-10 text-warning';
        return 'bg-info bg-opacity-10 text-info';
    }
@endphp
@endsection
