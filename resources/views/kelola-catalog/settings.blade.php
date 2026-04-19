@extends('layouts.app')

@section('title', 'Pengaturan Catalog')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Pengaturan Catalog
                        </h4>
                        <a href="{{ route('kelola-catalog.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Kelola Catalog
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('kelola-catalog.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Company Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-building me-2"></i>Informasi Perusahaan
                                </h5>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Company Photo -->
                            <div class="col-md-4">
                                <label class="form-label">Logo Perusahaan</label>
                                <div class="text-center mb-3">
                                    @if($company && $company->foto)
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ asset('storage/'.$company->foto) }}" 
                                                 alt="Company Logo" 
                                                 class="img-fluid rounded border"
                                                 style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                            <div class="position-absolute top-0 end-0">
                                                <span class="badge bg-success">Logo Saat Ini</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 200px; height: 200px; margin: 0 auto;">
                                            <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" 
                                       name="foto" 
                                       class="form-control" 
                                       accept="image/*"
                                       onchange="previewImage(event)">
                                <small class="text-muted">Format: JPG, PNG, GIF (Maks: 2MB)</small>
                            </div>

                            <!-- Company Details -->
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama Perusahaan *</label>
                                        <input type="text" 
                                               name="nama" 
                                               id="nama" 
                                               class="form-control" 
                                               value="{{ $company->nama ?? old('nama') }}" 
                                               required>
                                    </div>
                                    
                                                                        
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" 
                                               name="email" 
                                               id="email" 
                                               class="form-control" 
                                               value="{{ $company->email ?? old('email') }}" 
                                               required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label">Telepon *</label>
                                        <input type="text" 
                                               name="telepon" 
                                               id="telepon" 
                                               class="form-control" 
                                               value="{{ $company->telepon ?? old('telepon') }}" 
                                               required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat *</label>
                                        <textarea name="alamat" 
                                                  id="alamat" 
                                                  class="form-control" 
                                                  rows="3" 
                                                  required>{{ $company->alamat ?? old('alamat') }}</textarea>
                                    </div>
                                    
                                                                    </div>
                            </div>
                        </div>

                        <!-- Catalog Settings -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-store me-2"></i>Pengaturan Tampilan Catalog
                                </h5>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Catatan:</strong> Pengaturan ini akan mempengaruhi tampilan catalog publik yang dapat diakses oleh pelanggan.
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-eye me-2"></i>Preview Catalog
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <small class="text-muted">Preview Hero Section</small>
                                        </div>
                                        
                                        <!-- Mock Hero Slider -->
                                        <div class="border rounded p-3 mb-3 bg-white" style="min-height: 200px;">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="text-center">
                                                    @if($company && $company->foto)
                                                        <img src="{{ asset('storage/'.$company->foto) }}" 
                                                             alt="{{ $company->nama }}" 
                                                             class="img-fluid rounded mb-2"
                                                             style="max-width: 150px;">
                                                    @else
                                                        <div class="bg-light rounded d-inline-flex align-items-center justify-content-center mb-2" 
                                                             style="width: 150px; height: 150px;">
                                                            <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                                        </div>
                                                    @endif
                                                    <h6 class="mb-1">{{ $company->nama ?? 'Nama Perusahaan' }}</h6>
                                            <small class="text-muted">UMKM Produksi</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mock About Section -->
                                        <div class="border rounded p-3 bg-white">
                                            <div class="text-center mb-2">
                                                <small class="text-muted">Preview Tentang Perusahaan</small>
                                            </div>
                                            <h6 class="mb-2">Tentang {{ $company->nama ?? 'Nama Perusahaan' }}</h6>
                                            <p class="small text-muted">
                                                Perusahaan ini bergerak di bidang produksi makanan dengan komitmen menyediakan produk berkualitas tinggi.
                                            </p>
                                            <div class="small">
                                                <strong>Kontak:</strong><br>
                                                <i class="fas fa-envelope me-1"></i>{{ $company->email ?? 'email@perusahaan.com' }}<br>
                                                <i class="fas fa-phone me-1"></i>{{ $company->telepon ?? '081234567890' }}<br>
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $company->alamat ?? 'Alamat perusahaan' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('kelola-catalog.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </a>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('catalog') }}" target="_blank" class="btn btn-info">
                                            <i class="fas fa-external-link-alt me-1"></i>Lihat Catalog Publik
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Simpan Pengaturan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
}

.top-0 {
    top: 0;
}

.end-0 {
    right: 0;
}
</style>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Update preview in the current page
            const previewContainer = event.target.parentElement.querySelector('.text-center');
            if (previewContainer) {
                previewContainer.innerHTML = `
                    <div class="position-relative d-inline-block">
                        <img src="${e.target.result}" 
                             alt="Company Logo Preview" 
                             class="img-fluid rounded border"
                             style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0">
                            <span class="badge bg-warning text-dark">Preview</span>
                        </div>
                    </div>
                `;
            }
        };
        reader.readAsDataURL(file);
    }
}

// Auto-save draft functionality (optional)
let autoSaveTimer;
const form = document.querySelector('form');

form.addEventListener('input', function() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        // You could implement auto-save here if needed
        console.log('Auto-save draft...');
    }, 5000);
});

// Form validation
form.addEventListener('submit', function(e) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi.');
    }
});
</script>
@endsection
