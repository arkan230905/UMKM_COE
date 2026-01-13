@extends('layouts.gudang')

@section('title', 'Detail Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Detail Pembelian</h2>
    <a href="{{ route('gudang.pembelian') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Informasi Pembelian</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>No. Pembelian:</strong><br>
                <code>{{ $pembelian->nomor_pembelian }}</code>
            </div>
            <div class="col-md-3">
                <strong>Tanggal:</strong><br>
                {{ $pembelian->tanggal->format('d/m/Y') }}
            </div>
            <div class="col-md-3">
                <strong>Vendor:</strong><br>
                {{ $pembelian->vendor->nama_vendor ?? '-' }}
            </div>
            <div class="col-md-3">
                <strong>Status:</strong><br>
                @if($pembelian->status === 'lunas')
                    <span class="badge bg-success">Lunas</span>
                @else
                    <span class="badge bg-warning">Belum Lunas</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Detail Item</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tipe</th>
                    <th>Nama Item</th>
                    <th class="text-end">Jumlah</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga/Satuan</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pembelian->details as $i => $d)
                @php
                    $namaItem = '-';
                    $satuanItem = $d->satuan ?? '-';
                    if ($d->tipe_item === 'bahan_pendukung' && $d->bahanPendukung) {
                        $namaItem = $d->bahanPendukung->nama_bahan;
                    } elseif ($d->bahanBaku) {
                        $namaItem = $d->bahanBaku->nama_bahan;
                    }
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        @if($d->tipe_item === 'bahan_pendukung')
                            <span class="badge bg-warning text-dark">Bahan Pendukung</span>
                        @else
                            <span class="badge bg-primary">Bahan Baku</span>
                        @endif
                    </td>
                    <td>{{ $namaItem }}</td>
                    <td class="text-end">{{ number_format($d->jumlah, 2, ',', '.') }}</td>
                    <td>{{ $satuanItem }}</td>
                    <td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <th colspan="6" class="text-end">TOTAL:</th>
                    <th class="text-end">Rp {{ number_format($pembelian->total_harga ?? $pembelian->total ?? 0, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if($pembelian->keterangan)
<div class="card mt-4">
    <div class="card-body">
        <strong>Keterangan:</strong><br>
        {{ $pembelian->keterangan }}
    </div>
</div>
@endif
@endsection
