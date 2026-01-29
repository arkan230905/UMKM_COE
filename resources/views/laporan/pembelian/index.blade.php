@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Pembelian</h3>
        <div>
            <a href="{{ route('laporan.pembelian.export') }}" class="btn btn-success">
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
                    <h5 class="card-title text-dark">Total Pembelian</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalPembelianFiltered, 0, ',', '.') }}</h3>
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
                    <h5 class="card-title text-dark">Total Pembelian Tunai</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalPembelianTunai, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Pembayaran Cash</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Pembelian Belum Lunas</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalPembelianBelumLunas, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Sisa Utang</small>
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
                            <th>Vendor</th>
                            <th>Item Dibeli</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th style="width:12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembelian as $index => $p)
                            <tr>
                                <td>{{ $pembelian->firstItem() + $index }}</td>
                                <td><strong>{{ $p->nomor_pembelian ?? '-' }}</strong></td>
                                <td>{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
                                <td>
                                    @if($p->details && $p->details->count() > 0)
                                        <div class="small">
                                            @foreach($p->details as $detail)
                                                <div class="mb-1">
                                                    • 
                                                    @php
                                                        \Log::info('Debug Laporan - Pembelian ID: ' . $p->id . ', Detail ID: ' . $detail->id);
                                                        \Log::info('Debug Laporan - Tipe Item: ' . ($detail->tipe_item ?? 'null'));
                                                        \Log::info('Debug Laporan - Bahan Baku ID: ' . ($detail->bahan_baku_id ?? 'null'));
                                                        \Log::info('Debug Laporan - Bahan Pendukung ID: ' . ($detail->bahan_pendukung_id ?? 'null'));
                                                        \Log::info('Debug Laporan - BahanBaku exists: ' . ($detail->bahanBaku ? 'yes' : 'no'));
                                                        \Log::info('Debug Laporan - BahanPendukung exists: ' . ($detail->bahanPendukung ? 'yes' : 'no'));
                                                    @endphp
                                                    @if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku)
                                                        {{ $detail->bahanBaku->nama_bahan }}
                                                    @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung)
                                                        {{ $detail->bahanPendukung->nama_bahan }}
                                                    @elseif($detail->tipe_item === 'bahan_baku' && $detail->bahan_baku_id && !$detail->bahanBaku)
                                                        Bahan Baku (ID: {{ $detail->bahan_baku_id }})
                                                    @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahan_pendukung_id && !$detail->bahanPendukung)
                                                        Bahan Pendukung (ID: {{ $detail->bahan_pendukung_id }})
                                                    @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                        {{ $detail->bahanPendukung->nama_bahan }}
                                                    @elseif($detail->bahan_baku_id && $detail->bahanBaku)
                                                        {{ $detail->bahanBaku->nama_bahan }}
                                                    @elseif($detail->bahan_pendukung_id)
                                                        @php
                                                            \Log::info('Debug Laporan - Fallback: bahan_pendukung_id=' . $detail->bahan_pendukung_id . ' but bahanPendukung null');
                                                        @endphp
                                                        Bahan Pendukung (ID: {{ $detail->bahan_pendukung_id }})
                                                    @elseif($detail->bahan_baku_id)
                                                        @php
                                                            \Log::info('Debug Laporan - Fallback: bahan_baku_id=' . $detail->bahan_baku_id . ' but bahanBaku null');
                                                        @endphp
                                                        Bahan Baku (ID: {{ $detail->bahan_baku_id }})
                                                    @else
                                                        @php
                                                            \Log::info('Debug Laporan - Fallback: No ID fields found for detail ID ' . $detail->id);
                                                        @endphp
                                                        Item
                                                    @endif
                                                    <span class="text-muted">
                                                        ({{ number_format($detail->jumlah ?? 0, 0, ',', '.') }} 
                                                        @php
                                                            // Logic satuan yang sama dengan pegawai-pembelian
                                                            $satuanItem = 'unit';
                                                            
                                                            // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                                                            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                                                                // Prioritas: detail->satuan, lalu relation->satuanRelation->nama
                                                                $satuanItem = $detail->satuan ?: ($detail->bahanBaku->satuan->nama ?? 'unit');
                                                            }
                                                            // Jika item diinput sebagai bahan pendukung (berdasarkan relation yang ada)
                                                            elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                                                                // Prioritas: detail->satuan, lalu relation->satuanRelation->nama
                                                                $satuanItem = $detail->satuan ?: ($detail->bahanPendukung->satuanRelation->nama ?? 'unit');
                                                            }
                                                            // Fallback jika relation tidak ada
                                                            elseif ($detail->bahan_baku_id) {
                                                                $satuanItem = $detail->satuan ?: 'unit';
                                                            }
                                                            elseif ($detail->bahan_pendukung_id) {
                                                                $satuanItem = $detail->satuan ?: 'unit';
                                                            }
                                                        @endphp
                                                        {{ $satuanItem }})
                                                    </span>
                                                    - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                    = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Detail tidak tersedia
                                        </span>
                                        <div class="small text-muted mt-1">
                                            Total: Rp {{ number_format($p->total_harga ?? 0, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @php
                                        // Gunakan total yang sama dengan transaksi/pembelian
                                        $totalPembelian = 0;
                                        if ($p->details && $p->details->count() > 0) {
                                            $totalPembelian = $p->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                            });
                                        }
                                        
                                        // Jika ada total_harga di database, gunakan yang lebih besar (sama seperti admin)
                                        if ($p->total_harga > $totalPembelian) {
                                            $totalPembelian = $p->total_harga;
                                        }
                                    @endphp
                                    <strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-center">
                                    @php
                                        // Logic status sama dengan pegawai pembelian - cek apakah ada retur
                                        $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $p->id)->exists();
                                        
                                        if ($hasRetur) {
                                            $statusText = 'Ada Retur';
                                            $statusBadgeClass = 'bg-warning';
                                        } else {
                                            $statusText = 'Tidak Ada Retur';
                                            $statusBadgeClass = 'bg-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('transaksi.pembelian.show', $p) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('laporan.pembelian.invoice', $p) }}" target="_blank" class="btn btn-sm btn-primary" title="Cetak Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data pembelian</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pembelian->hasPages())
            <div class="card-footer">
                {{ $pembelian->withQueryString()->links('vendor.pagination.custom-small') }}
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
