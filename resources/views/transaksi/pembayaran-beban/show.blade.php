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
                    <span class="badge badge-success">
                        SELESAI
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Tanggal</th>
                            <td>{{ $pembayaran->tanggal->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Beban Operasional</th>
                            <td>{{ $pembayaran->nama_beban_operasional }}</td>
                        </tr>
                        <tr>
                            <th>Akun Beban</th>
                            <td>{{ $pembayaran->coaBeban->kode_akun }} - {{ $pembayaran->coaBeban->nama_akun }}</td>
                        </tr>
                        <tr>
                            <th>Akun Kas/Bank</th>
                            <td>{{ $pembayaran->coaKasBank->kode_akun }} - {{ $pembayaran->coaKasBank->nama_akun }}</td>
                        </tr>
                        <tr>
                            <th>Metode Bayar</th>
                            <td>{{ ucfirst($pembayaran->metode_bayar) }}</td>
                        </tr>
                        <tr>
                            <th>Nominal Pembayaran</th>
                            <td class="font-weight-bold">{{ $pembayaran->nominal_pembayaran_formatted }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $pembayaran->keterangan ?? '-' }}</td>
                        </tr>
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
                                    <td>{{ $pembayaran->coaBeban->kode_akun }} - {{ $pembayaran->coaBeban->nama_akun }}</td>
                                    <td class="text-right">{{ $pembayaran->nominal_pembayaran_formatted }}</td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td>{{ $pembayaran->coaKasBank->kode_akun }} - {{ $pembayaran->coaKasBank->nama_akun }}</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">{{ $pembayaran->nominal_pembayaran_formatted }}</td>
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
