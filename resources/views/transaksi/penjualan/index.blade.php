@extends('layouts.app')

@section('title', 'Transaksi Penjualan')

@push('styles')
<style>
/* Tab Navigation - Style seperti laporan penjualan */
.nav-tabs-custom {
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}

.nav-tabs-custom .tab-btn {
    background: none;
    border: none;
    padding: 1rem 2rem;
    font-size: 0.95rem;
    font-weight: 500;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -1px;
    margin-right: 2rem;
}

.nav-tabs-custom .tab-btn:hover {
    color: #495057;
    border-bottom-color: #d1d5db;
}

.nav-tabs-custom .tab-btn.active {
    color: #8B7355;
    border-bottom-color: #8B7355;
    font-weight: 600;
}

.tab-pane {
    display: none;
}

.tab-pane.show.active {
    display: block;
}

/* Summary Cards Style */
.summary-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #8B7355;
}

.summary-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.summary-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}

/* Action Layout Style - Layout yang lebih rapi dan kompak */
.action-layout {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.action-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    flex-wrap: wrap;
}

/* Modal fixes */
.modal-backdrop {
    z-index: 1050 !important;
}

.modal {
    z-index: 1055 !important;
}

.modal-dialog {
    z-index: 1060 !important;
}

/* Ensure modal content is clickable */
.modal-content {
    position: relative;
    z-index: 1065 !important;
    pointer-events: auto;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.btn-minimal {
    font-size: 0.7rem !important;
    text-decoration: none !important;
    padding: 4px 10px !important;
    border-radius: 0.2rem;
    transition: all 0.2s ease;
    cursor: pointer !important;
    border: 1px solid;
    background: white;
    font-weight: 500;
    white-space: nowrap;
    width: 60px !important;
    min-width: 60px !important;
    max-width: 60px !important;
    height: 28px !important;
    min-height: 28px !important;
    max-height: 28px !important;
    text-align: center;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    line-height: 1;
    box-sizing: border-box;
    pointer-events: auto !important;
    z-index: 1 !important;
}

.btn-minimal:hover {
    transform: translateY(-1px);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-minimal.btn-warning {
    color: #f59e0b;
    border-color: #f59e0b;
}

.btn-minimal.btn-warning:hover {
    background: #fef3c7;
    border-color: #d97706;
    color: #d97706;
}

.btn-minimal.btn-primary {
    color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    background: white !important;
}

.btn-minimal.btn-primary:hover {
    background: #dbeafe !important;
    border-color: #2563eb !important;
    color: #2563eb !important;
}

.btn-minimal.btn-info {
    color: #06b6d4;
    border-color: #06b6d4;
}

.btn-minimal.btn-info:hover {
    background: #cffafe;
    border-color: #0891b2;
    color: #0891b2;
}

.btn-minimal.btn-danger {
    color: #ef4444;
    border-color: #ef4444;
}

.btn-minimal.btn-danger:hover {
    background: #fee2e2;
    border-color: #dc2626;
    color: #dc2626;
}

/* DETAIL (abu soft) */
.btn-minimal.btn-detail {
    color: #62c7a6ff;        
    border-color: #75ceb0ff;
    background: #ecfdf5;
}

.btn-minimal.btn-detail:hover {
    background: #d1fae5;
    color: #047857;
}

/* JURNAL (biru) */
.btn-minimal.btn-jurnal {
    color: #3b82f6;
    border-color: #3b82f6;
}

.btn-minimal.btn-jurnal:hover {
    background: #dbeafe;
    color: #1d4ed8;
}

/* CETAK */
.btn-minimal.btn-success {
    color: #e8884d;
    border-color: #e8884d;
}

.btn-minimal.btn-success:hover {
    background: #d1fae5;
    border-color: #ba5414;
    color: #ba5414;
}

.modal-body .row.mt-4 {
    margin-top: 10px !important;
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
    max-height: 120px;
    object-fit: cover;
    cursor: pointer;
    transition: opacity 0.2s ease-in-out;
}

.bukti-image:hover {
    opacity: 0.8;
}

.bukti-actions .btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Multi-line product data styles */
.multi-product-container,
.multi-quantity,
.multi-price,
.multi-hpp,
.multi-profit,
.multi-discount {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.product-item {
    display: flex;
    align-items: center;
    padding: 0.125rem 0;
    border-bottom: 1px dotted #e9ecef;
    font-size: 0.875rem;
}

.product-item:last-child {
    border-bottom: none;
}

.qty-item,
.price-item,
.hpp-item,
.profit-item,
.discount-item {
    padding: 0.125rem 0;
    border-bottom: 1px dotted #e9ecef;
    font-size: 0.875rem;
}

.qty-item:last-child,
.price-item:last-child,
.hpp-item:last-child,
.profit-item:last-child,
.discount-item:last-child {
    border-bottom: none;
}

.single-product,
.single-quantity,
.single-price,
.single-hpp,
.single-profit,
.single-discount {
    padding: 0.25rem 0;
    font-size: 0.875rem;
}

/* Table cell adjustments for better alignment */
.table td {
    vertical-align: top;
    padding: 0.5rem;
}

/* Ensure proper spacing between rows */
.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:last-child {
    border-bottom: none;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Data Penjualan
        </h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="openPenjualanSetting()" title="Pengaturan Penjualan">
                <i class="fas fa-cog me-1"></i>Pengaturan
            </button>
            <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Penjualan
            </a>
        </div>
    </div>

    <!-- Summary Cards - Style seperti laporan -->
    <div class="row g-2 mb-4">
        <div class="col-md-2">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">Total Penjualan (Hari Ini)</div>
                    <h5 class="mb-1 text-primary fw-bold">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h5>
                    <small class="{{ $penjualanChange >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $penjualanChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($penjualanChange), 1) }}% dari kemarin
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">Total Transaksi (Hari Ini)</div>
                    <h5 class="mb-1 text-info fw-bold">{{ number_format($jumlahTransaksiHariIni, 0, ',', '.') }}</h5>
                    <small class="{{ $transaksiChange >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $transaksiChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($transaksiChange), 1) }}% dari kemarin
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">Total Produk Terjual (Hari Ini)</div>
                    <h5 class="mb-1 text-warning fw-bold">{{ number_format($totalProdukTerjual, 0, ',', '.') }}</h5>
                    <small class="{{ $produkChange >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $produkChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($produkChange), 1) }}% dari kemarin
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">Total Ongkir (Hari Ini)</div>
                    <h5 class="mb-1 text-info fw-bold">Rp {{ number_format($totalOngkir, 0, ',', '.') }}</h5>
                    <small class="{{ $ongkirChange >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $ongkirChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($ongkirChange), 1) }}% dari kemarin
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">Total Diskon (Hari Ini)</div>
                    <h5 class="mb-1 text-danger fw-bold">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</h5>
                    <small class="{{ $diskonChange >= 0 ? 'text-danger' : 'text-success' }}">
                        <i class="fas fa-arrow-{{ $diskonChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($diskonChange), 1) }}% dari kemarin
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">Total Profit (Hari Ini)</div>
                    <h5 class="mb-1 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h5>
                    <small class="{{ $profitChange >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $profitChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($profitChange), 1) }}% dari kemarin
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section - Compact Style -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3" style="color: #1f2937; font-weight: 600;">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
            <form method="GET" action="{{ route('transaksi.penjualan.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">Nomor Transaksi</label>
                    <input type="text" name="nomor_transaksi" class="form-control form-control-sm" 
                           value="{{ request('nomor_transaksi') }}" placeholder="Cari nomor transaksi...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control form-control-sm" 
                           value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control form-control-sm" 
                           value="{{ request('tanggal_selesai') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm" style="background: #8B7355; color: white; border-radius: 6px;">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Custom Tabs Navigation - Style seperti laporan -->
            <div class="nav-tabs-custom mb-4">
                <button class="tab-btn active" onclick="showTab('penjualan-list', this)">
                    Penjualan
                </button>
                <button class="tab-btn" onclick="showTab('retur-list', this)">
                    Retur
                </button>
            </div>
            
            <div class="tab-content" id="penjualanTabContent">
                <div class="tab-pane fade show active" id="penjualan-list" role="tabpanel">
                    <!-- Konten Penjualan -->
                    <h5 class="mb-3">
                        <i class="fas fa-list me-2"></i>Riwayat Penjualan
                        @if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'payment_method', 'status']))
                            <small class="text-muted">(Filter Aktif)</small>
                        @endif
                    </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pembayaran</th>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Harga/Satuan</th>
                            <th class="text-end">HPP</th>
                            <th class="text-end">Profit</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Total</th>
                            <th>Qty Retur</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualans as $key => $penjualan)
                            <tr class="{{ $key % 2 === 0 ? 'table-light' : '' }}">
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td><strong>{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                                <td>{{ optional($penjualan->tanggal_transaksi)->format('d-m-Y H:i') ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ ($penjualan->payment_method ?? '') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                        @switch($penjualan->payment_method ?? '')
                                            @case('cash') Tunai @break
                                            @case('transfer') Transfer @break
                                            @case('credit') Kredit @break
                                            @default Tidak Diketahui
                                        @endswitch
                                    </span>
                                </td>
                                @php $detailCount = $penjualan->details->count(); @endphp
                                <td>
                                    @if($detailCount > 1)
                                        <div class="multi-product-container">
                                            @foreach($penjualan->details as $d)
                                                <div class="product-item">
                                                    <i class="fas fa-box text-muted me-1" style="font-size: 0.7rem;"></i>
                                                    {{ $d->produk->nama_produk ?? '-' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($detailCount === 1)
                                        <div class="single-product">
                                            <i class="fas fa-box text-muted me-1" style="font-size: 0.7rem;"></i>
                                            {{ $penjualan->details[0]->produk->nama_produk ?? '-' }}
                                        </div>
                                    @else
                                        <div class="single-product">
                                            <i class="fas fa-box text-muted me-1" style="font-size: 0.7rem;"></i>
                                            {{ $penjualan->produk?->nama_produk ?? '-' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($detailCount > 1)
                                        <div class="multi-quantity">
                                            @foreach($penjualan->details as $d)
                                                <div class="qty-item">
                                                    <strong>{{ rtrim(rtrim(number_format($d->jumlah,2,',','.'),'0'),',') }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($detailCount === 1)
                                        <div class="single-quantity">
                                            <strong>{{ rtrim(rtrim(number_format($penjualan->details[0]->jumlah,2,',','.'),'0'),',') }}</strong>
                                        </div>
                                    @else
                                        <div class="single-quantity">
                                            <strong>{{ rtrim(rtrim(number_format($penjualan->jumlah,2,',','.'),'0'),',') }}</strong>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($detailCount > 1)
                                        <div class="multi-price">
                                            @foreach($penjualan->details as $d)
                                                @php
                                                    $hargaSatuan = $d->harga_satuan ?? 0;
                                                    if ($hargaSatuan == 0 && $d->produk) {
                                                        $hargaSatuan = $d->produk->harga_jual ?? 0;
                                                    }
                                                @endphp
                                                <div class="price-item">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</div>
                                            @endforeach
                                        </div>
                                    @elseif($detailCount === 1)
                                        <div class="single-price">
                                            @php
                                                $hargaSatuan = $penjualan->details[0]->harga_satuan ?? 0;
                                                if ($hargaSatuan == 0 && $penjualan->details[0]->produk) {
                                                    $hargaSatuan = $penjualan->details[0]->produk->harga_jual ?? 0;
                                                }
                                            @endphp
                                            Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="single-price">
                                            @php
                                                $hdrHarga = $penjualan->harga_satuan;
                                                if (is_null($hdrHarga) || $hdrHarga == 0) {
                                                    if ($penjualan->produk) {
                                                        $hdrHarga = $penjualan->produk->harga_jual ?? 0;
                                                    }
                                                    if (($hdrHarga == 0) && ($penjualan->jumlah ?? 0) > 0) {
                                                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                    }
                                                }
                                            @endphp
                                            Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($detailCount > 1)
                                        <div class="multi-hpp">
                                            @foreach($penjualan->details as $d)
                                                <div class="hpp-item">Rp {{ number_format($d->produk->getHPPForSaleDate($penjualan->tanggal), 0, ',', '.') }}</div>
                                            @endforeach
                                        </div>
                                    @elseif($detailCount === 1)
                                        <div class="single-hpp">
                                            Rp {{ number_format($penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal), 0, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="single-hpp">
                                            @php
                                                $hppValue = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                            @endphp
                                            Rp {{ number_format($hppValue, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($detailCount > 1)
                                        <div class="multi-profit">
                                            @foreach($penjualan->details as $d)
                                                @php $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah; @endphp
                                                <div class="profit-item {{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                            @endforeach
                                        </div>
                                    @elseif($detailCount === 1)
                                        <div class="single-profit">
                                            @php $actualHPP = $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal); $margin = ($penjualan->details[0]->harga_satuan - $actualHPP) * $penjualan->details[0]->jumlah; @endphp
                                            <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                        </div>
                                    @else
                                        <div class="single-profit">
                                            @php
                                                $hppValue = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                $hargaSatuan = $penjualan->harga_satuan ?? 0;
                                                $margin = ($hargaSatuan - $hppValue) * ($penjualan->jumlah ?? 0);
                                            @endphp
                                            <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($detailCount > 1)
                                        <div class="multi-discount">
                                            @foreach($penjualan->details as $d)
                                                @php $sub = (float)$d->jumlah * (float)$d->harga_satuan; $disc = (float)($d->diskon_nominal ?? 0); $pct = $sub>0 ? ($disc/$sub*100) : 0; @endphp
                                                <div class="discount-item">{{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})</div>
                                            @endforeach
                                        </div>
                                    @elseif($detailCount === 1)
                                        <div class="single-discount">
                                            @php $d=$penjualan->details[0]; $sub=(float)$d->jumlah*(float)$d->harga_satuan; $disc=(float)($d->diskon_nominal??0); $pct=$sub>0?($disc/$sub*100):0; @endphp
                                            {{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})
                                        </div>
                                    @else
                                        <div class="single-discount">
                                            @php 
                                                $disc = (float)($penjualan->diskon_nominal ?? 0);
                                                $pct = 0;
                                                if(($penjualan->jumlah ?? 0) > 0){
                                                    $hdrHarga = $penjualan->harga_satuan;
                                                    if(is_null($hdrHarga)){
                                                        $hdrHarga = ((float)$penjualan->total + $disc) / (float)$penjualan->jumlah;
                                                    }
                                                    $subtotal = $penjualan->jumlah * $hdrHarga;
                                                    $pct = $subtotal > 0 ? ($disc/$subtotal*100) : 0;
                                                }
                                            @endphp
                                            {{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold"><strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong></td>
                                <td>
                                    @php
                                        $totalQtyRetur = $penjualan->total_qty_retur;
                                    @endphp
                                    @if($totalQtyRetur > 0)
                                        <span class="badge bg-danger animate-pulse">
                                            <i class="fas fa-undo me-1"></i>{{ (int)$totalQtyRetur }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>0
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="action-layout">
                                        <div class="action-row gap-1 mb-1">
                                            <button type="button" class="btn-minimal btn-detail" data-bs-toggle="modal" data-bs-target="#detailModal{{ $penjualan->id }}" title="Detail Transaksi">
                                                Detail
                                            </button>
                                            <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn-minimal btn-warning" data-bs-toggle="tooltip" title="Edit Transaksi">
                                                Edit
                                            </a>
                                            <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn-minimal btn-jurnal" data-bs-toggle="tooltip" title="Lihat Jurnal">
                                                Jurnal
                                            </a>
                                        </div>
                                        <div class="action-row gap-1">
                                            <button type="button" class="btn-minimal btn-success" data-bs-toggle="modal" data-bs-target="#strukModal{{ $penjualan->id }}" title="Cetak Struk">
                                                Cetak
                                            </button>
                                            <a href="{{ route('transaksi.retur-penjualan.detail-retur', $penjualan->id) }}" class="btn-minimal btn-info" data-bs-toggle="tooltip" title="Proses Retur">
                                                Retur
                                            </a>
                                            <button type="submit" class="btn-minimal btn-danger" onclick="confirmDelete({{ $penjualan->id }})" data-bs-toggle="tooltip" title="Hapus Transaksi">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Hidden form for delete -->
                                    <form id="deleteForm{{ $penjualan->id }}" action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
                    <!-- Akhir Konten Penjualan -->
                </div>
                <div class="tab-pane fade" id="retur-list" role="tabpanel" aria-labelledby="retur-list-tab">
                    <!-- Konten Retur -->
                    <h5 class="mb-3">
                        <i class="fas fa-undo me-2"></i>Riwayat Retur Penjualan
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px">NO</th>
                                    <th>Tanggal</th>
                                    <th>Nomor Penjualan</th>
                                    <th>Deskripsi</th>
                                    <th>Kompensasi</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Retur</th>
                                    <th>Produk</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesReturns as $key => $retur)
                                    <tr>
                                        <td class="text-center">{{ $key + 1 }}</td>
                                        <td>{{ optional($retur->tanggal)->format('d-m-Y') ?? '-' }}</td>
                                        <td><strong>{{ $retur->penjualan?->nomor_penjualan ?? '-' }}</strong></td>
                                        <td>{{ $retur->keterangan ?? '-' }}</td>
                                        <td>
                                            @php
                                                $jenisRetur = $retur->jenis_retur ?? '';
                                                $jenisLabel = '';
                                                switch($jenisRetur) {
                                                    case 'refund':
                                                        $jenisLabel = 'Refund';
                                                        break;
                                                    case 'tukar_barang':
                                                        $jenisLabel = 'Tukar Barang';
                                                        break;
                                                    case 'kredit':
                                                        $jenisLabel = 'Kredit';
                                                        break;
                                                    default:
                                                        $jenisLabel = '-';
                                                }
                                            @endphp
                                            <span class="badge {{ $jenisRetur === 'refund' ? 'bg-danger' : ($jenisRetur === 'tukar_barang' ? 'bg-warning' : 'bg-info') }}">
                                                {{ $jenisLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $retur->status === 'selesai' ? 'bg-success' : ($retur->status === 'lunas' ? 'bg-info' : 'bg-warning') }}">
                                                {{ ucfirst($retur->status ?? '-') }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($retur->total_retur ?? 0, 0, ',', '.') }}</td>
                                        <td>
                                            @if($retur->detailReturPenjualans->count() > 0)
                                                @foreach($retur->detailReturPenjualans as $item)
                                                    <div>{{ $item->produk?->nama_produk ?? '-' }}</div>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="action-layout">
                                                <div class="action-row gap-1">
                                                    <button type="button" class="btn-minimal btn-detail" data-bs-toggle="modal" data-bs-target="#returDetailModal{{ $retur->id }}" title="Detail Retur">
                                                        Detail
                                                    </button>
                                                    <a href="{{ route('transaksi.retur-penjualan.edit', $retur->id) }}" class="btn-minimal btn-warning" data-bs-toggle="tooltip" title="Edit Retur">
                                                        Edit
                                                    </a>
                                                    <button type="button" class="btn-minimal btn-danger" onclick="confirmDeleteRetur({{ $retur->id }})" data-bs-toggle="tooltip" title="Hapus Retur">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Hidden form for delete retur -->
                                            <form id="deleteReturForm{{ $retur->id }}" action="{{ route('transaksi.retur-penjualan.destroy', $retur->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>Belum ada data retur penjualan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Akhir Konten Retur -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
@foreach($penjualans as $penjualan)
<div class="modal fade" id="detailModal{{ $penjualan->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $penjualan->id }}" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $penjualan->id }}">
                    <i class="fas fa-eye me-2"></i>Detail Transaksi Penjualan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    // Calculate summary data
                    $totalSubtotal = 0;
                    $totalHPP = 0;
                    $totalProfit = 0;
                    $detailCount = $penjualan->details->count();
                    
                    if($detailCount > 1) {
                        foreach($penjualan->details as $d) {
                            $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal);
                            $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah;
                            $subtotal = $d->jumlah * $d->harga_satuan;
                            
                            $totalSubtotal += $subtotal;
                            $totalHPP += $actualHPP * $d->jumlah;
                            $totalProfit += $margin;
                        }
                    } elseif($detailCount === 1) {
                        $d = $penjualan->details[0];
                        $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal);
                        $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah;
                        $subtotal = $d->jumlah * $d->harga_satuan;
                        
                        $totalSubtotal = $subtotal;
                        $totalHPP = $actualHPP * $d->jumlah;
                        $totalProfit = $margin;
                    } else {
                        $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                        $hdrHarga = $penjualan->harga_satuan;
                        if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                            $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                        }
                        $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                        $subtotal = ($penjualan->jumlah ?? 0) * $hdrHarga;
                        
                        $totalSubtotal = $subtotal;
                        $totalHPP = $actualHPP * ($penjualan->jumlah ?? 0);
                        $totalProfit = $margin;
                    }
                    
                    // Additional costs from database
                    $biayaOngkir = $penjualan->biaya_ongkir ?? 0;
                    $biayaPPN = $penjualan->biaya_ppn ?? 0;
                    
                    // Jika biaya_ppn belum tersimpan (transaksi lama), hitung dari selisih total
                    if ($biayaPPN == 0 && $penjualan->total > 0) {
                        $selisih = (float)$penjualan->total - $totalSubtotal - $biayaOngkir;
                        // Jika selisih mendekati 11% dari subtotal+ongkir, anggap itu PPN
                        $estimasiPPN = ($totalSubtotal + $biayaOngkir) * 0.11;
                        if ($selisih > 0 && abs($selisih - $estimasiPPN) < 1) {
                            $biayaPPN = $selisih;
                        }
                    }
                    
                    // Calculate grand total if not stored
                    $grandTotal = $penjualan->grand_total ?? ($totalSubtotal + $biayaPPN + $biayaOngkir - ($penjualan->diskon_nominal ?? 0));
                    
                    // Check return status
                    $hasRetur = $penjualan->returs()->exists();
                    $returnCount = $penjualan->returs()->count();
                @endphp
                
                <!-- Informasi Transaksi -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nomor Transaksi:</strong> {{ $penjualan->nomor_penjualan ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Tanggal:</strong> {{ optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Pelanggan:</strong> {{ $penjualan->pelanggan?->name ?? 'Umum' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Status Transaksi:</strong> 
                        <span class="badge {{ ($penjualan->status ?? 'lunas') === 'lunas' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($penjualan->status ?? 'lunas') }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Metode Pembayaran:</strong> 
                        <span class="badge {{ ($penjualan->payment_method ?? '') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                            @switch($penjualan->payment_method ?? '')
                                @case('cash') Tunai @break
                                @case('transfer') Transfer @break
                                @case('credit') Kredit @break
                                @default Tidak Diketahui
                            @endswitch
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Catatan:</strong> {{ $penjualan->catatan ?? '-' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Qty Retur:</strong> 
                        @php
                            $totalQtyRetur = $penjualan->total_qty_retur;
                        @endphp
                        @if($totalQtyRetur > 0)
                            <span class="badge bg-info">{{ (int)$totalQtyRetur }}</span>
                        @else
                            <span class="badge bg-success">0</span>
                        @endif
                    </div>
                </div>

                <h6 class="mb-3 mt-4"><i class="fas fa-box me-2"></i>Detail Produk</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">HPP</th>
                                <th class="text-end">Profit</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($detailCount > 1)
                                @foreach($penjualan->details as $d)
                                    @php 
                                    $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); 
                                    $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah;
                                    $subtotal = $d->jumlah * $d->harga_satuan;
                                    @endphp
                                    <tr>
                                        <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</td>
                                        <td class="text-end">Rp {{ number_format($d->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                        <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($margin, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @elseif($detailCount === 1)
                                @php 
                                $d = $penjualan->details[0];
                                $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); 
                                $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah;
                                $subtotal = $d->jumlah * $d->harga_satuan;
                                @endphp
                                <tr>
                                    <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">Rp {{ number_format($d->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                    <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                        Rp {{ number_format($margin, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @else
                                @php 
                                $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                $hdrHarga = $penjualan->harga_satuan;
                                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                }
                                $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                                $subtotal = ($penjualan->jumlah ?? 0) * $hdrHarga;
                                @endphp
                                <tr>
                                    <td>{{ $penjualan->produk?->nama_produk ?? '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($penjualan->jumlah,4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                    <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                        Rp {{ number_format($margin, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <h6 class="mb-3 mt-4"><i class="fas fa-receipt me-2"></i>Rincian Biaya</h6>
                <div class="row g-2">
                    <!-- Subtotal Produk -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted">Subtotal Produk:</span>
                            <span class="fw-bold">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <!-- Biaya Ongkir -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted">Biaya Ongkir:</span>
                            <span class="fw-bold {{ ($penjualan->biaya_ongkir ?? 0) > 0 ? 'text-warning' : '' }}">
                                Rp {{ number_format($penjualan->biaya_ongkir ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Biaya PPN -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            @php
                                $ppnPersen = 0;
                                if ($biayaPPN > 0 && $totalSubtotal > 0) {
                                    $ppnBase = $totalSubtotal + $biayaOngkir;
                                    $ppnPersen = $ppnBase > 0 ? ($biayaPPN / $ppnBase * 100) : 0;
                                }
                            @endphp
                            <span class="text-muted">PPN ({{ $ppnPersen > 0 ? number_format($ppnPersen, 0) : '0' }}%):</span>
                            <span class="fw-bold {{ $biayaPPN > 0 ? 'text-secondary' : '' }}">
                                Rp {{ number_format($biayaPPN, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Diskon -->
                    @if(($penjualan->diskon_nominal ?? 0) > 0)
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted">Diskon:</span>
                            <span class="fw-bold text-danger">
                                -Rp {{ number_format($penjualan->diskon_nominal ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
                
                <hr class="my-2">
                
                <!-- Grand Total -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark">TOTAL KESELURUHAN:</span>
                    <span class="fw-bold fs-5 text-primary">
                        Rp {{ number_format($penjualan->grand_total ?? $penjualan->total, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Bukti Pembayaran -->
                <h6 class="mb-3 mt-4"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h6>
                
                @php
                    // Ambil bukti pembayaran dari database
                    $buktiPembayaranModal = $penjualan->buktiPembayaran ?? collect();
                @endphp
                
                {{-- Upload Form --}}
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Upload Bukti Transfer</h6>
                    <form id="uploadBuktiFormModal{{ $penjualan->id }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="file" class="form-control" id="bukti_file_modal{{ $penjualan->id }}" name="bukti_file" 
                                           accept="image/*,.pdf,.doc,.docx" required>
                                    <div class="form-text">Format: JPG, PNG, PDF (Max 5MB)</div>
                                    <div class="invalid-feedback" id="file-error-modal{{ $penjualan->id }}"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="keterangan_modal{{ $penjualan->id }}" name="keterangan" 
                                           placeholder="Contoh: Transfer dari rekening pribadi, referensi: ...">
                                    <div class="form-text">Catatan (Opsional)</div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload Bukti
                        </button>
                    </form>
                </div>
                
                <hr>
                
                {{-- Daftar Bukti Pembayaran --}}
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Daftar Bukti Pembayaran</h6>
                    @if($buktiPembayaranModal->count() > 0)
                        <div class="row">
                            @foreach($buktiPembayaranModal as $bukti)
                                <div class="col-md-4 mb-3">
                                    <div class="card bukti-card">
                                        <div class="card-body text-center p-3">
                                            @if(in_array(strtolower(pathinfo($bukti->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                <img src="{{ asset('storage/' . $bukti->file_path) }}" 
                                                     class="img-fluid rounded mb-2 bukti-image" 
                                                     style="max-height: 120px; cursor: pointer;"
                                                     onclick="showImageModalPreview('{{ asset('storage/' . $bukti->file_path) }}', '{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}')">
                                            @else
                                                <div class="text-center py-3">
                                                    <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-0 small">{{ basename($bukti->file_path) }}</p>
                                                </div>
                                            @endif
                                            
                                            <small class="text-muted d-block mb-1">{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}</small>
                                            <small class="text-muted d-block mb-2">{{ $bukti->created_at->format('d/m/Y H:i') }}</small>
                                            
                                            <div class="bukti-actions">
                                                <a href="{{ asset('storage/' . $bukti->file_path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary me-1"
                                                   title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ asset('storage/' . $bukti->file_path) }}" 
                                                   download 
                                                   class="btn btn-sm btn-outline-success me-1"
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteBuktiModal({{ $bukti->id }}, {{ $penjualan->id }})"
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
                        <div class="text-center py-4">
                            <i class="fas fa-file-image fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Belum ada bukti pembayaran</h6>
                            <p class="text-muted">Upload bukti pembayaran untuk melengkapi transaksi</p>
                        </div>
                    @endif
                </div>

                <!-- Detail Retur -->
                @if($penjualan->returPenjualans && $penjualan->returPenjualans->count() > 0)
                <h6 class="mb-3 mt-4"><i class="fas fa-undo me-2"></i>Detail Retur</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nomor Retur</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Produk</th>
                                <th class="text-end">Qty Retur</th>
                                <th class="text-end">Total Retur</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan->returPenjualans as $retur)
                            <tr>
                                <td><strong>{{ $retur->nomor_retur }}</strong></td>
                                <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $retur->jenis_retur === 'refund' ? 'bg-danger' : ($retur->jenis_retur === 'tukar_barang' ? 'bg-warning' : 'bg-info') }}">
                                        {{ $retur->jenis_retur === 'tukar_barang' ? 'Tukar Barang' : ($retur->jenis_retur === 'refund' ? 'Refund' : 'Kredit') }}
                                    </span>
                                </td>
                                <td>
                                    @if($retur->detailReturPenjualans && $retur->detailReturPenjualans->count() > 0)
                                        @foreach($retur->detailReturPenjualans as $detail)
                                            <div>{{ $detail->produk?->nama_produk ?? '-' }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($retur->detailReturPenjualans && $retur->detailReturPenjualans->count() > 0)
                                        @foreach($retur->detailReturPenjualans as $detail)
                                            <div>{{ (int)($detail->qty_retur ?? 0) }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($retur->total_retur ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $retur->status === 'selesai' ? 'bg-success' : ($retur->status === 'lunas' ? 'bg-info' : 'bg-warning') }}">
                                        {{ ucfirst($retur->status ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <h6 class="mb-3 mt-4"><i class="fas fa-undo me-2"></i>Detail Retur</h6>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-info-circle me-2"></i>Belum ada retur untuk transaksi ini
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <a href="{{ route('transaksi.penjualan.show', $penjualan->id) }}" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i>Detail Lanjut
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Struk Modal -->
@foreach($penjualans as $penjualan)
<div class="modal fade" id="strukModal{{ $penjualan->id }}" tabindex="-1" aria-labelledby="strukModalLabel{{ $penjualan->id }}" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="strukModalLabel{{ $penjualan->id }}">
                    <i class="fas fa-print me-2"></i>Struk Penjualan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="strukContent{{ $penjualan->id }}">
                    <!-- Struk content akan dimuat via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat struk...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printStruk('strukContent{{ $penjualan->id }}')">
                    <i class="fas fa-print me-1"></i>Cetak
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Retur Detail Modal -->
@foreach($salesReturns as $retur)
<div class="modal fade" id="returDetailModal{{ $retur->id }}" tabindex="-1" aria-labelledby="returDetailModalLabel{{ $retur->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returDetailModalLabel{{ $retur->id }}">
                    <i class="fas fa-undo me-2"></i>Detail Retur Penjualan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informasi Retur -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nomor Retur:</strong> {{ $retur->nomor_retur ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Tanggal Retur:</strong> {{ optional($retur->tanggal)->format('d-m-Y') ?? '-' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nomor Penjualan:</strong> {{ $retur->penjualan?->nomor_penjualan ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Tanggal Penjualan:</strong> {{ optional($retur->penjualan?->tanggal)->format('d-m-Y') ?? '-' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <span class="badge {{ $retur->status === 'selesai' ? 'bg-success' : ($retur->status === 'lunas' ? 'bg-info' : 'bg-warning') }}">
                            {{ ucfirst($retur->status ?? '-') }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Nilai Retur:</strong>
                        <span class="text-danger fw-semibold">Rp {{ number_format($retur->total_retur ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Keterangan:</strong> {{ $retur->keterangan ?? '-' }}
                    </div>
                </div>
                
                <h6 class="mb-3"><i class="fas fa-box me-2"></i>Detail Produk Diretur</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Qty Diretur</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($retur->detailReturPenjualans->count() > 0)
                                @foreach($retur->detailReturPenjualans as $item)
                                    <tr>
                                        <td>{{ $item->produk?->nama_produk ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($item->qty_retur ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->harga_barang ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format(($item->qty_retur ?? 0) * ($item->harga_barang ?? 0), 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada detail produk</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
function loadStruk(penjualanId) {
    const strukContent = document.getElementById('strukContent' + penjualanId);
    
    fetch(`/transaksi/penjualan/${penjualanId}/struk`)
        .then(response => response.text())
        .then(html => {
            strukContent.innerHTML = html;
        })
        .catch(error => {
            strukContent.innerHTML = '<div class="alert alert-danger">Gagal memuat struk</div>';
        });
}

function printStruk(elementId) {
    const printContent = document.getElementById(elementId);
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Struk Penjualan</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                ${printContent.innerHTML}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Load struk saat modal dibuka
document.addEventListener('DOMContentLoaded', function() {
    @foreach($penjualans as $penjualan)
    const strukModal{{ $penjualan->id }} = document.getElementById('strukModal{{ $penjualan->id }}');
    if(strukModal{{ $penjualan->id }}) {
        strukModal{{ $penjualan->id }}.addEventListener('shown.bs.modal', function () {
            loadStruk({{ $penjualan->id }});
        });
    }
    @endforeach
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Fix modal backdrop issues
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            // Find the active modal and close it
            var activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                var modal = bootstrap.Modal.getInstance(activeModal);
                if (modal) {
                    modal.hide();
                }
            }
        }
    });
    
    // Ensure modals are properly initialized
    var modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(function(modalEl) {
        modalEl.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
        });
        
        modalEl.addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = 'auto';
        });
    });
});

function showTab(tabId, buttonElement) {
    // Hide all tabs
    var tabs = document.querySelectorAll('.tab-pane');
    tabs.forEach(function(tab) {
        tab.classList.remove('show', 'active');
    });
    
    // Remove active class from all buttons
    var buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabId).classList.add('show', 'active');
    
    // Add active class to clicked button
    buttonElement.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var urlParams = new URLSearchParams(window.location.search);
    var requestedTab = urlParams.get('tab');

    if (requestedTab === 'retur') {
        var returButton = document.querySelector('.tab-btn[onclick*="retur-list"]');
        if (returButton) {
            showTab('retur-list', returButton);
        }
    }
});

// Handle upload bukti pembayaran form in modal
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for all upload forms in modals
    document.querySelectorAll('[id^="uploadBuktiFormModal"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formId = this.id;
            const penjualanId = formId.replace('uploadBuktiFormModal', '');
            const fileInput = document.getElementById('bukti_file_modal' + penjualanId);
            const file = fileInput.files[0];
            const fileError = document.getElementById('file-error-modal' + penjualanId);
            
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
            
            fetch(`/transaksi/penjualan/${penjualanId}/bukti-pembayaran`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.log('Error response body:', text);
                        throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                    });
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
    });
});

// Function to show image modal preview
function showImageModalPreview(imageSrc, title) {
    // Create or update image preview modal
    let modal = document.getElementById('imagePreviewModalIndex');
    if (!modal) {
        // Create modal if it doesn't exist
        const modalHtml = `
            <div class="modal fade" id="imagePreviewModalIndex" tabindex="-1" aria-labelledby="imagePreviewModalIndexLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imagePreviewModalIndexLabel">Preview Bukti Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="previewImageIndex" src="" class="img-fluid" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('imagePreviewModalIndex');
    }
    
    document.getElementById('previewImageIndex').src = imageSrc;
    document.getElementById('imagePreviewModalIndexLabel').textContent = title;
    const imageModal = new bootstrap.Modal(modal);
    imageModal.show();
}

// Function to delete bukti pembayaran in modal
function deleteBuktiModal(buktiId, penjualanId) {
    if (confirm('Yakin ingin menghapus bukti pembayaran ini?')) {
        fetch(`/transaksi/penjualan/${penjualanId}/bukti-pembayaran/${buktiId}`, {
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

// Function to confirm delete penjualan
function confirmDelete(penjualanId) {
    if (confirm('Yakin ingin hapus transaksi ini?')) {
        document.getElementById('deleteForm' + penjualanId).submit();
    }
}

// Function to confirm delete retur
function confirmDeleteRetur(returId) {
    if (confirm('Yakin ingin hapus retur ini?')) {
        document.getElementById('deleteReturForm' + returId).submit();
    }
}
</script>

<style>
.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
}
</style>

<!-- ================================================================
     MODAL PENGATURAN PENJUALAN
     ================================================================ -->
<div class="modal fade" id="modalPengaturanPenjualan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#8B7355; color:white;">
                <div>
                    <h5 class="modal-title mb-0"><i class="fas fa-cog me-2"></i>Pengaturan Penjualan</h5>
                    <small style="opacity:.8;">Kelola pengaturan ongkir per kilo dan paket menu untuk transaksi penjualan.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Tab Nav — Paket Menu kiri, Ongkir kanan -->
                <div class="px-4 pt-3" style="border-bottom:1px solid #e5e7eb;">
                    <button class="setting-tab-btn active me-3" onclick="switchSettingTab('paket', this)">
                        <i class="fas fa-box-open me-1"></i>Paket Menu
                    </button>
                    <button class="setting-tab-btn" onclick="switchSettingTab('ongkir', this)">
                        <i class="fas fa-truck me-1"></i>Ongkir (Per Kilo)
                    </button>
                </div>

                <!-- TAB PAKET MENU (default active) -->
                <div id="setting-tab-paket" class="setting-tab-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">Pengaturan Paket Menu</h6>
                            <small class="text-muted">Buat paket menu untuk memudahkan penjualan dan berikan harga spesial untuk paket.</small>
                        </div>
                        <button class="btn btn-sm text-white" style="background:#8B7355;" onclick="showPaketModal()">
                            <i class="fas fa-plus me-1"></i>Tambah Paket Menu
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Paket</th>
                                    <th>Isi Paket</th>
                                    <th class="text-end">Harga Normal</th>
                                    <th class="text-end">Harga Paket</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="paket-tbody">
                                <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <small class="text-info"><i class="fas fa-info-circle me-1"></i>Paket menu akan muncul saat input transaksi pada pilihan Produk / Paket.</small>
                    </div>
                </div>

                <!-- TAB ONGKIR — simple, clean -->
                <div id="setting-tab-ongkir" class="setting-tab-content p-4" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">Daftar Ongkir (Per Jarak)</h6>
                            <small class="text-muted">Atur tarif ongkir berdasarkan jarak tempuh (km).</small>
                        </div>
                        <button class="btn btn-sm text-white" style="background:#8B7355;" onclick="showOngkirModal()">
                            <i class="fas fa-plus me-1"></i>Tambah
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Jarak (km)</th>
                                    <th class="text-end">Harga Ongkir (Rp)</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ongkir-tbody">
                                <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Ongkir (nested) -->
<div class="modal fade" id="modalOngkirForm" tabindex="-1" style="z-index:1070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#8B7355;color:white;">
                <h6 class="modal-title mb-0" id="ongkir-modal-title">Tambah Range Ongkir</h6>
                <button type="button" class="btn-close btn-close-white" onclick="closeOngkirModal()"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ongkir-edit-id">
                <div class="mb-3">
                    <label class="form-label small">Jarak Min (km)</label>
                    <input type="number" id="ongkir-jarak-min" class="form-control form-control-sm" step="0.1" min="0" placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Jarak Max (km) <span class="text-muted small">(kosong = tak terbatas)</span></label>
                    <input type="number" id="ongkir-jarak-max" class="form-control form-control-sm" step="0.1" min="0" placeholder="∞">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Harga Ongkir (Rp)</label>
                    <input type="text" id="ongkir-harga" class="form-control form-control-sm" placeholder="0" 
                           oninput="formatCurrency(this)" onblur="formatCurrency(this)">
                </div>
                <div class="mb-1">
                    <label class="form-label small">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="ongkir-status" checked>
                        <label class="form-check-label" for="ongkir-status">
                            <span id="ongkir-status-label">Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-sm btn-secondary" onclick="closeOngkirModal()">Batal</button>
                <button class="btn btn-sm text-white" style="background:#8B7355;" onclick="saveOngkir()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Paket Menu (nested) -->
<div class="modal fade" id="modalPaketForm" tabindex="-1" style="z-index:1070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#8B7355;color:white;">
                <h6 class="modal-title mb-0" id="paket-modal-title">Tambah Paket Menu</h6>
                <button type="button" class="btn-close btn-close-white" onclick="closePaketModal()"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="paket-edit-id">
                <div class="mb-3">
                    <label class="form-label small">Nama Paket</label>
                    <input type="text" id="paket-nama" class="form-control form-control-sm" placeholder="Contoh: Paket Hemat 1">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Harga Paket (Rp)</label>
                    <input type="text" id="paket-harga" class="form-control form-control-sm" placeholder="0" 
                           oninput="formatCurrency(this)" onblur="formatCurrency(this)">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Isi Paket</label>
                    <div id="paket-items-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 w-100" onclick="addPaketItem()">
                        <i class="fas fa-plus me-1"></i>Tambah Produk
                    </button>
                </div>
                <div class="mb-1">
                    <label class="form-label small">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="paket-status" checked>
                        <label class="form-check-label" for="paket-status">
                            <span id="paket-status-label">Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-sm btn-secondary" onclick="closePaketModal()">Batal</button>
                <button class="btn btn-sm text-white" style="background:#8B7355;" onclick="savePaket()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<style>
.setting-tab-btn {
    background: none;
    border: none;
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #6c757d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -1px;
    transition: all 0.2s;
}
.setting-tab-btn.active { color: #8B7355; border-bottom-color: #8B7355; font-weight: 600; }
.setting-tab-btn:hover { color: #495057; }
</style>

<script>
let allProduks = [];
let ongkirModalInstance = null;

function openPenjualanSetting() {
    const modal = new bootstrap.Modal(document.getElementById('modalPengaturanPenjualan'));
    modal.show();
    
    // Only fetch if data not yet loaded (no background refresh)
    if (!allProduks || allProduks.length === 0) {
        loadSettingData().catch(error => {
            console.error('Failed to load initial data:', error);
        });
    }
    // If already loaded, just show cached data — no re-fetch
}

function switchSettingTab(tab, btn) {
    document.querySelectorAll('.setting-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.setting-tab-content').forEach(c => c.style.display = 'none');
    btn.classList.add('active');
    document.getElementById('setting-tab-' + tab).style.display = 'block';
    
    // Ensure data is loaded when switching to paket tab
    if (tab === 'paket' && (!allProduks || allProduks.length === 0)) {
        loadSettingData();
    }
}

function loadSettingData() {
    const url = '{{ route("transaksi.penjualan-setting.index") }}';
    
    return fetch(url)
        .then(r => {
            if (!r.ok) {
                throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            }
            return r.json();
        })
        .then(data => {
            if (!data.produks) {
                throw new Error('Data produk tidak ditemukan dalam response');
            }
            
            allProduks = data.produks;
            
            // Update any existing dropdowns immediately
            setTimeout(() => {
                updatePaketDropdowns();
            }, 100);
            
            if (data.ongkir_settings) {
                renderOngkirTable(data.ongkir_settings);
            }
            if (data.paket_menus) {
                renderPaketTable(data.paket_menus);
            }
            
            return data;
        })
        .catch(error => {
            console.error('Error in loadSettingData:', error);
            showSettingToast('danger', 'Gagal memuat data: ' + (error?.message || 'Unknown error'));
            return null; // jangan re-throw agar tidak crash caller
        });
}

// ── ONGKIR ────────────────────────────────────────────────────────
function renderOngkirTable(rows) {
    const tbody = document.getElementById('ongkir-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data range ongkir</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => `
        <tr>
            <td>${i+1}</td>
            <td>${jarakLabel(r)}</td>
            <td class="text-end">Rp ${parseInt(r.harga_ongkir).toLocaleString('id-ID')}</td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" ${r.status ? 'checked' : ''} 
                           onchange="toggleOngkirStatus(${r.id}, this.checked)">
                </div>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-warning me-1" onclick="editOngkir(${JSON.stringify(r).replace(/"/g,'&quot;')})">Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteOngkir(${r.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('');
}

function jarakLabel(r) {
    if (r.jarak_max === null) return `> ${r.jarak_min} km`;
    if (r.jarak_min == 0) return `0 - ${r.jarak_max} km`;
    return `${r.jarak_min} - ${r.jarak_max} km`;
}

function showOngkirModal() {
    document.getElementById('ongkir-modal-title').textContent = 'Tambah Range Ongkir';
    document.getElementById('ongkir-edit-id').value = '';
    ['ongkir-jarak-min','ongkir-jarak-max','ongkir-harga'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ongkir-status').checked = true;
    updateStatusLabel();
    ongkirModalInstance = new bootstrap.Modal(document.getElementById('modalOngkirForm'));
    ongkirModalInstance.show();
}

function closeOngkirModal() {
    if (ongkirModalInstance) ongkirModalInstance.hide();
}

function editOngkir(r) {
    document.getElementById('ongkir-modal-title').textContent = 'Edit Range Ongkir';
    document.getElementById('ongkir-edit-id').value = r.id;
    document.getElementById('ongkir-jarak-min').value = r.jarak_min;
    document.getElementById('ongkir-jarak-max').value = r.jarak_max ?? '';
    document.getElementById('ongkir-harga').value = parseInt(r.harga_ongkir).toLocaleString('id-ID');
    document.getElementById('ongkir-status').checked = r.status;
    updateStatusLabel();
    ongkirModalInstance = new bootstrap.Modal(document.getElementById('modalOngkirForm'));
    ongkirModalInstance.show();
}

function updateStatusLabel() {
    const checkbox = document.getElementById('ongkir-status');
    const label = document.getElementById('ongkir-status-label');
    label.textContent = checkbox.checked ? 'Aktif' : 'Nonaktif';
}

function formatCurrency(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
        input.value = value;
    }
}

function parseCurrency(value) {
    return parseInt(value.replace(/[^\d]/g, '')) || 0;
}

function toggleOngkirStatus(id, status) {
    // Get current data from the row to send complete payload
    fetch('{{ route("transaksi.penjualan-setting.index") }}')
        .then(r => r.json())
        .then(data => {
            const ongkir = data.ongkir_settings.find(o => o.id == id);
            if (ongkir) {
                const payload = {
                    jarak_min: ongkir.jarak_min,
                    jarak_max: ongkir.jarak_max,
                    harga_ongkir: ongkir.harga_ongkir,
                    status: status
                };
                ajaxRequest(`/transaksi/penjualan-setting/ongkir/${id}`, 'PUT', payload, () => {
                    showSettingToast('success', status ? 'Ongkir diaktifkan' : 'Ongkir dinonaktifkan');
                });
            }
        });
}

function updateOngkirRowOptimistic(id, data) {
    const tbody = document.getElementById('ongkir-tbody');
    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const editBtn = row.querySelector(`button[onclick*="editOngkir"]`);
        if (editBtn && editBtn.getAttribute('onclick').includes(`"id":${id}`)) {
            // Update the row content
            const cells = row.querySelectorAll('td');
            cells[1].textContent = jarakLabel(data);
            cells[2].textContent = `Rp ${parseInt(data.harga_ongkir).toLocaleString('id-ID')}`;
            const checkbox = cells[3].querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = data.status;
        }
    });
}

function addOngkirRowOptimistic(data) {
    const tbody = document.getElementById('ongkir-tbody');
    const rowCount = tbody.querySelectorAll('tr').length;
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${rowCount + 1}</td>
        <td>${jarakLabel(data)}</td>
        <td class="text-end">Rp ${parseInt(data.harga_ongkir).toLocaleString('id-ID')}</td>
        <td class="text-center">
            <div class="form-check form-switch d-flex justify-content-center">
                <input class="form-check-input" type="checkbox" ${data.status ? 'checked' : ''} disabled>
            </div>
        </td>
        <td class="text-center">
            <span class="text-muted small">Menyimpan...</span>
        </td>
    `;
    tbody.appendChild(newRow);
}

function saveOngkir() {
    const id = document.getElementById('ongkir-edit-id').value;
    const jarakMin = parseFloat(document.getElementById('ongkir-jarak-min').value) || 0;
    const jarakMax = document.getElementById('ongkir-jarak-max').value ? parseFloat(document.getElementById('ongkir-jarak-max').value) : null;
    const hargaOngkir = parseCurrency(document.getElementById('ongkir-harga').value);
    
    // Validation
    if (jarakMin < 0) {
        showSettingToast('danger', 'Jarak minimum tidak boleh negatif');
        return;
    }
    if (jarakMax !== null && jarakMax <= jarakMin) {
        showSettingToast('danger', 'Jarak maksimum harus lebih besar dari jarak minimum');
        return;
    }
    if (hargaOngkir <= 0) {
        showSettingToast('danger', 'Harga ongkir harus lebih dari 0');
        return;
    }
    
    const payload = {
        jarak_min: jarakMin,
        jarak_max: jarakMax,
        harga_ongkir: hargaOngkir,
        status: document.getElementById('ongkir-status').checked,
    };
    
    const url = id ? `/transaksi/penjualan-setting/ongkir/${id}` : '/transaksi/penjualan-setting/ongkir';
    
    // Optimasi: Update UI dulu, baru kirim request
    const newData = {
        id: id || Date.now(), // temporary ID for new items
        jarak_min: jarakMin,
        jarak_max: jarakMax,
        harga_ongkir: hargaOngkir,
        status: payload.status
    };
    
    // Close modal immediately
    closeOngkirModal();
    
    // Update table optimistically
    if (id) {
        // Update existing row
        updateOngkirRowOptimistic(id, newData);
    } else {
        // Add new row
        addOngkirRowOptimistic(newData);
    }
    
    // Send request in background
    ajaxRequest(url, id ? 'PUT' : 'POST', payload, () => {
        // Refresh data to get correct IDs and sync
        loadSettingData();
    });
}

function deleteOngkir(id) {
    if (!confirm('Hapus range ongkir ini?')) return;
    ajaxRequest(`/transaksi/penjualan-setting/ongkir/${id}`, 'DELETE', {}, () => loadSettingData());
}

// Event listener untuk switch status
document.addEventListener('DOMContentLoaded', function() {
    const statusSwitch = document.getElementById('ongkir-status');
    if (statusSwitch) {
        statusSwitch.addEventListener('change', updateStatusLabel);
    }
    
    const paketStatusSwitch = document.getElementById('paket-status');
    if (paketStatusSwitch) {
        paketStatusSwitch.addEventListener('change', updatePaketStatusLabel);
    }
});

// ── PAKET MENU ────────────────────────────────────────────────────
function renderPaketTable(rows) {
    const tbody = document.getElementById('paket-tbody');
    if (!tbody) {
        console.error('paket-tbody element not found');
        return;
    }
    
    if (!rows || !rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data paket menu</td></tr>';
        return;
    }
    
    tbody.innerHTML = rows.map((p, i) => `
        <tr>
            <td>${i+1}</td>
            <td><strong>${p.nama_paket}</strong></td>
            <td>${p.details.map(d => `<div>• ${d.produk?.nama_produk ?? '-'} (${d.jumlah} porsi)</div>`).join('')}</td>
            <td class="text-end text-decoration-line-through text-muted">Rp ${parseInt(p.harga_normal).toLocaleString('id-ID')}</td>
            <td class="text-end text-success fw-bold">Rp ${parseInt(p.harga_paket).toLocaleString('id-ID')}</td>
            <td class="text-end"><span class="badge bg-warning text-dark">${parseFloat(p.diskon_persen).toFixed(2)}%</span></td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" ${p.status === 'aktif' ? 'checked' : ''} 
                           onchange="togglePaketStatus(${p.id}, this.checked)">
                </div>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-warning me-1" onclick="editPaket(${JSON.stringify(p).replace(/"/g,'&quot;')})">Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deletePaket(${p.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('');
}

let paketModalInstance = null;

function showPaketModal() {
    try {
        // Check if modal element exists
        const modalElement = document.getElementById('modalPaketForm');
        if (!modalElement) {
            console.error('Modal element not found');
            alert('Modal tidak ditemukan');
            return;
        }
        
        // Setup modal content
        document.getElementById('paket-modal-title').textContent = 'Tambah Paket Menu';
        document.getElementById('paket-edit-id').value = '';
        document.getElementById('paket-nama').value = '';
        document.getElementById('paket-harga').value = '';
        document.getElementById('paket-status').checked = true;
        updatePaketStatusLabel();
        document.getElementById('paket-items-container').innerHTML = '';
        
        // Show modal immediately
        paketModalInstance = new bootstrap.Modal(modalElement);
        paketModalInstance.show();
        
        // Use cached data if available, otherwise load
        if (allProduks && allProduks.length > 0) {
            addPaketItem();
        } else {
            // Add placeholder first
            addPaketItem();
            
            // Load data quickly
            loadSettingData()
                .then(() => {
                    updatePaketDropdowns();
                })
                .catch(error => {
                    console.error('Failed to load data:', error);
                    // Show error in dropdown
                    document.querySelectorAll('.paket-produk-select').forEach(select => {
                        select.innerHTML = '<option value="">Error memuat produk</option>';
                        select.disabled = true;
                    });
                });
        }
        
    } catch (error) {
        console.error('Error in showPaketModal:', error);
        alert('Terjadi kesalahan: ' + error.message);
    }
}

function closePaketModal() {
    if (paketModalInstance) paketModalInstance.hide();
}

function updatePaketStatusLabel() {
    const checkbox = document.getElementById('paket-status');
    const label = document.getElementById('paket-status-label');
    label.textContent = checkbox.checked ? 'Aktif' : 'Nonaktif';
}

function togglePaketStatus(id, status) {
    // Get current data to send complete payload
    fetch('{{ route("transaksi.penjualan-setting.index") }}')
        .then(r => r.json())
        .then(data => {
            const paket = data.paket_menus.find(p => p.id == id);
            if (paket) {
                const payload = {
                    nama_paket: paket.nama_paket,
                    harga_paket: paket.harga_paket,
                    status: status ? 'aktif' : 'nonaktif',
                    items: paket.details.map(d => ({
                        produk_id: d.produk_id,
                        jumlah: d.jumlah
                    }))
                };
                ajaxRequest(`/transaksi/penjualan-setting/paket/${id}`, 'PUT', payload, () => {
                    showSettingToast('success', status ? 'Paket diaktifkan' : 'Paket dinonaktifkan');
                });
            }
        });
}

function addPaketItem(selectedProdukId = '', selectedJumlah = '') {
    const container = document.getElementById('paket-items-container');
    const itemIndex = container.children.length;
    
    // Check if products are available
    if (!allProduks || allProduks.length === 0) {
        // Add placeholder item that will be updated when data loads
        const itemHtml = `
            <div class="paket-item mb-2" data-index="${itemIndex}">
                <div class="mb-2">
                    <select class="form-select form-select-sm paket-produk-select" required disabled>
                        <option value="">Memuat produk...</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <input type="number" class="form-control form-control-sm paket-jumlah-input flex-grow-1" 
                           placeholder="Jumlah" min="0.01" step="0.01" value="${selectedJumlah}" required>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePaketItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
        return;
    }
    
    const itemHtml = `
        <div class="paket-item mb-2" data-index="${itemIndex}">
            <div class="mb-2">
                <select class="form-select form-select-sm paket-produk-select" required>
                    <option value="">Pilih Produk</option>
                    ${allProduks.map(p => `<option value="${p.id}" ${p.id == selectedProdukId ? 'selected' : ''}>${p.nama_produk}</option>`).join('')}
                </select>
            </div>
            <div class="d-flex gap-2">
                <input type="number" class="form-control form-control-sm paket-jumlah-input flex-grow-1" 
                       placeholder="Jumlah" min="0.01" step="0.01" value="${selectedJumlah}" required>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePaketItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
}

// Function to update existing dropdowns when data loads
function updatePaketDropdowns() {
    if (!allProduks || allProduks.length === 0) {
        return;
    }
    
    const dropdowns = document.querySelectorAll('.paket-produk-select');
    
    dropdowns.forEach((select) => {
        const selectedValue = select.value;
        const optionsHtml = `
            <option value="">Pilih Produk</option>
            ${allProduks.map(p => `<option value="${p.id}" ${p.id == selectedValue ? 'selected' : ''}>${p.nama_produk}</option>`).join('')}
        `;
        
        select.innerHTML = optionsHtml;
        select.disabled = false;
    });
}

function removePaketItem(btn) {
    btn.closest('.paket-item').remove();
}

function editPaket(p) {
    document.getElementById('paket-modal-title').textContent = 'Edit Paket Menu';
    document.getElementById('paket-edit-id').value = p.id;
    document.getElementById('paket-nama').value = p.nama_paket;
    document.getElementById('paket-harga').value = parseInt(p.harga_paket).toLocaleString('id-ID');
    document.getElementById('paket-status').checked = p.status === 'aktif';
    updatePaketStatusLabel();
    
    // Load existing items
    document.getElementById('paket-items-container').innerHTML = '';
    
    // Ensure products are loaded before adding items
    if (allProduks && allProduks.length > 0) {
        p.details.forEach(detail => {
            addPaketItem(detail.produk_id, detail.jumlah);
        });
    } else {
        // Load products first if not available
        loadSettingData().then(() => {
            p.details.forEach(detail => {
                addPaketItem(detail.produk_id, detail.jumlah);
            });
        });
    }
    
    paketModalInstance = new bootstrap.Modal(document.getElementById('modalPaketForm'));
    paketModalInstance.show();
}

function savePaket() {
    const id = document.getElementById('paket-edit-id').value;
    const namaPaket = document.getElementById('paket-nama').value.trim();
    const hargaPaket = parseCurrency(document.getElementById('paket-harga').value);
    const status = document.getElementById('paket-status').checked ? 'aktif' : 'nonaktif';
    
    // Validation
    if (!namaPaket) {
        showSettingToast('danger', 'Nama paket harus diisi');
        return;
    }
    if (hargaPaket <= 0) {
        showSettingToast('danger', 'Harga paket harus lebih dari 0');
        return;
    }
    
    // Get items
    const items = [];
    document.querySelectorAll('.paket-item').forEach(item => {
        const produkId = item.querySelector('.paket-produk-select').value;
        const jumlah = parseFloat(item.querySelector('.paket-jumlah-input').value) || 0;
        if (produkId && jumlah > 0) {
            items.push({ produk_id: produkId, jumlah: jumlah });
        }
    });
    
    if (items.length === 0) {
        showSettingToast('danger', 'Minimal harus ada 1 produk dalam paket');
        return;
    }
    
    const payload = {
        nama_paket: namaPaket,
        harga_paket: hargaPaket,
        status: status,
        items: items
    };
    
    const url = id ? `/transaksi/penjualan-setting/paket/${id}` : '/transaksi/penjualan-setting/paket';
    
    // Close modal immediately for better UX
    closePaketModal();
    
    ajaxRequest(url, id ? 'PUT' : 'POST', payload, (response) => {
        showSettingToast('success', response.message || 'Paket menu berhasil disimpan');
        
        // Force reload data and ensure table refresh
        loadSettingData().then((data) => {
            // Force re-render the table with fresh data
            if (data.paket_menus) {
                renderPaketTable(data.paket_menus);
            }
        }).catch(error => {
            console.error('Error reloading data after save:', error);
            showSettingToast('warning', 'Data berhasil disimpan, silakan refresh halaman untuk melihat perubahan');
        });
    });
}

function deletePaket(id) {
    if (!confirm('Hapus paket menu ini?')) return;
    ajaxRequest(`/transaksi/penjualan-setting/paket/${id}`, 'DELETE', {}, () => loadSettingData());
}

// ── Helpers ───────────────────────────────────────────────────────
function fmt(n) { return Number(n).toLocaleString('id-ID'); }

function ajaxRequest(url, method, data, onSuccess) {
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: method !== 'DELETE' ? JSON.stringify(data) : undefined,
    })
    .then(r => {
        if (!r.ok) {
            throw new Error(`HTTP ${r.status}: ${r.statusText}`);
        }
        return r.json();
    })
    .then(res => {
        if (res.success) { 
            showSettingToast('success', res.message); 
            onSuccess(); 
        }
        else showSettingToast('danger', res.message || 'Terjadi kesalahan');
    })
    .catch(error => {
        console.error('Ajax error:', error);
        showSettingToast('danger', 'Terjadi kesalahan jaringan: ' + (error?.message || 'Unknown error'));
    });
}

function showSettingToast(type, msg) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    t.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:280px;';
    t.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
</script>
@endsection