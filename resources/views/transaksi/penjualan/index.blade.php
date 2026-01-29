@extends('layouts.app')

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
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Penjualan
                @if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'payment_method', 'status']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
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
                            <th class="text-end">Margin</th>
                            <th class="text-end">Diskon %</th>
                            <th class="text-end">Diskon (Rp)</th>
                            <th class="text-end">Total</th>
                            <th>Status Retur</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualans as $key => $penjualan)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td><strong>{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                                <td>{{ optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal }}</td>
                                <td>
                                    <span class="badge {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                        {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}
                                    </span>
                                </td>
                                @php $detailCount = $penjualan->details->count(); @endphp
                                <td>
                                    @if($detailCount > 1)
                                        @foreach($penjualan->details as $d)
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
                                            @php $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); @endphp
                                            <div>Rp {{ number_format($actualHPP, 0, ',', '.') }}</div>
                                        @endforeach
                                    @elseif($detailCount === 1)
                                        @php $actualHPP = $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal); @endphp
                                        Rp {{ number_format($actualHPP, 0, ',', '.') }}
                                    @else
                                        @php $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0; @endphp
                                        Rp {{ number_format($actualHPP, 0, ',', '.') }}
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
                                        @php $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0; $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0); @endphp
                                        <div class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</div>
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
                                <td class="text-end fw-semibold">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        // Cek apakah ada retur untuk penjualan ini
                                        $hasRetur = \App\Models\SalesReturn::where('penjualan_id', $penjualan->id)->exists();
                                    @endphp
                                    @if($hasRetur)
                                        <span class="badge bg-danger">Ada Retur</span>
                                    @else
                                        <span class="badge bg-success">Tidak Ada Retur</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('transaksi.retur-penjualan.create', ['penjualan_id' => $penjualan->id]) }}" class="btn btn-outline-info">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-book"></i> Jurnal
                                        </a>
                                        <form action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
