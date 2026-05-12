@extends('layouts.gudang')

@section('title', 'Dashboard Gudang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Dashboard Gudang</h1>
            <p class="text-muted">Selamat datang, {{ $pegawai['nama'] }} ({{ $pegawai['jabatan'] }})</p>
        </div>
        <div class="text-end">
            <small class="text-muted">{{ $perusahaan['nama'] }}</small><br>
            <small class="text-muted">Kode: {{ $perusahaan['kode'] }}</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['total_bahan_baku'] }}</h4>
                            <p class="mb-0">Bahan Baku</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x"></i>
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
                            <h4>{{ $stats['total_bahan_pendukung'] }}</h4>
                            <p class="mb-0">Bahan Pendukung</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tools fa-2x"></i>
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
                            <h4>{{ $stats['total_vendor'] }}</h4>
                            <p class="mb-0">Vendor</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-truck fa-2x"></i>
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
                            <h4>{{ $stats['total_pembelian_bulan_ini'] }}</h4>
                            <p class="mb-0">Pembelian Bulan Ini</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tachometer-alt"></i> Menu Utama</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('gudang.bahan-baku') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-boxes"></i><br>
                                Bahan Baku
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('gudang.bahan-pendukung') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-tools"></i><br>
                                Bahan Pendukung
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('gudang.vendor') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-truck"></i><br>
                                Vendor
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('gudang.pembelian') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-shopping-cart"></i><br>
                                Pembelian
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> Informasi Pegawai</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nama:</strong></td>
                            <td>{{ $pegawai['nama'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kode Pegawai:</strong></td>
                            <td>{{ $pegawai['kode'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $pegawai['email'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jabatan:</strong></td>
                            <td>{{ $pegawai['jabatan'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection