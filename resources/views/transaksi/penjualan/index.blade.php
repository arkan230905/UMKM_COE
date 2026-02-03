@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 sales-dashboard">
    @php
        $penjualanItems = ($penjualans instanceof \Illuminate\Pagination\LengthAwarePaginator || $penjualans instanceof \Illuminate\Pagination\Paginator)
            ? collect($penjualans->items())
            : collect($penjualans);

        $totalTransaksi = $penjualanItems->count();
        $totalNilai = (float) $penjualanItems->sum('total');
        $rataRata = $totalTransaksi > 0 ? $totalNilai / $totalTransaksi : 0;
        $totalHariIni = $penjualanItems->filter(function ($p) {
            $tanggal = $p->tanggal ?? null;
            if (!$tanggal) {
                return false;
            }

            if ($tanggal instanceof \Illuminate\Support\Carbon || $tanggal instanceof \Carbon\Carbon) {
                return $tanggal->isToday();
            }

            try {
                return \Carbon\Carbon::parse($tanggal)->isToday();
            } catch (\Exception $e) {
                return false;
            }
        })->sum('total');
    @endphp

    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Ringkasan Penjualan</h1>
            <p class="page-subtitle mb-2">Pantau performa transaksi dan akses tindakan penting dengan cepat.</p>
            <div class="filter-summary small text-white-75">
                @php
                    $summaryParts = [];
                    if (!empty($filters['start_date'] ?? null) || !empty($filters['end_date'] ?? null)) {
                        $start = $filters['start_date'] ? \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') : 'Awal';
                        $end = $filters['end_date'] ? \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') : 'Sekarang';
                        $summaryParts[] = "Periode {$start} - {$end}";
                    }
                    if (!empty($filters['payment_method'] ?? null)) {
                        $payments = [
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer',
                        ];
                        $summaryParts[] = 'Metode: ' . ($payments[$filters['payment_method']] ?? ucfirst($filters['payment_method']));
                    }
                    if (!empty($filters['produk_id'] ?? null)) {
                        $selectedProduk = $products->firstWhere('id', (int) $filters['produk_id']);
                        if ($selectedProduk) {
                            $summaryParts[] = 'Produk: ' . e($selectedProduk->nama_produk);
                        }
                    }
                    if ($categories->isNotEmpty() && !empty($filters['kategori_id'] ?? null)) {
                        $selectedKategori = $categories->firstWhere('id', (int) $filters['kategori_id']);
                        if ($selectedKategori) {
                            $summaryParts[] = 'Kategori: ' . e($selectedKategori->nama);
                        }
                    }
                @endphp
                @if(!empty($summaryParts))
                    <span class="badge bg-light text-dark rounded-pill fw-semibold">Filter Aktif</span>
                    <span class="ms-2">{!! implode(' • ', $summaryParts) !!}</span>
                @else
                    <span class="text-muted">Menampilkan semua data penjualan</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-gradient">
                <i class="bi bi-plus-circle me-2"></i>Tambah Penjualan
            </a>
            <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-outline-slate d-none d-md-inline-flex">
                <i class="bi bi-upload"></i>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-receipt"></i></div>
                <div>
                    <p class="stat-label">Total Transaksi</p>
                    <h3 class="stat-value">{{ number_format($totalTransaksi) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-currency-dollar"></i></div>
                <div>
                    <p class="stat-label">Total Nilai</p>
                    <h3 class="stat-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</h3>
                    <small class="stat-hint">{{ number_format($penjualanItems->sum('jumlah')) }} item terjual</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <p class="stat-label">Pendapatan Hari Ini</p>
                    <h3 class="stat-value">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</h3>
                    <small class="stat-hint">Rata-rata order Rp {{ number_format($rataRata, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header border-0 d-flex flex-column gap-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">Daftar Penjualan</h5>
                    <span class="table-count">{{ $penjualans->total() }} data • {{ $penjualans->perPage() }} per halaman</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-slate btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ (empty($summaryParts) ? 'false' : 'true') }}" aria-controls="filterCollapse">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('laporan.penjualan', array_filter($filters ?? [])) }}" class="btn btn-outline-slate btn-sm">
                        <i class="bi bi-box-arrow-up"></i> Export
                    </a>
                    @if(!empty(array_filter($filters ?? [])))
                        <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-slate btn-sm">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </div>

            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('transaksi.penjualan.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer" {{ ($filters['payment_method'] ?? '') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50">Produk</label>
                        <select name="produk_id" class="form-select">
                            <option value="">Semua Produk</option>
                            @foreach($products as $produk)
                                <option value="{{ $produk->id }}" {{ (int)($filters['produk_id'] ?? 0) === $produk->id ? 'selected' : '' }}>
                                    {{ $produk->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($categories->isNotEmpty())
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Kategori Produk</label>
                            <select name="kategori_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (int)($filters['kategori_id'] ?? 0) === $category->id ? 'selected' : '' }}>
                                        {{ $category->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-gradient btn-sm">
                            <i class="bi bi-funnel-fill me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-slate btn-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomor Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pembayaran</th>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon %</th>
                        <th class="text-end">Diskon (Rp)</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">
                            <span class="d-none d-md-inline">Aksi</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penjualans as $penjualan)
                        @php $detailCount = $penjualan->details->count(); @endphp
                        <tr>
                            <td>
                                <span class="text-muted">#{{ str_pad($penjualan->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $penjualan->nomor_penjualan ?? '-' }}</span>
                                <div class="badge bg-gradient bg-opacity-25 text-light badge-soft">ID Order: {{ $penjualan->order_id ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ optional($penjualan->tanggal)->format('d M Y') ?? $penjualan->tanggal }}</div>
                                <small class="text-muted">{{ optional($penjualan->created_at)->format('H:i') }}</small>
                            </td>
                            <td>
                                @php
                                    $method = $penjualan->payment_method ?? 'cash';
                                    $labelMap = [
                                        'cash' => 'Tunai',
                                        'credit' => 'Kredit',
                                        'transfer' => 'Transfer',
                                        'qris' => 'QRIS',
                                    ];
                                @endphp
                                <span class="badge payment-badge payment-{{ $method }}">{{ $labelMap[$method] ?? ucfirst($method) }}</span>
                            </td>
                            <td>
                                <div class="product-stack">
                                    @if($detailCount > 1)
                                        @foreach($penjualan->details as $d)
                                            <span class="product-chip">{{ $d->produk->nama_produk ?? '-' }}</span>
                                        @endforeach
                                    @elseif($detailCount === 1)
                                        <span class="product-chip">{{ $penjualan->details[0]->produk->nama_produk ?? '-' }}</span>
                                    @else
                                        <span class="product-chip">{{ $penjualan->produk?->nama_produk ?? '-' }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                @if($detailCount > 1)
                                    @foreach($penjualan->details as $d)
                                        <div>{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</div>
                                    @endforeach
                                @elseif($detailCount === 1)
                                    {{ rtrim(rtrim(number_format($penjualan->details[0]->jumlah,4,',','.'),'0'),',') }}
                                @else
                                    {{ rtrim(rtrim(number_format($penjualan->jumlah,4,',','.'),'0'),',') }}
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
                                        @php $sub = (float)$d->jumlah * (float)$d->harga_satuan; $disc = (float)($d->diskon_nominal ?? 0); $pct = $sub>0 ? ($disc/$sub*100) : 0; @endphp
                                        <div>{{ number_format($pct, 2, ',', '.') }}%</div>
                                    @endforeach
                                @elseif($detailCount === 1)
                                    @php $d=$penjualan->details[0]; $sub=(float)$d->jumlah*(float)$d->harga_satuan; $disc=(float)($d->diskon_nominal??0); $pct=$sub>0?($disc/$sub*100):0; @endphp
                                    {{ number_format($pct, 2, ',', '.') }}%
                                @else
                                    @php $pct=0; if(($penjualan->jumlah??0)>0){ $hdrHarga=$penjualan->harga_satuan; if(is_null($hdrHarga)){ $hdrHarga=((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0))/(float)$penjualan->jumlah; } $subtotal=$penjualan->jumlah*$hdrHarga; $pct=$subtotal>0?(((float)($penjualan->diskon_nominal ?? 0))/$subtotal*100):0; } @endphp
                                    {{ number_format($pct, 2, ',', '.') }}%
                                @endif
                            </td>
                            <td class="text-end">
                                @if($detailCount > 1)
                                    @foreach($penjualan->details as $d)
                                        <div>Rp {{ number_format($d->diskon_nominal ?? 0, 0, ',', '.') }}</div>
                                    @endforeach
                                @elseif($detailCount === 1)
                                    Rp {{ number_format($penjualan->details[0]->diskon_nominal ?? 0, 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($penjualan->diskon_nominal ?? 0, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                <div class="action-group">
                                    <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('transaksi.retur-penjualan.create', ['penjualan_id' => $penjualan->id]) }}" class="btn btn-sm btn-outline-info" title="Buat Retur">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                    <a href="{{ route('transaksi.penjualan.receipt', $penjualan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Struk">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale_cogs', 'ref_id' => $penjualan->id]) }}" class="btn btn-sm btn-outline-secondary" title="Jurnal HPP">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                    <form action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div class="text-muted small">Menampilkan {{ $penjualans->firstItem() ?? 0 }}-{{ $penjualans->lastItem() ?? 0 }} dari {{ $penjualans->total() }} data</div>
            {{ $penjualans->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.sales-dashboard {
    position: relative;
    z-index: 0;
    min-height: calc(100vh - 3rem);
    background: radial-gradient(circle at 10% 10%, rgba(99, 102, 241, 0.15) 0%, transparent 55%),
                radial-gradient(circle at 95% 20%, rgba(236, 72, 153, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, rgba(8, 20, 45, 0.95) 0%, rgba(8, 20, 45, 0.8) 100%);
}

.sales-dashboard::before,
.sales-dashboard::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: -1;
}

.sales-dashboard::before {
    background: radial-gradient(circle at 40% 0%, rgba(56, 189, 248, 0.18), transparent 60%);
}

.sales-dashboard::after {
    background: radial-gradient(circle at 80% 75%, rgba(147, 197, 253, 0.16), transparent 55%);
}

.page-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.85), rgba(79, 70, 229, 0.8));
    border: 1px solid rgba(148, 163, 233, 0.35);
    border-radius: 22px;
    padding: 1.75rem 2.25rem;
    box-shadow: 0 18px 36px rgba(14, 23, 42, 0.45);
    backdrop-filter: blur(16px);
}

.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #f8fafc;
    letter-spacing: 0.02em;
}

.page-subtitle {
    color: rgba(226, 232, 240, 0.78);
    font-size: 0.96rem;
}

.btn-gradient {
    background: linear-gradient(120deg, #8b5cf6, #6366f1, #22d3ee);
    border: none;
    color: #0f172a !important;
    padding: 0.65rem 1.4rem;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 14px 22px rgba(99, 102, 241, 0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 18px 30px rgba(99, 102, 241, 0.45);
}

.btn-outline-slate {
    border: 1px solid rgba(148, 163, 233, 0.4);
    color: rgba(226, 232, 240, 0.9);
    background-color: rgba(15, 23, 42, 0.45);
    font-weight: 600;
    transition: all 0.25s ease;
    border-radius: 12px;
}

.btn-outline-slate:hover {
    background: rgba(148, 163, 233, 0.15);
    border-color: rgba(148, 163, 233, 0.7);
    color: #f8fafc;
}

.stat-card {
    display: flex;
    gap: 18px;
    align-items: center;
    padding: 1.6rem 1.75rem;
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.88));
    border: 1px solid rgba(100, 116, 139, 0.35);
    box-shadow: 0 16px 30px rgba(2, 6, 23, 0.55);
    backdrop-filter: blur(18px);
}

.stat-card:nth-child(1) {
    background: linear-gradient(140deg, rgba(37, 99, 235, 0.85), rgba(30, 64, 175, 0.75));
}

.stat-card:nth-child(2) {
    background: linear-gradient(140deg, rgba(45, 212, 191, 0.85), rgba(34, 197, 94, 0.78));
}

.stat-card:nth-child(3) {
    background: linear-gradient(140deg, rgba(251, 191, 36, 0.9), rgba(245, 158, 11, 0.78));
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    font-size: 1.4rem;
    background: rgba(15, 23, 42, 0.25);
    color: #f8fafc;
    box-shadow: inset 0 0 0 1px rgba(248, 250, 252, 0.25);
}

.stat-label {
    margin-bottom: 0.25rem;
    font-size: 0.82rem;
    color: rgba(241, 245, 249, 0.78);
    text-transform: uppercase;
    letter-spacing: 0.18em;
}

.stat-value {
    font-size: 1.7rem;
    font-weight: 700;
    margin-bottom: 0;
    color: #0f172a;
}

.stat-card:nth-child(1) .stat-value,
.stat-card:nth-child(2) .stat-value,
.stat-card:nth-child(3) .stat-value {
    color: #0f172a;
}

.stat-hint {
    color: rgba(15, 23, 42, 0.8);
    font-weight: 500;
}

.table-card {
    border-radius: 22px;
    background: linear-gradient(155deg, rgba(15, 23, 42, 0.95), rgba(17, 24, 39, 0.82));
    border: 1px solid rgba(71, 85, 105, 0.45);
    box-shadow: 0 22px 45px rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(16px);
}

.table-card .card-header h5 {
    color: #e2e8f0;
}

.table-card .card-header .table-count {
    color: rgba(148, 163, 184, 0.9);
    font-weight: 500;
}

.table-card .card-header {
    background: transparent;
    padding: 1.5rem 1.9rem 1rem;
}

.table-card .card-footer {
    background: transparent;
    padding: 1rem 1.9rem 1.6rem;
}

.filter-summary .badge {
    background: rgba(226, 232, 240, 0.85) !important;
    color: #0f172a !important;
}

.filter-summary {
    font-weight: 500;
}

.table thead th {
    background: rgba(30, 41, 59, 0.78);
    border: none;
    color: #f8fafc;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    padding: 1rem 0.75rem;
}

.table tbody td {
    border-top: 1px solid rgba(51, 65, 85, 0.55);
    padding: 1.05rem 0.9rem;
    color: #e2e8f0;
    font-size: 0.92rem;
}

.table tbody tr:nth-child(even) {
    background-color: rgba(30, 41, 59, 0.35);
}

.table-hover tbody tr:hover {
    background-color: rgba(14, 165, 233, 0.18);
}

.product-stack {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.product-chip {
    padding: 6px 14px;
    border-radius: 999px;
    background: linear-gradient(120deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.45));
    color: #bfdbfe;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1px solid rgba(96, 165, 250, 0.4);
}

.badge-soft {
    background: rgba(30, 64, 175, 0.2);
    color: #c7d2fe;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 0.76rem;
    display: inline-block;
    margin-top: 6px;
    border: 1px solid rgba(99, 102, 241, 0.35);
}

.payment-badge {
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 0.74rem;
    font-weight: 600;
    border: 1px solid transparent;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.15);
}

.payment-cash {
    background: rgba(34, 197, 94, 0.18);
    color: #bbf7d0;
    border-color: rgba(34, 197, 94, 0.35);
}

.payment-credit {
    background: rgba(245, 158, 11, 0.22);
    color: #fef3c7;
    border-color: rgba(245, 158, 11, 0.35);
}

.payment-transfer {
    background: rgba(59, 130, 246, 0.2);
    color: #bfdbfe;
    border-color: rgba(59, 130, 246, 0.35);
}

.payment-qris {
    background: rgba(236, 72, 153, 0.22);
    color: #fbcfe8;
    border-color: rgba(236, 72, 153, 0.35);
}

.action-group {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
}

.action-group .btn {
    border-radius: 12px;
    width: 42px;
    height: 42px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148, 163, 184, 0.35) !important;
    background-color: rgba(15, 23, 42, 0.62) !important;
    color: rgba(226, 232, 240, 0.92) !important;
    transition: all 0.2s ease;
    box-shadow: 0 10px 18px rgba(2, 6, 23, 0.35);
}

.action-group .btn i {
    color: inherit !important;
    font-size: 1.05rem;
    line-height: 1;
}

.action-group .btn.btn-outline-warning {
    border-color: rgba(245, 158, 11, 0.55) !important;
    background-color: rgba(245, 158, 11, 0.12) !important;
    color: #fbbf24 !important;
}

.action-group .btn.btn-outline-info {
    border-color: rgba(56, 189, 248, 0.55) !important;
    background-color: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
}

.action-group .btn.btn-outline-primary {
    border-color: rgba(99, 102, 241, 0.6) !important;
    background-color: rgba(99, 102, 241, 0.14) !important;
    color: #a5b4fc !important;
}

.action-group .btn.btn-outline-secondary {
    border-color: rgba(148, 163, 184, 0.55) !important;
    background-color: rgba(148, 163, 184, 0.12) !important;
    color: #e2e8f0 !important;
}

.action-group .btn.btn-outline-danger {
    border-color: rgba(239, 68, 68, 0.55) !important;
    background-color: rgba(239, 68, 68, 0.12) !important;
    color: #fca5a5 !important;
}

.action-group .btn:hover {
    background-color: rgba(59, 130, 246, 0.25);
    border-color: rgba(59, 130, 246, 0.5);
    color: #f8fafc !important;
}

.action-group .btn:focus,
.action-group .btn:active {
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.22), 0 12px 24px rgba(2, 6, 23, 0.45);
}

.action-group .btn.btn-outline-warning:hover {
    background-color: rgba(245, 158, 11, 0.22) !important;
    border-color: rgba(245, 158, 11, 0.75) !important;
    color: #fde68a !important;
}

.action-group .btn.btn-outline-info:hover {
    background-color: rgba(56, 189, 248, 0.22) !important;
    border-color: rgba(56, 189, 248, 0.75) !important;
    color: #bae6fd !important;
}

.action-group .btn.btn-outline-primary:hover {
    background-color: rgba(99, 102, 241, 0.24) !important;
    border-color: rgba(99, 102, 241, 0.85) !important;
    color: #e0e7ff !important;
}

.action-group .btn.btn-outline-secondary:hover {
    background-color: rgba(148, 163, 184, 0.2) !important;
    border-color: rgba(226, 232, 240, 0.55) !important;
    color: #ffffff !important;
}

.action-group .btn.btn-outline-danger:hover {
    background-color: rgba(239, 68, 68, 0.22) !important;
    border-color: rgba(239, 68, 68, 0.85) !important;
    color: #fee2e2 !important;
}

.table-card .text-muted,
.table-card small,
.table-card .badge-soft,
.card-header .table-count {
    color: rgba(148, 163, 184, 0.86) !important;
}

@media (max-width: 991.98px) {
    .page-header {
        padding: 1.5rem;
    }

    .table-card .card-header,
    .table-card .card-footer {
        padding-left: 1.35rem;
        padding-right: 1.35rem;
    }
}

@media (max-width: 575.98px) {
    .stat-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .action-group {
        justify-content: flex-start;
    }
}
</style>
@endpush
