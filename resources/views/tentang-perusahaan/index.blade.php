@extends('layouts.app')

@section('title', 'Tentang Perusahaan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-building me-2"></i>Tentang Perusahaan
        </h2>
        @if(auth()->user()->role === 'owner')
            <a href="/tentang-perusahaan/edit" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Data
            </a>
        @endif
    </div>

    <!-- Info untuk admin bahwa ini adalah view-only -->
    @if(auth()->user()->role !== 'owner')
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Halaman ini bersifat read-only. Untuk mengubah data perusahaan, silakan hubungi owner.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-building me-2"></i>Informasi Perusahaan
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%" class="bg-light">Nama Perusahaan</th>
                                    <td>{{ $dataPerusahaan->nama }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Alamat</th>
                                    <td>{{ $dataPerusahaan->alamat }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Email</th>
                                    <td>{{ $dataPerusahaan->email }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Telepon</th>
                                    <td>{{ $dataPerusahaan->telepon }}</td>
                                </tr>
                                @if($dataPerusahaan->kode)
                                    <tr>
                                        <th class="bg-light">Kode Perusahaan</th>
                                        <td>
                                            <span class="badge bg-primary">{{ $dataPerusahaan->kode }}</span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Kode Perusahaan:</strong> Digunakan untuk login pegawai dan kasir
                                    </p>
                                    <p class="mb-2">
                                        <strong>Akses Edit:</strong> Hanya user dengan role Owner yang dapat mengubah data perusahaan
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Role yang Terhubung:</strong> Admin, Pegawai, Kasir
                                    </p>
                                    <p class="mb-2">
                                        <strong>Update Otomatis:</strong> Perubahan data akan langsung terupdate di seluruh sistem
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>

@endsection
