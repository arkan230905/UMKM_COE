@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Laporan Stok</h3>

    <form method="GET" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">Tipe Stok</label>
            <select name="tipe" class="form-select" onchange="this.form.submit()">
                <option value="material" {{ ($tipe ?? 'material') === 'material' ? 'selected' : '' }}>Bahan Baku</option>
                <option value="product" {{ ($tipe ?? '') === 'product' ? 'selected' : '' }}>Produk</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Item</label>
            @if(($tipe ?? 'material')==='material')
                <select name="item_id" class="form-select">
                    <option value="">-- Semua Bahan --</option>
                    @foreach(($materials ?? []) as $m)
                        <option value="{{ $m->id }}" {{ ($itemId ?? '')==$m->id ? 'selected' : '' }}>{{ $m->nama_bahan }}</option>
                    @endforeach
                </select>
            @else
                <select name="item_id" class="form-select">
                    <option value="">-- Semua Produk --</option>
                    @foreach(($products ?? []) as $p)
                        <option value="{{ $p->id }}" {{ ($itemId ?? '')==$p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="col-md-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-primary" type="submit">Terapkan</button>
        </div>
    </form>

    @if(!empty($itemId))
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Saldo Awal</h6>
                <div>
                    <span class="me-3"><strong>Qty:</strong> {{ rtrim(rtrim(number_format($saldoAwalQty ?? 0,4,',','.'),'0'),',') }}</span>
                    <span><strong>Nilai:</strong> Rp {{ number_format($saldoAwalNilai ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-2">Kartu Stok</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:12%">Tanggal</th>
                                <th>Referensi</th>
                                <th class="text-end">Masuk (Qty)</th>
                                <th class="text-end">Masuk (Rp)</th>
                                <th class="text-end">Keluar (Qty)</th>
                                <th class="text-end">Keluar (Rp)</th>
                                <th class="text-end">Saldo (Qty)</th>
                                <th class="text-end">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($running ?? []) as $row)
                                <tr>
                                    <td>{{ $row['tanggal'] }}</td>
                                    <td>{{ $row['ref'] }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row['in_qty'],4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">{{ $row['in_nilai']>0 ? 'Rp '.number_format($row['in_nilai'],0,',','.') : '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row['out_qty'],4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">{{ $row['out_nilai']>0 ? 'Rp '.number_format($row['out_nilai'],0,',','.') : '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row['saldo_qty'],4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['saldo_nilai'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Saldo per Item</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Nama</th>
                                <th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=1; @endphp
                            @if(($tipe ?? 'material')==='material')
                                @foreach(($materials ?? []) as $m)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $m->nama_bahan }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($saldoPerItem[$m->id] ?? 0,4,',','.'),'0'),',') }} {{ $m->satuan }}</td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach(($products ?? []) as $p)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $p->nama_produk }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($saldoPerItem[$p->id] ?? 0,4,',','.'),'0'),',') }} pcs</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
