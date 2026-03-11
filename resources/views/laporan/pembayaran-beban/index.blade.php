@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-dark mb-0">Laporan Pembayaran Beban</h5>
                    </div>
                    <div>
                        <form action="{{ route('laporan.pembayaran-beban') }}" method="GET" class="d-flex align-items-center">
                            <div class="me-2">
                                <input type="month" name="bulan" id="bulan" class="form-control" 
                                       value="{{ request('bulan', now()->format('Y-m')) }}"
                                       style="min-width: 150px;">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm me-1">Filter</button>
                            <a href="{{ route('laporan.pembayaran-beban') }}" class="btn btn-secondary btn-sm me-1">Reset</a>
                            <a href="{{ route('laporan.pembayaran-beban', ['export' => 'pdf', 'bulan' => request('bulan')]) }}" 
                               class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-dark">
                                    <h6 class="card-title">Total Budget</h6>
                                    <h4 class="text-primary">{{ format_rupiah($summary->total_budget) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-dark">
                                    <h6 class="card-title">Total Aktual</h6>
                                    <h4 class="text-info">{{ format_rupiah($summary->total_aktual) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card {{ $summary->total_selisih < 0 ? 'border-danger' : 'border-success' }}">
                                <div class="card-body text-dark">
                                    <h6 class="card-title">Total Selisih</h6>
                                    <h4 class="{{ $summary->total_selisih < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ format_rupiah($summary->total_selisih) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card {{ $summary->overall_status_color == 'danger' ? 'border-danger' : 'border-success' }}">
                                <div class="card-body text-dark">
                                    <h6 class="card-title">Status</h6>
                                    <h4 class="{{ $summary->overall_status_color == 'danger' ? 'text-danger' : 'text-success' }}">
                                        {{ $summary->overall_status }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-secondary">
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Nama Beban</th>
                                    <th class="text-right">Budget Bulanan</th>
                                    <th class="text-right">Aktual Bulan Ini</th>
                                    <th class="text-right">Selisih</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laporanData as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-muted">{{ $item->kategori }}</td>
                                    <td>{{ $item->nama_beban }}</td>
                                    <td class="text-right">{{ format_rupiah($item->budget_bulanan) }}</td>
                                    <td class="text-right">{{ format_rupiah($item->aktual_bulan_ini) }}</td>
                                    <td class="text-right {{ $item->selisih < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ format_rupiah($item->selisih) }}
                                    </td>
                                    <td class="text-center {{ $item->status_color == 'danger' ? 'text-danger' : 'text-success' }}">
                                        {{ $item->status }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada data beban operasional</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="3" class="text-right">TOTAL</th>
                                    <th class="text-right">{{ format_rupiah($summary->total_budget) }}</th>
                                    <th class="text-right">{{ format_rupiah($summary->total_aktual) }}</th>
                                    <th class="text-right {{ $summary->total_selisih < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ format_rupiah($summary->total_selisih) }}
                                    </th>
                                    <th class="text-center {{ $summary->overall_status_color == 'danger' ? 'text-danger' : 'text-success' }}">
                                        {{ $summary->overall_status }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($laporanData->count() > 0)
                    <div class="mt-3">
                        <div class="alert alert-light border">
                            <i class="fas fa-info-circle text-muted"></i>
                            <strong class="text-dark">Keterangan:</strong>
                            <ul class="mb-0 mt-2 text-muted">
                                <li>Budget Bulanan diambil dari master data Beban Operasional</li>
                                <li>Aktual Bulan Ini adalah total transaksi pembayaran beban pada periode terpilih</li>
                                <li>Selisih = Budget Bulanan - Aktual Bulan Ini</li>
                                <li>Status <span class="text-success">Aman</span> jika Aktual ≤ Budget</li>
                                <li>Status <span class="text-danger">Over Budget</span> jika Aktual > Budget</li>
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
