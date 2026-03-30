@extends('layouts.app')

@section('title', 'Detail Retur Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-eye me-2"></i>Detail Retur Penjualan
        </h2>
        <div>
            <a href="{{ route('transaksi.retur-penjualan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            @if($returPenjualan->status === 'belum_dibayar')
                <a href="{{ route('transaksi.retur-penjualan.edit', $returPenjualan) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Informasi Retur</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nomor Retur:</strong></td>
                            <td>{{ $returPenjualan->nomor_retur }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal:</strong></td>
                            <td>{{ $returPenjualan->tanggal->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nomor Penjualan:</strong></td>
                            <td>{{ $returPenjualan->penjualan->nomor_penjualan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pelanggan:</strong></td>
                            <td>{{ $returPenjualan->pelanggan->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Retur:</strong></td>
                            <td>
                                @switch($returPenjualan->jenis_retur)
                                    @case('tukar_barang')
                                        <span class="badge bg-warning">Tukar Barang</span>
                                        @break
                                    @case('refund')
                                        <span class="badge bg-info">Refund</span>
                                        @break
                                    @case('kredit')
                                        <span class="badge bg-secondary">Kredit</span>
                                        @break
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @switch($returPenjualan->status)
                                    @case('belum_dibayar')
                                        <span class="badge bg-danger">Belum Dibayar</span>
                                        @break
                                    @case('lunas')
                                        <span class="badge bg-success">Lunas</span>
                                        @break
                                    @case('selesai')
                                        <span class="badge bg-primary">Selesai</span>
                                        @break
                                @endswitch
                            </td>
                        </tr>
                        @if($returPenjualan->keterangan)
                        <tr>
                            <td><strong>Keterangan:</strong></td>
                            <td>{{ $returPenjualan->keterangan }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Ringkasan Total</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Total Harga:</strong></td>
                            <td class="text-end">Rp {{ number_format($returPenjualan->detailReturPenjualans->sum('subtotal'), 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>PPN (11%):</strong></td>
                            <td class="text-end">Rp {{ number_format($returPenjualan->ppn, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total Retur:</strong></td>
                            <td class="text-end"><strong>Rp {{ number_format($returPenjualan->total_retur, 2) }}</strong></td>
                        </tr>
                    </table>
                    
                    @if($returPenjualan->jenis_retur === 'kredit' && $returPenjualan->status === 'belum_dibayar')
                        <div class="mt-3">
                            <a href="{{ route('transaksi.retur-penjualan.bayar-kredit', $returPenjualan) }}" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin melunasi retur kredit ini?')">
                                <i class="fas fa-money-bill me-2"></i>Bayar Kredit
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Detail Produk yang Diretur</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th class="text-center">Qty Retur</th>
                            <th class="text-end">Harga Barang</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returPenjualan->detailReturPenjualans as $index => $detail)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $detail->produk->nama_produk }}</td>
                                <td class="text-center">{{ $detail->qty_retur }}</td>
                                <td class="text-end">Rp {{ number_format($detail->harga_barang, 2) }}</td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
