@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-sitemap me-2"></i>Bill of Materials (BOM)
        </h2>
        <a href="{{ route('master-data.bom.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Buat BOM Baru
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
            <form action="{{ route('master-data.bom.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="{{ request('nama_produk') }}" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status BOM</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua</option>
                            <option value="ada" {{ request('status') == 'ada' ? 'selected' : '' }}>Sudah Ada BOM</option>
                            <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Ada BOM</option>
                            <option value="lengkap" {{ request('status') == 'lengkap' ? 'selected' : '' }}>BOM Lengkap</option>
                            <option value="tidak_lengkap" {{ request('status') == 'tidak_lengkap' ? 'selected' : '' }}>BOM Tidak Lengkap</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">
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
                <i class="fas fa-list me-2"></i>Daftar BOM Produk
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
                            <th>Total Biaya BOM</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                            @php
                                $missingColumns = [];
                                if (($produk->total_biaya_bahan ?? 0) == 0) $missingColumns[] = 'Biaya Bahan';
                                if (($produk->total_btkl ?? 0) == 0) $missingColumns[] = 'Biaya BTKL';
                                if (($produk->total_bop ?? 0) == 0) $missingColumns[] = 'Biaya BOP';
                                $hasBom = $produk->bomJobCosting || $produk->boms->isNotEmpty();
                                $isIncomplete = !empty($missingColumns);
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
                                <td class="@if(($produk->total_biaya_bahan ?? 0) == 0) text-warning fw-bold @endif">
                                    <div class="fw-semibold @if(($produk->total_biaya_bahan ?? 0) == 0) text-warning @endif">
                                        Rp {{ number_format($produk->total_biaya_bahan ?? 0, 0, ',', '.') }}
                                        @if(($produk->total_biaya_bahan ?? 0) == 0)
                                            <i class="fas fa-exclamation-triangle ms-1" title="Biaya bahan kosong"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Otomatis masuk sesuai dengan data halaman biaya bahan
                                    </small>
                                </td>
                                <td class="@if(($produk->total_btkl ?? 0) == 0) text-warning fw-bold @endif">
                                    <div class="fw-semibold @if(($produk->total_btkl ?? 0) == 0) text-warning @endif">
                                        Rp {{ number_format($produk->total_btkl ?? 0, 0, ',', '.') }}
                                        @if(($produk->total_btkl ?? 0) == 0)
                                            <i class="fas fa-exclamation-triangle ms-1" title="Biaya BTKL kosong"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Otomatis masuk sesuai dengan data halaman btkl
                                    </small>
                                </td>
                                <td class="@if(($produk->total_bop ?? 0) == 0) text-warning fw-bold @endif">
                                    <div class="fw-semibold @if(($produk->total_bop ?? 0) == 0) text-warning @endif">
                                        Rp {{ number_format($produk->total_bop ?? 0, 0, ',', '.') }}
                                        @if(($produk->total_bop ?? 0) == 0)
                                            <i class="fas fa-exclamation-triangle ms-1" title="Biaya BOP kosong"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Otomatis masuk sesuai dengan data halaman bop
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">
                                        Rp {{ number_format($produk->total_bom_cost ?? 0, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-calculator"></i> Nominal Biaya bahan + BTKL + BOP, sistem otomatis menambahkan sendiri
                                    </small>
                                </td>
                                <td>
                                    @if($hasBom && !$isIncomplete)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Produk Sudah Memiliki BOM
                                        </span>
                                    @elseif($hasBom && $isIncomplete)
                                        <span class="badge bg-warning" title="BOM belum lengkap: {{ implode(', ', $missingColumns) }}">
                                            <i class="fas fa-exclamation-triangle me-1"></i>BOM Belum Lengkap
                                        </span>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-info-circle"></i> Kolom kosong: {{ implode(', ', $missingColumns) }}
                                        </div>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Belum Ada BOM
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('master-data.bom.show', $produk->id) }}" class="btn btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data BOM</p>
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
