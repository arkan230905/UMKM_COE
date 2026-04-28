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

                    <form method="POST" action="{{ route('kelola-catalog.settings.update') }}" enctype="multipart/form-data" id="companySettingsForm">
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
                        <div class="row mb-4 mt-5">
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-store me-2"></i>Pengaturan Tampilan Catalog
                                </h5>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="catalog_description" class="form-label">Deskripsi Catalog</label>
                                <textarea name="catalog_description" 
                                          id="catalog_description" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Deskripsi singkat tentang perusahaan yang akan ditampilkan di halaman catalog">{{ $company->catalog_description ?? '' }}</textarea>
                                <small class="text-muted">Deskripsi ini akan ditampilkan di bagian tentang perusahaan pada halaman catalog</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="maps_link" class="form-label">Link Google Maps</label>
                                <input type="url" 
                                       name="maps_link" 
                                       id="maps_link" 
                                       class="form-control" 
                                       value="{{ $company->maps_link ?? '' }}" 
                                       placeholder="https://maps.google.com/?q=alamat">
                                <small class="text-muted">Link Google Maps untuk lokasi perusahaan</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" 
                                       step="any" 
                                       name="latitude" 
                                       id="latitude" 
                                       class="form-control" 
                                       value="{{ $company->latitude ?? '' }}" 
                                       placeholder="-6.823456">
                                <small class="text-muted">Koordinat latitude</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" 
                                       step="any" 
                                       name="longitude" 
                                       id="longitude" 
                                       class="form-control" 
                                       value="{{ $company->longitude ?? '' }}" 
                                       placeholder="107.923456">
                                <small class="text-muted">Koordinat longitude</small>
                            </div>
                        </div>

                        <!-- Background Customization -->
                        <div class="row mb-4 mt-5">
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-palette me-2"></i>Kustomisasi Latar Belakang Catalog
                                </h5>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Pilih Tipe Latar Belakang</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="background_type" id="bg_color" value="color" 
                                           {{ (!$company->background_type || $company->background_type == 'color') ? 'checked' : '' }}
                                           onchange="toggleBackgroundType('color')">
                                    <label class="btn btn-outline-primary" for="bg_color">
                                        <i class="fas fa-fill-drip me-2"></i>Warna Solid
                                    </label>

                                    <input type="radio" class="btn-check" name="background_type" id="bg_gradient" value="gradient"
                                           {{ $company->background_type == 'gradient' ? 'checked' : '' }}
                                           onchange="toggleBackgroundType('gradient')">
                                    <label class="btn btn-outline-primary" for="bg_gradient">
                                        <i class="fas fa-paint-brush me-2"></i>Gradient
                                    </label>

                                    <input type="radio" class="btn-check" name="background_type" id="bg_image" value="image"
                                           {{ $company->background_type == 'image' ? 'checked' : '' }}
                                           onchange="toggleBackgroundType('image')">
                                    <label class="btn btn-outline-primary" for="bg_image">
                                        <i class="fas fa-image me-2"></i>Gambar
                                    </label>
                                </div>
                            </div>

                            <!-- Color Background -->
                            <div class="col-md-12" id="color_section" style="display: {{ (!$company->background_type || $company->background_type == 'color') ? 'block' : 'none' }};">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <label for="background_color" class="form-label">Pilih Warna Latar Belakang</label>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <input type="color" 
                                                       name="background_color" 
                                                       id="background_color" 
                                                       class="form-control form-control-color w-100" 
                                                       value="{{ $company->background_color ?? '#ffffff' }}"
                                                       style="height: 60px;">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="button" class="btn btn-sm" style="background-color: #ffffff; border: 2px solid #dee2e6;" onclick="setColor('#ffffff')">Putih</button>
                                                    <button type="button" class="btn btn-sm" style="background-color: #f8f9fa; border: 2px solid #dee2e6;" onclick="setColor('#f8f9fa')">Abu Terang</button>
                                                    <button type="button" class="btn btn-sm" style="background-color: #e9ecef; border: 2px solid #dee2e6;" onclick="setColor('#e9ecef')">Abu</button>
                                                    <button type="button" class="btn btn-sm" style="background-color: #fef3c7; border: 2px solid #fbbf24;" onclick="setColor('#fef3c7')">Krem</button>
                                                    <button type="button" class="btn btn-sm" style="background-color: #dbeafe; border: 2px solid #3b82f6;" onclick="setColor('#dbeafe')">Biru Muda</button>
                                                    <button type="button" class="btn btn-sm" style="background-color: #dcfce7; border: 2px solid #22c55e;" onclick="setColor('#dcfce7')">Hijau Muda</button>
                                                    <button type="button" class="btn btn-sm" style="background-color: #fce7f3; border: 2px solid #ec4899;" onclick="setColor('#fce7f3')">Pink Muda</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gradient Background -->
                            <div class="col-md-12" id="gradient_section" style="display: {{ $company->background_type == 'gradient' ? 'block' : 'none' }};">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <label class="form-label">Pilih Warna Gradient</label>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="gradient_color_1" class="form-label small">Warna 1</label>
                                                <input type="color" 
                                                       name="gradient_color_1" 
                                                       id="gradient_color_1" 
                                                       class="form-control form-control-color w-100" 
                                                       value="{{ $company->gradient_color_1 ?? '#667eea' }}"
                                                       style="height: 60px;">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="gradient_color_2" class="form-label small">Warna 2</label>
                                                <input type="color" 
                                                       name="gradient_color_2" 
                                                       id="gradient_color_2" 
                                                       class="form-control form-control-color w-100" 
                                                       value="{{ $company->gradient_color_2 ?? '#764ba2' }}"
                                                       style="height: 60px;">
                                            </div>
                                            <div class="col-md-12">
                                                <label for="gradient_direction" class="form-label small">Arah Gradient</label>
                                                <select name="gradient_direction" id="gradient_direction" class="form-select">
                                                    <option value="to right" {{ ($company->gradient_direction ?? '') == 'to right' ? 'selected' : '' }}>Kiri ke Kanan</option>
                                                    <option value="to left" {{ ($company->gradient_direction ?? '') == 'to left' ? 'selected' : '' }}>Kanan ke Kiri</option>
                                                    <option value="to bottom" {{ ($company->gradient_direction ?? '') == 'to bottom' ? 'selected' : '' }}>Atas ke Bawah</option>
                                                    <option value="to top" {{ ($company->gradient_direction ?? '') == 'to top' ? 'selected' : '' }}>Bawah ke Atas</option>
                                                    <option value="to bottom right" {{ ($company->gradient_direction ?? '') == 'to bottom right' ? 'selected' : '' }}>Diagonal (Kiri Atas ke Kanan Bawah)</option>
                                                    <option value="to bottom left" {{ ($company->gradient_direction ?? '') == 'to bottom left' ? 'selected' : '' }}>Diagonal (Kanan Atas ke Kiri Bawah)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">Preset Gradient:</small>
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                <button type="button" class="btn btn-sm" style="background: linear-gradient(to right, #667eea, #764ba2); color: white;" onclick="setGradient('#667eea', '#764ba2')">Purple</button>
                                                <button type="button" class="btn btn-sm" style="background: linear-gradient(to right, #f093fb, #f5576c); color: white;" onclick="setGradient('#f093fb', '#f5576c')">Pink</button>
                                                <button type="button" class="btn btn-sm" style="background: linear-gradient(to right, #4facfe, #00f2fe); color: white;" onclick="setGradient('#4facfe', '#00f2fe')">Blue</button>
                                                <button type="button" class="btn btn-sm" style="background: linear-gradient(to right, #43e97b, #38f9d7); color: white;" onclick="setGradient('#43e97b', '#38f9d7')">Green</button>
                                                <button type="button" class="btn btn-sm" style="background: linear-gradient(to right, #fa709a, #fee140); color: white;" onclick="setGradient('#fa709a', '#fee140')">Sunset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Background -->
                            <div class="col-md-12" id="image_section" style="display: {{ $company->background_type == 'image' ? 'block' : 'none' }};">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <label for="background_image" class="form-label">Upload Gambar Latar Belakang</label>
                                        @if($company && $company->background_image)
                                            <div class="mb-3">
                                                <img src="{{ asset('storage/'.$company->background_image) }}" 
                                                     alt="Background" 
                                                     class="img-fluid rounded border"
                                                     style="max-height: 200px; object-fit: cover;">
                                                <div class="mt-2">
                                                    <span class="badge bg-success">Gambar Saat Ini</span>
                                                </div>
                                            </div>
                                        @endif
                                        <input type="file" 
                                               name="background_image" 
                                               id="background_image" 
                                               class="form-control" 
                                               accept="image/*"
                                               onchange="previewBackgroundImage(event)">
                                        <small class="text-muted">Format: JPG, PNG (Maks: 5MB). Gambar akan digunakan sebagai latar belakang catalog.</small>
                                        
                                        <div class="mt-3">
                                            <label for="background_opacity" class="form-label small">Transparansi Overlay ({{ $company->background_opacity ?? 50 }}%)</label>
                                            <input type="range" 
                                                   name="background_opacity" 
                                                   id="background_opacity" 
                                                   class="form-range" 
                                                   min="0" 
                                                   max="100" 
                                                   value="{{ $company->background_opacity ?? 50 }}"
                                                   oninput="document.getElementById('opacity_value').textContent = this.value + '%'">
                                            <small class="text-muted">Overlay gelap untuk meningkatkan keterbacaan teks. <span id="opacity_value">{{ $company->background_opacity ?? 50 }}%</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Background -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <label class="form-label">Preview Latar Belakang</label>
                                        <div id="background_preview" class="border rounded p-4 text-center" style="min-height: 200px; position: relative; overflow: hidden;">
                                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;" id="preview_bg"></div>
                                            <div style="position: relative; z-index: 2;">
                                                <h4 class="text-white mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">{{ $company->nama ?? 'Nama Perusahaan' }}</h4>
                                                <p class="text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Contoh tampilan catalog dengan latar belakang yang dipilih</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Catatan:</strong> Pengaturan ini akan mempengaruhi tampilan catalog publik yang dapat diakses oleh pelanggan.
                                    <br>Foto catalog dapat dikelola di halaman <a href="{{ route('kelola-catalog.photos') }}" class="alert-link">Kelola Foto Catalog</a>.
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mb-4 mt-5">
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-eye me-2"></i>Preview Catalog
                                </h5>
                            </div>
                        </div>

                        <div class="row mb-5">
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
                                                {{ $company->catalog_description ?? 'Deskripsi perusahaan akan ditampilkan di sini.' }}
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

                        <!-- Action Button - Moved to Bottom -->
                        <div class="row mt-5 pt-4 border-top">
                            <div class="col-md-12">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg py-3" style="font-size: 1.1rem;">
                                        Update Semua Perubahan
                                    </button>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Tombol ini akan menyimpan semua perubahan yang Anda buat di halaman ini</small>
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
// Preview image for company logo
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
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

