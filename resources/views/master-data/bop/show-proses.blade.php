@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="fas fa-eye me-2"></i>Detail BOP Proses
    </h2>
    <div>
        <a href="{{ route('master-data.bop.edit-proses', $bopProses->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit BOP Komponen
        </a>
        <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    // Calculate variables
    $kapasitas = $bopProses->kapasitas_per_jam ?? 0;
    $totalBop = $bopProses->total_bop_per_jam ?? 0;
    $btklPerJam = $bopProses->prosesProduksi->tarif_btkl ?? 0;
    $btklPerPcs = $kapasitas > 0 ? $btklPerJam / $kapasitas : 0;
    $bopPerPcs = $kapasitas > 0 ? $totalBop / $kapasitas : 0;
    $biayaPerProduk = $btklPerPcs + $bopPerPcs;
    $biayaPerJam = $btklPerJam + $totalBop;
    
    $komponenBop = is_array($bopProses->komponen_bop) ? $bopProses->komponen_bop : json_decode($bopProses->komponen_bop, true);
    if (!is_array($komponenBop)) $komponenBop = [];
@endphp

<!-- Header Information -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Informasi Proses</h5>
        
        <!-- Simple Info Grid -->
        <div class="row g-2">
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Proses:</strong>
                    <span>{{ $bopProses->prosesProduksi->nama_proses ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Kapasitas:</strong>
                    <span>{{ $kapasitas }} pcs/jam</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>BTKL / jam:</strong>
                    <span>Rp {{ number_format($btklPerJam, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>BTKL / pcs:</strong>
                    <span>Rp {{ number_format($btklPerPcs, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Components Table -->
@if(!empty($komponenBop))
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Komponen BOP</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Komponen</th>
                        <th style="width: 20%" class="text-end">Rp / Jam</th>
                        <th style="width: 30%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($komponenBop as $index => $komponen)
                        @php
                            $rate = floatval($komponen['rate_per_hour'] ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $komponen['component'] ?? 'N/A' }}</td>
                            <td class="text-end">Rp {{ number_format($rate, 0, ',', '.') }}</td>
                            <td>{{ $komponen['description'] ?? $komponen['keterangan'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary">
                    <tr class="fw-bold">
                        <td colspan="2">Total BOP / jam</td>
                        <td class="text-end">Rp {{ number_format($totalBop, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            Belum ada komponen BOP yang didefinisikan untuk proses ini.
        </div>
    </div>
</div>
@endif

<!-- Summary -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Ringkasan Biaya</h5>
        <div class="row g-2">
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Total BOP / jam:</strong>
                    <span class="text-primary">Rp {{ number_format($totalBop, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>BOP / pcs:</strong>
                    <span class="text-success">Rp {{ number_format($bopPerPcs, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Biaya / produk:</strong>
                    <span class="text-warning">Rp {{ number_format($biayaPerProduk, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Biaya / jam:</strong>
                    <span class="text-danger">Rp {{ number_format($biayaPerJam, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information -->
@if(!empty($bopProses->keterangan))
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Keterangan</h5>
        <p>{{ $bopProses->keterangan }}</p>
    </div>
</div>
@endif

@endsection
