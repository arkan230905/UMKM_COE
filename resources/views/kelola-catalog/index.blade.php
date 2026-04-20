@extends('layouts.app')

@section('title', 'Kelola Catalog')

@section('content')
<!-- Edit Mode Indicator -->
<div class="edit-mode-indicator">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-edit me-2"></i>
                <span><strong>Mode Edit Catalog</strong> - Edit langsung setiap elemen</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('catalog') }}" target="_blank" class="btn btn-success btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>Lihat Publik
                </a>
                <a href="{{ route('kelola-catalog.photos') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-images me-1"></i>Kelola Foto
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- ================= HERO SLIDER ================= -->
<section class="hero-slider editable-section" data-section="hero">
    <div class="edit-fab" onclick="togglePhotoControls()">
        <i class="fas fa-images"></i>
    </div>
    
    <!-- Photo Management Controls -->
    <div id="photoControls" class="photo-controls d-none">
        <div class="container">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSelectedPhoto()">
                                <i class="fas fa-trash me-1"></i>Hapus Foto
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="addNewPhoto()">
                                <i class="fas fa-plus me-1"></i>Tambah Foto
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <small class="text-muted">Pilih foto untuk dihapus atau tambah foto baru</small>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="togglePhotoControls()">
                                <i class="fas fa-times me-1"></i>Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="slider-container-full">
        <div class="slider-wrapper">
            @forelse($catalogPhotos as $index => $photo)
            <div class="slide {{ $index == 0 ? 'active' : '' }} {{ $index == 0 ? 'selected' : '' }}" data-photo-id="{{ $photo->id }}" onclick="selectPhoto({{ $photo->id }})">
                <img src="{{ asset('storage/'.$photo->foto) }}" alt="{{ $photo->judul ?: 'Foto Catalog' }}">
                <div class="photo-selection-indicator">
                    <i class="fas fa-check-circle"></i>
                </div>
                @if($photo->judul || $photo->deskripsi)
                <div class="slide-overlay">
                    <div class="slide-content">
                        @if($photo->judul)
                        <h2>{{ $photo->judul }}</h2>
                        @endif
                        @if($photo->deskripsi)
                        <p>{{ $photo->deskripsi }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @empty
            <!-- Fallback to product photos if no catalog photos -->
            @forelse($produks->take(3) as $index => $produk)
            <div class="slide {{ $index == 0 ? 'active' : '' }} {{ $index == 0 ? 'selected' : '' }}" data-product-photo="{{ $produk->id }}" onclick="selectProductPhoto({{ $produk->id }})">
                @if($produk->foto)
                    <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama_produk }}">
                @else
                    <img src="/images/no-image.png" alt="{{ $produk->nama_produk }}">
                @endif
                <div class="photo-selection-indicator">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            @empty
            <!-- Default fallback images -->
            <div class="slide active selected" data-default-photo="1" onclick="selectDefaultPhoto(1)">
                <img src="/images/umkm-default-1.jpg" alt="UMKM Produk">
                <div class="photo-selection-indicator">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="slide" data-default-photo="2" onclick="selectDefaultPhoto(2)">
                <img src="/images/umkm-default-2.jpg" alt="UMKM Produk">
                <div class="photo-selection-indicator">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="slide" data-default-photo="3" onclick="selectDefaultPhoto(3)">
                <img src="/images/umkm-default-3.jpg" alt="UMKM Produk">
                <div class="photo-selection-indicator">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            @endforelse
            @endforelse
        </div>
        
        <!-- Slider Controls -->
        <button class="slider-btn prev-btn" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-btn next-btn" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <!-- Slider Indicators -->
        <div class="slider-indicators">
            @php
                $totalSlides = $catalogPhotos->count() > 0 ? $catalogPhotos->count() : max($produks->count(), 3);
            @endphp
            @for($i = 0; $i < $totalSlides; $i++)
            <span class="indicator {{ $i == 0 ? 'active' : '' }}" onclick="goToSlide({{ $i }})"></span>
            @endfor
        </div>
    </div>
</section>

<!-- ================= TENTANG PERUSAHAAN ================= -->
@if($company)
<section class="section-white editable-section" data-section="company">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        <div class="company-logo-section position-relative">
                            @if($company->foto)
                                <img src="{{ asset('storage/'.$company->foto) }}" alt="Logo {{ $company->nama }}" class="img-fluid rounded-3 shadow">
                            @else
                                <img src="/images/company-default.jpg" alt="Logo {{ $company->nama }}" class="img-fluid rounded-3 shadow">
                            @endif
                            <div class="logo-edit-controls">
                                <button type="button" class="btn btn-sm btn-primary" onclick="editCompanyLogo()">
                                    <i class="fas fa-camera"></i> Ganti Logo
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="desa-content">
                            <div class="company-info-section">
                            <!-- Judul Section -->
                            <div class="company-title-section mb-4">
                                <label class="form-label fw-bold">Judul Perusahaan</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="companyTitle" 
                                       value="Tentang {{ $company->nama }}"
                                       placeholder="Masukkan judul perusahaan">
                            </div>
                            
                            <!-- Deskripsi Section -->
                            <div class="company-description-section mb-4">
                                <label class="form-label fw-bold">Deskripsi Perusahaan</label>
                                <!-- DEBUG: Current time: {{ now() }} -->
                                <style>
                                    #companyDescription {
                                        height: 750px !important;
                                        min-height: 750px !important;
                                        width: 100% !important;
                                        resize: vertical !important;
                                        font-size: 16px !important;
                                        border: 2px solid black !important;
                                        background-color: white !important;
                                    }
                                </style>
                                <textarea class="form-control" 
                                          id="companyDescription" 
                                          rows="60" 
                                          style="height: 450px !important; min-height: 450px !important; width: 100% !important; resize: vertical !important; font-size:16px !important; border: 2px solid black !important; background-color: white !important;" 
                                          placeholder="Deskripsikan perusahaan Anda...">@if($company->catalog_description)
{{ $company->catalog_description }}
@else
{{ $company->nama }} adalah sebuah UMKM yang bergerak di bidang {{ $company->jenis_usaha ?? 'produksi makanan' }}, terletak di {{ $company->alamat }}. Perusahaan ini berkomitmen untuk menyediakan produk berkualitas tinggi dengan bahan baku pilihan dan proses produksi yang higienis.

