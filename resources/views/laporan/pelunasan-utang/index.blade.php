@extends('layouts.app')

@section('title', 'Laporan Pelunasan Utang')

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
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Export PDF
            </a>
            <button class="btn btn-success btn-sm" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Tagihan</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Semua Transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Refund</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalRefund, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Pengembalian</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Dibayar</h5>
                    <h3 class="mb-0 text-dark">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</h3>
                    <small class="text-dark opacity-75">Pembayaran</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Jumlah Transaksi</h5>
                    <h3 class="mb-0 text-dark">{{ $jumlahTransaksi }}</h3>
                    <small class="text-dark opacity-75">Total Data</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('laporan.pelunasan-utang') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <input type="month" name="bulan" class="form-control" 
                               value="{{ request('bulan', now()->format('Y-m')) }}">
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="sebagian" {{ request('status') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <a href="{{ route('laporan.pelunasan-utang') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo me-1"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                                <span class="badge bg-{{ $item->status_class }}">
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
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-file-invoice me-2"></i>Detail Pelunasan Utang
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="text-muted small">No. Pelunasan</label>
                                                    <div class="fw-bold">{{ $item->kode_transaksi }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Tanggal</label>
                                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Vendor</label>
                                                    <div class="fw-bold">{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small">No. Faktur</label>
                                                    <div class="fw-bold">{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="text-muted small">Total Tagihan</label>
                                                    <div class="fw-bold text-primary">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Total Refund</label>
                                                    <div class="fw-bold text-warning">Rp {{ number_format($item->total_refund, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Dibayar</label>
                                                    <div class="fw-bold text-success">Rp {{ number_format($item->dibayar_bersih, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Sisa Utang</label>
                                                    <div class="fw-bold text-danger">Rp {{ number_format($item->sisa_utang, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="text-muted small">Status</label>
                                                    <div>
                                                        <span class="badge bg-{{ $item->status_class }}">
                                                            {{ $item->status_display }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="text-muted small">Metode Pembayaran</label>
                                                    <div class="fw-bold">{{ ucfirst($item->pembelian->payment_method ?? '-') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($item->keterangan)
                                        <div class="mb-3">
                                            <label class="text-muted small">Keterangan</label>
                                            <div class="fw-bold">{{ $item->keterangan }}</div>
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
