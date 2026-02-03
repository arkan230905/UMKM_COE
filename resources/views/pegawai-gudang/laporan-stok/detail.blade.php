@extends('layouts.app')

@section('title', 'Detail Kartu Stok - Pegawai Gudang')

@push('styles')
<style>
/* Kartu Stok Detail */
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

.summary-cards {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

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

.btn {
    border-radius: 6px !important;
    font-weight: 500 !important;
}

.text-nowrap {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
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
                <i class="fas fa-file-alt me-2"></i>Kartu Stok - {{ $item->nama_bahan ?? $item->nama_produk }}
            </h2>
            <p class="text-muted mb-0">
                Periode: {{ \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pegawai-gudang.laporan-stok.index', [
                'tipe' => $tipe,
                'dari_tanggal' => $dariTanggal,
                'sampai_tanggal' => $sampaiTanggal
            ]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Cetak
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="row">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">Total Masuk</h5>
                        <h3 class="mb-1">{{ number_format($masukQty, 2, ',', '.') }}</h3>
                        <small class="text-muted">{{ optional($item->satuan->nama) ?? 'PCS' }}</small>
                        <div class="mt-1">
                            <strong>Rp {{ number_format($masukNilai, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h5 class="card-title text-danger">Total Keluar</h5>
                        <h3 class="mb-1">{{ number_format($keluarQty, 2, ',', '.') }}</h3>
                        <small class="text-muted">{{ optional($item->satuan->nama) ?? 'PCS' }}</small>
                        <div class="mt-1">
                            <strong>Rp {{ number_format($keluarNilai, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title text-info">Saldo Akhir</h5>
                        <h3 class="mb-1">{{ number_format($saldoQty, 2, ',', '.') }}</h3>
                        <small class="text-muted">{{ optional($item->satuan->nama) ?? 'PCS' }}</small>
                        <div class="mt-1">
                            <strong>Rp {{ number_format($saldoNilai, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">Harga Rata-rata</h5>
                        <h3 class="mb-1">Rp {{ number_format($saldoQty > 0 ? $saldoNilai / $saldoQty : 0, 0, ',', '.') }}</h3>
                        <small class="text-muted">per {{ optional($item->satuan->nama) ?? 'PCS' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Detail Pergerakan Stok
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Referensi</th>
                            <th class="text-end">Masuk (Qty)</th>
                            <th class="text-end">Masuk (Nilai)</th>
                            <th class="text-end">Keluar (Qty)</th>
                            <th class="text-end">Keluar (Nilai)</th>
                            <th class="text-end">Saldo (Qty)</th>
                            <th class="text-end">Saldo (Nilai)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $runningQty = 0;
                            $runningNilai = 0;
                        @endphp
                        @forelse($stockMovements as $movement)
                            @php
                                if ($movement->direction === 'in') {
                                    $runningQty += $movement->qty;
                                    $runningNilai += ($movement->unit_cost ?? 0) * $movement->qty;
                                    $masukQty = $movement->qty;
                                    $masukNilai = ($movement->unit_cost ?? 0) * $movement->qty;
                                    $keluarQty = 0;
                                    $keluarNilai = 0;
                                } else {
                                    $runningQty -= $movement->qty;
                                    $runningNilai -= ($movement->unit_cost ?? 0) * $movement->qty;
                                    $masukQty = 0;
                                    $masukNilai = 0;
                                    $keluarQty = $movement->qty;
                                    $keluarNilai = ($movement->unit_cost ?? 0) * $movement->qty;
                                }
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($movement->tanggal)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $movement->ref_type }}#{{ $movement->ref_id }}
                                    </span>
                                </td>
                                <td class="text-end text-success">{{ number_format($masukQty, 2, ',', '.') }}</td>
                                <td class="text-end text-success">Rp {{ number_format($masukNilai, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format($keluarQty, 2, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($keluarNilai, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($runningQty, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($runningNilai, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">
                                        Tidak ada pergerakan stok dalam periode ini
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td>TOTAL</td>
                            <td></td>
                            <td class="text-end text-success">{{ number_format($masukQty, 2, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($masukNilai, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ number_format($keluarQty, 2, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($keluarNilai, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($saldoQty, 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($saldoNilai, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style media="print">
    .d-flex.justify-content-between,
    .btn {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .table th {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    
    .summary-cards {
        background: #f8f9fa !important;
    }
</style>
@endsection