Dengan pengalaman dalam industri {{ $company->jenis_usaha ?? 'makanan' }}, {{ $company->nama }} terus berinovasi untuk menghadirkan produk terbaik bagi konsumen. Kami menjunjung tinggi nilai-nilai kualitas, kebersihan, dan kepuasan pelanggan dalam setiap produk yang kami hasilkan.
@endif</textarea>
                            </div>
                            
                            <!-- Kontak Section -->
                            <div class="company-contact-section mb-4">
                                <label class="form-label fw-bold">Informasi Kontak</label>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" 
                                                   class="form-control form-control-lg" 
                                                   id="companyEmail" 
                                                   value="{{ $company->email }}"
                                                   placeholder="email@perusahaan.com">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small">Telepon</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" 
                                                   class="form-control form-control-lg" 
                                                   id="companyPhone" 
                                                   value="{{ $company->telepon }}"
                                                   placeholder="0812-3456-7890">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small">Alamat</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <input type="text" 
                                                   class="form-control form-control-lg" 
                                                   id="companyAddress" 
                                                   value="{{ $company->alamat }}"
                                                   placeholder="Jl. Contoh No. 123">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Update Form -->
                            <div class="company-update-section">
                                <form action="{{ route('kelola-catalog.settings.company.update') }}" method="POST" id="companyUpdateForm">
                                    @csrf
                                    <input type="hidden" name="nama" id="formNama" value="">
                                    <input type="hidden" name="catalog_description" id="formDescription" value="">
                                    <input type="hidden" name="email" id="formEmail" value="">
                                    <input type="hidden" name="telepon" id="formPhone" value="">
                                    <input type="hidden" name="alamat" id="formAddress" value="">
                                    
                                    <button type="submit" 
                                            class="btn btn-primary btn-lg px-4" 
                                            onclick="populateFormAndSubmit(event)">
                                        <i class="fas fa-save me-2"></i>Update Semua Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= PETA LOKASI ================= -->
@if($company)
<section class="section-white editable-section" data-section="maps">
    <div class="edit-fab" onclick="editMaps()">
        <i class="fas fa-map-marker-alt"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">Lokasi {{ $company->nama }}</h2>
                <div class="map-container">
                    <div class="map-wrapper">
                        @if($company->maps_link)
                        <!-- Use custom maps link if provided -->
                        <iframe 
                            src="{{ $company->maps_link }}"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        @elseif($company->latitude && $company->longitude)
                        <!-- Use coordinates if available -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!2d{{ $company->longitude }}!3d{{ $company->latitude }}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sen!2sid!4v1234567890123"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        @else
                        <!-- Fallback to address search -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!2d107.9234567890123!3d-6.8234567890123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sen!2sid!4v1234567890123"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        @endif
                    </div>
                    <div class="map-info mt-3">
                        <p class="text-center mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <strong>{{ $company->nama }}</strong>, {{ $company->alamat }}
                        </p>
                        <p class="text-center mt-2">
                            @if($company->maps_link)
                            <a href="{{ $company->maps_link }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                            </a>
                            @else
                            <a href="https://maps.google.com/?q={{ urlencode($company->alamat) }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                            </a>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= PRODUK UMKM ================= -->
