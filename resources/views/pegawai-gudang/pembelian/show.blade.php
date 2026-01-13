@extends('layouts.pegawai-gudang')

@section('title', 'Detail Pembelian')

@push('styles')
<style>
    .detail-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .detail-card:hover {
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .detail-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }
    
    .info-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
    }
    
    .info-value {
        color: #212529;
    }
    
    .table-modern {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .table-modern thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem 0.75rem;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }
    
    .badge-item-type {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }
    
    .retur-item {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .btn-modern {
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .total-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 1.5rem;
        margin-top: 1rem;
    }
    
    .status-badge {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-invoice me-2"></i>Detail Pembelian</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pegawai-gudang.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pegawai-gudang.pembelian.index') }}">Pembelian</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pegawai-gudang.pembelian.index') }}" class="btn btn-secondary btn-modern">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Informasi Pembelian -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Pembelian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-4 info-label">Nomor Pembelian:</div>
                                    <div class="col-8 info-value">
                                        <span class="badge bg-primary">{{ $pembelian->nomor_pembelian ?? 'PB-' . str_pad($pembelian->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-4 info-label">Tanggal:</div>
                                    <div class="col-8 info-value">
                                        <i class="fas fa-calendar me-2 text-muted"></i>
                                        {{ $pembelian->tanggal?->format('d F Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-4 info-label">Vendor:</div>
                                    <div class="col-8 info-value">
                                        <i class="fas fa-building me-2 text-muted"></i>
                                        {{ $pembelian->vendor->nama_vendor ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-4 info-label">Metode Bayar:</div>
                                    <div class="col-8 info-value">
                                        <span class="badge {{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                            <i class="fas {{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'fa-credit-card' : 'fa-money-bill' }} me-1"></i>
                                            {{ ($pembelian->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-4 info-label">Total Pembelian:</div>
                                    <div class="col-8 info-value">
                                        <h5 class="text-success mb-0">
                                            <i class="fas fa-money-bill-wave me-2"></i>
                                            Rp {{ number_format($pembelian->total, 0, ',', '.') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-4 info-label">Status:</div>
                                    <div class="col-8 info-value">
                                        <span class="status-badge bg-success text-white">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Selesai
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Barang -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Rincian Barang ({{ $pembelian->details->count() }} item)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th>Nama Bahan</th>
                                    <th class="text-end">Kuantitas</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                    <th>Retur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($pembelian->details ?? []) as $i => $d)
                                @php
                                    $namaItem = '-';
                                    $satuanItem = $d->satuan ?? '-';
                                    if ($d->tipe_item === 'bahan_pendukung' && $d->bahanPendukung) {
                                        $namaItem = $d->bahanPendukung->nama_bahan;
                                        $satuanItem = $d->satuan ?: ($d->bahanPendukung->satuanRelation->kode ?? '-');
                                    } elseif ($d->bahanBaku) {
                                        $namaItem = $d->bahanBaku->nama_bahan;
                                        $satuanItem = $d->satuan ?: ($d->bahanBaku->satuanRelation->kode ?? $d->bahanBaku->satuan ?? '-');
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>
                                        @if($d->tipe_item === 'bahan_pendukung')
                                            <span class="badge badge-item-type bg-warning text-dark">
                                                <i class="fas fa-tools me-1"></i>BP
                                            </span>
                                        @else
                                            <span class="badge badge-item-type bg-primary">
                                                <i class="fas fa-boxes me-1"></i>BB
                                            </span>
                                        @endif
                                        <strong>{{ $namaItem }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold">{{ rtrim(rtrim(number_format($d->jumlah,2,',','.'),'0'),',') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $satuanItem }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">Rp {{ number_format($d->harga_satuan,0,',','.') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-primary fw-bold">Rp {{ number_format($d->subtotal,0,',','.') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $returDetails = \App\Models\ReturDetail::whereHas('retur', function($q) use ($pembelian) {
                                                $q->where('type', 'purchase')->where('pembelian_id', $pembelian->id);
                                            })->where('ref_detail_id', $d->id)->with('retur')->get();
                                        @endphp
                                        @if($returDetails->count() > 0)
                                            @foreach($returDetails as $rd)
                                            <div class="retur-item">
                                                <small class="text-muted">
                                                    <i class="fas fa-undo me-1"></i>
                                                    {{ \Carbon\Carbon::parse($rd->retur->tanggal)->format('d/m/Y') }} - 
                                                    Qty: <strong>{{ number_format($rd->qty, 2, ',', '.') }}</strong>
                                                    <br><em>{{ $rd->retur->alasan }}</em>
                                                </small>
                                            </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-minus-circle me-1"></i>Tidak ada
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Total Section -->
                    <div class="total-section">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-0">Total Pembelian</h6>
                                <small class="text-muted">{{ $pembelian->details->count() }} item dibeli</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <h4 class="text-success mb-0">
                                    Rp {{ number_format($pembelian->total, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Retur -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-undo me-2"></i>
                        Riwayat Retur Pembelian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th>Tanggal Retur</th>
                                    <th>Alasan</th>
                                    <th>Detail Retur</th>
                                    <th class="text-end">Total Retur</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $returs = \App\Models\Retur::where('type', 'purchase')
                                        ->where('pembelian_id', $pembelian->id)
                                        ->with('details')
                                        ->orderBy('created_at', 'desc')
                                        ->get();
                                @endphp
                                @if($returs->count() > 0)
                                    @foreach($returs as $i => $retur)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>
                                            <i class="fas fa-calendar me-2 text-muted"></i>
                                            {{ \Carbon\Carbon::parse($retur->tanggal)->format('d F Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ $retur->alasan }}</span>
                                        </td>
                                        <td>
                                            @if($retur->details && $retur->details->count() > 0)
                                                @foreach($retur->details as $rd)
                                                    @php
                                                        $namaItem = '-';
                                                        if ($rd->refDetail) {
                                                            if ($rd->refDetail->tipe_item === 'bahan_pendukung' && $rd->refDetail->bahanPendukung) {
                                                                $namaItem = $rd->refDetail->bahanPendukung->nama_bahan;
                                                            } elseif ($rd->refDetail->bahanBaku) {
                                                                $namaItem = $rd->refDetail->bahanBaku->nama_bahan;
                                                            }
                                                        }
                                                        $subtotal = $rd->qty * $rd->harga_satuan_asal;
                                                    @endphp
                                                    <div class="retur-item">
                                                        <div class="fw-medium">{{ $namaItem }}</div>
                                                        <div class="text-muted small">
                                                            Qty: {{ number_format($rd->qty, 2, ',', '.') }} × 
                                                            Rp {{ number_format($rd->harga_satuan_asal, 0, ',', '.') }} = 
                                                            <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-danger fw-bold">
                                                Rp {{ number_format($retur->jumlah, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $retur->status == 'posted' ? 'bg-success' : 'bg-warning' }}">
                                                <i class="fas {{ $retur->status == 'posted' ? 'fa-check' : 'fa-clock' }} me-1"></i>
                                                {{ $retur->status == 'posted' ? 'Posted' : 'Draft' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <br>Belum ada retur untuk pembelian ini
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('pegawai-gudang.pembelian.index') }}" class="btn btn-secondary btn-modern">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                </a>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-modern" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Print functionality
    function printPage() {
        window.print();
    }
    
    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        // Animate cards on load
        const cards = document.querySelectorAll('.detail-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 200);
        });
    });
</script>
@endpush

@endsection