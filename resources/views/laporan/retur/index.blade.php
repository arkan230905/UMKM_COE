@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Retur Pembelian</h3>
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
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="purchase_start_date" class="form-control" value="{{ request('purchase_start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="purchase_end_date" class="form-control" value="{{ request('purchase_end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="purchase_status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('purchase_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="disetujui" {{ request('purchase_status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="dikirim" {{ request('purchase_status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                        <option value="selesai" {{ request('purchase_status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
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
                                <br><i class="fas fa-info-circle me-1"></i>Sudah termasuk PPN 11%
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
                            <th>Nomor Transaksi</th>
                            <th>Vendor</th>
                            <th>Jenis Retur</th>
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
                                <td><strong>{{ $return->return_number ?? 'RET-' . str_pad($return->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($return->pembelian)
                                        <strong>{{ $return->pembelian->nomor_pembelian }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $return->pembelian->vendor->nama_vendor ?? '-' }}</td>
                                <td>
                                    @if($return->jenis_retur === 'tukar_barang')
                                        Tukar Barang
                                    @elseif($return->jenis_retur === 'refund')
                                        Refund
                                    @else
                                        {{ $return->jenis_retur ?? 'Tidak Diketahui' }}
                                    @endif
                                </td>
                                <td>
                                    @if($return->items && $return->items->count() > 0)
                                        <div class="small">
                                            @foreach($return->items as $item)
                                                <div class="mb-1">
                                                    @if($item->bahanBaku)
                                                        • <span class="text-primary fw-semibold">BB</span> {{ $item->bahanBaku->nama_bahan }}
                                                    @elseif($item->bahanPendukung)
                                                        • <span class="text-info fw-semibold">BP</span> {{ $item->bahanPendukung->nama_bahan }}
                                                    @else
                                                        • Item tidak diketahui
                                                    @endif
                                                    <span class="text-muted">
                                                        ({{ number_format($item->quantity ?? 0, 2) }} {{ $item->unit ?? 'unit' }})
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
                                <td>{{ $return->reason ?? '-' }}</td>
                                <td class="text-end">
                                    @php
                                        $subtotal = $return->total_retur ?? 0;
                                        $ppnAmount = $subtotal * 0.11;
                                        $totalWithPpn = $subtotal + $ppnAmount;
                                    @endphp
                                    <div class="small text-muted">
                                        Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}<br>
                                        PPN 11%: Rp {{ number_format($ppnAmount, 0, ',', '.') }}
                                    </div>
                                    <strong class="text-primary">Rp {{ number_format($totalWithPpn, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = $return->status_badge ?? ['class' => 'bg-secondary', 'text' => ucfirst($return->status ?? 'Unknown')];
                                    @endphp
                                    {{ $statusBadge['text'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
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
