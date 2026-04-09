@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Retur Pembelian</h1>
        <a href="{{ route('transaksi.retur-pembelian.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Informasi Retur - Full Width -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi Retur
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="40%"><strong>Nomor Retur:</strong></td>
                                    <td>{{ $retur->return_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Retur:</strong></td>
                                    <td>{{ $retur->return_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Referensi Pembelian:</strong></td>
                                    <td>
                                        @if($retur->pembelian)
                                            <a href="{{ route('transaksi.pembelian.show', $retur->pembelian->id) }}" class="text-decoration-none">
                                                {{ $retur->pembelian->nomor_pembelian }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>
                                        @if($retur->pembelian && $retur->pembelian->vendor)
                                            {{ $retur->pembelian->vendor->nama_vendor }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="40%"><strong>Jenis Retur:</strong></td>
                                    <td>
                                        @if($retur->jenis_retur === 'tukar_barang')
                                            Tukar Barang
                                        @elseif($retur->jenis_retur === 'refund')
                                            Refund (Pengembalian Uang)
                                        @else
                                            {{ $retur->jenis_retur ?? 'Tidak Diketahui' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($retur->status === 'completed')
                                            <span class="text-success fw-semibold">Selesai</span>
                                        @elseif($retur->status === 'approved')
                                            <span class="text-info fw-semibold">Disetujui</span>
                                        @else
                                            <span class="text-warning fw-semibold">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Alasan:</strong></td>
                                    <td>{{ $retur->reason ?? '-' }}</td>
                                </tr>
                                @if($retur->notes)
                                <tr>
                                    <td><strong>Catatan:</strong></td>
                                    <td>{{ $retur->notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Retur & Summary - Side by Side -->
    <div class="row">
        <!-- Item Retur - Left Side (col-md-8) -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Item Retur
                    </h5>
                </div>
                <div class="card-body">
                    @if($retur->items && $retur->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bahan Baku</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalHarga = 0;
                                    @endphp
                                    @foreach($retur->items as $item)
                                        @php
                                            $subtotal = $item->subtotal ?? 0;
                                            $totalHarga += $subtotal;
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($item->bahanBaku)
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-primary fw-semibold me-2">BB</span>
                                                        <span>{{ $item->bahanBaku->nama_bahan }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Item ID: {{ $item->bahan_baku_id }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                            <td class="text-center">{{ $item->unit }}</td>
                                            <td class="text-end">Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada item retur.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Retur - Right Side (col-md-4) -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Summary Retur
                    </h5>
                </div>
                <div class="card-body">
                    @if($retur->items && $retur->items->count() > 0)
                        @php
                            $ppnRate = 0.11; // 11%
                            $ppnAmount = $totalHarga * $ppnRate;
                            $totalRetur = $totalHarga + $ppnAmount;
                        @endphp
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="fw-semibold">Total Harga:</span>
                                <span class="fw-semibold">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="fw-semibold">PPN (11%):</span>
                                <span class="fw-semibold">Rp {{ number_format($ppnAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary fs-5">Total Retur:</span>
                                <span class="fw-bold text-primary fs-4">Rp {{ number_format($totalRetur, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Total sudah termasuk PPN 11%
                            </small>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calculator fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Summary akan muncul setelah ada item retur</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection