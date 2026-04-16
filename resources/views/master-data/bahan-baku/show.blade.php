@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Detail Bahan Baku
            <small class="text-muted fw-normal">- {{ $bahanBaku->nama_bahan }}</small>
        </h2>
        <div class="btn-group">
            <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('master-data.bahan-baku.edit', $bahanBaku->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-box me-2"></i>Informasi Utama
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Bahan:</strong></td>
                            <td>
                                <span class="fw-semibold">{{ $bahanBaku->nama_bahan }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Satuan Utama:</strong></td>
                            <td>
                                @if($bahanBaku->satuan)
                                    {{ $bahanBaku->satuan->nama }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Harga Satuan Utama:</strong></td>
                            <td>
                                <span class="fw-bold text-success">Rp {{ number_format($bahanBaku->harga_satuan_display ?? $bahanBaku->harga_satuan ?? 0, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Stok Saat Ini:</strong></td>
                            <td>
                                <span class="fw-semibold">{{ $bahanBaku->stok ? rtrim(rtrim(number_format($bahanBaku->stok, 5, ',', '.'), '0'), ',') : '0' }}</span>
                                @if(($bahanBaku->stok ?? 0) <= ($bahanBaku->stok_minimum ?? 0) && ($bahanBaku->stok ?? 0) > 0)
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stok hampir habis"></i>
                                @elseif(($bahanBaku->stok ?? 0) <= 0)
                                    <i class="fas fa-times-circle text-danger ms-1" title="Stok habis"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Stok Minimum:</strong></td>
                            <td>
                                <span class="text-muted">{{ $bahanBaku->stok_minimum ? rtrim(rtrim(number_format($bahanBaku->stok_minimum, 5, ',', '.'), '0'), ',') : '0' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>
                                <span class="text-muted">{{ $bahanBaku->deskripsi ?: '-' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Konversi Satuan Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-exchange-alt me-2"></i>Konversi Satuan
            </h6>
        </div>
        <div class="card-body">
            @if(count($subSatuanPrices) > 0)
                <div class="row">
                    @foreach($subSatuanPrices as $index => $subSatuan)
                        <div class="col-md-4">
                            <div class="card border-{{ $index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning') }}">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-{{ $index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning') }}">
                                        <i class="fas fa-cube me-2"></i>Sub Satuan {{ $index + 1 }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h5 class="text-{{ $index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning') }} fw-bold">
                                            Rp {{ number_format($subSatuan['harga_per_unit'], 0, ',', '.') }}
                                        </h5>
                                        <small class="text-muted">per {{ $subSatuan['satuan_nama'] }}</small>
                                    </div>
                                    <div class="alert alert-{{ $index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning') }}">
                                        <small class="mb-0">
                                            <strong>{{ $subSatuan['konversi_text'] }}</strong>
                                        </small>
                                    </div>
                                    <div class="bg-light p-2 rounded">
                                        <p class="mb-0">
                                            {{ $subSatuan['formula_text'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-cube fa-3x mb-3"></i>
                    <h5>Tidak Ada Sub Satuan</h5>
                    <p class="mb-0">Bahan baku ini belum memiliki konversi sub satuan</p>
                </div>
            @endif
        </div>
    </div>

    <!-- COA Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-book me-2"></i>Akun COA
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-success fw-bold">COA Pembelian</h6>
                        @if($bahanBaku->coaPembelian)
                            <div class="fw-semibold">{{ $bahanBaku->coaPembelian->nama_akun }}</div>
                            <small class="text-muted">{{ $bahanBaku->coaPembelian->kode_akun }}</small>
                        @else
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-info fw-bold">COA Persediaan</h6>
                        @if($bahanBaku->coaPersediaan)
                            <div class="fw-semibold">{{ $bahanBaku->coaPersediaan->nama_akun }}</div>
                            <small class="text-muted">{{ $bahanBaku->coaPersediaan->kode_akun }}</small>
                        @else
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-warning fw-bold">COA HPP</h6>
                        @if($bahanBaku->coaHpp)
                            <div class="fw-semibold">{{ $bahanBaku->coaHpp->nama_akun }}</div>
                            <small class="text-muted">{{ $bahanBaku->coaHpp->kode_akun }}</small>
                        @else
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .alert {
        border: none;
        border-radius: 0.5rem;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush
@endsection
