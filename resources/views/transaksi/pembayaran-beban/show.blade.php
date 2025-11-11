@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Detail Pembayaran Beban</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('transaksi.pembayaran-beban.print', $pembayaran->id) }}" 
               class="btn btn-primary" target="_blank">
                <i class="fas fa-print"></i> Cetak
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pembayaran</h6>
                    <span class="badge badge-{{ $pembayaran->status == 'lunas' ? 'success' : 'warning' }}">
                        {{ strtoupper($pembayaran->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Tanggal</th>
                            <td>{{ $pembayaran->tanggal->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>No. Transaksi</th>
                            <td>{{ $pembayaran->kode_transaksi }}</td>
                        </tr>
                        <tr>
                            <th>Akun Beban</th>
                            <td>{{ $pembayaran->coaBeban->kode }} - {{ $pembayaran->coaBeban->nama }}</td>
                        </tr>
                        <tr>
                            <th>Akun Kas</th>
                            <td>{{ $pembayaran->coaKas->kode }} - {{ $pembayaran->coaKas->nama }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah</th>
                            <td class="font-weight-bold">{{ format_rupiah($pembayaran->jumlah) }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $pembayaran->keterangan }}</td>
                        </tr>
                        @if($pembayaran->catatan)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $pembayaran->catatan }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $pembayaran->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $pembayaran->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Jurnal</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Akun</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $pembayaran->coaBeban->kode }} - {{ $pembayaran->coaBeban->nama }}</td>
                                    <td class="text-right">{{ format_rupiah($pembayaran->jumlah) }}</td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td>{{ $pembayaran->coaKas->kode }} - {{ $pembayaran->coaKas->nama }}</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">{{ format_rupiah($pembayaran->jumlah) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen</h6>
                </div>
                <div class="card-body text-center">
                    <a href="{{ route('transaksi.pembayaran-beban.print', $pembayaran->id) }}" 
                       class="btn btn-outline-primary btn-block mb-2" target="_blank">
                        <i class="fas fa-print"></i> Cetak Bukti Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
