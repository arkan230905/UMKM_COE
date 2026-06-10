@extends('layouts.app')

@section('title', 'Detail Penjualan')

@push('styles')
<style>
    /* Theme color styling */
    .bg-theme-gradient {
        background: linear-gradient(135deg, var(--brown), var(--brown-light)) !important;
        color: white;
    }
    .text-theme {
        color: var(--brown) !important;
    }
    .text-theme-light {
        color: var(--brown-light) !important;
    }
    
    /* Modern card styles */
    .card-modern {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.04);
        overflow: hidden;
        background-color: #fff;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    .card-modern:hover {
        box-shadow: 0 10px 35px rgba(0,0,0,0.06);
    }
    .card-modern .card-header {
        border-bottom: 1px solid #f3efea;
        background-color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .card-modern .card-header-theme {
        background: linear-gradient(135deg, var(--brown), var(--brown-light)) !important;
        color: white !important;
        padding: 1.25rem 1.5rem;
        border: none;
    }
    .card-modern .card-body {
        padding: 1.5rem;
    }
    
    /* Transaction info items */
    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background-color: #faf7f2;
        border: 1px solid #f1eae1;
        height: 100%;
        transition: all 0.2s ease;
    }
    .info-item:hover {
        background-color: #f7ece1;
        border-color: #e5d7c6;
    }
    .info-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(92, 61, 46, 0.08);
        color: var(--brown);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .info-item-content {
        flex-grow: 1;
    }
    .info-item-label {
        font-size: 0.8rem;
        color: #7c7267;
        margin-bottom: 2px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-item-value {
        font-size: 0.95rem;
        color: #3e3327;
        font-weight: 600;
    }
    
    /* Transaction summary items */
    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: background-color 0.2s ease;
    }
    .summary-item:hover {
        background-color: rgba(92, 61, 46, 0.03);
    }
    .summary-item-label {
        font-weight: 500;
        color: #555;
    }
    
    .grand-total-box {
        background: linear-gradient(135deg, #fdfaf6, #f5efe6);
        border: 1px dashed var(--brown-light);
        border-radius: 12px;
        padding: 18px;
        margin-top: 15px;
        text-align: center;
    }
    
    /* Tabs styling */
    .nav-tabs-modern {
        border-bottom: 2px solid #f1eae1;
        gap: 10px;
    }
    .nav-tabs-modern .nav-link {
        border: none !important;
        border-bottom: 3px solid transparent !important;
        color: #8c8276 !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 0 !important;
        transition: all 0.3s ease;
        background: transparent !important;
    }
    .nav-tabs-modern .nav-link:hover {
        color: var(--brown) !important;
        border-bottom-color: rgba(92, 61, 46, 0.3) !important;
    }
    .nav-tabs-modern .nav-link.active {
        color: var(--brown) !important;
        border-bottom-color: var(--brown) !important;
        font-weight: 700;
    }
    
    /* Table modern styling */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-bottom: 0;
    }
    .table-modern th {
        background-color: #faf7f2;
        border-bottom: 2px solid #eeddcc !important;
        color: var(--brown);
        font-weight: 600;
        padding: 12px 16px;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .table-modern td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1eae1;
        color: #4a3e3d;
    }
    .table-modern tr:last-child td {
        border-bottom: none;
    }
    .table-modern tbody tr:hover {
        background-color: #faf9f6;
    }
    
    /* Modern buttons grid */
    .btn-action-modern {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px 10px;
        border-radius: 12px;
        border: 1px solid #f1eae1;
        background-color: #fff;
        color: #7c7267;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        text-decoration: none !important;
        font-weight: 600;
    }
    .btn-action-modern i {
        font-size: 1.4rem;
        margin-bottom: 8px;
        transition: transform 0.3s ease;
    }
    .btn-action-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(92, 61, 46, 0.1);
        border-color: var(--brown-light);
    }
    
    /* Theme buttons variants */
    .btn-action-detail:hover { color: #2e7d32; border-color: #81c784; background-color: #f1f8e9; }
    .btn-action-edit:hover { color: #f57f17; border-color: #fff176; background-color: #fffde7; }
    .btn-action-jurnal:hover { color: #1565c0; border-color: #90caf9; background-color: #e3f2fd; }
    .btn-action-print:hover { color: #37474f; border-color: #b0bec5; background-color: #eceff1; }
    .btn-action-retur:hover { color: #00838f; border-color: #80deea; background-color: #e0f7fa; }
    .btn-action-delete:hover { color: #c62828; border-color: #ef9a9a; background-color: #ffebee; }

    /* Custom badges */
    .badge-theme-success {
        background-color: rgba(46, 125, 50, 0.1);
        color: #2e7d32;
        border: 1px solid rgba(46, 125, 50, 0.2);
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 6px;
    }
    .badge-theme-warning {
        background-color: rgba(245, 127, 23, 0.1);
        color: #e65100;
        border: 1px solid rgba(245, 127, 23, 0.2);
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 6px;
    }
    .badge-theme-danger {
        background-color: rgba(198, 40, 40, 0.1);
        color: #c62828;
        border: 1px solid rgba(198, 40, 40, 0.2);
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 6px;
    }
    
    /* Layout styling overrides */
    .btn-back-theme {
        border: 1px solid var(--brown-light);
        color: var(--brown);
        font-weight: 500;
        border-radius: 8px;
        padding: 8px 18px;
        transition: all 0.2s;
        background-color: #fff;
        text-decoration: none;
    }
    .btn-back-theme:hover {
        background-color: var(--brown);
        color: white;
        border-color: var(--brown);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h3 class="mb-1 text-theme fw-bold">
                <i class="fas fa-eye me-2"></i>Detail Transaksi Penjualan
            </h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Kelola, tinjau, dan cetak detail transaksi penjualan Anda.
            </p>
        </div>
        <div>
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn-back-theme">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Notifikasi jurnal belum bisa dibuat --}}
    @if(session('warning_jurnal'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start gap-3">
                <i class="fas fa-exclamation-triangle fa-lg mt-1 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong>Jurnal Akuntansi Belum Dapat Dibuat</strong>
                    <div class="mt-1" style="white-space: pre-line;">{{ session('warning_jurnal') }}</div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <a href="{{ route('transaksi.penjualan.jurnal', $penjualan->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-book me-1"></i>Lihat Detail Jurnal
                        </a>
                        <a href="{{ route('master-data.coa.create') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-plus me-1"></i>Tambah Akun COA
                        </a>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $detailCount = $penjualan->details->count();
        $totalSubtotal = 0; $totalHPP = 0; $totalProfit = 0; $totalDiskon = 0;
        if ($detailCount > 0) {
            foreach ($penjualan->details as $d) {
                $hpp = $d->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                $sub = $d->subtotal ?? ($d->jumlah * $d->harga_satuan - ($d->diskon_nominal ?? 0));
                $totalSubtotal += $sub;
                $totalHPP += $hpp * $d->jumlah;
                $totalProfit += ($d->harga_satuan - $hpp) * $d->jumlah;
                // Hitung diskon: pakai diskon_nominal jika ada, fallback dari diskon_persen
                $diskonBaris = (float)($d->diskon_nominal ?? 0);
                if ($diskonBaris == 0 && ($d->diskon_persen ?? 0) > 0) {
                    $diskonBaris = round($d->harga_satuan * $d->jumlah * $d->diskon_persen / 100);
                }
                $totalDiskon += $diskonBaris;
            }
        } else {
            $hpp = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
            $hdrHarga = $penjualan->harga_satuan;
            if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
            }
            $totalSubtotal = ($penjualan->jumlah ?? 0) * $hdrHarga;
            $totalHPP = $hpp * ($penjualan->jumlah ?? 0);
            $totalProfit = ($hdrHarga - $hpp) * ($penjualan->jumlah ?? 0);
            $totalDiskon = $penjualan->diskon_nominal ?? 0;
        }
        
        // Additional costs
        $biayaOngkir = $penjualan->biaya_ongkir ?? 0;
        // Pakai biaya_ppn dari DB, fallback hitung 11% jika belum tersimpan
        $biayaPPN = ($penjualan->biaya_ppn ?? 0) > 0
            ? (float)$penjualan->biaya_ppn
            : round(($totalSubtotal + $biayaOngkir) * 0.11);
        
        // Calculate grand total
        $grandTotal = $penjualan->grand_total
            ?: ($totalSubtotal + $biayaPPN + $biayaOngkir - $totalDiskon);
    @endphp

    {{-- Row 1: Informasi Transaksi + Ringkasan --}}
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card card-modern h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-theme fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                                <div class="info-item-content">
                                    <div class="info-item-label">Nomor Transaksi</div>
                                    <div class="info-item-value text-theme-light">{{ $penjualan->nomor_penjualan ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="far fa-calendar-alt"></i>
                                </div>
                                <div class="info-item-content">
                                    <div class="info-item-label">Tanggal</div>
                                    <div class="info-item-value">
                                        @if(is_a($penjualan->tanggal, 'Carbon\Carbon'))
                                            {{ $penjualan->tanggal->isoFormat('D MMMM YYYY') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($penjualan->tanggal)->isoFormat('D MMMM YYYY') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div class="info-item-content">
                                    <div class="info-item-label">Metode Pembayaran</div>
                                    <div class="info-item-value">
                                        <span class="badge {{ ($penjualan->payment_method ?? '') === 'credit' ? 'badge-theme-warning' : 'badge-theme-success' }}">
                                            @switch($penjualan->payment_method ?? '')
                                                @case('cash') Tunai @break
                                                @case('transfer') Transfer @break
                                                @default {{ ucfirst($penjualan->payment_method ?? '') }}
                                            @endswitch
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="info-item-content">
                                    <div class="info-item-label">Status Transaksi</div>
                                    <div class="info-item-value">
                                        <span class="badge {{ ($penjualan->status ?? 'lunas') === 'lunas' ? 'badge-theme-success' : 'badge-theme-warning' }}">
                                            {{ ucfirst($penjualan->status ?? 'lunas') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-undo"></i>
                                </div>
                                <div class="info-item-content">
                                    <div class="info-item-label">Qty Retur</div>
                                    <div class="info-item-value">
                                        @php $totalQtyRetur = $penjualan->total_qty_retur ?? 0; @endphp
                                        @if($totalQtyRetur > 0)
                                            <span class="badge badge-theme-danger">{{ (int)$totalQtyRetur }}</span>
                                        @else
                                            <span class="badge badge-theme-success">0</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="far fa-comment-dots"></i>
                                </div>
                                <div class="info-item-content">
                                    <div class="info-item-label">Catatan</div>
                                    <div class="info-item-value text-muted" style="font-weight: 500;">
                                        {{ $penjualan->catatan ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-modern h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-theme fw-bold"><i class="fas fa-calculator me-2"></i>Ringkasan Transaksi</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="summary-item">
                            <span class="summary-item-label">Subtotal Produk</span>
                            <strong class="text-theme-light">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-item-label">Total HPP</span>
                            <strong class="text-muted">Rp {{ number_format($totalHPP, 0, ',', '.') }}</strong>
                        </div>
                        <div class="summary-item" style="background-color: rgba(46, 125, 50, 0.04); border-radius: 8px;">
                            <span class="summary-item-label text-success">Total Profit</span>
                            <strong class="{{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</strong>
                        </div>
                        
                        {{-- Additional Costs --}}
                        @if($biayaOngkir > 0)
                        <div class="summary-item">
                            <span class="summary-item-label">Biaya Ongkir</span>
                            <strong class="text-secondary">Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</strong>
                        </div>
                        @endif
                        
                        @if($biayaPPN > 0)
                        <div class="summary-item">
                            <span class="summary-item-label">Biaya PPN (11%)</span>
                            <strong class="text-warning-theme text-theme-light">Rp {{ number_format($biayaPPN, 0, ',', '.') }}</strong>
                        </div>
                        @endif

                        @if($totalDiskon > 0)
                        <div class="summary-item" style="background-color: rgba(198, 40, 40, 0.04); border-radius: 8px;">
                            <span class="summary-item-label text-danger">Total Diskon</span>
                            <strong class="text-danger">-Rp {{ number_format($totalDiskon, 0, ',', '.') }}</strong>
                        </div>
                        @endif
                    </div>
                    
                    <div class="grand-total-box">
                        <small class="text-muted d-block mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Total Penjualan</small>
                        <div class="fw-bold fs-4 text-theme">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                            *Termasuk PPN, Ongkir & Servis
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Detail Produk + Aksi --}}
    <div class="row mt-4">
        <div class="col-12">
            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs nav-tabs-modern mb-4" id="penjualanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail-pane" type="button" role="tab" aria-controls="detail-pane" aria-selected="true">
                        <i class="fas fa-list me-2"></i>Detail Transaksi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="struk-tab" data-bs-toggle="tab" data-bs-target="#struk-pane" type="button" role="tab" aria-controls="struk-pane" aria-selected="false">
                        <i class="fas fa-receipt me-2"></i>Struk Penjualan
                    </button>
                </li>
                <li class="nav-item" role="presentation" style="display: block !important;">
                    <button class="nav-link" id="bukti-pembayaran-tab" data-bs-toggle="tab" data-bs-target="#bukti-pembayaran-pane" type="button" role="tab" aria-controls="bukti-pembayaran-pane" aria-selected="false" style="display: block !important; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                        <i class="fas fa-file-image me-2"></i>Bukti Pembayaran
                    </button>
                </li>
            </ul>
            
            <!-- DEBUG: Tab count = 3 tabs should be visible - Updated {{ date('Y-m-d H:i:s') }} -->

            {{-- Tab Content --}}
            <div class="tab-content" id="penjualanTabsContent">
                {{-- Detail Tab --}}
                <div class="tab-pane fade show active" id="detail-pane" role="tabpanel" aria-labelledby="detail-tab">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-modern mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0 text-theme fw-bold"><i class="fas fa-box me-2"></i>Detail Produk</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-modern mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Produk</th>
                                                    <th class="text-end">Qty</th>
                                                    <th class="text-end">Harga</th>
                                                    <th class="text-end">HPP</th>
                                                    <th class="text-end">Profit</th>
                                                    @if($totalDiskon > 0)<th class="text-end">Diskon</th>@endif
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($detailCount > 0)
                                                    @foreach($penjualan->details as $detail)
                                                        @php
                                                            $actualHPP = $detail->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                            $margin = ($detail->harga_satuan - $actualHPP) * $detail->jumlah;
                                                            $diskonNomDetail = (float)($detail->diskon_nominal ?? 0);
                                                            if ($diskonNomDetail == 0 && ($detail->diskon_persen ?? 0) > 0) {
                                                                $diskonNomDetail = round($detail->harga_satuan * $detail->jumlah * $detail->diskon_persen / 100);
                                                            }
                                                            $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - $diskonNomDetail);
                                                        @endphp
                                                        <tr>
                                                            <td class="fw-semibold">{{ $detail->produk->nama_produk ?? '-' }}</td>
                                                            <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah,2,',','.'),'0'),',') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                                            <td class="text-end text-muted">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                                            <td class="text-end {{ $margin > 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                                            @if($totalDiskon > 0)
                                                            <td class="text-end text-danger">
                                                                @if(($detail->diskon_persen ?? 0) > 0)
                                                                    {{ number_format($detail->diskon_persen, 0) }}%
                                                                    @if($diskonNomDetail > 0)<br><small>-Rp {{ number_format($diskonNomDetail, 0, ',', '.') }}</small>@endif
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            @endif
                                                            <td class="text-end fw-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @php
                                                        $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                        $hdrHarga = $penjualan->harga_satuan;
                                                        if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                            $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                        }
                                                        $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-semibold">{{ $penjualan->produk?->nama_produk ?? '-' }}</td>
                                                        <td class="text-end">{{ rtrim(rtrim(number_format($penjualan->jumlah,2,',','.'),'0'),',') }}</td>
                                                        <td class="text-end">Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</td>
                                                        <td class="text-end text-muted">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                                        <td class="text-end {{ $margin > 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                                        @if($totalDiskon > 0)
                                                        <td class="text-end text-danger">
                                                            -Rp {{ number_format($totalDiskon, 0, ',', '.') }}
                                                        </td>
                                                        @endif
                                                        <td class="text-end fw-semibold">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <div class="col-md-4">
                            <div class="card card-modern mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0 text-theme fw-bold"><i class="fas fa-cogs me-2"></i>Aksi Transaksi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <a href="{{ route('transaksi.penjualan.show', $penjualan->id) }}" class="btn-action-modern btn-action-detail">
                                                <i class="fas fa-eye text-success"></i>
                                                <small class="mt-1">Detail</small>
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn-action-modern btn-action-edit">
                                                <i class="fas fa-edit text-warning"></i>
                                                <small class="mt-1">Edit</small>
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn-action-modern btn-action-jurnal">
                                                <i class="fas fa-book text-primary"></i>
                                                <small class="mt-1">Jurnal</small>
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <a href="#" onclick="showStrukTab()" class="btn-action-modern btn-action-print">
                                                <i class="fas fa-print text-secondary"></i>
                                                <small class="mt-1">Cetak</small>
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <a href="{{ route('transaksi.retur-penjualan.detail-retur', $penjualan->id) }}" class="btn-action-modern btn-action-retur">
                                                <i class="fas fa-undo text-info"></i>
                                                <small class="mt-1">Retur</small>
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" onclick="confirmDeletePenjualan({{ $penjualan->id }})" class="btn-action-modern btn-action-delete w-100">
                                                <i class="fas fa-trash text-danger"></i>
                                                <small class="mt-1">Hapus</small>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden form for delete -->
                                    <form id="deletePenjualanForm{{ $penjualan->id }}" action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Bukti Pembayaran Section --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-modern mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0 text-theme fw-bold"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        // Ambil bukti pembayaran dari field penjualan
                                        $buktiPembayaranPath = $penjualan->bukti_pembayaran;
                                    @endphp
                                    
                                    {{-- Upload Form --}}
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Upload Bukti Transfer</h6>
                                        <form id="uploadBuktiFormInline" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <input type="file" class="form-control" id="bukti_file_inline" name="bukti_file" 
                                                               accept="image/*,.pdf,.doc,.docx" required>
                                                        <div class="form-text">Format: JPG, PNG, PDF (Max 5MB)</div>
                                                        <div class="invalid-feedback" id="file-error-inline"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="keterangan_inline" name="keterangan" 
                                                               placeholder="Contoh: Transfer dari rekening pribadi, referensi: ...">
                                                        <div class="form-text">Catatan (Opsional)</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-back-theme px-4" style="background-color: var(--brown); color: white;">
                                                <i class="fas fa-upload me-2"></i>Upload Bukti
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <hr>
                                    
                                    {{-- Daftar Bukti Pembayaran --}}
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-3">Bukti Pembayaran</h6>
                                        @if($penjualan->payment_method === 'transfer')
                                            @if($buktiPembayaranPath)
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <div class="card bukti-card">
                                                            <div class="card-body text-center p-3">
                                                                @if(in_array(strtolower(pathinfo($buktiPembayaranPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                                    <img src="{{ url('/storage/' . $buktiPembayaranPath) }}" 
                                                                         class="img-fluid rounded mb-2 bukti-image" 
                                                                         onclick="showImageModal('{{ url('/storage/' . $buktiPembayaranPath) }}', 'Bukti Pembayaran')">
                                                                @else
                                                                    <div class="text-center py-4">
                                                                        <i class="fas fa-file-alt fa-3x text-muted mb-2"></i>
                                                                        <p class="mb-0 small">{{ basename($buktiPembayaranPath) }}</p>
                                                                    </div>
                                                                @endif
                                                                
                                                                <small class="text-muted d-block mb-1">Bukti Pembayaran</small>
                                                                @if($penjualan->catatan_pembayaran)
                                                                    <small class="text-muted d-block mb-2">{{ $penjualan->catatan_pembayaran }}</small>
                                                                @endif
                                                                
                                                                <div class="bukti-actions">
                                                                    <a href="{{ url('/storage/' . $buktiPembayaranPath) }}" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-outline-primary me-1"
                                                                       title="Lihat">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    <a href="{{ url('/storage/' . $buktiPembayaranPath) }}" 
                                                                       download 
                                                                       class="btn btn-sm btn-outline-success me-1"
                                                                       title="Download">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="empty-bukti">
                                                    <i class="fas fa-file-image fa-3x text-muted mb-3"></i>
                                                    <h6 class="text-muted">Belum ada bukti pembayaran</h6>
                                                    <p class="text-muted">Upload bukti pembayaran untuk melengkapi transaksi</p>
                                                </div>
                                            @endif
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Pembayaran tunai tidak memerlukan bukti pembayaran.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Struk Tab --}}
                <div class="tab-pane fade" id="struk-pane" role="tabpanel" aria-labelledby="struk-tab">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card card-modern mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-theme fw-bold"><i class="fas fa-receipt me-2"></i>Struk Penjualan</h5>
                                    <button type="button" class="btn btn-back-theme" style="background-color: var(--brown); color: white;" onclick="printStruk()">
                                        <i class="fas fa-print me-2"></i>Cetak Struk
                                    </button>
                                </div>
                                <div class="card-body d-flex justify-content-center p-2">
                                    <div id="strukContent" class="struk-container">
                                        {{-- Header Perusahaan --}}
                                        <div class="struk-header">
                                            @php
                                                // Get company data with fallback
                                                $dataPerusahaan = (object)[
                                                    'nama' => 'TOKO ANDA',
                                                    'alamat' => 'Alamat Toko',
                                                    'telepon' => '021-12345678'
                                                ];
                                                
                                                try {
                                                    $company = \App\Models\Perusahaan::select('nama', 'alamat', 'telepon')
                                                        ->where('user_id', auth()->id())
                                                        ->first();
                                                    if ($company) {
                                                        $dataPerusahaan = $company;
                                                    }
                                                } catch (Exception $e) {
                                                    // Use fallback data
                                                }
                                            @endphp
                                            <div class="company-name">{{ strtoupper($dataPerusahaan->nama ?? 'MANUFAKTUR COE') }}</div>
                                            <div class="company-info">
                                                {{ $dataPerusahaan->alamat ?? 'Jl. Kebon No. 123' }}<br>
                                                Telp: {{ $dataPerusahaan->telepon ?? '0812-3456-7890' }}
                                            </div>
                                        </div>
                                        
                                        {{-- Info Transaksi --}}
                                        <div class="transaction-info">
                                            <div class="info-row">
                                                <span>No. Transaksi</span>
                                                <span>: {{ $penjualan->nomor_penjualan ?? 'SJ-' . date('Ymd') . '-' . str_pad($penjualan->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Tanggal</span>
                                                <span>: {{ optional($penjualan->tanggal_transaksi)->format('d/m/Y H:i') ?? date('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Kasir</span>
                                                <span>: {{ strtoupper($penjualan->kasir_nama ?? auth()->user()->name ?? 'KASIR') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">--------------------------------</div>
                                        
                                        {{-- Items --}}
                                        <div class="items-section">
                                            @if($detailCount > 0)
                                                @foreach($penjualan->details as $detail)
                                                    @php
                                                        $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - ($detail->diskon_nominal ?? 0));
                                                    @endphp
                                                    <div class="item">
                                                        <div class="item-name">{{ $detail->produk->nama_produk ?? '-' }}</div>
                                                        <div class="item-detail">
                                                            <span>{{ number_format($detail->jumlah, 0) }} x {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</span>
                                                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                                        </div>
                                                        @if(($detail->diskon_nominal ?? 0) > 0)
                                                            <div class="item-discount">
                                                                <span>Diskon:</span>
                                                                <span>-Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                @php
                                                    $hdrHarga = $penjualan->harga_satuan;
                                                    if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                    }
                                                @endphp
                                                <div class="item">
                                                    <div class="item-name">{{ $penjualan->produk?->nama_produk ?? '-' }}</div>
                                                    <div class="item-detail">
                                                        <span>{{ number_format($penjualan->jumlah, 0) }} x Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</span>
                                                        <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
                                                    </div>
                                                    @if(($penjualan->diskon_nominal ?? 0) > 0)
                                                        <div class="item-discount">
                                                            <span>Diskon:</span>
                                                            <span>-Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Summary --}}
                                        <div class="summary-section">
                                            <div class="summary-row">
                                                <span>Subtotal Produk:</span>
                                                <span>Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</span>
                                            </div>
                                            @if($biayaOngkir > 0)
                                            <div class="summary-row">
                                                <span>Biaya Ongkir:</span>
                                                <span>Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</span>
                                            </div>
                                            @endif
                                            <div class="summary-row">
                                                <span>Biaya PPN (11%):</span>
                                                <span>Rp {{ number_format($biayaPPN, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="summary-row">
                                                <span>Total Diskon:</span>
                                                <span>-Rp {{ number_format($totalDiskon, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="total-section">
                                            <div class="total-row">
                                                <span>Total Pembayaran:</span>
                                                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">--------------------------------</div>
                                        
                                        {{-- Payment Info --}}
                                        <div class="payment-info">
                                            <div class="info-row">
                                                <span>Pembayaran</span>
                                                <span>: 
                                                    @switch($penjualan->payment_method ?? 'cash')
                                                        @case('cash') Tunai @break
                                                        @case('transfer') Transfer Bank @break
                                                        @default {{ ucfirst($penjualan->payment_method ?? '-') }}
                                                    @endswitch
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">--------------------------------</div>
                                        
                                        {{-- Footer --}}
                                        <div class="footer">
                                            Terima kasih atas kunjungan Anda!<br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bukti Pembayaran Tab --}}
                <div class="tab-pane fade" id="bukti-pembayaran-pane" role="tabpanel" aria-labelledby="bukti-pembayaran-tab">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadBuktiModal">
                                    <i class="fas fa-plus me-2"></i>Tambah Bukti
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                // Ambil bukti pembayaran dari database (asumsi ada relasi)
                                $buktiPembayaran = $penjualan->buktiPembayaran ?? collect();
                            @endphp
                            
                            @if($buktiPembayaran->count() > 0)
                                <div class="row">
                                    @foreach($buktiPembayaran as $bukti)
                                        <div class="col-md-4 mb-3">
                                            <div class="card bukti-card">
                                                <div class="card-body text-center p-3">
                                                    @if(in_array(strtolower(pathinfo($bukti->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                        <img src="{{ url('/storage/' . $bukti->file_path) }}" 
                                                             class="img-fluid rounded mb-2 bukti-image" 
                                                             onclick="showImageModal('{{ url('/storage/' . $bukti->file_path) }}', '{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}')">
                                                    @else
                                                        <div class="text-center py-4">
                                                            <i class="fas fa-file-alt fa-3x text-muted mb-2"></i>
                                                            <p class="mb-0 small">{{ basename($bukti->file_path) }}</p>
                                                        </div>
                                                    @endif
                                                    
                                                    <small class="text-muted d-block mb-1">{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}</small>
                                                    <small class="text-muted d-block mb-2">{{ $bukti->created_at->format('d/m/Y H:i') }}</small>
                                                    
                                                    <div class="bukti-actions">
                                                        <a href="{{ url('/storage/' . $bukti->file_path) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary me-1"
                                                           title="Lihat">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ url('/storage/' . $bukti->file_path) }}" 
                                                           download 
                                                           class="btn btn-sm btn-outline-success me-1"
                                                           title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteBukti({{ $bukti->id }})"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-bukti">
                                    <i class="fas fa-file-image fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada bukti pembayaran</h5>
                                    <p class="text-muted">Klik tombol "Tambah Bukti" untuk mengunggah bukti pembayaran</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Riwayat Retur --}}
    @if($penjualan->returPenjualans->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-undo me-2"></i>Riwayat Retur</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Retur</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Produk</th>
                                    <th class="text-end">Total Retur</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualan->returPenjualans as $retur)
                                <tr>
                                    <td><strong>{{ $retur->nomor_retur }}</strong></td>
                                    <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $retur->jenis_retur === 'tukar_barang' ? 'Tukar Barang' : 'Refund' }}</td>
                                    <td>
                                        @foreach($retur->detailReturPenjualans as $d)
                                            <div>{{ $d->produk?->nama_produk }} ({{ (int)$d->qty_retur }} pcs)</div>
                                        @endforeach
                                    </td>
                                    <td class="text-end">Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</td>
                                    <td>
                                    span class="badge {{ $retur->status === 'selesai' ? 'bg-success' : ($retur->status === 'lunas' ? 'bg-info' : 'bg-warning') }}">
                                            {{ ucfirst($retur->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

        </div>
    </div>
    @endif
</div>

{{-- Modal Upload Bukti Pembayaran --}}
<div class="modal fade" id="uploadBuktiModal" tabindex="-1" aria-labelledby="uploadBuktiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadBuktiModalLabel">
                    <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadBuktiForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bukti_file" class="form-label">File Bukti Pembayaran</label>
                        <input type="file" class="form-control" id="bukti_file" name="bukti_file" 
                               accept="image/*,.pdf,.doc,.docx" required>
                        <div class="form-text">Format yang didukung: JPG, PNG, PDF, DOC, DOCX (Max: 5MB)</div>
                        <div class="invalid-feedback" id="file-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" 
                                  placeholder="Masukkan keterangan bukti pembayaran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Preview Image --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Preview Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>

<style>
.struk-container {
    width: 280px;
    background: white;
    padding: 15px;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.4;
    border: 1px solid #ddd;
    margin: 0 auto;
}

.struk-header {
    text-align: center;
    margin-bottom: 10px;
    border-bottom: 1px dashed #333;
    padding-bottom: 8px;
}

.company-name {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 3px;
    text-transform: uppercase;
}

.company-info {
    font-size: 9px;
    line-height: 1.2;
    color: #555;
}

.divider {
    text-align: center;
    margin: 8px 0;
    font-size: 10px;
    color: #666;
}

.transaction-info {
    margin-bottom: 8px;
    font-size: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
}

.items-section {
    margin-bottom: 8px;
}

.item {
    margin-bottom: 6px;
}

.item-name {
    font-weight: bold;
    font-size: 10px;
    margin-bottom: 1px;
}

.item-detail {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
}

.item-discount {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    color: #666;
    font-style: italic;
}

.summary-section {
    padding-top: 6px;
    margin-bottom: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
    font-size: 10px;
}

.total-section {
    margin-bottom: 8px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 12px;
    border-top: 1px solid #333;
    padding-top: 4px;
    margin-top: 4px;
}

.payment-info {
    margin-bottom: 8px;
    font-size: 10px;
}

.footer {
    text-align: center;
    border-top: 1px dashed #333;
    padding-top: 8px;
    font-size: 8px;
    color: #666;
    line-height: 1.3;
}

/* Print preparation styles */
body.printing {
    overflow: hidden;
}

body.printing * {
    -webkit-print-color-adjust: exact !important;
    color-adjust: exact !important;
}

/* Ensure struk is ready for print */
.tab-pane#struk-pane.active .struk-container {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

/* Bukti Pembayaran Styles */
.bukti-card {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #dee2e6;
}

.bukti-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bukti-image {
    max-height: 150px;
    object-fit: cover;
    cursor: pointer;
    transition: opacity 0.2s ease-in-out;
}

.bukti-image:hover {
    opacity: 0.8;
}

.bukti-actions .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.empty-bukti {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Force tab visibility */
#bukti-pembayaran-tab {
    display: block !important;
    visibility: visible !important;
}

.nav-tabs .nav-item {
    display: block !important;
}
</style>

<script>
function showStrukTab() {
    try {
        // Activate struk tab
        const strukTab = new bootstrap.Tab(document.getElementById('struk-tab'));
        strukTab.show();
    } catch (error) {
        console.error('Error showing struk tab:', error);
        alert('Terjadi kesalahan saat membuka tab struk. Silakan refresh halaman.');
    }
}

function printStruk() {
    try {
        window.open(`/transaksi/penjualan/{{ $penjualan->id }}/struk?print=1`, '_blank', 'width=400,height=600,scrollbars=yes');
    } catch (error) {
        console.error('Error printing struk:', error);
        alert('Terjadi kesalahan saat mencetak. Silakan coba lagi.');
    }
}

// Add keyboard shortcut for print when on struk tab
document.addEventListener('keydown', function(e) {
    try {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            const strukTab = document.getElementById('struk-pane');
            if (strukTab && strukTab.classList.contains('active')) {
                e.preventDefault();
                printStruk();
            }
        }
    } catch (error) {
        console.error('Error handling keyboard shortcut:', error);
    }
});

// Function to show image modal
function showImageModal(imageSrc, title) {
    document.getElementById('previewImage').src = imageSrc;
    document.getElementById('imagePreviewModalLabel').textContent = title;
    const imageModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    imageModal.show();
}

// Function to delete bukti pembayaran
function deleteBukti(buktiId) {
    if (confirm('Yakin ingin menghapus bukti pembayaran ini?')) {
        fetch(`/transaksi/penjualan/{{ $penjualan->id }}/bukti-pembayaran/${buktiId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus bukti pembayaran: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus bukti pembayaran: ' + error.message);
        });
    }
}

// Handle upload bukti pembayaran form
document.getElementById('uploadBuktiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('bukti_file');
    const file = fileInput.files[0];
    const fileError = document.getElementById('file-error');
    
    // Reset error state
    fileInput.classList.remove('is-invalid');
    fileError.textContent = '';
    
    // Validate file size (5MB = 5 * 1024 * 1024 bytes)
    if (file && file.size > 5 * 1024 * 1024) {
        fileInput.classList.add('is-invalid');
        fileError.textContent = 'Ukuran file tidak boleh lebih dari 5MB';
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    submitBtn.disabled = true;
    
    fetch(`/transaksi/penjualan/{{ $penjualan->id }}/bukti-pembayaran`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Reload page to show new bukti
            location.reload();
        } else {
            alert('Gagal upload bukti pembayaran: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat upload bukti pembayaran: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle upload bukti pembayaran form (inline in detail tab)
document.getElementById('uploadBuktiFormInline')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('bukti_file_inline');
    const file = fileInput.files[0];
    const fileError = document.getElementById('file-error-inline');
    
    // Reset error state
    fileInput.classList.remove('is-invalid');
    fileError.textContent = '';
    
    // Validate file size (5MB = 5 * 1024 * 1024 bytes)
    if (file && file.size > 5 * 1024 * 1024) {
        fileInput.classList.add('is-invalid');
        fileError.textContent = 'Ukuran file tidak boleh lebih dari 5MB';
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    submitBtn.disabled = true;
    
    fetch(`/transaksi/penjualan/{{ $penjualan->id }}/bukti-pembayaran`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Reload page to show new bukti
            location.reload();
        } else {
            alert('Gagal upload bukti pembayaran: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat upload bukti pembayaran: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Prevent any potential 404 requests
document.addEventListener('DOMContentLoaded', function() {
    // Force initialize Bootstrap tabs if needed
    try {
        const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabElements.forEach(function(tabElement) {
            new bootstrap.Tab(tabElement);
        });
    } catch (error) {
        console.error('Error initializing Bootstrap tabs:', error);
    }
});
window.addEventListener('error', function(e) {
    if (e.target && e.target.src && e.target.src.includes('404')) {
        console.warn('Blocked 404 request:', e.target.src);
        e.preventDefault();
    }
});

// Function to confirm delete penjualan
function confirmDeletePenjualan(penjualanId) {
    if (confirm('Yakin ingin hapus transaksi ini?')) {
        document.getElementById('deletePenjualanForm' + penjualanId).submit();
    }
}
</script>

@endsection
            </div>