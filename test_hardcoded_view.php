@extends('layouts.app')

@section('title', 'Detail BOP Proses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="fas fa-eye me-2"></i>Detail BOP Proses (TEST)
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

<div class="alert alert-danger">
    <strong>TEST MODE:</strong> This is a hardcoded test view to verify data flow.
</div>

<!-- Header Information -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Informasi Proses</h5>
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
                    <span>{{ $kapasitas ?? 0 }} pcs/jam</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>BTKL / jam:</strong>
                    <span class="text-primary">Rp {{ number_format($bopProses->prosesProduksi->tarif_btkl ?? 0) }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>BTKL / produk:</strong>
                    <span>Rp {{ number_format($btklPerProduk ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Components Table -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Komponen BOP (HARDCODED TEST)</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Komponen</th>
                        <th style="width: 20%" class="text-end">Rp / produk</th>
                        <th style="width: 30%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Gas / BBM</td>
                        <td class="text-end">Rp 67</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Air & Kebersihan</td>
                        <td class="text-end">Rp 28</td>
                        <td>-</td>
                    </tr>
                    <tr style="background: yellow;">
                        <td colspan="4">
                            <strong>DEBUG INFO:</strong><br>
                            komponenBop count: {{ count($komponenBop ?? []) }}<br>
                            totalBopPerProduk: {{ $totalBopPerProduk ?? 'NULL' }}<br>
                            @if(!empty($komponenBop))
                                @foreach($komponenBop as $i => $k)
                                    Component {{ $i+1 }}: {{ $k['component'] }} -> {{ $k['rate_per_produk'] ?? 'NULL' }}<br>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary">
                    <tr class="fw-bold">
                        <td colspan="2">Total BOP / produk</td>
                        <td class="text-end">Rp 95</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Ringkasan Biaya</h5>
        <div class="row g-2">
            <div class="col-md-6">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Total BOP / produk:</strong>
                    <strong class="text-primary fs-5">Rp 95</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <strong>Biaya / produk:</strong>
                    <strong class="text-success fs-5">Rp 262</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <strong>If you see this hardcoded test view, then the routing is working.</strong><br>
    If you still see 0 values in the original view, there might be caching issues or the view file is not being updated.
</div>

@endsection
