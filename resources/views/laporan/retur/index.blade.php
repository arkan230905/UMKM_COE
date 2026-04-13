@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Retur Pembelian</h3>
        <div class="alert alert-info mb-0">
            <small><i class="fas fa-info-circle me-1"></i>Laporan Retur Penjualan telah dipindahkan ke <a href="{{ route('laporan.penjualan') }}?tab=retur" class="alert-link">Laporan Penjualan → Tab Retur</a></small>
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
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="purchase_start_date" class="form-control" value="{{ request('purchase_start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
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
                                <td><strong>{{ $return->memo ?? 'RET-' . str_pad($return->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $return->tanggal ? \Carbon\Carbon::parse($return->tanggal)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $return->pembelian->nomor_pembelian ?? '-' }}</td>
                                <td>{{ $return->pembelian->vendor->nama_vendor ?? '-' }}</td>
                                <td>
                                    @if($return->details && $return->details->count() > 0)
                                        <div class="small">
                                            @foreach($return->details as $detail)
                                                <div class="mb-1">
                                                    • {{ $detail->produk->nama_produk ?? 'Bahan Baku' }}
                                                    <span class="text-muted">
                                                        ({{ number_format($detail->qty ?? 0, 0, ',', '.') }})
                                                    </span>
                                                    - Rp {{ number_format($detail->harga_satuan_asal ?? 0, 0, ',', '.') }}
                                                    = <strong>Rp {{ number_format(($detail->qty ?? 0) * ($detail->harga_satuan_asal ?? 0), 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $return->alasan ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp {{ number_format($return->details->sum(function($detail) { return ($detail->qty ?? 0) * ($detail->harga_satuan_asal ?? 0); }), 0, ',', '.') }}</strong>
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
