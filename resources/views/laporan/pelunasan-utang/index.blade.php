@extends('layouts.app')

@section('title', 'Laporan Pelunasan Utang')

@push('styles')
<style>
    /* Card Summary Styles */
    .summary-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .summary-card .card-body {
        padding: 1.5rem;
    }
    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }
    .summary-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }
    
    /* Filter Section */
    .filter-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    
    /* Search Bar Styles */
    .search-wrapper {
        position: relative;
    }
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }
    .search-input {
        padding-left: 45px;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        height: 45px;
        transition: all 0.3s;
    }
    .search-input:focus {
        border-color: #8B7355;
        box-shadow: 0 0 0 0.2rem rgba(139, 115, 85, 0.15);
    }
    
    /* Table Styles */
    .table-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .table thead th {
        background: linear-gradient(135deg, #8B7355 0%, #6B5344 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem 0.75rem;
        font-size: 0.875rem;
    }
    .table tbody td {
        padding: 0.875rem 0.75rem;
        vertical-align: middle;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Badge Styles */
    .badge-status {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
    }
    
    /* Button Styles */
    .btn-export {
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    /* Modal Styles */
    .modal-header {
        background: linear-gradient(135deg, #8B7355 0%, #6B5344 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }
    .detail-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #6c757d;
    }
    .detail-value {
        font-weight: 500;
        color: #212529;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i>Laporan Pelunasan Utang</h3>
            <p class="text-muted mb-0">Pantau dan kelola pelunasan utang pembelian</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('laporan.export.pelunasan-utang', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
               class="btn btn-danger btn-export" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Export PDF
            </a>
            <button class="btn btn-success btn-export" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="summary-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="summary-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="summary-label">Total Tagihan</p>
                            <h4 class="summary-value text-primary">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="summary-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-undo"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="summary-label">Total Refund</p>
                            <h4 class="summary-value text-warning">Rp {{ number_format($totalRefund, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="summary-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="summary-label">Total Dibayar</p>
                            <h4 class="summary-value text-success">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="summary-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="summary-label">Jumlah Transaksi</p>
                            <h4 class="summary-value text-info">{{ $jumlahTransaksi }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card card">
        <div class="card-body">
            <form action="{{ route('laporan.pelunasan-utang') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <input type="month" name="bulan" class="form-control" 
                               value="{{ request('bulan', now()->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">Semua Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->nama_vendor }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="sebagian" {{ request('status') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari Vendor</label>
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" class="form-control search-input" 
                                   placeholder="Cari nama vendor..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('laporan.pelunasan-utang') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-card card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Tanggal</th>
                            <th>No. Pelunasan</th>
                            <th>Vendor</th>
                            <th>No. Faktur</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Total Refund</th>
                            <th class="text-end">Dibayar</th>
                            <th class="text-end">Sisa Utang</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelunasanUtang as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td><strong>{{ $item->kode_transaksi }}</strong></td>
                            <td>{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</td>
                            <td>{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                            <td class="text-end">
                                @if($item->total_refund > 0)
                                    <span class="text-success">Rp {{ number_format($item->total_refund, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($item->dibayar_bersih, 0, ',', '.') }}</td>
                            <td class="text-end">
                                @if($item->sisa_utang > 0)
                                    <span class="text-danger fw-bold">Rp {{ number_format($item->sisa_utang, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-success">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-status bg-{{ $item->status_class }}">
                                    {{ $item->status_display }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal{{ $item->id }}">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>

                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-file-invoice me-2"></i>Detail Pelunasan Utang
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="detail-row">
                                                    <div class="detail-label">No. Pelunasan</div>
                                                    <div class="detail-value">{{ $item->kode_transaksi }}</div>
                                                </div>
                                                <div class="detail-row">
                                                    <div class="detail-label">Tanggal</div>
                                                    <div class="detail-value">{{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}</div>
                                                </div>
                                                <div class="detail-row">
                                                    <div class="detail-label">Vendor</div>
                                                    <div class="detail-value">{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</div>
                                                </div>
                                                <div class="detail-row">
                                                    <div class="detail-label">No. Faktur</div>
                                                    <div class="detail-value">{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="detail-row">
                                                    <div class="detail-label">Total Tagihan</div>
                                                    <div class="detail-value text-primary fw-bold">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="detail-row">
                                                    <div class="detail-label">Total Refund</div>
                                                    <div class="detail-value text-warning fw-bold">Rp {{ number_format($item->total_refund, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="detail-row">
                                                    <div class="detail-label">Dibayar</div>
                                                    <div class="detail-value text-success fw-bold">Rp {{ number_format($item->dibayar_bersih, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="detail-row">
                                                    <div class="detail-label">Sisa Utang</div>
                                                    <div class="detail-value text-danger fw-bold">Rp {{ number_format($item->sisa_utang, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="detail-row">
                                                    <div class="detail-label">Status</div>
                                                    <div class="detail-value">
                                                        <span class="badge badge-status bg-{{ $item->status_class }}">
                                                            {{ $item->status_display }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="detail-row">
                                                    <div class="detail-label">Metode Pembayaran</div>
                                                    <div class="detail-value">{{ $item->pembelian->payment_method ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($item->keterangan)
                                        <div class="detail-row">
                                            <div class="detail-label">Keterangan</div>
                                            <div class="detail-value">{{ $item->keterangan }}</div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data pelunasan utang</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($pelunasanUtang->count() > 0)
                    <tfoot>
                        <tr class="table-secondary fw-bold">
                            <th colspan="5" class="text-end">Total</th>
                            <th class="text-end">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($totalRefund, 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($pelunasanUtang->hasPages())
        <div class="card-footer">
            {{ $pelunasanUtang->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
