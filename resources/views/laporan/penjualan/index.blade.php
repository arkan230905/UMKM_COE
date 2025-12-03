@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Penjualan</h3>
        <div>
            <a href="{{ route('laporan.penjualan.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Transaksi</h5>
                    <h3 class="mb-0">{{ $totalTransaksi }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Penjualan</h5>
                    <h3 class="mb-0">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata/Transaksi</h5>
                    <h3 class="mb-0">Rp {{ $totalTransaksi > 0 ? number_format($totalPenjualan / $totalTransaksi, 0, ',', '.') : 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Produk Terjual</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Total</th>
                            <th style="width:12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penjualan as $index => $p)
                            <tr>
                                <td>{{ $penjualan->firstItem() + $index }}</td>
                                <td><strong>{{ $p->nomor_penjualan ?? '-' }}</strong></td>
                                <td>{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @if($p->details && $p->details->count() > 0)
                                        <div class="small">
                                            @foreach($p->details as $detail)
                                                <div class="mb-1">
                                                    • {{ $detail->produk->nama_produk ?? 'Produk' }}
                                                    <span class="text-muted">
                                                        ({{ number_format($detail->jumlah ?? 0, 2, ',', '.') }} pcs)
                                                    </span>
                                                    - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                    @if(($detail->diskon_nominal ?? 0) > 0)
                                                        <span class="badge bg-warning text-dark">
                                                            Diskon: Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ $p->produk->nama_produk ?? '-' }}
                                        <span class="text-muted">
                                            ({{ number_format($p->jumlah ?? 0, 2, ',', '.') }} pcs)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->payment_method === 'cash')
                                        <span class="badge bg-success">Tunai</span>
                                    @elseif($p->payment_method === 'transfer')
                                        <span class="badge bg-info">Transfer</span>
                                    @else
                                        <span class="badge bg-warning">Kredit</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('laporan.penjualan.invoice', $p->id) }}" target="_blank" class="btn btn-sm btn-primary" title="Cetak Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data penjualan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($penjualan->hasPages())
            <div class="card-footer">
                {{ $penjualan->withQueryString()->links('vendor.pagination.custom-small') }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table th { white-space: nowrap; }
    .card-title { font-size: 0.9rem; margin-bottom: 0.5rem; }
    .card h3 { font-size: 1.5rem; font-weight: 600; }
    
    /* Memperkecil ukuran pagination - SUPER FORCE */
    .pagination {
        font-size: 0.7rem !important;
        margin: 0 !important;
    }
    
    .pagination .page-link {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.7rem !important;
        line-height: 1 !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    .pagination .page-item {
        margin: 0 1px !important;
    }
    
    /* Memperkecil icon panah di pagination - SUPER FORCE */
    .pagination .page-link svg,
    .pagination .page-link i,
    .pagination .page-link span {
        width: 8px !important;
        height: 8px !important;
        font-size: 8px !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
    
    /* Target semua elemen di dalam page-link */
    .pagination .page-link * {
        font-size: 8px !important;
        width: 8px !important;
        height: 8px !important;
    }
    
    /* Override Bootstrap default */
    nav[aria-label="Page navigation"] .pagination,
    nav .pagination,
    .card-footer .pagination {
        font-size: 0.7rem !important;
    }
    
    /* Khusus untuk Laravel pagination arrows */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        font-size: 0.6rem !important;
    }
    
    /* Hide text, show only small arrow */
    .pagination .page-item:first-child .page-link::before {
        content: "‹" !important;
        font-size: 10px !important;
    }
    
    .pagination .page-item:last-child .page-link::before {
        content: "›" !important;
        font-size: 10px !important;
    }
    
    .pagination .page-item:first-child .page-link svg,
    .pagination .page-item:last-child .page-link svg {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Perkecil pagination arrows
        setTimeout(function() {
            const paginationLinks = document.querySelectorAll('.pagination .page-link');
            paginationLinks.forEach(function(link) {
                // Ganti SVG dengan text kecil
                const svg = link.querySelector('svg');
                if (svg) {
                    const parent = link.querySelector('.page-item');
                    const isFirst = link.closest('.page-item:first-child');
                    const isLast = link.closest('.page-item:last-child');
                    
                    if (isFirst || link.textContent.includes('Previous') || link.textContent.includes('«')) {
                        link.innerHTML = '<span style="font-size: 10px;">‹</span>';
                    } else if (isLast || link.textContent.includes('Next') || link.textContent.includes('»')) {
                        link.innerHTML = '<span style="font-size: 10px;">›</span>';
                    }
                }
                
                // Paksa style kecil
                link.style.padding = '0.2rem 0.4rem';
                link.style.fontSize = '0.7rem';
                link.style.lineHeight = '1';
            });
        }, 100);
    });
</script>
@endpush
@endsection
