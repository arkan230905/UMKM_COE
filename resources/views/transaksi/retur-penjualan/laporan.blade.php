@extends('layouts.app')

@section('title', 'Laporan Retur Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-print me-2"></i>Laporan Retur Penjualan
        </h2>
        <div>
            <a href="{{ route('transaksi.retur-penjualan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.retur-penjualan.laporan') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Retur</label>
                        <select name="jenis_retur" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="tukar_barang" {{ request('jenis_retur') == 'tukar_barang' ? 'selected' : '' }}>Tukar Barang</option>
                            <option value="refund" {{ request('jenis_retur') == 'refund' ? 'selected' : '' }}>Refund</option>
                            <option value="kredit" {{ request('jenis_retur') == 'kredit' ? 'selected' : '' }}>Kredit</option>
                        </select>
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
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.retur-penjualan.laporan') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Laporan Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Nomor Retur</th>
                            <th>Tanggal</th>
                            <th>Nomor Transaksi</th>
                            <th>Produk</th>
                            <th>Qty Retur</th>
                            <th>Jenis Retur</th>
                            <th>Total Retur</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($returPenjualans->count() > 0)
                            @foreach($returPenjualans as $retur)
                                @foreach($retur->detailReturPenjualans as $detail)
                                    <tr>
                                        <td>{{ $retur->nomor_retur }}</td>
                                        <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                        <td>{{ $retur->penjualan->nomor_penjualan ?? '-' }}</td>
                                        <td>{{ $detail->produk->nama_produk }}</td>
                                        <td class="text-center">{{ $detail->qty_retur }}</td>
                                        <td>
                                            @switch($retur->jenis_retur)
                                                @case('tukar_barang')
                                                    <span class="badge bg-warning">Tukar Barang</span>
                                                    @break
                                                @case('refund')
                                                    <span class="badge bg-info">Refund</span>
                                                    @break
                                                @case('kredit')
                                                    <span class="badge bg-secondary">Kredit</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="text-end">
                                            @if($retur->jenis_retur === 'tukar_barang')
                                                <span class="text-muted">Rp 0.00</span>
                                            @else
                                                Rp {{ number_format($retur->total_retur, 2) }}
                                            @endif
                                        </td>
                                        <td>{{ $retur->pelanggan->nama_pelanggan ?? '-' }}</td>
                                        <td>
                                            @switch($retur->status)
                                                @case('belum_dibayar')
                                                    <span class="badge bg-danger">Belum Dibayar</span>
                                                    @break
                                                @case('lunas')
                                                    <span class="badge bg-success">Lunas</span>
                                                    @break
                                                @case('selesai')
                                                    <span class="badge bg-primary">Selesai</span>
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data retur penjualan</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Ringkasan Laporan</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Transaksi Retur:</strong><br>
                                    <span class="text-primary">{{ $returPenjualans->count() }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Qty Retur:</strong><br>
                                    <span class="text-info">{{ $returPenjualans->sum(function($r) { return $r->detailReturPenjualans->sum('qty_retur'); }) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Nilai Refund:</strong><br>
                                    <span class="text-success">Rp {{ number_format($returPenjualans->where('jenis_retur', 'refund')->sum('total_retur'), 2) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Utang Kredit:</strong><br>
                                    <span class="text-warning">Rp {{ number_format($returPenjualans->where('jenis_retur', 'kredit')->where('status', 'belum_dibayar')->sum('total_retur'), 2) }}</span>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <strong>Tukar Barang:</strong><br>
                                    <span class="badge bg-warning">{{ $returPenjualans->where('jenis_retur', 'tukar_barang')->count() }} transaksi</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Refund:</strong><br>
                                    <span class="badge bg-info">{{ $returPenjualans->where('jenis_retur', 'refund')->count() }} transaksi</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Kredit:</strong><br>
                                    <span class="badge bg-secondary">{{ $returPenjualans->where('jenis_retur', 'kredit')->count() }} transaksi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 12px;
        }
        
        .badge {
            color: black !important;
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
        }
    }
    
    .badge {
        font-size: 0.75em;
    }
</style>
@endpush

@push('scripts')
<script>
// Auto-calculate summary when filters are applied
$(document).ready(function() {
    // Any additional JavaScript for the report can be added here
});
</script>
@endpush
