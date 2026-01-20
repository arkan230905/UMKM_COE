@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Perhitungan Biaya Bahan
        </h2>
        <div class="btn-group">
            <form action="{{ route('master-data.biaya-bahan.recalculate') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Yakin ingin menghitung ulang semua biaya bahan?')">
                    <i class="fas fa-sync-alt"></i> Hitung Ulang Semua
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Data
                @if(request()->hasAny(['nama_produk', 'harga_min', 'harga_max']))
                    <small class="text-white-50">(Filter Aktif)</small>
                @endif
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.biaya-bahan.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="{{ request('nama_produk') }}" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_min" class="form-label">Harga BOM Min</label>
                        <input type="number" class="form-control" id="harga_min" name="harga_min" 
                               value="{{ request('harga_min') }}" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_max" class="form-label">Harga BOM Max</label>
                        <input type="number" class="form-control" id="harga_max" name="harga_max" 
                               value="{{ request('harga_max') }}" placeholder="999999999">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 3%;" class="text-center">#</th>
                            <th style="width: 25%;">Produk</th>
                            <th style="width: 15%;" class="text-center">Bahan Baku</th>
                            <th style="width: 15%;" class="text-center">Bahan Pendukung</th>
                            <th style="width: 17%;" class="text-end">Total Biaya</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                            @php
                                $biaya = $produkBiaya[$produk->id] ?? [];
                                $totalBiaya = $biaya['total_biaya'] ?? 0;
                                $totalBiayaBahanBaku = $biaya['total_biaya_bahan_baku'] ?? 0;
                                $totalBiayaBahanPendukung = $biaya['total_biaya_bahan_pendukung'] ?? 0;
                                $jumlahBahanBaku = count($biaya['detail_bahan_baku'] ?? []);
                                $jumlahBahanPendukung = count($biaya['detail_bahan_pendukung'] ?? []);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($produk->foto)
                                            <img src="{{ Storage::url($produk->foto) }}" 
                                                 alt="{{ $produk->nama_produk }}" 
                                                 class="rounded me-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $produk->nama_produk }}</div>
                                            @if($produk->barcode)
                                                <small class="text-muted">{{ $produk->barcode }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($jumlahBahanBaku > 0)
                                        <div class="mb-1">
                                            <span class="badge bg-info">{{ $jumlahBahanBaku }} item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($jumlahBahanPendukung > 0)
                                        <div class="mb-1">
                                            <span class="badge bg-warning text-dark">{{ $jumlahBahanPendukung }} item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            Rp {{ number_format($totalBiayaBahanPendukung, 0, ',', '.') }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($totalBiaya > 0)
                                        <div class="fw-bold text-success fs-5">
                                            Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                                        </div>
                                        @if($produk->harga_jual)
                                            @php
                                                $margin = $produk->harga_jual > 0 ? (($produk->harga_jual - $totalBiaya) / $produk->harga_jual * 100) : 0;
                                            @endphp
                                            <small class="text-muted">
                                                Margin: 
                                                <span class="badge {{ $margin >= 20 ? 'bg-success' : ($margin >= 10 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                    {{ number_format($margin, 1) }}%
                                                </span>
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">Rp 0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($totalBiaya > 0)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Lengkap
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus-circle"></i> Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($totalBiaya > 0)
                                            <a href="{{ route('master-data.biaya-bahan.show', $produk->id) }}" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('master-data.biaya-bahan.edit', $produk->id) }}" 
                                               class="btn btn-outline-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('master-data.biaya-bahan.destroy', $produk->id) }}" 
                                                  method="POST" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus semua biaya bahan untuk {{ $produk->nama_produk }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-outline-danger" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" 
                                               class="btn btn-success btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Tambah Biaya Bahan">
                                                <i class="fas fa-plus"></i> Tambah
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-calculator fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada data perhitungan biaya bahan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($produks->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total Keseluruhan:</th>
                            <th class="text-center">
                                <div class="badge bg-info">
                                    {{ collect($produkBiaya)->sum(fn($item) => count($item['detail_bahan_baku'] ?? [])) }} item
                                </div>
                                <div class="small text-muted mt-1">
                                    Rp {{ number_format(collect($produkBiaya)->sum('total_biaya_bahan_baku'), 0, ',', '.') }}
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="badge bg-warning text-dark">
                                    {{ collect($produkBiaya)->sum(fn($item) => count($item['detail_bahan_pendukung'] ?? [])) }} item
                                </div>
                                <div class="small text-muted mt-1">
                                    Rp {{ number_format(collect($produkBiaya)->sum('total_biaya_bahan_pendukung'), 0, ',', '.') }}
                                </div>
                            </th>
                            <th class="text-end">
                                <div class="fw-bold text-success fs-5">
                                    Rp {{ number_format(collect($produkBiaya)->sum('total_biaya'), 0, ',', '.') }}
                                </div>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $produks->firstItem() }} sampai {{ $produks->lastItem() }} dari {{ $produks->total() }} data
                </div>
                {{ $produks->links() }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    /* Hover effect untuk row */
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.2s ease;
    }
    
    /* Style untuk gambar produk */
    .table img {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Style untuk total biaya */
    .fs-5 {
        font-size: 1.1rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
@endsection
