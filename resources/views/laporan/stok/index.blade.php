@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Laporan Stok</h2>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.stok') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipe</label>
                    <select name="tipe" class="form-select" id="tipeSelect">
                        <option value="material" {{ request('tipe', 'material') == 'material' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="product" {{ request('tipe') == 'product' ? 'selected' : '' }}>Produk</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item (Opsional)</label>
                    <select name="item_id" class="form-select" id="itemSelect">
                        <option value="">Semua Item</option>
                        @if(request('tipe', 'material') == 'material')
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" {{ request('item_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_bahan }}
                                </option>
                            @endforeach
                        @else
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('item_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_produk }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('laporan.stok') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(request('item_id'))
        <!-- Kartu Stok Detail untuk Item Tertentu -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 Kartu Stok - 
                    @if($tipe == 'material')
                        {{ $materials->firstWhere('id', request('item_id'))->nama_bahan ?? 'Bahan Baku' }}
                    @else
                        {{ $products->firstWhere('id', request('item_id'))->nama_produk ?? 'Produk' }}
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <!-- Saldo Awal -->
                @if($saldoAwalQty > 0 || $saldoAwalNilai > 0)
                <div class="alert alert-info">
                    @php
                        $desimalAwal = ($saldoAwalQty != floor($saldoAwalQty)) ? 2 : 0;
                    @endphp
                    <strong>Saldo Awal (sebelum {{ request('from') ?? 'periode' }}):</strong> 
                    {{ number_format($saldoAwalQty, $desimalAwal, ',', '.') }} unit | 
                    Nilai: Rp {{ number_format($saldoAwalNilai, 0, ',', '.') }}
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Referensi</th>
                                <th class="text-end">Masuk (Qty)</th>
                                <th class="text-end">Masuk (Nilai)</th>
                                <th class="text-end">Keluar (Qty)</th>
                                <th class="text-end">Keluar (Nilai)</th>
                                <th class="text-end">Saldo (Qty)</th>
                                <th class="text-end">Saldo (Nilai)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($running as $r)
                            <tr>
                                @php
                                    $desimalIn = ($r['in_qty'] != floor($r['in_qty'])) ? 2 : 0;
                                    $desimalOut = ($r['out_qty'] != floor($r['out_qty'])) ? 2 : 0;
                                    $desimalSaldo = ($r['saldo_qty'] != floor($r['saldo_qty'])) ? 2 : 0;
                                @endphp
                                <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                                <td>{{ $r['ref'] }}</td>
                                <td class="text-end">{{ $r['in_qty'] > 0 ? number_format($r['in_qty'], $desimalIn, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $r['in_nilai'] > 0 ? 'Rp '.number_format($r['in_nilai'], 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $r['out_qty'] > 0 ? number_format($r['out_qty'], $desimalOut, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $r['out_nilai'] > 0 ? 'Rp '.number_format($r['out_nilai'], 0, ',', '.') : '-' }}</td>
                                <td class="text-end"><strong>{{ number_format($r['saldo_qty'], $desimalSaldo, ',', '.') }}</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($r['saldo_nilai'], 0, ',', '.') }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Tidak ada pergerakan stok dalam periode ini
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- Ringkasan Stok Per Item -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">📦 Ringkasan Stok {{ $tipe == 'material' ? 'Bahan Baku' : 'Produk' }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="40%">Nama Item</th>
                                <th width="20%" class="text-end">Stok Saat Ini</th>
                                <th width="15%">Satuan</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($tipe == 'material')
                                @forelse($materials as $index => $m)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $m->nama_bahan }}</td>
                                    <td class="text-end">
                                        @php
                                            $stok = $saldoPerItem[$m->id] ?? $m->stok ?? 0;
                                            $desimal = ($stok != floor($stok)) ? 2 : 0;
                                        @endphp
                                        <strong>{{ number_format($stok, $desimal, ',', '.') }}</strong>
                                    </td>
                                    <td>{{ $m->satuan->nama ?? 'KG' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('laporan.stok', ['tipe' => 'material', 'item_id' => $m->id, 'from' => request('from'), 'to' => request('to')]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat Kartu Stok
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Tidak ada data bahan baku
                                    </td>
                                </tr>
                                @endforelse
                            @else
                                @forelse($products as $index => $p)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $p->nama_produk }}</td>
                                    <td class="text-end">
                                        @php
                                            $stok = $saldoPerItem[$p->id] ?? $p->stok ?? 0;
                                            $desimal = ($stok != floor($stok)) ? 2 : 0;
                                        @endphp
                                        <strong>{{ number_format($stok, $desimal, ',', '.') }}</strong>
                                    </td>
                                    <td>{{ $p->satuan->nama ?? 'PCS' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('laporan.stok', ['tipe' => 'product', 'item_id' => $p->id, 'from' => request('from'), 'to' => request('to')]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat Kartu Stok
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Tidak ada data produk
                                    </td>
                                </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-reload item dropdown when tipe changes
    document.getElementById('tipeSelect').addEventListener('change', function() {
        // Submit form to reload with new tipe
        this.form.submit();
    });
</script>

@endsection