@if($company)
<section class="section-white editable-section" data-section="products">
    <div class="edit-fab" onclick="toggleProductEditMode()">
        <i class="fas fa-box"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">PRODUK {{ strtoupper($company->nama) }}</h2>
                    <div class="btn-group">
                        <a href="{{ route('master-data.produk.print-barcode-all') }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-barcode"></i> Cetak Semua Barcode
                        </a>
                        <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </a>
                    </div>
                </div>
                
                <!-- Product Edit Mode Controls -->
                <div id="productEditControls" class="d-none mb-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-success" onclick="selectAllProducts()">
                                        <i class="fas fa-check-square me-1"></i>Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="showSelectedProducts()">
                                        <i class="fas fa-eye me-1"></i>Tampilkan
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="hideSelectedProducts()">
                                        <i class="fas fa-eye-slash me-1"></i>Sembunyikan
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleProductEditMode()">
                                    <i class="fas fa-times me-1"></i>Tutup Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="scroll-hint">Geser tabel ke kiri/kanan untuk melihat semua kolom</div>
                        <div class="table-scroll-wrapper">
                            <table class="table table-bordered table-hover" id="dataTable" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Foto</th>
                                        <th>Barcode</th>
                                        <th>Nama Produk</th>
                                        <th>Deskripsi</th>
                                        <th class="text-right">Harga Pokok Produksi</th>
                                        <th class="text-right">Harga Jual</th>
                                        <th class="text-center">Stok</th>
                                        <th width="8%" class="text-center">Catalog</th>
                                        <th width="15%" class="text-center">Kelola Foto</th>
                                        <th width="12%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($produks as $produk)
                                        @php
                                            $hargaBomProduk = $produk->hpp_calculated ?? 0;
                                            $hargaJual = $produk->harga_jual ?? 0;
                                            $stok = (float) $produk->stok;
                                        @endphp
                                        <tr class="{{ $produk->show_in_catalog ? '' : 'table-secondary' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-center">
                                                @if($produk->foto)
                                                    <div class="product-image-wrapper" 
                                                         onclick="showImageModal('{{ asset('storage/'.$produk->foto) }}', '{{ addslashes($produk->nama_produk) }}')"
                                                         style="width: 35px !important; height: 35px !important; cursor: pointer; position: relative; display: inline-block;">
                                                    <img src="{{ asset('storage/'.$produk->foto) }}" 
                                                         alt="{{ $produk->nama_produk }}" 
                                                         class="product-thumbnail"
                                                         style="width: 35px !important; height: 35px !important; object-fit: cover; border-radius: 4px;"
                                                         onerror="this.onerror=null; this.src='/images/no-image.png';">
                                                    <div class="image-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; border-radius: 4px;">
                                                        <i class="fas fa-search-plus" style="color: white; font-size: 14px;"></i>
                                                    </div>
                                                    </div>
                                                @else
                                                    <div class="no-image-placeholder" style="width: 35px !important; height: 35px !important;">
                                                        <i class="fas fa-image" style="font-size: 12px;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="barcode-cell text-center">
                                                @if($produk->barcode)
                                                    <div class="barcode-wrapper">
                                                        <svg class="barcode-svg" data-barcode="{{ $produk->barcode }}"></svg>
                                                        <div class="barcode-number">{{ $produk->barcode }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $produk->nama_produk }}</td>
                                            <td>{{ $produk->deskripsi ? \Illuminate\Support\Str::limit($produk->deskripsi, 50) : '-' }}</td>
                                            <td class="text-right">Rp {{ number_format($hargaBomProduk, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold">Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                                            <td class="text-center {{ $stok <= 0 ? 'text-danger font-weight-bold' : '' }}">
                                                {{ number_format($stok, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="catalog_{{ $produk->id }}"
                                                           {{ $produk->show_in_catalog ? 'checked' : '' }}
                                                           onchange="toggleCatalogVisibility({{ $produk->id }}, this.checked)">
                                                    <label class="form-check-label" for="catalog_{{ $produk->id }}">
                                                        <small>{{ $produk->show_in_catalog ? 'Ya' : 'Tidak' }}</small>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="photo-management-cell">
                                                    <!-- Current Photo Display -->
                                                    <div class="current-photo-display mb-2">
                                                        @if($produk->foto)
                                                            <img src="{{ asset('storage/'.$produk->foto) }}" 
                                                                 alt="{{ $produk->nama_produk }}" 
                                                                 class="current-photo-thumb"
                                                                 onclick="showImageModal('{{ asset('storage/'.$produk->foto) }}', '{{ addslashes($produk->nama_produk) }}')"
                                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid #ddd;">
                                                        @else
                                                            <div class="no-photo-placeholder" 
                                                                 style="width: 50px; height: 50px; background: #f8f9fa; border: 2px dashed #ddd; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-image text-muted" style="font-size: 20px;"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Photo Management Buttons -->
                                                    <div class="photo-management-buttons d-flex gap-1 justify-content-center">
                                                        @if($produk->foto)
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    onclick="deleteProductPhoto({{ $produk->id }})"
                                                                    data-bs-toggle="tooltip" 
                                                                    title="Hapus Foto">
                                                                <i class="fas fa-trash" style="font-size: 10px;"></i>
                                                            </button>
                                                        @endif
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                onclick="changeProductPhoto({{ $produk->id }})"
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $produk->foto ? 'Ganti Foto' : 'Tambah Foto' }}">
                                                            <i class="fas fa-{{ $produk->foto ? 'edit' : 'plus' }}" style="font-size: 10px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    @if($produk->barcode)
                                                    <a href="{{ route('master-data.produk.print-barcode', $produk->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Cetak Label Barcode"
                                                       target="_blank">
                                                        <i class="fas fa-barcode"></i>
                                                    </a>
                                                    @endif
                                                    <a href="{{ route('master-data.produk.edit', $produk->id) }}" 
                                                       class="btn btn-sm btn-warning" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('master-data.produk.destroy', $produk->id) }}" 
                                                          method="POST" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">
                                                <p class="text-muted mb-2">Belum ada produk tersedia</p>
                                                <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus me-1"></i>Tambah Produk Pertama
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= TOMBOL BELI ================= -->
<section class="section-white editable-section" data-section="action">
    <div class="edit-fab" onclick="editBuyButton()">
        <i class="fas fa-shopping-cart"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <button class="btn-beli" onclick="window.location.href='/pelanggan/login'">
                    klik disini untuk membeli
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Edit Modals -->
<div class="modal fade" id="editCompanyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Informasi Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCompanyForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="nama" class="form-control" value="{{ $company->nama ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $company->email ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="{{ $company->telepon ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo Perusahaan</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah logo</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" required>{{ $company->alamat ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi Catalog</label>
                            <textarea name="catalog_description" class="form-control" rows="4" placeholder="Deskripsi singkat tentang perusahaan">{{ $company->catalog_description ?? '' }}</textarea>
                            <small class="text-muted">Deskripsi ini akan ditampilkan di halaman catalog</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editMapsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Peta Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMapsForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" name="maps_link" class="form-control" value="{{ $company->maps_link ?? '' }}" 
                               placeholder="https://maps.google.com/?q=alamat">
                        <small class="text-muted">Link embed Google Maps untuk lokasi perusahaan</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control" 
                                   value="{{ $company->latitude ?? '' }}" placeholder="-6.823456">
                            <small class="text-muted">Koordinat latitude (opsional)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control" 
                                   value="{{ $company->longitude ?? '' }}" placeholder="107.923456">
                            <small class="text-muted">Koordinat longitude (opsional)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Foto Catalog</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPhotoForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Foto</label>
                        <input type="text" name="judul" class="form-control" placeholder="Judul foto (opsional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto *</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG, GIF (Maks: 4MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi foto (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kelola Foto Catalog -->
<div class="modal fade" id="kelolaFotoModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-images me-2"></i>Kelola Foto Catalog
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Upload Area -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-upload me-2"></i>Upload Foto Baru
                                </h6>
                                <form id="uploadFotoForm" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Pilih Foto</label>
                                            <input type="file" name="foto" class="form-control" accept="image/*" required>
                                            <small class="text-muted">Format: JPG, PNG, GIF (Maks: 4MB)</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Judul (Opsional)</label>
                                            <input type="text" name="judul" class="form-control" placeholder="Masukkan judul foto">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Deskripsi (Opsional)</label>
                                            <input type="text" name="deskripsi" class="form-control" placeholder="Masukkan deskripsi foto">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-1"></i>Upload Foto
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Foto Gallery -->
                <div class="row">
                    <div class="col-12">
                        <h6><i class="fas fa-images me-2"></i>Foto Catalog Saat Ini</h6>
                        <div class="row" id="fotoGallery">
                            @forelse($catalogPhotos as $photo)
                            <div class="col-md-4 col-lg-3 mb-3" data-photo-id="{{ $photo->id }}">
                                <div class="card">
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/'.$photo->foto) }}" 
                                             alt="{{ $photo->judul ?: 'Foto Catalog' }}" 
                                             class="card-img-top" 
                                             style="height: 200px; object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deleteFotoFromModal({{ $photo->id }})"
                                                    title="Hapus Foto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <h6 class="card-title small mb-1">{{ $photo->judul ?: 'Tanpa Judul' }}</h6>
                                        <p class="card-text small text-muted mb-0">
                                            {{ $photo->deskripsi ? \Illuminate\Support\Str::limit($photo->deskripsi, 50) : 'Tanpa deskripsi' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12" id="noPhotosMessage">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-images fa-3x mb-3"></i>
                                    <p>Belum ada foto catalog. Upload foto pertama Anda!</p>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Table Styles (same as master-data produk) */
.card-body {
    padding: 1rem;
}

.table-scroll-wrapper {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar {
    height: 10px;
}

.table-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

/* Photo Management Cell Styles */
.photo-management-cell {
    min-width: 120px;
    padding: 8px;
}

.current-photo-display {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 8px;
}

.current-photo-thumb {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.current-photo-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.no-photo-placeholder {
    transition: all 0.3s ease;
}

.no-photo-placeholder:hover {
    background: #e9ecef !important;
    border-color: #adb5bd !important;
}

.photo-management-buttons {
    gap: 4px;
}

.photo-management-buttons .btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.photo-management-buttons .btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

#dataTable {
    min-width: 1400px !important;
    width: 100%;
    margin-bottom: 0;
}

#dataTable th, #dataTable td {
    white-space: nowrap;
    vertical-align: middle;
    padding: 0.5rem 0.75rem;
}

.scroll-hint {
    text-align: center;
    padding: 5px;
    background: #e9ecef;
    color: #666;
    font-size: 12px;
    border-radius: 0 0 4px 4px;
}

.product-image-wrapper:hover .image-overlay {
    opacity: 1;
}

.no-image-placeholder {
    width: 35px !important;
    height: 35px !important;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.barcode-wrapper {
    text-align: center;
}

.barcode-svg {
    width: 80px;
    height: 20px;
}

.barcode-number {
    font-size: 10px;
    color: #6c757d;
    margin-top: 2px;
}

/* Company Edit Controls */
.company-logo-section {
    position: relative;
}

.logo-edit-controls {
    position: absolute;
    bottom: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.company-logo-section:hover .logo-edit-controls {
    opacity: 1;
}

.company-title-section {
    position: relative;
}

.title-edit-controls {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.company-title-section:hover .title-edit-controls {
    opacity: 1;
}

.company-description-section {
    position: relative;
}

.description-edit-controls {
    position: absolute;
    bottom: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.company-description-section:hover .description-edit-controls {
    opacity: 1;
}

/* Photo Controls */
.photo-controls {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 1rem 0;
    margin-bottom: 1rem;
    border-radius: 0 0 15px 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.photo-selection-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    background: rgba(76, 175, 80, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    z-index: 10;
}

.slide.selected .photo-selection-indicator {
    opacity: 1;
    transform: scale(1);
}

.slide {
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
}

.slide:hover {
    transform: scale(1.02);
}

.slide.selected {
    border: 3px solid #4CAF50;
    box-shadow: 0 0 20px rgba(76, 175, 80, 0.5);
}

/* Edit Mode Indicator */
.edit-mode-indicator {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Catalog Styles (same as original catalog) */
.hero-slider {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.slider-container-full {
    position: relative;
    width: 100%;
    height: 70vh;
    min-height: 500px;
    overflow: hidden;
}

.slider-wrapper {
    display: flex;
    height: 100%;
    transition: transform 0.5s ease-in-out;
}

.slide {
    min-width: 100%;
    height: 100%;
    position: relative;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slide-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
    padding: 2rem;
}

.slide-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.slide-content p {
    font-size: 1.2rem;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    border: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #3a3a3a;
    transition: all 0.3s;
    z-index: 10;
}

.slider-btn:hover {
    background: rgba(255,255,255,1);
    transform: translateY(-50%) scale(1.1);
}

.prev-btn {
    left: 30px;
}

.next-btn {
    right: 30px;
}

.slider-indicators {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    z-index: 10;
}

.indicator {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s;
}

.indicator.active {
    background: #ffc107;
    width: 40px;
    border-radius: 8px;
}

.section-soft {
    background: #f7f4ef;
    padding: 80px 0;
}
.section-white {
    background: #ffffff;
    padding: 80px 0;
}
.section-title {
    font-weight: 500;
    color: #3a3a3a;
}

.desa-content {
    padding-left: 2rem;
    padding-right: 0;
}

.desa-description {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
    margin-bottom: 0.5rem;
    text-align: justify;
}

.desa-content .section-title {
    color: #3a3a3a;
    font-size: 2.5rem;
    font-weight: 800;
}

.section-white img {
    max-height: 450px;
    width: 100%;
    object-fit: cover;
}

.map-container {
    margin-top: 2rem;
}

.map-wrapper {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.map-wrapper iframe {
    width: 100%;
    height: 450px;
    border: none;
}

.map-info {
    text-align: center;
    color: #555;
}

.btn-beli {
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: #3a3a3a;
    border: none;
    padding: 15px 40px;
    font-size: 1.2rem;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-beli:hover {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
}

.btn-beli:active {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .slider-container-full {
        height: 60vh;
        min-height: 400px;
    }
    
    .slider-btn {
        width: 45px;
        height: 45px;
        font-size: 16px;
    }
    
    .prev-btn {
        left: 15px;
    }
    
    .next-btn {
        right: 15px;
    }
    
    .slider-indicators {
        bottom: 20px;
        gap: 10px;
    }
    
    .indicator {
        width: 10px;
        height: 10px;
    }
    
    .indicator.active {
        width: 25px;
    }
    
    .desa-content {
        padding-left: 1rem;
        padding-right: 0;
        margin-top: 1rem;
    }
    
    .desa-description {
        font-size: 1rem;
        line-height: 1.6;
    }
    
    .desa-content .section-title {
        font-size: 1.8rem;
        text-align: center;
    }
    
    .section-white img {
        max-height: 400px;
    }
    
    .edit-fab {
        width: 56px;
        height: 56px;
        font-size: 20px;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: 3px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    .edit-fab:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    }
}
</style>

<script>
// Slider functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const indicators = document.querySelectorAll('.indicator');
const totalSlides = slides.length;

function showSlide(index) {
    if (slides.length === 0) return;
    
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));
    
    slides[index].classList.add('active');
    indicators[index].classList.add('active');
    
    const sliderWrapper = document.querySelector('.slider-wrapper');
    sliderWrapper.style.transform = `translateX(-${index * 100}%)`;
}

function changeSlide(direction) {
    if (totalSlides === 0) return;
    
    currentSlide += direction;
    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    showSlide(currentSlide);
}

function goToSlide(index) {
    if (totalSlides === 0) return;
    currentSlide = index;
    showSlide(currentSlide);
}

// Auto-slide functionality
function startAutoSlide() {
    if (totalSlides === 0) return;
    
    setInterval(() => {
        changeSlide(1);
    }, 4000);
}

// Initialize slider
document.addEventListener('DOMContentLoaded', function() {
    if (slides.length > 0) {
        showSlide(0);
        startAutoSlide();
        
        const sliderContainer = document.querySelector('.slider-container');
        sliderContainer.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        
        sliderContainer.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
    }
});

// Photo Management Functions
let selectedPhotoId = null;
let selectedPhotoType = null;

function togglePhotoControls() {
    const controls = document.getElementById('photoControls');
    controls.classList.toggle('d-none');
}

function selectPhoto(photoId) {
    document.querySelectorAll('.slide').forEach(slide => {
        slide.classList.remove('selected');
    });
    
    const photoSlide = document.querySelector(`[data-photo-id="${photoId}"]`);
    if (photoSlide) {
        photoSlide.classList.add('selected');
        selectedPhotoId = photoId;
        selectedPhotoType = 'catalog';
    }
}

// New Photo Management Functions
function deletePhoto(photoId, event) {
    event.stopPropagation(); // Prevent slide selection
    
    if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
        const deleteBtn = event.target.closest('.photo-delete-btn');
        const originalContent = deleteBtn.innerHTML;
        
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;
        
        fetch(`/kelola-catalog/photos/${photoId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Foto berhasil dihapus', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal menghapus foto: ' + data.message, 'error');
                deleteBtn.innerHTML = originalContent;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus foto', 'error');
            deleteBtn.innerHTML = originalContent;
            deleteBtn.disabled = false;
        });
    }
}

function addPhotoAfter(index, event) {
    event.stopPropagation(); // Prevent slide selection
    
    // Create and show add photo modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'addPhotoModal';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Tambah Foto Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPhotoForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="foto" class="form-label">Pilih Foto</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                        </div>
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul (Opsional)</label>
                            <input type="text" class="form-control" id="judul" name="judul" placeholder="Masukkan judul foto">
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Masukkan deskripsi foto"></textarea>
                        </div>
                        <input type="hidden" name="urutan" value="${index + 1}">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="addPhotoForm" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload Foto
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Handle form submission
    document.getElementById('addPhotoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';
        
        fetch('/kelola-catalog/photos', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrapModal.hide();
                showToast('Foto berhasil ditambahkan', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal menambah foto: ' + data.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat mengupload foto', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

// Function for adding new catalog photo (when no catalog photos exist)
function addNewCatalogPhoto(index, event) {
    event.stopPropagation(); // Prevent slide selection
    
    // Create and show add photo modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'addCatalogPhotoModal';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Tambah Foto Catalog
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan menambahkan foto catalog pertama. Foto ini akan menggantikan foto produk di slider.
                    </div>
                    <form id="addCatalogPhotoForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="catalogFoto" class="form-label">Pilih Foto</label>
                            <input type="file" class="form-control" id="catalogFoto" name="foto" accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 4MB.</div>
                        </div>
                        <div class="mb-3">
                            <label for="catalogJudul" class="form-label">Judul (Opsional)</label>
                            <input type="text" class="form-control" id="catalogJudul" name="judul" placeholder="Masukkan judul foto">
                        </div>
                        <div class="mb-3">
                            <label for="catalogDeskripsi" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="catalogDeskripsi" name="deskripsi" rows="3" placeholder="Masukkan deskripsi foto"></textarea>
                        </div>
                        <input type="hidden" name="urutan" value="1">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="addCatalogPhotoForm" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload Foto
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Handle form submission
    document.getElementById('addCatalogPhotoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';
        
        fetch('/kelola-catalog/photos', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrapModal.hide();
                showToast('Foto catalog berhasil ditambahkan', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal menambah foto: ' + data.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat mengupload foto', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function selectProductPhoto(productId) {
    document.querySelectorAll('.slide').forEach(slide => {
        slide.classList.remove('selected');
    });
    
    const photoSlide = document.querySelector(`[data-product-photo="${productId}"]`);
    if (photoSlide) {
        photoSlide.classList.add('selected');
        selectedPhotoId = productId;
        selectedPhotoType = 'product';
    }
}

function selectDefaultPhoto(photoId) {
    document.querySelectorAll('.slide').forEach(slide => {
        slide.classList.remove('selected');
    });
    
    const photoSlide = document.querySelector(`[data-default-photo="${photoId}"]`);
    if (photoSlide) {
        photoSlide.classList.add('selected');
        selectedPhotoId = photoId;
        selectedPhotoType = 'default';
    }
}

function deleteSelectedPhoto() {
    if (!selectedPhotoId) {
        showToast('Pilih foto terlebih dahulu', 'warning');
        return;
    }

    if (selectedPhotoType === 'catalog') {
        if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
            fetch(`/kelola-catalog/photos/${selectedPhotoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Gagal menghapus foto: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menghapus foto', 'error');
            });
        }
    } else if (selectedPhotoType === 'product') {
        showToast('Foto produk tidak dapat dihapus dari sini. Gunakan halaman master data produk.', 'info');
    } else if (selectedPhotoType === 'default') {
        showToast('Foto default tidak dapat dihapus', 'info');
    }
}

function addNewPhoto() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.multiple = true;
    input.onchange = function(e) {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            uploadMultiplePhotos(files);
        }
    };
    input.click();
}

function uploadMultiplePhotos(files) {
    const totalFiles = files.length;
    let uploadedCount = 0;
    let errorCount = 0;

    files.forEach((file, index) => {
        const formData = new FormData();
        formData.append('foto', file);
        formData.append('judul', `Foto ${index + 1}`);
        formData.append('deskripsi', `Foto catalog ke-${index + 1}`);

        fetch('/kelola-catalog/photos', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                uploadedCount++;
            } else {
                errorCount++;
                console.error('Upload failed:', data.message);
            }

            if (uploadedCount + errorCount === totalFiles) {
                if (uploadedCount > 0) {
                    showToast(`${uploadedCount} foto berhasil ditambahkan${errorCount > 0 ? `, ${errorCount} gagal` : ''}`, uploadedCount === totalFiles ? 'success' : 'warning');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Semua foto gagal diupload', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorCount++;
            
            if (uploadedCount + errorCount === totalFiles) {
                showToast('Terjadi kesalahan saat upload foto', 'error');
            }
        });
    });
}

// Edit functions
function editCompanyInfo() {
    const modal = new bootstrap.Modal(document.getElementById('editCompanyModal'));
    modal.show();
}

function editCompanyName() {
    const currentName = '{{ $company->nama ?? '' }}';
    const newName = prompt('Edit Nama Perusahaan:', currentName);
    
    if (newName && newName !== currentName) {
        fetch('/kelola-catalog/settings/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                nama: newName,
                email: '{{ $company->email ?? '' }}',
                telepon: '{{ $company->telepon ?? '' }}',
                alamat: '{{ $company->alamat ?? '' }}',
                catalog_description: '{{ $company->catalog_description ?? '' }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Nama perusahaan berhasil diperbarui', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal menyimpan: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan', 'error');
        });
    }
}

function editCompanyLogo() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('foto', file);
            formData.append('nama', '{{ $company->nama ?? '' }}');
            formData.append('email', '{{ $company->email ?? '' }}');
            formData.append('telepon', '{{ $company->telepon ?? '' }}');
            formData.append('alamat', '{{ $company->alamat ?? '' }}');
            formData.append('catalog_description', '{{ $company->catalog_description ?? '' }}');
            
            fetch('/kelola-catalog/settings/update', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Logo berhasil diperbarui', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Gagal mengupload logo: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan', 'error');
            });
        }
    };
    input.click();
}

function editMaps() {
    const modal = new bootstrap.Modal(document.getElementById('editMapsModal'));
    modal.show();
}

function editBuyButton() {
    alert('Fitur edit tombol beli akan segera tersedia');
}

function toggleProductEditMode() {
    const controls = document.getElementById('productEditControls');
    const productControls = document.querySelectorAll('.product-edit-controls');
    
    if (controls.classList.contains('d-none')) {
        controls.classList.remove('d-none');
        productControls.forEach(control => control.classList.remove('d-none'));
    } else {
        controls.classList.add('d-none');
        productControls.forEach(control => control.classList.add('d-none'));
    }
}

// Save company description
function saveCompanyDescription() {
    const content = document.getElementById('companyDescription').value;
    const saveBtn = document.querySelector('.description-edit-controls button');
    const originalText = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
    
    fetch('/kelola-catalog/settings/catalog', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            catalog_description: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Deskripsi perusahaan berhasil disimpan!', 'success');
            
            // Update the display in real-time (optional enhancement)
            setTimeout(() => {
                // Reload to show updated content in public catalog view
                window.location.reload();
            }, 1500);
        } else {
            showToast('Gagal menyimpan: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menyimpan deskripsi', 'error');
    })
    .finally(() => {
        // Restore button state
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Function to populate form and submit
function populateFormAndSubmit(event) {
    event.preventDefault();
    
    // Get values from input fields
    document.getElementById('formNama').value = document.getElementById('companyTitle').value.replace('Tentang ', '');
    document.getElementById('formDescription').value = document.getElementById('companyDescription').value;
    document.getElementById('formEmail').value = document.getElementById('companyEmail').value;
    document.getElementById('formPhone').value = document.getElementById('companyPhone').value;
    document.getElementById('formAddress').value = document.getElementById('companyAddress').value;
    
    // Submit the form
    document.getElementById('companyUpdateForm').submit();
}

// Update all company information - SIMPLIFIED VERSION
function updateAllCompanyInfo() {
    alert('UPDATE FUNCTION CALLED!');
    
    try {
        // Get form values
        var title = document.getElementById('companyTitle').value;
        var description = document.getElementById('companyDescription').value;
        var email = document.getElementById('companyEmail').value;
        var phone = document.getElementById('companyPhone').value;
        var address = document.getElementById('companyAddress').value;
        
        alert('Values: ' + title + ', ' + email + ', ' + phone + ', ' + address);
        
        // Create form data for traditional POST
        var formData = new FormData();
        formData.append('nama', title.replace('Tentang ', ''));
        formData.append('catalog_description', description);
        formData.append('email', email);
        formData.append('telepon', phone);
        formData.append('alamat', address);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        alert('About to send request...');
        
        // Use traditional form submission instead of fetch
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/kelola-catalog/settings/company-info', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                alert('Response received: ' + xhr.responseText);
                if (xhr.status == 200) {
                    alert('Success! Reloading...');
                    location.reload();
                } else {
                    alert('Error occurred!');
                }
            }
        };
        xhr.send(formData);
        
    } catch(error) {
        alert('Error in update function: ' + error.message);
    }
}

// Product management functions
function selectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

function showSelectedProducts() {
    const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        showToast('Pilih produk terlebih dahulu', 'warning');
        return;
    }
    
    selectedIds.forEach(id => {
        fetch(`/kelola-catalog/${id}/toggle-visibility`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ show_in_catalog: true })
        });
    });
    
    showToast(`${selectedIds.length} produk ditampilkan di catalog`, 'success');
    setTimeout(() => location.reload(), 1000);
}

function hideSelectedProducts() {
    const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        showToast('Pilih produk terlebih dahulu', 'warning');
        return;
    }
    
    selectedIds.forEach(id => {
        fetch(`/kelola-catalog/${id}/toggle-visibility`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ show_in_catalog: false })
        });
    });
    
    showToast(`${selectedIds.length} produk disembunyikan dari catalog`, 'success');
    setTimeout(() => location.reload(), 1000);
}

