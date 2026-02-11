@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-chart-line me-2"></i>Laporan Stok Real-Time
        </h2>
        <div class="btn-group">
            <button type="button" class="btn btn-success" onclick="refreshStockData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-info" onclick="exportStockReport()">
                <i class="fas fa-download"></i> Export
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Item</h6>
                            <h3 class="mb-0">{{ $summary['total_items'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Stok Aman</h6>
                            <h3 class="mb-0">{{ $summary['items_aman'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Stok Menipis</h6>
                            <h3 class="mb-0">{{ $summary['items_menipis'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Stok Habis</h6>
                            <h3 class="mb-0">{{ $summary['items_habis'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Laporan
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.stock-realtime') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="filter" class="form-label">Tipe Item</label>
                        <select class="form-select" id="filter" name="filter">
                            <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Semua Item</option>
                            <option value="bahan_baku" {{ $filter == 'bahan_baku' ? 'selected' : '' }}>Bahan Baku</option>
                            <option value="bahan_pendukung" {{ $filter == 'bahan_pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                            <option value="produk" {{ $filter == 'produk' ? 'selected' : '' }}>Produk Jadi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status Stok</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="aman" {{ $status == 'aman' ? 'selected' : '' }}>Aman</option>
                            <option value="menipis" {{ $status == 'menipis' ? 'selected' : '' }}>Menipis</option>
                            <option value="habis" {{ $status == 'habis' ? 'selected' : '' }}>Habis</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('laporan.stock-realtime') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Report Tables -->
    @foreach($stockReport as $itemType => $items)
        @if(count($items) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-{{ $itemType == 'bahan_baku' ? 'leaf' : ($itemType == 'bahan_pendukung' ? 'tools' : 'box') }} me-2"></i>
                        {{ ucwords(str_replace('_', ' ', $itemType)) }} ({{ count($items) }} item)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 25%;">Nama Item</th>
                                    <th style="width: 10%;" class="text-center">Satuan</th>
                                    <th style="width: 12%;" class="text-end">Stok Real-Time</th>
                                    @if($itemType != 'produk')
                                        <th style="width: 12%;" class="text-end">Stok Minimum</th>
                                    @endif
                                    <th style="width: 12%;" class="text-end">Harga Rata-rata</th>
                                    <th style="width: 12%;" class="text-end">Nilai Stok</th>
                                    <th style="width: 10%;" class="text-center">Status</th>
                                    <th style="width: 12%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item['nama'] }}</strong>
                                            @if($item['stok_realtime'] != $item['stok_sistem'])
                                                <br><small class="text-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Sistem: {{ number_format($item['stok_sistem'], 2) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item['satuan'] }}</td>
                                        <td class="text-end">
                                            <span class="fw-bold">{{ number_format($item['stok_realtime'], 2) }}</span>
                                        </td>
                                        @if($itemType != 'produk')
                                            <td class="text-end">{{ number_format($item['stok_minimum'], 2) }}</td>
                                        @endif
                                        <td class="text-end">Rp {{ number_format($item['harga_rata_rata'], 2) }}</td>
                                        <td class="text-end">
                                            <strong>Rp {{ number_format($item['stok_realtime'] * $item['harga_rata_rata'], 2) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if($item['status'] == 'aman')
                                                <span class="badge bg-success">Aman</span>
                                            @elseif($item['status'] == 'menipis')
                                                <span class="badge bg-warning">Menipis</span>
                                            @else
                                                <span class="badge bg-danger">Habis</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('laporan.stock-movements') }}?item_type={{ $itemType == 'bahan_baku' ? 'material' : ($itemType == 'bahan_pendukung' ? 'support' : 'product') }}&item_id={{ $item['id'] }}" 
                                                   class="btn btn-outline-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Lihat Pergerakan Stok">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-success" 
                                                        onclick="syncStock('{{ $itemType == 'bahan_baku' ? 'material' : ($itemType == 'bahan_pendukung' ? 'support' : 'product') }}', {{ $item['id'] }})"
                                                        data-bs-toggle="tooltip" 
                                                        title="Sinkronisasi Stok">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @if(empty($stockReport) || array_sum(array_map('count', $stockReport)) == 0)
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada data stok yang ditemukan</h5>
                <p class="text-muted">Coba ubah filter atau tambahkan data stok terlebih dahulu.</p>
            </div>
        </div>
    @endif
</div>

<script>
function refreshStockData() {
    location.reload();
}

function syncStock(itemType, itemId) {
    if (confirm('Yakin ingin menyinkronkan stok untuk item ini?')) {
        fetch('{{ route("laporan.stock-sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                item_type: itemType,
                item_id: itemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Stok berhasil disinkronkan: ' + data.new_stock);
                location.reload();
            } else {
                alert('Gagal menyinkronkan stok: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyinkronkan stok');
        });
    }
}

function exportStockReport() {
    // Implement export functionality
    alert('Fitur export akan segera tersedia');
}

// Auto-refresh every 5 minutes
setInterval(function() {
    refreshStockData();
}, 300000);
</script>
@endsection