@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Laporan Retur</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('laporan.retur') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan">Pilih Bulan</label>
                                    <input type="month" name="bulan" id="bulan" class="form-control" 
                                           value="{{ request('bulan', now()->format('Y-m')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('laporan.retur') }}" class="btn btn-secondary ml-2">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Retur</th>
                                    <th>Customer</th>
                                    <th>No. Penjualan</th>
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returs as $retur)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ optional($retur->tanggal)->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $retur->nomor_retur }}</td>
                                    <td>{{ $retur->resolveCustomerName() }}</td>
                                    <td>{{ $retur->resolveReferensiNomor() }}</td>
                                    <td class="text-right">{{ format_rupiah($retur->calculateTotalNilai()) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#returDetailModal{{ $retur->id }}">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data retur</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total</th>
                                    <th class="text-right">{{ format_rupiah($total) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('laporan.retur', ['bulan' => request('bulan'), 'export' => 'pdf']) }}" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($returs as $retur)
<div class="modal fade" id="returDetailModal{{ $retur->id }}" tabindex="-1" aria-labelledby="returDetailModalLabel{{ $retur->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returDetailModalLabel{{ $retur->id }}">Detail Retur {{ $retur->nomor_retur }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $tanggalRetur = optional($retur->tanggal_retur)->format('d M Y');
                    $kompensasiRaw = $retur->tipe_kompensasi ?? $retur->kompensasi;
                    $kompensasiLabel = match ($kompensasiRaw) {
                        'barang' => 'Tukar Barang',
                        'uang' => 'Refund Uang',
                        'credit' => 'Credit Note',
                        'refund' => 'Refund',
                        default => $kompensasiRaw ? ucfirst($kompensasiRaw) : '-',
                    };
                    $statusLabel = $retur->status
                        ? ucwords(str_replace(['_', '-'], ' ', $retur->status))
                        : '-';
                    $jenisRetur = $retur->jenis_retur ? ucwords(str_replace(['_', '-'], ' ', $retur->jenis_retur)) : '-';
                @endphp

                <div class="detail-meta-card rounded-3 px-3 py-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-label">Tanggal</div>
                            <div class="detail-value">{{ $tanggalRetur ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Referensi</div>
                            <div class="detail-value">{{ $retur->resolveReferensiNomor() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Customer / Vendor</div>
                            <div class="detail-value">{{ $retur->resolveCustomerName() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Jenis Retur</div>
                            <div class="detail-value">{{ $jenisRetur }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Kompensasi</div>
                            <div class="detail-value">{{ $kompensasiLabel }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">{{ $statusLabel }}</div>
                        </div>
                    </div>
                </div>

                @if($retur->alasan)
                <div class="detail-meta-card rounded-3 px-3 py-3 mb-3">
                    <div class="detail-label mb-1">Alasan</div>
                    <div class="detail-value">{{ $retur->alasan }}</div>
                </div>
                @endif

                @if($retur->keterangan)
                <div class="detail-meta-card rounded-3 px-3 py-3 mb-3">
                    <div class="detail-label mb-1">Keterangan</div>
                    <div class="detail-value">{{ $retur->keterangan }}</div>
                </div>
                @endif

                <h6 class="mt-4 mb-3">Item yang Diretur</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($retur->details as $detail)
                            <tr>
                                <td>{{ $detail->item_nama }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($detail->qty_display, 3, ',', '.'), '0'), ',') ?: '0' }}
                                </td>
                                <td class="text-end">Rp {{ number_format($detail->harga_display, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($detail->calculateSubtotal(), 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada item.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Retur:</th>
                                <th class="text-end">Rp {{ number_format($retur->calculateTotalNilai(), 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('laporan.retur.pdf', $retur->id) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach
<style>
    .detail-meta-card {
        background: #f1f5f9;
        border: 1px solid #cbd5f5;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        color: #0f172a;
    }

    .detail-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.45px;
        color: #475569 !important;
    }

    .detail-value {
        font-weight: 600;
        color: #0f172a !important;
    }

    .detail-value a {
        color: inherit;
        text-decoration: none;
    }

    .detail-value a:hover {
        text-decoration: underline;
    }
</style>
@endsection
