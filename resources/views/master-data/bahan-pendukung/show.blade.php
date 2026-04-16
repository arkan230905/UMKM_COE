@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-flask me-2"></i>Detail Bahan Pendukung
            <small class="text-muted fw-normal">- {{ $bahanPendukung->nama_bahan }}</small>
        </h2>
        <div class="btn-group">
            <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('master-data.bahan-pendukung.edit', $bahanPendukung->id) }}" class="btn btn-warning">
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
        <div class="card-header bg-warning text-white">
            <h6 class="mb-0">
                <i class="fas fa-flask me-2"></i>Informasi Utama
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Bahan:</strong></td>
                            <td>
                                <span class="fw-semibold">{{ $bahanPendukung->nama_bahan }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Satuan Utama:</strong></td>
                            <td>
                                @if($bahanPendukung->satuan)
                                    {{ $bahanPendukung->satuan->nama }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Harga Satuan Utama:</strong></td>
                            <td>
                                <span class="fw-bold text-success">Rp {{ number_format($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Stok Saat Ini:</strong></td>
                            <td>
                                <span class="fw-semibold">{{ $bahanPendukung->stok ? rtrim(rtrim(number_format($bahanPendukung->stok, 5, ',', '.'), '0'), ',') : '0' }}</span>
                                @if(($bahanPendukung->stok ?? 0) <= ($bahanPendukung->stok_minimum ?? 0) && ($bahanPendukung->stok ?? 0) > 0)
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stok hampir habis"></i>
                                @elseif(($bahanPendukung->stok ?? 0) <= 0)
                                    <i class="fas fa-times-circle text-danger ms-1" title="Stok habis"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Stok Minimum:</strong></td>
                            <td>
                                <span class="text-muted">{{ $bahanPendukung->stok_minimum ? rtrim(rtrim(number_format($bahanPendukung->stok_minimum, 5, ',', '.'), '0'), ',') : '0' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>
                                <span class="text-muted">{{ $bahanPendukung->deskripsi ?: '-' }}</span>
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
            @if(isset($subSatuanPrices) && count($subSatuanPrices) > 0)
                <div class="row">
                    @foreach($subSatuanPrices as $index => $subSatuan)
                        <div class="col-md-4 mb-3">
                            @php
                                $borderColors = ['border-primary', 'border-success', 'border-warning'];
                                $textColors = ['text-primary', 'text-success', 'text-warning'];
                                $alertColors = ['alert-primary', 'alert-success', 'alert-warning'];
                                $borderColor = $borderColors[$index] ?? 'border-primary';
                                $textColor = $textColors[$index] ?? 'text-primary';
                                $alertColor = $alertColors[$index] ?? 'alert-primary';
                            @endphp
                            <div class="card {{ $borderColor }}">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 {{ $textColor }}">
                                        <i class="fas fa-cube me-2"></i>Sub Satuan {{ $index + 1 }}
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <!-- Harga per Unit -->
                                    <div class="mb-3">
                                        <h5 class="{{ $textColor }} fw-bold">
                                            Rp {{ number_format($subSatuan['harga_per_unit'], 0, ',', '.') }}
                                        </h5>
                                        <small class="text-muted">per {{ $subSatuan['satuan_nama'] }}</small>
                                    </div>
                                    
                                    <!-- Konversi -->
                                    <div class="alert {{ $alertColor }} mb-3">
                                        <small class="mb-0">
                                            <strong>{{ $subSatuan['konversi_text'] }}</strong>
                                        </small>
                                    </div>
                                    
                                    <!-- Formula Perhitungan -->
                                    <div class="bg-light p-2 rounded">
                                        <small class="text-muted">
                                            <strong>Rumus:</strong><br>
                                            {{ $subSatuan['formula_text'] }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Penjelasan Formula -->
                <div class="alert alert-info mt-3">
                    <p class="mb-0">
                        <strong>Rumus:</strong> Rp 62.000 ÷ 1000 = Rp 62/Gram
                    </p>
                </div>
            @else
                <!-- Fallback ke perhitungan lama jika subSatuanPrices tidak ada -->
                <div class="row">
                    <!-- Sub Satuan 1 -->
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-cube me-2"></i>Sub Satuan 1
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($bahanPendukung->subSatuan1 && ($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0) > 0)
                                    @php
                                        $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0;
                                        $konversi1 = $bahanPendukung->sub_satuan_1_konversi ?? 1;
                                        // FORMULA YANG BENAR: harga_sub = harga_utama ÷ konversi
                                        $hargaSubSatuan1 = $hargaUtama / $konversi1;
                                    @endphp
                                    <div class="text-center mb-3">
                                        <h5 class="text-primary fw-bold">
                                            Rp {{ number_format($hargaSubSatuan1, 0, ',', '.') }}
                                        </h5>
                                        <small class="text-muted">per {{ $bahanPendukung->subSatuan1->nama }}</small>
                                    </div>
                                    <div class="alert alert-primary">
                                        <small class="mb-0">
                                            <strong>1 {{ $bahanPendukung->satuan ? $bahanPendukung->satuan->nama : '' }} = {{ rtrim(rtrim(number_format($konversi1, 5, ',', '.'), '0'), ',') }} {{ $bahanPendukung->subSatuan1->nama }}</strong>
                                        </small>
                                    </div>
                                    <div class="bg-light p-2 rounded">
                                        <small class="text-muted">
                                            <strong>Rumus:</strong><br>
                                            Rp {{ number_format($hargaUtama, 0, ',', '.') }} ÷ {{ rtrim(rtrim(number_format($konversi1, 5, ',', '.'), '0'), ',') }} = Rp {{ number_format($hargaSubSatuan1, 0, ',', '.') }}
                                        </small>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-cube fa-2x mb-2"></i>
                                        <p class="mb-0">Sub Satuan 1 tidak tersedia</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sub Satuan 2 -->
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-success">
                                    <i class="fas fa-cube me-2"></i>Sub Satuan 2
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($bahanPendukung->subSatuan2 && ($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0) > 0)
                                    @php
                                        $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0;
                                        $konversi2 = $bahanPendukung->sub_satuan_2_konversi ?? 1;
                                        // FORMULA YANG BENAR: harga_sub = harga_utama ÷ konversi
                                        $hargaSubSatuan2 = $hargaUtama / $konversi2;
                                    @endphp
                                    <div class="text-center mb-3">
                                        <h5 class="text-success fw-bold">
                                            Rp {{ number_format($hargaSubSatuan2, 0, ',', '.') }}
                                        </h5>
                                        <small class="text-muted">per {{ $bahanPendukung->subSatuan2->nama }}</small>
                                    </div>
                                    <div class="alert alert-success">
                                        <small class="mb-0">
                                            <strong>1 {{ $bahanPendukung->satuan ? $bahanPendukung->satuan->nama : '' }} = {{ rtrim(rtrim(number_format($konversi2, 5, ',', '.'), '0'), ',') }} {{ $bahanPendukung->subSatuan2->nama }}</strong>
                                        </small>
                                    </div>
                                    <div class="bg-light p-2 rounded">
                                        <small class="text-muted">
                                            <strong>Rumus:</strong><br>
                                            Rp {{ number_format($hargaUtama, 0, ',', '.') }} ÷ {{ rtrim(rtrim(number_format($konversi2, 5, ',', '.'), '0'), ',') }} = Rp {{ number_format($hargaSubSatuan2, 0, ',', '.') }}
                                        </small>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-cube fa-2x mb-2"></i>
                                        <p class="mb-0">Sub Satuan 2 tidak tersedia</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sub Satuan 3 -->
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-warning">
                                    <i class="fas fa-cube me-2"></i>Sub Satuan 3
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($bahanPendukung->subSatuan3 && ($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0) > 0)
                                    @php
                                        $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0;
                                        $konversi3 = $bahanPendukung->sub_satuan_3_konversi ?? 1;
                                        // FORMULA YANG BENAR: harga_sub = harga_utama ÷ konversi
                                        $hargaSubSatuan3 = $hargaUtama / $konversi3;
                                    @endphp
                                    <div class="text-center mb-3">
                                        <h5 class="text-warning fw-bold">
                                            Rp {{ number_format($hargaSubSatuan3, 0, ',', '.') }}
                                        </h5>
                                        <small class="text-muted">per {{ $bahanPendukung->subSatuan3->nama }}</small>
                                    </div>
                                    <div class="alert alert-warning">
                                        <small class="mb-0">
                                            <strong>1 {{ $bahanPendukung->satuan ? $bahanPendukung->satuan->nama : '' }} = {{ rtrim(rtrim(number_format($konversi3, 5, ',', '.'), '0'), ',') }} {{ $bahanPendukung->subSatuan3->nama }}</strong>
                                        </small>
                                    </div>
                                    <div class="bg-light p-2 rounded">
                                        <small class="text-muted">
                                            <strong>Rumus:</strong><br>
                                            Rp {{ number_format($hargaUtama, 0, ',', '.') }} ÷ {{ rtrim(rtrim(number_format($konversi3, 5, ',', '.'), '0'), ',') }} = Rp {{ number_format($hargaSubSatuan3, 0, ',', '.') }}
                                        </small>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-cube fa-2x mb-2"></i>
                                        <p class="mb-0">Sub Satuan 3 tidak tersedia</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penjelasan Formula -->
                <div class="alert alert-info mt-3">
                    <p class="mb-0">
                        <strong>Rumus:</strong> Rp 62.000 ÷ 1000 = Rp 62/Gram
                    </p>
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
                        @if($bahanPendukung->coaPembelian)
                            <div class="fw-semibold">{{ $bahanPendukung->coaPembelian->nama_akun }}</div>
                            <small class="text-muted">{{ $bahanPendukung->coaPembelian->kode_akun }}</small>
                        @else
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-info fw-bold">COA Persediaan</h6>
                        @if($bahanPendukung->coaPersediaan)
                            <div class="fw-semibold">{{ $bahanPendukung->coaPersediaan->nama_akun }}</div>
                            <small class="text-muted">{{ $bahanPendukung->coaPersediaan->kode_akun }}</small>
                        @else
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-warning fw-bold">COA HPP</h6>
                        @if($bahanPendukung->coaHpp)
                            <div class="fw-semibold">{{ $bahanPendukung->coaHpp->nama_akun }}</div>
                            <small class="text-muted">{{ $bahanPendukung->coaHpp->kode_akun }}</small>
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
