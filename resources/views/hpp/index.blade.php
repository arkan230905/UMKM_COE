@extends('layouts.app')

@section('title', 'Dashboard Harga Pokok Produksi')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Dashboard Harga Pokok Produksi
        </h2>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ count($produkStats) }}</h4>
                            <p class="mb-0">Total Produk</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ collect($produkStats)->where('harga_pokok', '>', 0)->count() }}</h4>
                            <p class="mb-0">Produk dengan HPP</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ collect($produkStats)->where('margin', '>', 0)->count() }}</h4>
                            <p class="mb-0">Produk Profitable</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format(collect($produkStats)->avg('margin'), 1) }}%</h4>
                            <p class="mb-0">Rata-rata Margin</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Produk dengan HPP -->
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #5a3a1a; color: white;">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Produk dan Harga Pokok Produksi
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="hppTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Produk</th>
                            <th>Harga Jual</th>
                            <th>Total Biaya Bahan</th>
                            <th>Harga Pokok</th>
                            <th>Margin</th>
                            <th>Status HPP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produkStats as $stat)
                            <tr>
                                <td>
                                    <strong>{{ $stat['produk']->nama_produk }}</strong>
                                    @if($stat['produk']->stok > 0)
                                        <span class="badge bg-success ms-2">Stok: {{ $stat['produk']->stok }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($stat['produk']->harga_jual, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($stat['total_biaya_bahan'], 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    @if($stat['harga_pokok'] > 0)
                                        <strong class="text-primary">Rp {{ number_format($stat['harga_pokok'], 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">Belum dihitung</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($stat['margin'] > 0)
                                        <span class="badge bg-success">{{ number_format($stat['margin'], 1) }}%</span>
                                    @elseif($stat['margin'] < 0)
                                        <span class="badge bg-danger">{{ number_format($stat['margin'], 1) }}%</span>
                                    @else
                                        <span class="badge bg-secondary">{{ number_format($stat['margin'], 1) }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if($stat['bom'])
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Sudah Ada
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation me-1"></i>Belum Ada
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($stat['bom'])
                                            <a href="{{ route('hpp.show', $stat['produk']->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                            <a href="{{ route('hpp.recalculate', $stat['produk']->id) }}" class="btn btn-outline-warning btn-sm" title="Hitung Ulang">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('hpp.create', $stat['produk']->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus me-1"></i>Hitung HPP
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada data produk. Silakan tambahkan produk terlebih dahulu.</p>
                                    <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tambah Produk
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#hppTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        order: [[0, 'asc']],
        columns: [
            null,
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false }
        ]
    });
});
</script>
@endpush
@endsection
