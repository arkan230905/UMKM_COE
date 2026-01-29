@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-arrow-return-left"></i> Laporan Retur Pembelian
        </h2>
        <p class="text-muted">Lihat riwayat retur pembelian yang telah dilakukan</p>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group" role="group">
            <a href="{{ route('pegawai-pembelian.laporan.pembelian') }}" 
               class="btn btn-outline-primary">
                <i class="bi bi-cart-plus"></i> Pembelian
            </a>
            <a href="{{ route('pegawai-pembelian.laporan.retur') }}" 
               class="btn btn-primary">
                <i class="bi bi-arrow-return-left"></i> Retur
            </a>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Fitur Laporan Retur</strong><br>
            Halaman ini akan menampilkan riwayat retur pembelian yang telah dilakukan. 
            Fitur ini sedang dalam pengembangan dan akan segera tersedia.
        </div>
    </div>
</div>

<!-- Coming Soon Card -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-tools" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="mt-3">Fitur dalam Pengembangan</h4>
        <p class="text-muted">Laporan retur pembelian akan segera tersedia</p>
        <a href="{{ route('pegawai-pembelian.laporan.pembelian') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Laporan Pembelian
        </a>
    </div>
</div>
@endsection
