@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Laporan Pelunasan Utang</h5>
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
                                    <td>PU-{{ $item->id }}</td>
                                    <td>{{ $item->pembelian->vendor->nama_vendor ?? ($item->vendor->nama_vendor ?? '-') }}</td>
                                    <td>PB-{{ $item->pembelian_id }}</td>
                                    <td class="text-right">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->dibayar_bersih, 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $pembelian = $item->pembelian;
                                            $isLunas = $pembelian && $pembelian->status == 'lunas';
                                        @endphp
                                        @if($isLunas)
                                            <span class="badge badge-success">Lunas</span>
                                        @else
                                            <span class="badge badge-warning">Belum Lunas</span>
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
                                    <th class="text-right">{{ format_rupiah($pelunasanUtang->sum('total_tagihan')) }}</th>
                                    <th class="text-right">{{ format_rupiah($total) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('laporan.pelunasan-utang', ['bulan' => request('bulan'), 'export' => 'pdf']) }}" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
