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
                    <h5 class="card-title">Total Pembelian</h5>
                    <h3 class="mb-0">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata/Transaksi</h5>
                    <h3 class="mb-0">Rp {{ $totalTransaksi > 0 ? number_format($totalPembelian / $totalTransaksi, 0, ',', '.') : 0 }}</h3>
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
                                <td>{{ $p->no_pembelian }}</td>
                                <td>{{ optional($p->tanggal)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
                                <td>
                                    @if($p->details && $p->details->count() > 0)
                                        <div class="small">
                                            @foreach($p->details as $detail)
                                                <div class="mb-1">
                                                    • {{ $detail->bahanBaku->nama_bahan ?? $detail->bahan_baku->nama_bahan ?? 'Item' }}
                                                    <span class="text-muted">
                                                        ({{ number_format($detail->jumlah ?? 0, 2, ',', '.') }} 
                                                        {{ $detail->satuan ?? ($detail->bahanBaku->satuan->kode ?? 'unit') }})
                                                    </span>
                                                    - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Data detail hilang
                                        </span>
                                        <div class="small text-muted mt-1">
                                            Pembelian ini tidak memiliki detail item. Silakan hubungi admin.
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($p->payment_method === 'cash' || $p->status === 'lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-warning">Belum Lunas</span>
                                    @endif
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
                {{ $pembelian->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table th { white-space: nowrap; }
    .card-title { font-size: 0.9rem; margin-bottom: 0.5rem; }
    .card h3 { font-size: 1.5rem; font-weight: 600; }
</style>
@endpush

@push('scripts')
<script>
    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
@endsection