function toggleCatalogVisibility(productId, isVisible) {
    fetch(`/kelola-catalog/${productId}/toggle-visibility`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ show_in_catalog: isVisible })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(isVisible ? 'Produk ditampilkan' : 'Produk disembunyikan', 'success');
        } else {
            showToast('Gagal mengubah visibility', 'error');
        }
    });
}

function editProduct(productId) {
    window.location.href = `/master-data/produk/${productId}/edit`;
}

function deleteProduct(productId) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        window.location.href = `/master-data/produk/${productId}`;
    }
}

// Form submissions
document.getElementById('editCompanyForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
    
    fetch('/kelola-catalog/settings/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editCompanyModal')).hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Gagal menyimpan: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menyimpan', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

document.getElementById('editMapsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
    
    const data = {
        maps_link: formData.get('maps_link'),
        latitude: formData.get('latitude'),
        longitude: formData.get('longitude')
    };
    
    fetch('/kelola-catalog/settings/catalog', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editMapsModal')).hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Gagal menyimpan: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menyimpan peta', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

document.getElementById('addPhotoForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/kelola-catalog/photos', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addPhotoModal')).hide();
            location.reload();
        } else {
            alert('Gagal mengunggah: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});

// Image Modal Function
function showImageModal(imageSrc, altText) {
    const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${altText}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageSrc}" alt="${altText}" class="img-fluid" style="max-height: 500px;">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    document.getElementById('imageModal').addEventListener('hidden.bs.modal', function () {
        this.remove();
    });
}

// Product Photo Management Functions
function deleteProductPhoto(productId) {
    if (confirm('Apakah Anda yakin ingin menghapus foto produk ini?')) {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('remove_photo', '1');
        
        fetch(`/master-data/produk/${productId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Foto produk berhasil dihapus', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal menghapus foto: ' + (data.message || 'Terjadi kesalahan'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus foto', 'error');
        });
    }
}

function changeProductPhoto(productId) {
    // Create and show photo upload modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'changePhotoModal';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-camera me-2"></i>Kelola Foto Produk
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changePhotoForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="productPhoto" class="form-label">Pilih Foto Baru</label>
                            <input type="file" class="form-control" id="productPhoto" name="foto" accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                        </div>
                        <input type="hidden" name="_method" value="PUT">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="changePhotoForm" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload Foto
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Handle form submission
    document.getElementById('changePhotoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';
        
        fetch(`/master-data/produk/${productId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrapModal.hide();
                showToast('Foto produk berhasil diperbarui', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal mengupload foto: ' + (data.message || 'Terjadi kesalahan'), 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat mengupload foto', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

// Initialize barcode functionality
document.addEventListener('DOMContentLoaded', function() {
    const barcodeElements = document.querySelectorAll('.barcode-svg');
    barcodeElements.forEach(function(element) {
        const barcode = element.getAttribute('data-barcode');
        if (barcode) {
            element.innerHTML = generateBarcode(barcode);
        }
    });
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Simple barcode generator
function generateBarcode(code) {
    const width = 80;
    const height = 20;
    const barWidth = 2;
    const barHeight = height;
    
    let svg = `<svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">`;
    
    for (let i = 0; i < code.length; i++) {
        const digit = parseInt(code[i]);
        const barCount = digit % 4 + 1;
        
        for (let j = 0; j < barCount; j++) {
            const x = (i * 8) + (j * 2);
            if (x < width) {
                svg += `<rect x="${x}" y="0" width="${barWidth}" height="${barHeight}" fill="#000"/>`;
            }
        }
    }
    
    svg += '</svg>';
    return svg;
}

// Kelola Foto Modal Functions
function openKelolaFotoModal() {
    const modal = new bootstrap.Modal(document.getElementById('kelolaFotoModal'));
    modal.show();
}

function deleteFotoFromModal(photoId) {
    if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
        const photoCard = document.querySelector(`[data-photo-id="${photoId}"]`);
        const deleteBtn = photoCard.querySelector('.btn-danger');
        const originalContent = deleteBtn.innerHTML;
        
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;
        
        fetch(`/kelola-catalog/photos/${photoId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove photo card from modal
                photoCard.remove();
                
                // Check if no photos left
                const remainingPhotos = document.querySelectorAll('#fotoGallery [data-photo-id]');
                if (remainingPhotos.length === 0) {
                    document.getElementById('fotoGallery').innerHTML = `
                        <div class="col-12" id="noPhotosMessage">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-images fa-3x mb-3"></i>
                                <p>Belum ada foto catalog. Upload foto pertama Anda!</p>
                            </div>
                        </div>
                    `;
                }
                
                showToast('Foto berhasil dihapus', 'success');
                
                // Reload page after 1 second to update slider
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Gagal menghapus foto: ' + data.message, 'error');
                deleteBtn.innerHTML = originalContent;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus foto', 'error');
            deleteBtn.innerHTML = originalContent;
            deleteBtn.disabled = false;
        });
    }
}

// Handle upload form in modal
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadFotoForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';
            
            fetch('/kelola-catalog/photos', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Foto berhasil diupload', 'success');
                    
                    // Reset form
                    this.reset();
                    
                    // Close modal and reload page
                    bootstrap.Modal.getInstance(document.getElementById('kelolaFotoModal')).hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Gagal mengupload foto: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat mengupload foto', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

// Toast notification
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
    
    setTimeout(() => {
        toastContainer.remove();
    }, 5000);
}
</script>
@endsection
