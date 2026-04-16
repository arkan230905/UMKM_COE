@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Detail Bahan Pendukung - {{ $bahan->nama_bahan }}</h4>
                </div>
                <div class="card-body">
                    <!-- Informasi Utama -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nama Bahan:</strong></td>
                                    <td>{{ $bahan->nama_bahan }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Satuan Utama:</strong></td>
                                    <td>{{ $bahan->satuan_utama_nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Harga Satuan Utama:</strong></td>
                                    <td class="text-success font-weight-bold">
                                        Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Stok Saat Ini:</strong></td>
                                    <td>{{ number_format($bahan->stok, 2, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Stok Minimum:</strong></td>
                                    <td>{{ number_format($bahan->stok_minimum, 2, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Deskripsi:</strong></td>
                                    <td>{{ $bahan->deskripsi }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Konversi Satuan -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Konversi Satuan</h5>
                        </div>
                        <div class="card-body">
                            @if(count($subSatuanPrices) > 0)
                                <div class="row">
                                    @foreach($subSatuanPrices as $index => $subSatuan)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white text-center">
                                                    <h6 class="mb-0">Sub Satuan {{ $index + 1 }}</h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <!-- Harga per Unit -->
                                                    <div class="mb-2">
                                                        <span class="badge badge-success badge-lg p-2">
                                                            Rp {{ number_format($subSatuan['harga_per_unit'], 0, ',', '.') }} / {{ $subSatuan['satuan_nama'] }}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Konversi -->
                                                    <div class="mb-2">
                                                        <small class="text-muted">{{ $subSatuan['konversi_text'] }}</small>
                                                    </div>
                                                    
                                                    <!-- Formula Perhitungan -->
                                                    <div class="border-top pt-2">
                                                        <small class="text-info">
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
                                        Rp 62.000 ÷ 1000 = Rp 62/Gram
                                    </p>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Belum ada konversi satuan yang didefinisikan untuk bahan ini.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div class="mt-4">
                        <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('master-data.bahan-pendukung.edit', $bahan->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.card-body .text-info {
    font-size: 0.85rem;
}

.border-primary {
    border-color: #007bff !important;
}
</style>
@endsection