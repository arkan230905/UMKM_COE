@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Retur Pembelian #{{ $pembelian->id }}</h3>
        <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" class="btn btn-secondary">Kembali</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('transaksi.purchase-returns.store', $pembelian->id) }}" method="POST">
        @csrf
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Retur</label>
                        <input type="date" name="return_date" class="form-control" value="{{ old('return_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alasan Singkat</label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-2">Pilih Barang yang Diretur</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width:5%">#</th>
                        <th>Nama Bahan</th>
                        <th class="text-end">Qty Beli</th>
                        <th class="text-end">Qty Retur Sebelumnya</th>
                        <th class="text-end">Qty Maks Retur</th>
                        <th class="text-end" style="width:15%">Qty Retur</th>
                        <th>Satuan</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal (otomatis)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($pembelian->details ?? []) as $i => $d)
                        @php
                            $returned = 0;
                            if (isset($existingReturns[$d->id])) {
                                $returned = $existingReturns[$d->id]->sum('quantity');
                            }
                            $maxRet = max(0, (float)($d->jumlah ?? 0) - (float)$returned);
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $d->bahanBaku->nama_bahan ?? '-' }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($returned,4,',','.'),'0'),',') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($maxRet,4,',','.'),'0'),',') }}</td>
                            <td>
                                <input type="hidden" name="items[{{ $i }}][pembelian_detail_id]" value="{{ $d->id }}">
                                <input type="number" step="0.0001" min="0" max="{{ $maxRet }}" name="items[{{ $i }}][quantity]" class="form-control text-end" value="{{ old("items.$i.quantity", 0) }}">
                            </td>
                            <td>{{ $d->satuan ?: ($d->bahanBaku->satuan ?? '-') }}</td>
                            <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="text-end text-muted small">Akan dihitung saat simpan</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <small class="text-muted">Qty retur tidak boleh melebihi qty beli dikurangi total retur sebelumnya.</small>
            <button type="submit" class="btn btn-primary">Simpan Retur</button>
        </div>
    </form>
</div>
@endsection
