@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 text-dark">
                <i class="fas fa-history me-2"></i>Pergerakan Stok - {{ $itemDetails['nama'] }}
            </h2>
            <p class="text-muted mb-0">
                Stok Saat Ini: <strong>{{ number_format($currentStock, 2) }} {{ $itemDetails['satuan'] }}</strong>
                @if($itemDetails['stok_minimum'] > 0)
                    | Stok Minimum: <strong>{{ number_format($itemDetails['stok_minimum'], 2) }} {{ $itemDetails['satuan'] }}</strong>
                @endif
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('laporan.stock-realtime') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-success" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
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

    <!-- Date Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fas fa-calendar me-2"></i>Filter Tanggal
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.stock-movements') }}" method="GET">
                <input type="hidden" name="item_type" value="{{ $itemType }}">
                <input type="hidden" name="item_id" value="{{ $itemId }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="date_from" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('laporan.stock-movements') }}?item_type={{ $itemType }}&item_id={{ $itemId }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Movements Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Pergerakan Stok
            </h6>
        </div>
        <div class="card-body p-0">
            @if($movements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 12%;">Tanggal</th>
                                <th style="width: 15%;">Jenis Transaksi</th>
                                <th style="width: 15%;">Referensi</th>
                                <th style="width: 8%;" class="text-center">Arah</th>
                                <th style="width: 12%;" class="text-end">Qty</th>
                                <th style="width: 10%;" class="text-center">Satuan</th>
                                <th style="width: 12%;" class="text-end">Harga Satuan</th>
                                <th style="width: 12%;" class="text-end">Total Nilai</th>
                                <th style="width: 12%;" class="text-end">Saldo Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $movement)
                                <tr>
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($movement->tanggal)->format('d/m/Y') }}</strong>
                                        <br><small class="text-muted">{{ $movement->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @switch($movement->ref_type)
                                            @case('purchase')
                                                <span class="badge bg-success">Pembelian</span>
                                                @break
                                            @case('production')
                                                <span class="badge bg-primary">Produksi</span>
                                                @break
                                            @case('production_material')
                                                <span class="badge bg-warning">Bahan Produksi</span>
                                                @break
                                            @case('sale')
                                                <span class="badge bg-info">Penjualan</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($movement->ref_type) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <strong>#{{ $movement->ref_id }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($movement->direction == 'in')
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-up"></i> Masuk
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-down"></i> Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="{{ $movement->direction == 'in' ? 'text-success' : 'text-danger' }}">
                                            {{ $movement->direction == 'in' ? '+' : '-' }}{{ number_format($movement->qty, 2) }}
                                        </strong>
                                    </td>
                                    <td class="text-center">{{ $movement->satuan }}</td>
                                    <td class="text-end">Rp {{ number_format($movement->unit_cost, 2) }}</td>
                                    <td class="text-end">
                                        <strong class="{{ $movement->direction == 'in' ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($movement->total_cost, 2) }}
                                        </strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($movement->running_balance, 2) }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer">
                    {{ $movements->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada pergerakan stok</h5>
                    <p class="text-muted">Belum ada transaksi untuk item ini pada periode yang dipilih.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Card -->
    @if($movements->count() > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Ringkasan Periode</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success mb-0">{{ number_format($movements->where('direction', 'in')->sum('qty'), 2) }}</h4>
                                    <small class="text-muted">Total Masuk</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-danger mb-0">{{ number_format($movements->where('direction', 'out')->sum('qty'), 2) }}</h4>
                                    <small class="text-muted">Total Keluar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Nilai Transaksi</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success mb-0">Rp {{ number_format($movements->where('direction', 'in')->sum('total_cost'), 2) }}</h4>
                                    <small class="text-muted">Nilai Masuk</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-danger mb-0">Rp {{ number_format($movements->where('direction', 'out')->sum('total_cost'), 2) }}</h4>
                                    <small class="text-muted">Nilai Keluar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
// Auto-refresh every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endsection