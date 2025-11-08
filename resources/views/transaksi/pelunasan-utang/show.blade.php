@extends('layouts.app')

@section('title', 'Detail Pelunasan Utang: ' . $pelunasanUtang->kode_transaksi)

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Detail Pelunasan Utang: {{ $pelunasanUtang->kode_transaksi }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('transaksi.pelunasan-utang.index') }}">Pelunasan Utang</a></div>
                <div class="breadcrumb-item active">Detail</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="invoice-title">
                            <h2>Pelunasan Utang</h2>
                            <div class="invoice-number">No. {{ $pelunasanUtang->kode_transaksi }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <address>
                                    <strong>Vendor:</strong><br>
                                    {{ $pelunasanUtang->pembelian->vendor->nama }}<br>
                                    {{ $pelunasanUtang->pembelian->vendor->alamat }}<br>
                                    {{ $pelunasanUtang->pembelian->vendor->telepon }}
                                </address>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <address>
                                    <strong>Tanggal:</strong> {{ $pelunasanUtang->tanggal->format('d/m/Y') }}<br>
                                    <strong>Status:</strong> {!! $pelunasanUtang->status_badge !!}
                                </address>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="section-title">Detail Pembayaran</div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Keterangan</th>
                                        <th class="text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Pembayaran untuk Pembelian {{ $pelunasanUtang->pembelian->kode_pembelian }}</td>
                                        <td class="text-right">{{ format_rupiah($pelunasanUtang->jumlah) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Total</strong></td>
                                        <td class="text-right"><strong>{{ format_rupiah($pelunasanUtang->jumlah) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="section-title">Informasi Akun</div>
                        <p class="section-lead">Akun yang digunakan untuk pembayaran:</p>
                        <p>
                            <strong>{{ $pelunasanUtang->akunKas->kode }}</strong> - {{ $pelunasanUtang->akunKas->nama }}
                        </p>
                        
                        @if($pelunasanUtang->keterangan)
                        <div class="section-title mt-4">Keterangan</div>
                        <p class="section-lead">{{ $pelunasanUtang->keterangan }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="section-title">Jurnal</div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Akun</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pelunasanUtang->jurnals as $jurnal)
                                    <tr>
                                        <td>{{ $jurnal->coa->kode }} - {{ $jurnal->coa->nama }}</td>
                                        <td class="text-right">{{ $jurnal->debit ? format_rupiah($jurnal->debit) : '-' }}</td>
                                        <td class="text-right">{{ $jurnal->kredit ? format_rupiah($jurnal->kredit) : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="section-title">Informasi Tambahan</div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted">Dibuat oleh: {{ $pelunasanUtang->user->name }}</p>
                                <p class="text-muted">Dibuat pada: {{ $pelunasanUtang->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6 text-md-right">
                                @if($pelunasanUtang->updated_at->gt($pelunasanUtang->created_at))
                                    <p class="text-muted">Diperbarui pada: {{ $pelunasanUtang->updated_at->format('d/m/Y H:i') }}</p>
                                @endif
                                @if($pelunasanUtang->deleted_at)
                                    <p class="text-muted">Dihapus pada: {{ $pelunasanUtang->deleted_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-md-right mt-4">
                    <div class="float-left">
                        <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <button onclick="window.print()" class="btn btn-warning btn-icon icon-left">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .invoice, .invoice * {
            visibility: visible;
        }
        .invoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            font-size: 12px;
        }
        .no-print {
            display: none !important;
        }
        .table td, .table th {
            padding: 0.5rem;
        }
    }
</style>

@endsection
