@extends('layouts.app')

@section('title', 'Dashboard Laporan Penjualan')

@push('styles')
<style>
/* Summary Cards Style - Lebih kecil dan rapi */
.summary-card {
    background: white;
    border: none;
    border-radius: 6px;
    padding: 0.8rem;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    min-height: 100px;
}

.summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-color);
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.summary-card .icon {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.4rem;
    font-size: 0.9rem;
    color: white;
    background: var(--card-color);
}

.summary-card .value {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
    color: #1a202c;
    line-height: 1.2;
}
.summary-card .label {
    font-size: 0.65rem;
    color: #4a5568;
    margin-bottom: 0.1rem;
    font-weight: 600;
    line-height: 1.2;
}

.summary-card .subtitle {
    font-size: 0.6rem;
    color: #718096;
    font-weight: 500;
}

/* Card Colors */
.card-green { --card-color: #10b981; }
.card-blue { --card-color: #3b82f6; }
.card-purple { --card-color: #8b5cf6; }
.card-orange { --card-color: #f59e0b; }
.card-red { --card-color: #ef4444; }
.card-teal { --card-color: #14b8a6; }
.card-yellow { --card-color: #eab308; }

/* Filter Section - Card Style seperti Laporan Pembelian */
.filter-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.filter-section .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.filter-section .form-control,
.filter-section .form-select {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 0.5rem 0.75rem;
    transition: all 0.2s ease;
}

.filter-section .form-control:focus,
.filter-section .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Chart Container - Card Style dengan ukuran yang tepat */
.chart-container {
    height: 250px;
}

.chart-container canvas {
    max-height: 200px !important;
    width: 100% !important;
}

/* Table Styles - Card Style seperti Laporan Pembelian */
.table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}
.table thead th {
    background: #f9fafb;
    border: none;
    font-weight: 700;
    color: #374151;
    padding: 1.25rem 1rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table tbody td {
    padding: 1.25rem 1rem;
    border-color: #f3f4f6;
    vertical-align: middle;
    font-weight: 500;
}

.table tbody tr:hover {
    background: #f9fafb;
}

/* Status Badges */
.status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-lunas {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.payment-tunai {
    background: #dbeafe;
    color: #1e40af;
}

.payment-transfer {
    background: #e0e7ff;
    color: #5b21b6;
}

.payment-credit {
    background: #fed7d7;
    color: #991b1b;
}

/* Export Button - Warna coklat sesuai tema */
.btn-export {
    background: #8B7355;
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(139, 115, 85, 0.2);
}

.btn-export:hover {
    background: #7a6348;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(139, 115, 85, 0.3);
    color: white;
}

/* Tabs - Style seperti di gambar dengan garis bawah */
.nav-tabs {
    border: none;
    margin-bottom: 1.5rem;
    background: transparent;
    border-bottom: 1px solid #e5e7eb;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 1rem 2rem;
    border-radius: 0;
    transition: all 0.3s ease;
    margin-right: 0;
    background: transparent;
    border-bottom: 3px solid transparent;
    margin-bottom: -1px;
}

.nav-tabs .nav-link:hover {
    border-color: transparent transparent #d1d5db transparent;
    color: #495057;
    background: transparent;
}

.nav-tabs .nav-link.active {
    color: #8B7355;
    background: transparent;
    border-color: transparent transparent #8B7355 transparent;
    font-weight: 600;
}

/* Card styling untuk grafik */
.card .chart-container {
    height: 200px;
    position: relative;
}

.card .chart-container canvas {
    max-height: 180px !important;
    width: 100% !important;
}

.rekap-table {
    margin: 0;
}

.rekap-table td {
    padding: 0.7rem 0;
    border: none;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.8rem;
}

.rekap-table td:first-child {
    color: #6b7280;
    font-weight: 600;
}

.rekap-table td:last-child {
    font-weight: 700;
    color: #1f2937;
}

.rekap-table .highlight {
    background: #f0fdf4;
    padding: 0.7rem;
    border-radius: 6px;
    border: 2px solid #bbf7d0;
}

.rekap-table .highlight td {
    border: none;
    color: #065f46;
    font-weight: 800;
    font-size: 0.8rem;
}
</style>
@endpush
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0" style="font-weight: 600; color: #1f2937; font-size: 1.5rem;">Laporan Penjualan</h2>
        </div>
        <button class="btn btn-export">
            <i class="fas fa-file-pdf me-2"></i>Export PDF
        </button>
    </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="laporanTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="penjualan-tab" data-bs-toggle="tab" data-bs-target="#penjualan" type="button" role="tab" style="font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-shopping-cart me-2"></i>Laporan Penjualan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="retur-tab" data-bs-toggle="tab" data-bs-target="#retur" type="button" role="tab" style="font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-undo me-2"></i>Laporan Retur
                </button>
            </li>
        </ul>

        <div class="tab-content" id="laporanTabsContent">
            <!-- TAB PENJUALAN -->
            <div class="tab-pane fade show active" id="penjualan" role="tabpanel">
                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3" style="color: #1f2937; font-weight: 600; font-size: 0.95rem;">
                            <i class="fas fa-filter me-2"></i>Filter Laporan
                        </h6>
                        <form method="GET" action="{{ route('laporan.penjualan') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" 
                                       value="{{ request('tanggal_mulai', date('Y-m-01')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" 
                                       value="{{ request('tanggal_selesai', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Periode</label>
                                <select name="periode" class="form-select">
                                    <option value="harian" {{ request('periode') == 'harian' ? 'selected' : '' }}>Harian</option>
                                    <option value="mingguan" {{ request('periode') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="bulanan" {{ request('periode') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="form-select">
                                    <option value="">Semua Metode</option>
                                    <option value="cash" {{ request('metode_pembayaran') == 'cash' ? 'selected' : '' }}>Tunai</option>
                                    <option value="transfer" {{ request('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="credit" {{ request('metode_pembayaran') == 'credit' ? 'selected' : '' }}>Kredit</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" style="border-radius: 6px; font-weight: 500; padding: 0.5rem;">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="summary-card card-green">
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="value">Rp {{ number_format($summaryData['total_penjualan_produk'] ?? 4872900, 0, ',', '.') }}</div>
                            <div class="label">Total Penjualan Produk</div>
                            <div class="subtitle">Dari {{ $summaryData['total_transaksi'] ?? 24 }} transaksi</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="summary-card card-blue">
                            <div class="icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <div class="value">Rp {{ number_format($summaryData['total_ongkir'] ?? 350000, 0, ',', '.') }}</div>
                            <div class="label">Total Ongkir</div>
                            <div class="subtitle">Dari {{ $summaryData['total_transaksi'] ?? 24 }} transaksi</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="summary-card card-purple">
                            <div class="icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="value">Rp {{ number_format($summaryData['total_ppn'] ?? 536019, 0, ',', '.') }}</div>
                            <div class="label">Total PPN (11%)</div>
                            <div class="subtitle">Dari {{ $summaryData['total_transaksi'] ?? 24 }} transaksi</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="summary-card card-orange">
                            <div class="icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="value">Rp {{ number_format($summaryData['total_pendapatan_kotor'] ?? 5758919, 0, ',', '.') }}</div>
                            <div class="label">Total Pendapatan Kotor</div>
                            <div class="subtitle">Penjualan + Ongkir + PPN</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="summary-card card-red">
                            <div class="icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="value">Rp {{ number_format($summaryData['total_diskon'] ?? 0, 0, ',', '.') }}</div>
                            <div class="label">Total Diskon</div>
                            <div class="subtitle">Dari {{ $summaryData['total_transaksi'] ?? 24 }} transaksi</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="summary-card card-teal">
                            <div class="icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="value">{{ $summaryData['total_transaksi'] ?? 24 }}</div>
                            <div class="label">Total Transaksi</div>
                            <div class="subtitle">Periode ini</div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Bulanan dan Grafik -->
                <div class="row g-2 mb-3">
                    <!-- Rekap Bulanan -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>
                                    <i class="fas fa-calendar-alt me-2"></i>Rekap Bulanan ({{ date('F Y') }})
                                </h6>
                                <table class="table rekap-table">
                                    <tbody>
                                        <tr>
                                            <td>Total Penjualan Produk</td>
                                            <td class="text-end">Rp {{ number_format($summaryData['total_penjualan_produk'] ?? 4872900, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Pendapatan Ongkir</td>
                                            <td class="text-end">Rp {{ number_format($summaryData['total_ongkir'] ?? 350000, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total PPN (11%)</td>
                                            <td class="text-end">Rp {{ number_format($summaryData['total_ppn'] ?? 536019, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Pendapatan Kotor</td>
                                            <td class="text-end">Rp {{ number_format($summaryData['total_pendapatan_kotor'] ?? 5758919, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Diskon</td>
                                            <td class="text-end">Rp {{ number_format($summaryData['total_diskon'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="highlight">
                                            <td><strong>Total Pendapatan Bersih</strong></td>
                                            <td class="text-end"><strong>Rp {{ number_format($summaryData['total_pendapatan_bersih'] ?? 5758919, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Penjualan -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3" style="color: #1f2937; font-weight: 600; font-size: 0.9rem;">
                                    <i class="fas fa-chart-area me-2"></i>Grafik Penjualan ({{ date('F Y') }})
                                </h6>
                                <div class="chart-container">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Detail Transaksi Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0" style="color: #1f2937; font-weight: 600; font-size: 0.95rem;">
                                <i class="fas fa-list me-2"></i>Detail Transaksi Penjualan
                            </h6>
                        </div>
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Pembayaran</th>
                                    <th>Produk</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Harga/Satuan</th>
                                    <th class="text-end">HPP</th>
                                    <th class="text-end">Profit</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-end">Total Akhir</th>
                                    <th>Qty Retur</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penjualans ?? [] as $key => $penjualan)
                                <tr>
                                    <td>{{ $penjualans->firstItem() + $key }}</td>
                                    <td><strong>{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                                    <td>{{ optional($penjualan->tanggal_transaksi)->format('d-m-Y H:i') ?? optional($penjualan->tanggal)->format('d-m-Y') ?? '-' }}</td>
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
                                            @foreach($penjualan->details as $index => $d)
                                                @if($index > 0)<br>@endif
                                                <div>{{ $d->produk->nama_produk ?? '-' }}</div>
                                            @endforeach
                                        @elseif($detailCount === 1)
                                            {{ $penjualan->details[0]->produk->nama_produk ?? '-' }}
                                        @else
                                            {{ $penjualan->produk?->nama_produk ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($detailCount > 1)
                                            @foreach($penjualan->details as $d)
                                                <div>{{ rtrim(rtrim(number_format($d->jumlah,2,',','.'),'0'),',') }}</div>
                                            @endforeach
                                        @elseif($detailCount === 1)
                                            {{ rtrim(rtrim(number_format($penjualan->details[0]->jumlah,2,',','.'),'0'),',') }}
                                        @else
                                            {{ rtrim(rtrim(number_format($penjualan->jumlah ?? 0,2,',','.'),'0'),',') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($detailCount > 1)
                                            @foreach($penjualan->details as $d)
                                                <div>Rp {{ number_format($d->harga_satuan ?? 0, 0, ',', '.') }}</div>
                                            @endforeach
                                        @elseif($detailCount === 1)
                                            Rp {{ number_format($penjualan->details[0]->harga_satuan ?? 0, 0, ',', '.') }}
                                        @else
                                            @php
                                                $hdrHarga = $penjualan->harga_satuan;
                                                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                }
                                            @endphp
                                            Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($detailCount > 1)
                                            @foreach($penjualan->details as $d)
                                                @php
                                                    try {
                                                        $hpp = $d->produk ? $d->produk->getHPPForSaleDate($penjualan->tanggal) : 0;
                                                    } catch (\Exception $e) {
                                                        $hpp = 0;
                                                    }
                                                @endphp
                                                <div>Rp {{ number_format($hpp, 0, ',', '.') }}</div>
                                            @endforeach
                                        @elseif($detailCount === 1)
                                            @php
                                                try {
                                                    $hpp = $penjualan->details[0]->produk ? $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal) : 0;
                                                } catch (\Exception $e) {
                                                    $hpp = 0;
                                                }
                                            @endphp
                                            Rp {{ number_format($hpp, 0, ',', '.') }}
                                        @else
                                            @php
                                                $hdrHarga = $penjualan->harga_satuan;
                                                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                }
                                            @endphp
                                            Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($detailCount > 1)
                                            @foreach($penjualan->details as $d)
                                                @php 
                                                    try {
                                                        $actualHPP = $d->produk ? $d->produk->getHPPForSaleDate($penjualan->tanggal) : 0;
                                                    } catch (\Exception $e) {
                                                        $actualHPP = 0;
                                                    }
                                                    $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah; 
                                                @endphp
                                                <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                            @endforeach
                                        @elseif($detailCount === 1)
                                            @php 
                                                try {
                                                    $actualHPP = $penjualan->details[0]->produk ? $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal) : 0;
                                                } catch (\Exception $e) {
                                                    $actualHPP = 0;
                                                }
                                                $margin = ($penjualan->details[0]->harga_satuan - $actualHPP) * $penjualan->details[0]->jumlah; 
                                            @endphp
                                            <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                        @else
                                            @php
                                                $hdrHarga = $penjualan->harga_satuan;
                                                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                }
                                                try {
                                                    $actualHPP = $penjualan->produk ? $penjualan->produk->getHPPForSaleDate($penjualan->tanggal) : 0;
                                                } catch (\Exception $e) {
                                                    $actualHPP = 0;
                                                }
                                                $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                                            @endphp
                                            <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($detailCount > 1)
                                            @foreach($penjualan->details as $d)
                                                @php 
                                                    $sub = (float)$d->jumlah * (float)$d->harga_satuan; 
                                                    $disc = (float)($d->diskon_nominal ?? 0); 
                                                    $pct = $sub > 0 ? ($disc / $sub * 100) : 0; 
                                                @endphp
                                                <div>{{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})</div>
                                            @endforeach
                                        @elseif($detailCount === 1)
                                            @php 
                                                $d = $penjualan->details[0]; 
                                                $sub = (float)$d->jumlah * (float)$d->harga_satuan; 
                                                $disc = (float)($d->diskon_nominal ?? 0); 
                                                $pct = $sub > 0 ? ($disc / $sub * 100) : 0; 
                                            @endphp
                                            {{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})
                                        @else
                                            @php 
                                                $pct = 0; 
                                                $disc = (float)($penjualan->diskon_nominal ?? 0); 
                                                if (($penjualan->jumlah ?? 0) > 0) { 
                                                    $hdrHarga = $penjualan->harga_satuan; 
                                                    if (is_null($hdrHarga)) { 
                                                        $hdrHarga = ((float)$penjualan->total + $disc) / (float)$penjualan->jumlah; 
                                                    } 
                                                    $subtotal = $penjualan->jumlah * $hdrHarga; 
                                                    $pct = $subtotal > 0 ? ($disc / $subtotal * 100) : 0; 
                                                } 
                                            @endphp
                                            {{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold"><strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @php
                                            $totalQtyRetur = $penjualan->total_qty_retur ?? 0;
                                        @endphp
                                        @if($totalQtyRetur > 0)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-undo me-1"></i>{{ (int)$totalQtyRetur }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>0
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" style="border-radius: 8px;" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block" style="color: #d1d5db;"></i>
                                        <h6>Tidak ada data transaksi</h6>
                                        <p class="mb-0">Silakan ubah filter untuk melihat data lainnya</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                    @if($penjualans->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    <strong>Menampilkan {{ $penjualans->firstItem() ?? 1 }} - {{ $penjualans->lastItem() ?? $penjualans->count() }} dari {{ $penjualans->total() ?? $penjualans->count() }} transaksi</strong>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    {{ $penjualans->appends(request()->query())->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB RETUR -->
            <div class="tab-pane fade" id="retur" role="tabpanel">
                <!-- Filter Section Retur -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3" style="color: #1f2937; font-weight: 600; font-size: 0.95rem;">
                            <i class="fas fa-filter me-2"></i>Filter Laporan Retur
                        </h6>
                        <form method="GET" action="{{ route('laporan.penjualan') }}" class="row g-3">
                            <input type="hidden" name="tab" value="retur">
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai_retur" class="form-control" 
                                       value="{{ request('tanggal_mulai_retur', date('Y-m-01')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai_retur" class="form-control" 
                                       value="{{ request('tanggal_selesai_retur', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jenis Retur</label>
                                <select name="jenis_retur" class="form-select">
                                    <option value="">Semua Jenis</option>
                                    <option value="refund">Refund</option>
                                    <option value="tukar_barang">Tukar Barang</option>
                                    <option value="kredit">Kredit</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status Retur</label>
                                <select name="status_retur" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="completed">Selesai</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Periode</label>
                                <select name="periode_retur" class="form-select">
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan" selected>Bulanan</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" style="border-radius: 6px; font-weight: 500; padding: 0.5rem;">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards Retur -->
                <div class="row g-2 mb-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-card card-red">
                            <div class="icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="value">Rp {{ number_format($returData['total_nilai_retur'] ?? 0, 0, ',', '.') }}</div>
                            <div class="label">Total Nilai Retur</div>
                            <div class="subtitle">Dari {{ $returData['total_retur'] ?? 0 }} retur</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-card card-orange">
                            <div class="icon">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="value">{{ $returData['total_retur'] ?? 0 }}</div>
                            <div class="label">Total Retur</div>
                            <div class="subtitle">Periode ini</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-card card-yellow">
                            <div class="icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="value">{{ $returData['total_tukar_barang'] ?? 0 }}</div>
                            <div class="label">Tukar Barang</div>
                            <div class="subtitle">Retur tukar</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-card card-blue">
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="value">{{ $returData['total_refund'] ?? 0 }}</div>
                            <div class="label">Refund</div>
                            <div class="subtitle">Pengembalian uang</div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Retur dan Grafik -->
                <div class="row g-2 mb-3">
                    <!-- Rekap Retur -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>
                                    <i class="fas fa-undo me-2"></i>Rekap Retur ({{ date('F Y') }})
                                </h6>
                                <table class="table rekap-table">
                                    <tbody>
                                        <tr>
                                            <td>Total Retur Refund</td>
                                            <td class="text-end">{{ $returData['total_refund'] ?? 0 }} transaksi</td>
                                        </tr>
                                        <tr>
                                            <td>Total Retur Tukar Barang</td>
                                            <td class="text-end">{{ $returData['total_tukar_barang'] ?? 0 }} transaksi</td>
                                        </tr>
                                        <tr>
                                            <td>Total Retur Kredit</td>
                                            <td class="text-end">{{ ($returData['retur_list'] ?? collect([]))->where('jenis_retur', 'kredit')->count() }} transaksi</td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah Transaksi Retur</td>
                                            <td class="text-end">{{ $returData['total_retur'] ?? 0 }}</td>
                                        </tr>
                                        <tr class="highlight">
                                            <td><strong>Total Nilai Retur</strong></td>
                                            <td class="text-end"><strong>Rp {{ number_format($returData['total_nilai_retur'] ?? 0, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Retur -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3" style="color: #1f2937; font-weight: 600; font-size: 0.9rem;">
                                    <i class="fas fa-chart-area me-2"></i>Grafik Retur ({{ date('F Y') }})
                                </h6>
                                <div class="chart-container">
                                    <canvas id="returChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Retur Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0" style="color: #1f2937; font-weight: 600; font-size: 0.95rem;">
                                <i class="fas fa-list me-2"></i>Detail Transaksi Retur
                            </h6>
                        </div>
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>No. Retur</th>
                                    <th>Tanggal</th>
                                    <th>No. Penjualan</th>
                                    <th>Deskripsi</th>
                                    <th>Kompensasi</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Retur</th>
                                    <th>Produk</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($returData['retur_list'] ?? collect([])) as $key => $retur)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong>{{ $retur->nomor_retur ?? '-' }}</strong></td>
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
                                        @if($retur->detailReturPenjualans && $retur->detailReturPenjualans->count() > 0)
                                            @foreach($retur->detailReturPenjualans as $item)
                                                <div>{{ $item->produk?->nama_produk ?? '-' }} ({{ rtrim(rtrim(number_format($item->qty_retur ?? 0, 2, ',', '.'), '0'), ',') }})</div>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" style="border-radius: 8px;" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block" style="color: #d1d5db;"></i>
                                        <h6>Tidak ada data retur</h6>
                                        <p class="mb-0">Silakan ubah filter untuk melihat data lainnya</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <strong>Menampilkan {{ ($returData['retur_list'] ?? collect([]))->count() }} dari {{ ($returData['retur_list'] ?? collect([]))->count() }} retur</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'],
            datasets: [{
                label: 'Penjualan Produk',
                data: [200000, 180000, 220000, 250000, 300000, 280000, 320000, 350000, 380000, 400000, 420000, 380000, 360000, 340000, 380000, 400000, 420000, 380000, 360000, 340000, 380000, 400000, 420000, 380000, 360000, 340000, 380000, 400000, 420000, 380000],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
            }, {
                label: 'Ongkir',
                data: [15000, 12000, 18000, 20000, 25000, 22000, 28000, 30000, 32000, 35000, 38000, 32000, 30000, 28000, 32000, 35000, 38000, 32000, 30000, 28000, 32000, 35000, 38000, 32000, 30000, 28000, 32000, 35000, 38000, 32000],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: false,
                borderWidth: 3
            }, {
                label: 'PPN',
                data: [22000, 19800, 24200, 27500, 33000, 30800, 35200, 38500, 41800, 44000, 46200, 41800, 39600, 37400, 41800, 44000, 46200, 41800, 39600, 37400, 41800, 44000, 46200, 41800, 39600, 37400, 41800, 44000, 46200, 41800],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: false,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 10,
                        font: {
                            size: 10,
                            weight: 600
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp' + (value/1000) + 'k';
                        },
                        font: {
                            size: 9,
                            weight: 500
                        }
                    }
                },
                x: {
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        font: {
                            size: 9,
                            weight: 500
                        }
                    }
                }
            }
        }
    });

    // Retur Chart
    const returCtx = document.getElementById('returChart').getContext('2d');
    const returChart = new Chart(returCtx, {
        type: 'bar',
        data: {
            labels: ['Refund', 'Tukar Barang', 'Kredit'],
            datasets: [{
                label: 'Jumlah Retur',
                data: [
                    {{ $returData['total_refund'] ?? 0 }}, 
                    {{ $returData['total_tukar_barang'] ?? 0 }}, 
                    {{ ($returData['retur_list'] ?? collect([]))->where('jenis_retur', 'kredit')->count() }}
                ],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(59, 130, 246, 0.8)'
                ],
                borderColor: [
                    '#ef4444',
                    '#f59e0b',
                    '#3b82f6'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 9,
                            weight: 500
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 9,
                            weight: 600
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection