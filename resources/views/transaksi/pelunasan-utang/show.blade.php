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
                        <div class="section-title">Detail Pembelian</div>
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>Item</th>
                                            <th class="text-right">Harga Satuan</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                        @foreach($pelunasan->pembelian->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{!! nl2br(e($item)) !!}</td>
                                            <td class="text-right">
                                                @php
                                                    // Extract price from the item string
                                                    preg_match('/- Rp (.*?) =/', $item, $matches);
                                                    echo isset($matches[1]) ? 'Rp ' . $matches[1] : '-';
                                                @endphp
                                            </td>
                                            <td class="text-right">
                                                @php
                                                    // Extract subtotal from the item string
                                                    preg_match('/= Rp (.*?)$/', $item, $matches);
                                                    echo isset($matches[1]) ? 'Rp ' . $matches[1] : '-';
                                                @endphp
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Total Pembelian</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($pelunasan->pembelian->total_harga, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Sudah Dibayar</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($pelunasan->pembelian->terbayar, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Sisa Utang</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($pelunasan->pembelian->sisa_pembayaran + $pelunasan->jumlah, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="3" class="text-right"><strong>Jumlah Pelunasan Ini</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($pelunasan->jumlah, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="3" class="text-right"><strong>Sisa Utang Setelah Pelunasan</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($pelunasan->pembelian->sisa_pembayaran, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Pembayaran</h4>
                            </div>
                            <div class="card-body">
                                <div class="section-title">Akun Kas</div>
                                <p class="section-lead">
                                    <strong>{{ $pelunasan->akunKas->kode }}</strong> - {{ $pelunasan->akunKas->nama }}
                                </p>
                                
                                <div class="section-title mt-4">Tanggal Pembayaran</div>
                                <p class="section-lead">{{ $pelunasan->tanggal->format('d F Y') }}</p>
                                
                                @if($pelunasan->keterangan)
                                <div class="section-title">Keterangan</div>
                                <p class="section-lead">{{ $pelunasan->keterangan }}</p>
                                @endif
                            </div>
                        </div>
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
