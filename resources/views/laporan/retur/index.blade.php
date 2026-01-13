@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Retur</h3>
    </div>

    <!-- Retur Penjualan Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-undo me-2"></i>Retur Penjualan
            </h5>
        </div>
        <div class="card-body">
            <!-- Filter Form for Sales Returns -->
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai (Retur Penjualan)</label>
                    <input type="date" name="sales_start_date" class="form-control" value="{{ request('sales_start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai (Retur Penjualan)</label>
                    <input type="date" name="sales_end_date" class="form-control" value="{{ request('sales_end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('laporan.retur') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
                <!-- Hidden fields to preserve purchase filter -->
                <input type="hidden" name="purchase_start_date" value="{{ request('purchase_start_date') }}">
                <input type="hidden" name="purchase_end_date" value="{{ request('purchase_end_date') }}">
            </form>

            <!-- Summary Card for Sales Returns -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-info text-dark">
                        <div class="card-body">
                            <h6 class="card-title text-dark">Total Retur Penjualan</h6>
                            <h4 class="mb-0 text-dark">Rp {{ number_format($totalSalesReturns, 0, ',', '.') }}</h4>
                            <small class="text-dark opacity-75">
                                @if(request('sales_start_date') && request('sales_end_date'))
                                    {{ \Carbon\Carbon::parse(request('sales_start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('sales_end_date'))->format('d/m/Y') }}
                                @else
                                    Semua Periode
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Returns Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>No. Retur</th>
                            <th>Tanggal</th>
                            <th>No. Penjualan</th>
                            <th>Item Diretur</th>
                            <th>Alasan</th>
                            <th class="text-end">Total Retur</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesReturns as $index => $return)
                            <tr>
                                <td>{{ $salesReturns->firstItem() + $index }}</td>
                                <td><strong>{{ $return->return_number ?? '-' }}</strong></td>
                                <td>{{ $return->return_date ? $return->return_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $return->penjualan->nomor_penjualan ?? '-' }}</td>
                                <td>
                                    @if($return->items && $return->items->count() > 0)
                                        <div class="small">
                                            @foreach($return->items as $item)
                                                <div class="mb-1">
                                                    • {{ $item->penjualanDetail->produk->nama_produk ?? 'Produk' }}
                                                    <span class="text-muted">
                                                        ({{ number_format($item->quantity ?? 0, 0, ',', '.') }} pcs)
                                                    </span>
                                                    - Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                                                    = <strong>Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $return->reason ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp {{ number_format($return->total_return_amount ?? 0, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $return->status === 'approved' ? 'bg-success' : ($return->status === 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                        {{ ucfirst($return->status ?? 'pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data retur penjualan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($salesReturns->hasPages())
                <div class="mt-3">
                    {{ $salesReturns->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Retur Pembelian Section -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-undo me-2"></i>Retur Pembelian
            </h5>
        </div>
        <div class="card-body">
            <!-- Filter Form for Purchase Returns -->
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai (Retur Pembelian)</label>
                    <input type="date" name="purchase_start_date" class="form-control" value="{{ request('purchase_start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai (Retur Pembelian)</label>
                    <input type="date" name="purchase_end_date" class="form-control" value="{{ request('purchase_end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('laporan.retur') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
                <!-- Hidden fields to preserve sales filter -->
                <input type="hidden" name="sales_start_date" value="{{ request('sales_start_date') }}">
                <input type="hidden" name="sales_end_date" value="{{ request('sales_end_date') }}">
            </form>

            <!-- Summary Card for Purchase Returns -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h6 class="card-title text-dark">Total Retur Pembelian</h6>
                            <h4 class="mb-0 text-dark">Rp {{ number_format($totalPurchaseReturns, 0, ',', '.') }}</h4>
                            <small class="text-dark opacity-75">
                                @if(request('purchase_start_date') && request('purchase_end_date'))
                                    {{ \Carbon\Carbon::parse(request('purchase_start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('purchase_end_date'))->format('d/m/Y') }}
                                @else
                                    Semua Periode
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Returns Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>No. Retur</th>
                            <th>Tanggal</th>
                            <th>No. Pembelian</th>
                            <th>Vendor</th>
                            <th>Item Diretur</th>
                            <th>Alasan</th>
                            <th class="text-end">Total Retur</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseReturns as $index => $return)
                            <tr>
                                <td>{{ $purchaseReturns->firstItem() + $index }}</td>
                                <td><strong>{{ $return->return_number ?? '-' }}</strong></td>
                                <td>{{ $return->return_date ? $return->return_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $return->pembelian->nomor_pembelian ?? '-' }}</td>
                                <td>{{ $return->pembelian->vendor->nama_vendor ?? '-' }}</td>
                                <td>
                                    @if($return->items && $return->items->count() > 0)
                                        <div class="small">
                                            @foreach($return->items as $item)
                                                <div class="mb-1">
                                                    • {{ $item->pembelianDetail->bahanBaku->nama_bahan ?? 'Bahan Baku' }}
                                                    <span class="text-muted">
                                                        ({{ number_format($item->quantity ?? 0, 0, ',', '.') }} {{ $item->pembelianDetail->bahanBaku->satuan->nama ?? 'unit' }})
                                                    </span>
                                                    - Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                                                    = <strong>Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $return->reason ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp {{ number_format($return->total_return_amount ?? 0, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $return->status === 'approved' ? 'bg-success' : ($return->status === 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                        {{ ucfirst($return->status ?? 'pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data retur pembelian</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($purchaseReturns->hasPages())
                <div class="mt-3">
                    {{ $purchaseReturns->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
