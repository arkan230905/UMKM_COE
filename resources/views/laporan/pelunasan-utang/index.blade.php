@extends('layouts.app')

@section('title', 'Laporan Pelunasan Utang')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Laporan Pelunasan Utang</h5>
                    <a href="{{ route('laporan.export.pelunasan-utang', ['bulan' => request('bulan', now()->format('Y-m'))]) }}" 
                       class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('laporan.pelunasan-utang') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan">Pilih Bulan</label>
                                    <input type="month" name="bulan" id="bulan" class="form-control" 
                                           value="{{ request('bulan', now()->format('Y-m')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('laporan.pelunasan-utang') }}" class="btn btn-secondary ml-2">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Pelunasan</th>
                                    <th>Vendor</th>
                                    <th>No. Faktur</th>
                                    <th>Total Tagihan</th>
                                    <th>Dibayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pelunasanUtang as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $item->kode_transaksi }}</td>
                                    <td>{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</td>
                                    <td>{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($item->pembelian->total_harga ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $statusPembayaran = $item->pembelian->status_pembayaran;
                                        @endphp
                                        @if($statusPembayaran === 'Lunas')
                                            <span class="badge badge-success">Lunas</span>
                                        @elseif($statusPembayaran === 'Sebagian')
                                            <span class="badge badge-warning">Sebagian</span>
                                        @else
                                            <span class="badge badge-danger">Belum Bayar</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pelunasan utang</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total</th>
                                    <th class="text-right">
                                        Rp {{ number_format($pelunasanUtang->sum(function($item) { return $item->pembelian->total_harga ?? 0; }), 0, ',', '.') }}
                                    </th>
                                    <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
