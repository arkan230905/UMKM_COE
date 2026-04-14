@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Penjualan</h3>
        <div>
            <a href="{{ route('laporan.penjualan.export') }}" class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="laporanTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="penjualan-tab" data-bs-toggle="tab" data-bs-target="#penjualan" type="button" role="tab" aria-controls="penjualan" aria-selected="true">
                <i class="fas fa-shopping-cart me-2"></i>Penjualan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="retur-tab" data-bs-toggle="tab" data-bs-target="#retur" type="button" role="tab" aria-controls="retur" aria-selected="false">
                <i class="fas fa-undo me-2"></i>Retur
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="laporanTabsContent">
        <!-- Penjualan Tab -->
        <div class="tab-pane fade show active" id="penjualan" role="tabpanel" aria-labelledby="penjualan-tab">

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
            <div class="card bg-primary text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Penjualan</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalPenjualanFiltered, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">
                        @if(request('start_date') && request('end_date'))
                            {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                        @else
                            Semua Periode
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Penjualan Tunai</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalPenjualanTunai, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Pembayaran Cash</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Penjualan Kredit</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalPenjualanKredit, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Pembayaran Kredit</small>
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
                            <th style="width:5%">NO</th>
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
                                                        ({{ number_format($detail->jumlah ?? 0, 0, ',', '.') }} pcs)
                                                    </span>
                                                    - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                    @if(($detail->diskon_nominal ?? 0) > 0)
                                                        <span class="badge bg-warning text-dark">
                                                            Diskon: Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                    = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0), 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ $p->produk->nama_produk ?? '-' }}
                                        <span class="text-muted">
                                            ({{ number_format($p->jumlah ?? 0, 0, ',', '.') }} pcs)
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
                                <td class="text-end">
                                    @php
                                        $totalPenjualanItem = $p->total ?? 0;
                                        // Jika total = 0, hitung dari details
                                        if ($totalPenjualanItem == 0 && $p->details && $p->details->count() > 0) {
                                            $totalPenjualanItem = $p->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0);
                                            });
                                        }
                                    @endphp
                                    <strong>Rp {{ number_format($totalPenjualanItem, 0, ',', '.') }}</strong>
                                </td>
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
    <!-- End Penjualan Tab -->

    <!-- Retur Tab -->
    <div class="tab-pane fade" id="retur" role="tabpanel" aria-labelledby="retur-tab">
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3" id="returFilterForm">
                    <input type="hidden" name="tab" value="retur">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum_dibayar" {{ request('status') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('laporan.penjualan') }}?tab=retur" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="card-title mb-3">Total Retur Penjualan</h6>
                <h3 class="text-primary mb-2">
                    @php
                        $totalReturPenjualan = isset($returPenjualans) ? $returPenjualans->sum('total_retur') : 0;
                    @endphp
                    Rp {{ number_format($totalReturPenjualan, 0, ',', '.') }}
                </h3>
                <small class="text-muted">
                    @if(request('tanggal_mulai') && request('tanggal_selesai'))
                        Periode: {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}
                    @else
                        Semua Periode
                    @endif
                </small>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>No. Retur</th>
                                <th>Tanggal</th>
                                <th>Nomor Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Jenis Retur</th>
                                <th>Item Diretur</th>
                                <th>Catatan</th>
                                <th class="text-end">Total Retur</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($returPenjualans) && $returPenjualans->count() > 0)
                                @foreach($returPenjualans as $index => $retur)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $retur->nomor_retur ?? '-' }}</strong></td>
                                        <td>{{ $retur->tanggal ? $retur->tanggal->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $retur->penjualan->nomor_penjualan ?? '-' }}</td>
                                        <td>{{ $retur->pelanggan->name ?? 'Umum' }}</td>
                                        <td>
                                            @switch($retur->jenis_retur ?? '')
                                                @case('tukar_barang')
                                                    <span class="badge bg-warning text-dark">Tukar Barang</span>
                                                    @break
                                                @case('refund')
                                                    <span class="badge bg-info">Refund</span>
                                                    @break
                                                @case('kredit')
                                                    <span class="badge bg-secondary">Kredit</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ ucfirst($retur->jenis_retur ?? '-') }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($retur->detailReturPenjualans && $retur->detailReturPenjualans->count() > 0)
                                                @foreach($retur->detailReturPenjualans as $detail)
                                                    <div class="small mb-1">
                                                        • {{ $detail->produk->nama_produk ?? '-' }}
                                                        <span class="text-muted">({{ number_format($detail->qty_retur ?? 0, 0, ',', '.') }} pcs)</span>
                                                        - Rp {{ number_format($detail->harga_barang ?? 0, 0, ',', '.') }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $retur->keterangan ?? '-' }}</td>
                                        <td class="text-end">
                                            <strong>
                                                @if(($retur->jenis_retur ?? '') === 'tukar_barang')
                                                    <span class="text-muted">Rp 0</span>
                                                @else
                                                    Rp {{ number_format($retur->total_retur ?? 0, 0, ',', '.') }}
                                                @endif
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            @switch($retur->status ?? '')
                                                @case('belum_dibayar')
                                                    <span class="badge bg-danger">Belum Dibayar</span>
                                                    @break
                                                @case('lunas')
                                                    <span class="badge bg-success">Lunas</span>
                                                    @break
                                                @case('selesai')
                                                    <span class="badge bg-primary">Selesai</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($retur->status ?? '-') }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                            <p class="mb-0">Tidak ada data retur penjualan</p>
                                            @if(config('app.debug'))
                                                <small class="text-info">
                                                    Debug: returPenjualans = {{ isset($returPenjualans) ? 'set (' . $returPenjualans->count() . ' items)' : 'not set' }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- End Retur Tab -->
    </div>
    <!-- End Tab Content -->
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
        console.log('DOM loaded');
        
        // Test if Bootstrap is loaded
        if (typeof bootstrap !== 'undefined') {
            console.log('Bootstrap is loaded');
        } else {
            console.log('Bootstrap is NOT loaded');
        }
        
        // Test if tab elements exist
        const returTab = document.getElementById('retur-tab');
        const returPane = document.getElementById('retur');
        console.log('Retur tab element:', returTab);
        console.log('Retur pane element:', returPane);
        
        // Inisialisasi tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Handle tab switching based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        console.log('Active tab from URL:', activeTab);
        
        if (activeTab === 'retur') {
            console.log('Switching to retur tab');
            // Switch to retur tab
            const returTab = document.getElementById('retur-tab');
            const returPane = document.getElementById('retur');
            const penjualanTab = document.getElementById('penjualan-tab');
            const penjualanPane = document.getElementById('penjualan');
            
            if (returTab && returPane && penjualanTab && penjualanPane) {
                // Remove active from penjualan
                penjualanTab.classList.remove('active');
                penjualanTab.setAttribute('aria-selected', 'false');
                penjualanPane.classList.remove('show', 'active');
                
                // Add active to retur
                returTab.classList.add('active');
                returTab.setAttribute('aria-selected', 'true');
                returPane.classList.add('show', 'active');
                console.log('Tab switched successfully');
            } else {
                console.log('Tab elements not found');
            }
        }
        
        // Update form action when switching tabs
        const returTabElement = document.getElementById('retur-tab');
        if (returTabElement) {
            returTabElement.addEventListener('click', function() {
                console.log('Retur tab clicked');
                // Add tab parameter to retur filter form
                const returForm = document.getElementById('returFilterForm');
                if (returForm) {
                    const tabInput = returForm.querySelector('input[name="tab"]');
                    if (tabInput) {
                        tabInput.value = 'retur';
                    }
                }
            });
        }
        
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
