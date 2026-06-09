@extends('layouts.app')

@section('title', 'Edit Data Perusahaan')

@section('content')
<style>
    .theme-brown { color: #5C3D2E !important; }
    .theme-brown-light { color: #8A6B48 !important; }
    .bg-theme-brown { background-color: #5C3D2E !important; }
    .bg-theme-brown-light { background-color: #8A6B48 !important; }
    .btn-theme {
        background-color: #5C3D2E;
        color: white;
        border: none;
    }
    .btn-theme:hover {
        background-color: #4a3125;
        color: white;
    }
    .btn-theme-outline {
        color: #5C3D2E;
        border: 1px solid #5C3D2E;
        background-color: transparent;
    }
    .btn-theme-outline:hover {
        background-color: rgba(92, 61, 46, 0.05);
        color: #5C3D2E;
    }
    .form-control:focus {
        border-color: #8A6B48;
        box-shadow: 0 0 0 0.25rem rgba(138, 107, 72, 0.25);
    }
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #5C3D2E 0%, #8A6B48 100%); border-radius: 15px;">
                <div class="card-body p-4 text-white d-flex align-items-center">
                    <div class="bg-white p-3 rounded-circle shadow-sm me-4 d-flex align-items-center justify-content-center theme-brown" style="width: 60px; height: 60px;">
                        <i class="fas fa-building fa-xl"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 text-white fw-bold">Edit Data Perusahaan</h3>
                        <p class="mb-0 opacity-75"><i class="fas fa-edit me-2"></i>Perbarui informasi profil perusahaan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0" style="border-radius: 12px;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <form method="POST" action="/tentang-perusahaan">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="nama" class="form-label fw-bold theme-brown">Nama Perusahaan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-font text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="nama" name="nama" value="{{ old('nama', $dataPerusahaan->nama) }}" required placeholder="Masukkan nama perusahaan">
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="alamat" class="form-label fw-bold theme-brown">Alamat Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <textarea class="form-control border-start-0 ps-0" id="alamat" name="alamat" rows="3" required placeholder="Masukkan alamat lengkap">{{ old('alamat', $dataPerusahaan->alamat) }}</textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold theme-brown">Email Resmi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="{{ old('email', $dataPerusahaan->email) }}" required placeholder="email@perusahaan.com">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telepon" class="form-label fw-bold theme-brown">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone-alt text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="telepon" name="telepon" value="{{ old('telepon', $dataPerusahaan->telepon) }}" required placeholder="Contoh: 021-12345678">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 d-flex gap-2">
                            <button type="submit" class="btn btn-theme px-4 py-2 fw-bold" style="border-radius: 8px;">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="/tentang-perusahaan/detail" class="btn btn-theme-outline px-4 py-2 fw-bold" style="border-radius: 8px;">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px; background-color: #FAFAF8;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-white p-4 rounded-circle shadow-sm d-inline-block mb-3 theme-brown" style="width: 80px; height: 80px;">
                            <i class="fas fa-info-circle fa-2x mt-1"></i>
                        </div>
                        <h5 class="fw-bold theme-brown">Informasi Penting</h5>
                    </div>
                    
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Pastikan <strong>Nama Perusahaan</strong> diisi dengan benar karena akan tampil pada kop surat atau laporan PDF.</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span><strong>Alamat Lengkap</strong> akan digunakan sebagai referensi pengiriman dan kontak surat menyurat.</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span><strong>Email</strong> dan <strong>Telepon</strong> adalah saluran utama yang bisa dihubungi oleh pihak luar.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