// Preview background image
function previewBackgroundImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            updateBackgroundPreview();
        };
        reader.readAsDataURL(file);
    }
}

// Toggle background type
function toggleBackgroundType(type) {
    document.getElementById('color_section').style.display = type === 'color' ? 'block' : 'none';
    document.getElementById('gradient_section').style.display = type === 'gradient' ? 'block' : 'none';
    document.getElementById('image_section').style.display = type === 'image' ? 'block' : 'none';
    updateBackgroundPreview();
}

// Set color preset
function setColor(color) {
    document.getElementById('background_color').value = color;
    updateBackgroundPreview();
}

// Set gradient preset
function setGradient(color1, color2) {
    document.getElementById('gradient_color_1').value = color1;
    document.getElementById('gradient_color_2').value = color2;
    updateBackgroundPreview();
}

// Update background preview
function updateBackgroundPreview() {
    const previewBg = document.getElementById('preview_bg');
    const bgType = document.querySelector('input[name="background_type"]:checked').value;
    
    if (bgType === 'color') {
        const color = document.getElementById('background_color').value;
        previewBg.style.background = color;
    } else if (bgType === 'gradient') {
        const color1 = document.getElementById('gradient_color_1').value;
        const color2 = document.getElementById('gradient_color_2').value;
        const direction = document.getElementById('gradient_direction').value;
        previewBg.style.background = `linear-gradient(${direction}, ${color1}, ${color2})`;
    } else if (bgType === 'image') {
        const fileInput = document.getElementById('background_image');
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const opacity = document.getElementById('background_opacity').value;
                previewBg.style.backgroundImage = `linear-gradient(rgba(0,0,0,${opacity/100}), rgba(0,0,0,${opacity/100})), url(${e.target.result})`;
                previewBg.style.backgroundSize = 'cover';
                previewBg.style.backgroundPosition = 'center';
            };
            reader.readAsDataURL(fileInput.files[0]);
        } else {
            // Use existing image if available
            @if($company && $company->background_image)
                const opacity = document.getElementById('background_opacity').value;
                previewBg.style.backgroundImage = `linear-gradient(rgba(0,0,0,${opacity/100}), rgba(0,0,0,${opacity/100})), url({{ asset('storage/'.$company->background_image) }})`;
                previewBg.style.backgroundSize = 'cover';
                previewBg.style.backgroundPosition = 'center';
            @endif
        }
    }
}

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBackgroundPreview();
    
    // Update preview when color changes
    document.getElementById('background_color').addEventListener('input', updateBackgroundPreview);
    document.getElementById('gradient_color_1').addEventListener('input', updateBackgroundPreview);
    document.getElementById('gradient_color_2').addEventListener('input', updateBackgroundPreview);
    document.getElementById('gradient_direction').addEventListener('change', updateBackgroundPreview);
    document.getElementById('background_opacity').addEventListener('input', updateBackgroundPreview);
});

// Form submission
const form = document.getElementById('companySettingsForm');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    
    // Create FormData
    const formData = new FormData(form);
    
    // Submit form
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            form.insertBefore(alertDiv, form.firstChild);
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Reload after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            ${error.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        form.insertBefore(alertDiv, form.firstChild);
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
@endsection
