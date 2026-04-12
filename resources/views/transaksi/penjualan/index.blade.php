@extends('layouts.app')

@push('styles')
<style>
/* Horizontal Tabs Style - Mengikuti gaya Satuan | Konversi */
.horizontal-tabs {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #f0e6dc;
    padding-bottom: 0;
    background: linear-gradient(to bottom, #faf8f6, transparent);
    padding: 1rem 0 0;
    margin: -1rem -1rem 1.5rem -1rem;
    padding-left: 1rem;
    padding-right: 1rem;
    position: relative;
    z-index: 10;
}

.tab-btn {
    background: none;
    border: none;
    padding: 0.75rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 500;
    color: #8B7355;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    border-radius: 8px 8px 0 0;
    position: relative;
    z-index: 11;
    pointer-events: all;
}

.tab-btn:hover {
    color: #7a6348;
    background: rgba(139, 115, 85, 0.05);
    transform: translateY(-1px);
}

.tab-btn.active {
    color: #8B7355;
    font-weight: 600;
    border-bottom-color: #8B7355;
    background: rgba(139, 115, 85, 0.08);
}

.tab-separator {
    color: #d4c4b0;
    font-size: 1.2rem;
    margin: 0 0.75rem;
    user-select: none;
    font-weight: 300;
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

/* Action Layout Style - 2 Baris Grid di Tengah */
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

.action-left {
    display: flex;
    align-items: center;
}

.action-right {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Data Penjualan
        </h2>
        <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Penjualan
        </a>
    </div>

    <!-- Ringkasan Penjualan Harian -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>Ringkasan Penjualan Harian
                <small class="text-white ms-2">(Hari Ini: {{ now()->format('d/m/Y') }})</small>
            </h6>
        </div>
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">Total Penjualan</div>
                        <div class="summary-value text-primary">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">Jumlah Transaksi</div>
                        <div class="summary-value text-info">{{ number_format($jumlahTransaksiHariIni, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">Produk Terjual</div>
                        <div class="summary-value text-warning">{{ number_format($totalProdukTerjual, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="summary-label">Total Profit</div>
                        <div class="summary-value {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.penjualan.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nomor Transaksi</label>
                        <input type="text" name="nomor_transaksi" class="form-control" 
                               value="{{ request('nomor_transaksi') }}" placeholder="Cari nomor transaksi...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Custom Tabs Navigation -->
            <div class="horizontal-tabs">
                <button class="tab-btn active" onclick="showTab('penjualan-list', this)">
                    <i class="fas fa-shopping-cart me-2"></i>Penjualan
                </button>
                <span class="tab-separator">|</span>
                <button class="tab-btn" onclick="showTab('retur-list', this)">
                    <i class="fas fa-undo me-2"></i>Retur
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
                            <th class="text-center" style="width: 50px">#</th>
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
                                <td>{{ optional($penjualan->tanggal)->format('d-m-Y H:i') ?? $penjualan->tanggal }}</td>
                                <td>
                                    <span class="badge {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                        {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}
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
                                            @if($loop->first)
                                                {{ $d->produk->nama_produk }} - {{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}
                                            @else
                                                &nbsp;&nbsp;{{ $d->produk->nama_produk }} - {{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}
                                            @endif
                                        @endforeach
                                    @elseif($detailCount === 1)
                                        {{ $penjualan->details[0]->produk->nama_produk }} - {{ rtrim(rtrim(number_format($penjualan->details[0]->jumlah,4,',','.'),'0'),',') }}
                                    @else
                                        {{ $penjualan->produk?->nama_produk }} - {{ rtrim(rtrim(number_format($penjualan->jumlah,4,',','.'),'0'),',') }}
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
                                            <div>Rp {{ number_format($d->produk->getHPPForSaleDate($penjualan->tanggal), 0, ',', '.') }}</div>
                                        @endforeach
                                    @elseif($detailCount === 1)
                                        Rp {{ number_format($penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal), 0, ',', '.') }}
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
                                            @php $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah; @endphp
                                            <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
                                        @endforeach
                                    @elseif($detailCount === 1)
                                        @php $actualHPP = $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal); $margin = ($penjualan->details[0]->harga_satuan - $actualHPP) * $penjualan->details[0]->jumlah; @endphp
                                        <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
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
                                            @php $sub = (float)$d->jumlah * (float)$d->harga_satuan; $disc = (float)($d->diskon_nominal ?? 0); $pct = $sub>0 ? ($disc/$sub*100) : 0; @endphp
                                            <div>{{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})</div>
                                        @endforeach
                                    @elseif($detailCount === 1)
                                        @php $d=$penjualan->details[0]; $sub=(float)$d->jumlah*(float)$d->harga_satuan; $disc=(float)($d->diskon_nominal??0); $pct=$sub>0?($disc/$sub*100):0; @endphp
                                        {{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})
                                    @else
                                        @php $pct=0; $disc=(float)($penjualan->diskon_nominal ?? 0); if(($penjualan->jumlah??0)>0){ $hdrHarga=$penjualan->harga_satuan; if(is_null($hdrHarga)){ $hdrHarga=((float)$penjualan->total + $disc)/(float)$penjualan->jumlah; } $subtotal=$penjualan->jumlah*$hdrHarga; $pct=$subtotal>0?($disc/$subtotal*100):0; } @endphp
                                        {{ number_format($pct, 0) }}% (Rp {{ number_format($disc, 0, ',', '.') }})
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
                                            <form action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-minimal btn-danger" data-bs-toggle="tooltip" title="Hapus Transaksi">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
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
                                    <th class="text-center" style="width: 50px">#</th>
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
                                                <div class="action-left">
                                                    <button type="button" class="btn-minimal btn-detail" data-bs-toggle="modal" data-bs-target="#returDetailModal{{ $retur->id }}">
                                                        Detail
                                                    </button>
                                                </div>
                                                <div class="action-right">
                                                    <div class="action-row">
                                                        <a href="{{ route('transaksi.retur-penjualan.edit', $retur->id) }}" class="btn-minimal btn-warning" data-bs-toggle="tooltip" title="Edit Retur">
                                                            Edit
                                                        </a>
                                                        <form action="{{ route('transaksi.retur-penjualan.destroy', $retur->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus retur ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-minimal btn-danger" data-bs-toggle="tooltip" title="Hapus Retur">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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
                        <span class="badge {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                            {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}
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
                
                <h6 class="mb-3"><i class="fas fa-box me-2"></i>Detail Produk</h6>
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
                
                <!-- Ringkasan Transaksi -->
                <h6 class="mb-2 mt-1"><i class="fas fa-calculator me-2"></i>Ringkasan Transaksi</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Subtotal Produk</small>
                            <strong class="text-primary">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Total HPP</small>
                            <strong class="text-info">Rp {{ number_format($totalHPP, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Total Profit</small>
                            <strong class="{{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Total Penjualan</small>
                            <strong class="text-dark">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
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
@endsection