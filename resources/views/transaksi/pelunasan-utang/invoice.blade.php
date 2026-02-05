@extends('layouts.app')

@section('title', 'Invoice Pelunasan Utang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-receipt me-2"></i>Invoice Pelunasan Utang
        </h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Cetak
            </button>
            <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle me-2"></i>Pembayaran Berhasil!
                </h5>
                <h4 class="mb-0">Kode Transaksi: {{ $pelunasan->kode_transaksi }}</h4>
            </div>
        </div>
        <div class="card-body">
            <!-- Informasi Pelunasan -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted">Informasi Pelunasan</h6>
                    <table class="table table-sm">
                        <tr>
                            <td width="150"><strong>Kode Transaksi:</strong></td>
                            <td>{{ $pelunasan->kode_transaksi }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($pelunasan->tanggal)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Metode Pembayaran:</strong></td>
                            <td>
                                @switch($pelunasan->metode_bayar)
                                    @case('tunai')
                                        <span class="badge bg-primary">Tunai</span>
                                    @case('transfer')
                                        <span class="badge bg-info">Transfer Bank</span>
                                    @case('cash')
                                        <span class="badge bg-warning">Cash</span>
                                    @default
                                        <span class="badge bg-secondary">{{ $pelunasan->metode_bayar }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah:</strong></td>
                            <td class="text-end">Rp {{ number_format($pelunasan->dibayar_bersih, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-success">Lunas</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Informasi Pembelian</h6>
                    <table class="table table-sm">
                        <tr>
                            <td width="150"><strong>Nomor Pembelian:</strong></td>
                            <td>{{ optional($pelunasan->pembelian->vendor)->nama_vendor ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Pembelian:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($pelunasan->pembelian->tanggal)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Vendor:</strong></td>
                            <td>{{ optional($pelunasan->pembelian->vendor)->nama_vendor ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Pembelian:</strong></td>
                            <td class="text-end">Rp {{ number_format($pelunasan->pembelian->total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terbayar Sebelumnya:</strong></td>
                            <td class="text-end">Rp {{ number_format($pelunasan->pembelian->terbayar - $pelunasan->jumlah, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terbayar Sekarang:</strong></td>
                            <td class="text-end">Rp {{ number_format($pelunasan->pembelian->terbayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Sisa Utang:</strong></td>
                            <td class="text-end">
                                @if($pelunasan->pembelian->total - $pelunasan->pembelian->terbayar > 0)
                                    <span class="badge bg-warning">Rp {{ number_format($pelunasan->pembelian->total - $pelunasan->pembelian->terbayar, 0, ',', '.') }}</span>
                                @else
                                    <span class="badge bg-success">Lunas</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($pelunasan->keterangan)
            <div class="alert alert-info">
                <strong>Keterangan:</strong> {{ $pelunasan->keterangan }}
            </div>
            @endif
            
            <div class="text-center mt-4">
                <h4 class="text-success">
                    <i class="fas fa-check-circle me-2"></i>Terima Kasih!
                </h4>
                <p class="text-muted">Pembayaran Anda telah berhasil diproses.</p>
                <div class="mt-3">
                    <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Lihat Riwayat Pelunasan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .btn {
        display: none !important;
    }
    
    .card-header {
        background: #28a745 !important;
        color: white !important;
    }
    
    .alert-info {
        display: none !important;
    }
    
    .text-center h4 {
        display: none !important;
    }
    
    .text-center p {
        display: none !important;
    }
    
    .text-center .mt-3 {
        display: none !important;
    }
}
</style>

@endsection
