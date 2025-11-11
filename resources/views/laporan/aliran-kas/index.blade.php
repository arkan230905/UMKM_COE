@extends('layouts.app')

@section('title', 'Laporan Aliran Kas dan Bank')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Laporan Aliran Kas dan Bank</h2>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.aliran-kas') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Laporan -->
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h4>
            
            <!-- Saldo Awal -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Saldo Awal Kas:</strong> Rp {{ number_format($saldoAwalKas, 0, ',', '.') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Saldo Awal Bank:</strong> Rp {{ number_format($saldoAwalBank, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Tabel Transaksi -->
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">Tanggal</th>
                        <th width="50%">Keterangan</th>
                        <th width="20%" class="text-end">Uang Masuk</th>
                        <th width="20%" class="text-end">Uang Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $t)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($t['tanggal'])->format('d/m/Y') }}</td>
                        <td>{{ $t['keterangan'] }}</td>
                        <td class="text-end text-success">
                            @if($t['uang_masuk'] > 0)
                                <strong>Rp {{ number_format($t['uang_masuk'], 0, ',', '.') }}</strong>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end text-danger">
                            @if($t['uang_keluar'] > 0)
                                <strong>Rp {{ number_format($t['uang_keluar'], 0, ',', '.') }}</strong>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada transaksi dalam periode ini</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="text-end">Total:</th>
                        <th class="text-end text-success">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</th>
                        <th class="text-end text-danger">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</th>
                    </tr>
                    <tr class="table-primary">
                        <th colspan="2" class="text-end">Saldo Akhir (Saldo Awal + Uang Masuk - Uang Keluar):</th>
                        <th colspan="2" class="text-end">
                            <h5 class="mb-0">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</h5>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
